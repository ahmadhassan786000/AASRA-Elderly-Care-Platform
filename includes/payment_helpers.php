<?php
/* Cash payments for completed bookings.
   - Cash only: there is no gateway, card, JazzCash or Easypaisa, and nothing is ever marked paid automatically.
   - AASRA takes 0% commission: the provider receives the full booking amount.
   - Workflow:  Pending -> Paid  (the provider's cash-received confirmation is the final step; the Admin only VIEWS the record)
   - Legacy rows: 'Verification Requested' and 'Rejected' come from the earlier Admin verify/reject workflow. They are still
     displayed, and a legacy 'Rejected' payment can still be confirmed by the provider.
   - Service completion (bookings.status) and payment status (payments.payment_status) are separate states.
   Every state change below runs in a transaction, re-checks ownership and status on the server, and uses a
   conditional UPDATE so a double click cannot apply the same transition twice. */

const PAYMENT_COMMISSION_PERCENT = 0;

function payment_dt($v): string
{
    return $v ? fmt_date($v, 'd M Y, g:i A') : 'â€”';
}

function payment_badge(?string $status): string
{
    $map = ['Pending' => 'warning', 'Verification Requested' => 'info', 'Paid' => 'success', 'Rejected' => 'danger', 'Failed' => 'danger'];
    $status = (string)$status;
    return '<span class="badge text-bg-' . ($map[$status] ?? 'secondary') . '">' . e($status) . '</span>';
}

// Creates the Pending record for a booking if it has none. The amount is read from the booking row, never from a request.
function payment_ensure_record(int $bookingId): void
{
    $st = q("INSERT IGNORE INTO payments(booking_id,amount,payment_method,payment_status) SELECT id,charges,'Cash','Pending' FROM bookings WHERE id=?", [$bookingId]);
    if ($st->affected_rows === 1) {
        try {
            payment_history_add((int)$st->insert_id, 'created', null, 'Pending', 'system', null, null);
        } catch (Throwable $e) {
            error_log('payment history (created) skipped: ' . $e->getMessage()); // payment_history table missing: run the migration
        }
    }
}

// Audit trail. Requires the payment_history table from database/migrations/add_cash_payment_workflow.sql.
function payment_history_add(int $paymentId, string $event, ?string $from, string $to, string $role, ?int $actorUserId, ?string $note): void
{
    q('INSERT INTO payment_history(payment_id,event,from_status,to_status,actor_role,actor_user_id,note) VALUES(?,?,?,?,?,?,?)', [$paymentId, $event, $from, $to, $role, $actorUserId, $note]);
}

// History rows for many payments at once: [paymentId => [rows oldest first]]. Admin names are only for the admin panel.
function payment_histories(array $paymentIds): array
{
    $paymentIds = array_values(array_filter(array_map('intval', $paymentIds)));
    if (!$paymentIds) return [];
    $in = implode(',', $paymentIds); // integers only
    $out = [];
    foreach (rows("SELECT h.*,u.name actor_name FROM payment_history h LEFT JOIN users u ON u.id=h.actor_user_id WHERE h.payment_id IN ($in) ORDER BY h.created_at ASC, h.id ASC") as $r) $out[(int)$r['payment_id']][] = $r;
    return $out;
}

function payment_event_label(array $h, bool $showAdminName): string
{
    $who = $showAdminName && $h['actor_name'] ? ' (' . $h['actor_name'] . ')' : '';
    switch ($h['event']) {
        case 'created':              return 'Payment record created';
        case 'provider_confirmed':   return 'Provider confirmed cash received';
        case 'provider_resubmitted': return 'Provider resubmitted the confirmation';
        case 'admin_verified':       return 'Admin verified the payment' . $who;
        case 'admin_rejected':       return 'Admin rejected the confirmation' . $who;
    }
    return (string)$h['event'];
}

// Renders a compact, collapsible history list.
function payment_history_html(array $events, bool $showAdminName): string
{
    if (!$events) return '';
    $h = '<details class="mt-1"><summary class="small text-secondary">History (' . count($events) . ')</summary><ul class="small list-unstyled mb-0 mt-1">';
    foreach ($events as $ev) {
        $h .= '<li class="mb-1"><b>' . e(payment_event_label($ev, $showAdminName)) . '</b><br><span class="text-secondary">' . e(payment_dt($ev['created_at'])) . '</span>';
        if (!empty($ev['note'])) $h .= '<br>Reason: ' . e($ev['note']);
        $h .= '</li>';
    }
    return $h . '</ul></details>';
}

// Status shown in the admin Status column. A cancelled or rejected BOOKING is shown as such (the payment can no longer
// be collected); otherwise the payment's own status is shown. Nothing is written: payments.payment_status is never changed.
function payment_display_status(array $pay): string
{
    $b = (string)($pay['bstatus'] ?? '');
    return in_array($b, ['Cancelled', 'Rejected'], true) ? $b : (string)$pay['payment_status'];
}

// Admin History column: read-only, always visible (no dropdown). Shows ONLY the provider's cash-received confirmation and
// its time of day. The time comes from payments.provider_confirmed_at, or, if that is empty, from a real provider_confirmed /
// provider_resubmitted row in payment_history. 'Payment record created' and every other event are never shown here.
// Without a confirmation the cell shows a dash; nothing is invented.
function payment_admin_history_html(array $pay, array $events): string
{
    $at = !empty($pay['provider_confirmed_at']) ? (string)$pay['provider_confirmed_at'] : '';
    if ($at === '') foreach ($events as $ev) if (in_array($ev['event'], ['provider_confirmed', 'provider_resubmitted'], true)) $at = (string)$ev['created_at'];
    if ($at === '') return '<span class="text-secondary">—</span>';
    return '<div class="small"><b class="d-block">Provider confirmed cash received</b><span class="text-secondary text-nowrap d-block mt-1">' . e(fmt_date($at, 'd M Y, g:i A')) . '</span></div>';}

// One plain-language sentence explaining where a payment stands. $role: 'user' | 'provider' | 'admin'.
function payment_status_text(array $r, string $role): string
{
    $b = (string)($r['bstatus'] ?? '');
    switch ($r['payment_status']) {
        case 'Paid':
            if (!empty($r['verified_at'])) // legacy: verified by an Admin under the earlier workflow
                return 'Verified by AASRA on ' . payment_dt($r['verified_at']) . '. The full amount belongs to the provider; no commission is charged.';
            return $role === 'provider'
                ? 'You confirmed the cash on ' . payment_dt($r['provider_confirmed_at'] ?? null) . '. The full amount is yours; no commission is charged.'
                : 'The provider confirmed receiving the cash on ' . payment_dt($r['provider_confirmed_at'] ?? null) . '. The full amount belongs to the provider; no commission is charged.';
        case 'Verification Requested': // legacy status from the earlier workflow
            return $role === 'provider'
                ? 'You confirmed the cash on ' . payment_dt($r['provider_confirmed_at']) . '.'
                : 'The provider confirmed receiving the cash payment on ' . payment_dt($r['provider_confirmed_at']) . '.';
        case 'Rejected': // legacy status from the earlier workflow
            $why = trim((string)$r['admin_note']);
            $base = 'The earlier confirmation was not accepted' . ($why !== '' ? ': ' . $why : '.');
            return $role === 'provider' ? $base . ' You can fix the problem and confirm again.' : $base . ' The provider can confirm again.';
        case 'Failed':
            return 'This legacy payment record was marked failed.';
    }
    // Pending
    if ($b === 'Completed') {
        return $role === 'provider' ? 'Service completed. Once the client has paid you in cash, confirm it below.'
             : ($role === 'user' ? 'Service completed. Please pay the provider the amount in cash. The provider will then confirm receipt.'
             : 'Service completed. Waiting for the provider to confirm the cash payment.');
    }
    if (in_array($b, ['Cancelled', 'Rejected'], true)) return 'Not payable: this booking was ' . strtolower($b) . '.';
    return 'Waiting for the service to be completed.';
}

// Provider confirms the cash was received. The confirmation is recorded in payments + payment_history and the payment
// becomes Paid; no Admin action is needed. Returns [flashType, message].
function payment_provider_confirm(int $providerId, int $paymentId, int $actorUserId): array
{
    db_begin();
    try {
        $r = row('SELECT pay.id,pay.payment_status,b.status bstatus,b.charges,b.user_id,b.booking_date FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.id=? AND b.provider_id=? FOR UPDATE', [$paymentId, $providerId]);
        if (!$r) { db_rollback(); return ['danger', 'That payment was not found.']; }
        if ($r['bstatus'] !== 'Completed') { db_rollback(); return ['danger', 'Cash can only be confirmed after the service is marked completed.']; }
        if (!in_array($r['payment_status'], ['Pending', 'Rejected'], true)) { db_rollback(); return ['danger', 'The cash for this payment has already been confirmed.']; }
        $from = $r['payment_status'];
        $st = q("UPDATE payments SET payment_status='Paid',payment_method='Cash',amount=?,provider_confirmed_at=NOW(),paid_at=NOW(),admin_note=NULL WHERE id=? AND payment_status IN ('Pending','Rejected')", [$r['charges'], $paymentId]);
        if ($st->affected_rows !== 1) { db_rollback(); return ['danger', 'This payment was just updated. Please refresh the page.']; }
        payment_history_add($paymentId, $from === 'Rejected' ? 'provider_resubmitted' : 'provider_confirmed', $from, 'Paid', 'provider', $actorUserId, null);
        notify((int)$r['user_id'], null, 'Cash payment confirmed', 'The provider confirmed receiving your cash payment for the booking on ' . fmt_date($r['booking_date']) . '. The payment is now marked Paid.', 'payment');
        db_commit();
        return ['success', 'Cash receipt confirmed. The payment is now marked Paid.'];
    } catch (Throwable $e) {
        db_rollback();
        error_log('payment_provider_confirm: ' . $e->getMessage());
        return ['danger', 'Something went wrong. Please try again.'];
    }
}
