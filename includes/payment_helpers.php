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
    return $v ? fmt_date($v, 'd M Y, g:i A') : '—';
}

function payment_badge(?string $status): string
{
    $map = ['Pending' => 'warning', 'Verification Requested' => 'info', 'Paid' => 'success', 'Rejected' => 'danger', 'Failed' => 'danger', 'Cancelled' => 'secondary'];
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
        case 'booking_cancelled_by_user':     return 'User cancelled the service.';
        case 'booking_rejected_by_provider':  return 'Provider rejected the service.';
        case 'booking_cancelled_by_provider': return 'Provider cancelled the service.';
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

// Status shown in the Status column of the Admin, User and Provider payment pages. A cancelled or rejected BOOKING is shown
// as Cancelled / Rejected instead of a misleading "Pending". A real financial state (Paid, Failed) is never overridden.
// Nothing is written: payments.payment_status is never changed by a cancellation or rejection.
function payment_display_status(array $pay): string
{
    $b = (string)($pay['bstatus'] ?? '');
    if (in_array($pay['payment_status'], ['Paid', 'Failed'], true)) return (string)$pay['payment_status'];
    return in_array($b, ['Cancelled', 'Rejected'], true) ? $b : (string)$pay['payment_status'];
}
// The same rule in SQL (filters and summaries). Aliases: pay = payments, b = bookings.
const PAYMENT_EFFECTIVE_STATUS_SQL = "(CASE WHEN pay.payment_status IN ('Paid','Failed') THEN pay.payment_status WHEN b.status IN ('Cancelled','Rejected') THEN b.status ELSE pay.payment_status END)";

// Records the booking outcome in the payment's history. Call ONLY right after the booking row was really changed by a
// conditional UPDATE (so a refresh or repeated request cannot reach it twice). No payment row is created here, and the
// financial status is copied unchanged (from_status = to_status). A missing table/enum value never breaks the booking action.
function payment_record_booking_outcome(int $bookingId, string $event, string $role, ?int $actorUserId): void
{
    try {
        $pay = row('SELECT id,payment_status FROM payments WHERE booking_id=?', [$bookingId]);
        if (!$pay) return;
        if (row('SELECT id FROM payment_history WHERE payment_id=? AND event=? LIMIT 1', [(int)$pay['id'], $event])) return;
        payment_history_add((int)$pay['id'], $event, $pay['payment_status'], $pay['payment_status'], $role, $actorUserId, null);
    } catch (Throwable $e) {
        error_log('payment outcome history skipped (run database/migrations/006_age_handicap_documents_payment_outcome.sql): ' . $e->getMessage());
    }
}

// [text, time|null] describing how the BOOKING ended, or null while it is active/completed. Uses the real payment_history
// event when there is one; for older bookings that predate it, the real booking status (and cancellation_reason) is used
// and no time is shown, because none was recorded.
function payment_booking_outcome(array $pay, array $events): ?array
{
    foreach ($events as $ev)
        if (in_array($ev['event'], ['booking_cancelled_by_user', 'booking_rejected_by_provider', 'booking_cancelled_by_provider'], true))
            return [payment_event_label($ev, false), (string)$ev['created_at']];
    $b = (string)($pay['bstatus'] ?? '');
    if ($b === 'Rejected') return ['Provider rejected the service.', null];
    if ($b === 'Cancelled') {
        $why = (string)($pay['cancellation_reason'] ?? '');
        return [$why === 'Cancelled by user' ? 'User cancelled the service.' : ($why === 'Cancelled by provider' ? 'Provider cancelled the service.' : 'The booking was cancelled.'), null];
    }
    return null;
}

// Admin History column: read-only, always visible (no dropdown). Shows (1) the provider's cash-received confirmation with its
// time of day (from payments.provider_confirmed_at, or a real provider_confirmed/provider_resubmitted history row) and
// (2) how the booking ended when it was cancelled or rejected, with the booking ID and exact service name.
// 'Payment record created' and other events are not shown. Without either, the cell shows a dash; nothing is invented.
function payment_admin_history_html(array $pay, array $events): string
{
    $out = '';
    $at = !empty($pay['provider_confirmed_at']) ? (string)$pay['provider_confirmed_at'] : '';
    if ($at === '') foreach ($events as $ev) if (in_array($ev['event'], ['provider_confirmed', 'provider_resubmitted'], true)) $at = (string)$ev['created_at'];
    if ($at !== '') $out .= '<div class="small mb-2"><b class="d-block">Provider confirmed cash received</b><span class="text-secondary text-nowrap d-block mt-1">' . e(fmt_date($at, 'd M Y, g:i A')) . '</span></div>';
    $o = payment_booking_outcome($pay, $events);
    if ($o) $out .= '<div class="small"><b class="d-block">' . e($o[0]) . '</b><span class="text-secondary d-block mt-1">Booking ' . (int)$pay['booking_id'] . ' · ' . e((string)($pay['service_name'] ?? '')) . ($o[1] ? ' · ' . e(fmt_date($o[1], 'd M Y, g:i A')) : '') . '</span></div>';
    return $out !== '' ? $out : '<span class="text-secondary">—</span>';
}

// One plain-language sentence explaining where a payment stands. $role: 'user' | 'provider' | 'admin'.
function payment_status_text(array $r, string $role): string
{
    $b = (string)($r['bstatus'] ?? '');
    switch ($r['payment_status']) {
        case 'Paid':
            if (!empty($r['verified_at'])) // legacy: verified by an Admin under the earlier workflow
                return 'Verified by AASRA on ' . payment_dt($r['verified_at']) ;
            return $role === 'provider'
                ? 'You confirmed the cash on ' . payment_dt($r['provider_confirmed_at'] ?? null) 
                : 'The provider confirmed receiving the cash on ' . payment_dt($r['provider_confirmed_at'] ?? null) ;
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
    if ($b === 'Rejected') return $role === 'provider' ? 'You rejected this booking, so no payment is due.' : 'The provider rejected this booking, so no payment is due.';
    if ($b === 'Cancelled') {
        $why = (string)($r['cancellation_reason'] ?? '');
        if ($why === 'Cancelled by user') return $role === 'user' ? 'You cancelled this booking, so no payment is due.' : 'The user cancelled this booking, so no payment is due.';
        if ($why === 'Cancelled by provider') return $role === 'provider' ? 'You cancelled this booking, so no payment is due.' : 'The provider cancelled this booking, so no payment is due.';
        return 'This booking was cancelled, so no payment is due.';
    }
    return 'Waiting for the service to be completed.';
}

// Provider confirms the cash was received. The confirmation is recorded in payments + payment_history and the payment
// becomes Paid; no Admin action is needed. Returns [flashType, message].
function payment_provider_confirm(int $providerId, int $paymentId, int $actorUserId): array
{
    db_begin();
    try {
        $r = row('SELECT pay.id,pay.booking_id,pay.payment_status,b.status bstatus,b.charges,b.user_id,b.booking_date FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.id=? AND b.provider_id=? FOR UPDATE', [$paymentId, $providerId]);
        if (!$r) { db_rollback(); return ['danger', 'That payment was not found.']; }
        if ($r['bstatus'] !== 'Completed') { db_rollback(); return ['danger', 'Cash can only be confirmed after the service is marked completed.']; }
        if (!in_array($r['payment_status'], ['Pending', 'Rejected'], true)) { db_rollback(); return ['danger', 'The cash for this payment has already been confirmed.']; }
        $from = $r['payment_status'];
        $st = q("UPDATE payments SET payment_status='Paid',payment_method='Cash',amount=?,provider_confirmed_at=NOW(),paid_at=NOW(),admin_note=NULL WHERE id=? AND payment_status IN ('Pending','Rejected')", [$r['charges'], $paymentId]);
        if ($st->affected_rows !== 1) { db_rollback(); return ['danger', 'This payment was just updated. Please refresh the page.']; }
        payment_history_add($paymentId, $from === 'Rejected' ? 'provider_resubmitted' : 'provider_confirmed', $from, 'Paid', 'provider', $actorUserId, null);
        notify_payment_confirmed((int)$r['booking_id']);
        db_commit();
        return ['success', 'Cash receipt confirmed. The payment is now marked Paid.'];
    } catch (Throwable $e) {
        db_rollback();
        error_log('payment_provider_confirm: ' . $e->getMessage());
        return ['danger', 'Something went wrong. Please try again.'];
    }
}
