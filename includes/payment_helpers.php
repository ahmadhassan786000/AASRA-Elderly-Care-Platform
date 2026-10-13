<?php
/* Cash payments for completed bookings.
   - Cash only: there is no gateway, card, JazzCash or Easypaisa, and nothing is ever marked paid automatically.
   - AASRA takes 0% commission: the provider receives the full booking amount.
   - Workflow:  Pending -> Verification Requested -> Paid   |   Verification Requested -> Rejected -> Verification Requested
   - Service completion (bookings.status) and payment verification (payments.payment_status) are separate states.
   Every state change below runs in a transaction, re-checks ownership and status on the server, and uses a
   conditional UPDATE so a double click or a second admin cannot apply the same transition twice. */

const PAYMENT_COMMISSION_PERCENT = 0;

function payment_dt($v): string
{
    return $v ? fmt_date($v, 'd M Y, g:i A') : '—';
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

// One plain-language sentence explaining where a payment stands. $role: 'user' | 'provider' | 'admin'.
function payment_status_text(array $r, string $role): string
{
    $b = (string)($r['bstatus'] ?? '');
    switch ($r['payment_status']) {
        case 'Paid':
            return 'Verified by AASRA on ' . payment_dt($r['verified_at'] ?? null) . '. The full amount belongs to the provider; no commission is charged.';
        case 'Verification Requested':
            return $role === 'provider'
                ? 'You confirmed the cash on ' . payment_dt($r['provider_confirmed_at']) . '. Waiting for Admin verification.'
                : 'The provider confirmed receiving your cash payment on ' . payment_dt($r['provider_confirmed_at']) . '. Waiting for Admin verification.';
        case 'Rejected':
            $why = trim((string)$r['admin_note']);
            $base = 'The Admin did not verify the confirmation' . ($why !== '' ? ': ' . $why : '.');
            return $role === 'provider' ? $base . ' You can fix the problem and resubmit.' : $base . ' The provider can resubmit.';
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

// Provider confirms the cash was received. Returns [flashType, message].
function payment_provider_confirm(int $providerId, int $paymentId, int $actorUserId): array
{
    db_begin();
    try {
        $r = row('SELECT pay.id,pay.payment_status,b.status bstatus,b.charges,b.user_id,b.booking_date FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.id=? AND b.provider_id=? FOR UPDATE', [$paymentId, $providerId]);
        if (!$r) { db_rollback(); return ['danger', 'That payment was not found.']; }
        if ($r['bstatus'] !== 'Completed') { db_rollback(); return ['danger', 'Cash can only be confirmed after the service is marked completed.']; }
        if (!in_array($r['payment_status'], ['Pending', 'Rejected'], true)) { db_rollback(); return ['danger', 'This payment is already waiting for verification or has been paid.']; }
        $from = $r['payment_status'];
        $st = q("UPDATE payments SET payment_status='Verification Requested',payment_method='Cash',amount=?,provider_confirmed_at=NOW(),admin_note=NULL WHERE id=? AND payment_status IN ('Pending','Rejected')", [$r['charges'], $paymentId]);
        if ($st->affected_rows !== 1) { db_rollback(); return ['danger', 'This payment was just updated. Please refresh the page.']; }
        payment_history_add($paymentId, $from === 'Rejected' ? 'provider_resubmitted' : 'provider_confirmed', $from, 'Verification Requested', 'provider', $actorUserId, null);
        notify((int)$r['user_id'], null, 'Cash payment confirmed', 'The provider confirmed your cash payment for the booking on ' . fmt_date($r['booking_date']) . '. It is now waiting for Admin verification.', 'payment');
        db_commit();
        return ['success', 'Cash receipt confirmed. It is now waiting for Admin verification.'];
    } catch (Throwable $e) {
        db_rollback();
        error_log('payment_provider_confirm: ' . $e->getMessage());
        return ['danger', 'Something went wrong. Please try again.'];
    }
}

// Admin verifies a requested payment -> Paid.
function payment_admin_verify(int $adminId, int $paymentId): array
{
    db_begin();
    try {
        $r = row('SELECT pay.id,pay.payment_status,b.status bstatus,b.charges,b.user_id,b.provider_id,b.booking_date FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.id=? FOR UPDATE', [$paymentId]);
        if (!$r) { db_rollback(); return ['danger', 'Payment not found.']; }
        if ($r['payment_status'] === 'Paid') { db_rollback(); return ['warning', 'This payment was already verified.']; }
        if ($r['payment_status'] !== 'Verification Requested') { db_rollback(); return ['danger', 'Only payments with a provider confirmation can be verified.']; }
        if ($r['bstatus'] !== 'Completed') { db_rollback(); return ['danger', 'The service for this booking is not completed, so it cannot be verified.']; }
        $st = q("UPDATE payments SET payment_status='Paid',amount=?,verified_by=?,verified_at=NOW(),paid_at=NOW(),admin_note=NULL WHERE id=? AND payment_status='Verification Requested'", [$r['charges'], $adminId, $paymentId]);
        if ($st->affected_rows !== 1) { db_rollback(); return ['warning', 'This payment was just updated by someone else. Please refresh.']; }
        payment_history_add($paymentId, 'admin_verified', 'Verification Requested', 'Paid', 'admin', $adminId, null);
        notify((int)$r['user_id'], null, 'Payment verified', 'Your cash payment for the booking on ' . fmt_date($r['booking_date']) . ' was verified.', 'payment');
        notify(null, (int)$r['provider_id'], 'Payment verified', 'The cash payment for the booking on ' . fmt_date($r['booking_date']) . ' was verified. The full amount is yours; AASRA charges no commission.', 'payment');
        db_commit();
        return ['success', 'Payment verified and marked Paid.'];
    } catch (Throwable $e) {
        db_rollback();
        error_log('payment_admin_verify: ' . $e->getMessage());
        return ['danger', 'Something went wrong. Please try again.'];
    }
}

// Admin rejects a requested payment with a mandatory reason -> Rejected (the provider may resubmit).
function payment_admin_reject(int $adminId, int $paymentId, string $reason): array
{
    $reason = trim($reason);
    if ($reason === '') return ['danger', 'Please enter a reason for the rejection.'];
    if (mb_strlen($reason) > 500) return ['danger', 'The reason is too long (max 500 characters).'];
    db_begin();
    try {
        $r = row('SELECT pay.id,pay.payment_status,b.status bstatus,b.provider_id,b.user_id,b.booking_date FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.id=? FOR UPDATE', [$paymentId]);
        if (!$r) { db_rollback(); return ['danger', 'Payment not found.']; }
        if ($r['payment_status'] !== 'Verification Requested') { db_rollback(); return ['danger', 'Only payments waiting for verification can be rejected.']; }
        $st = q("UPDATE payments SET payment_status='Rejected',admin_note=? WHERE id=? AND payment_status='Verification Requested'", [$reason, $paymentId]);
        if ($st->affected_rows !== 1) { db_rollback(); return ['warning', 'This payment was just updated by someone else. Please refresh.']; }
        payment_history_add($paymentId, 'admin_rejected', 'Verification Requested', 'Rejected', 'admin', $adminId, $reason);
        notify(null, (int)$r['provider_id'], 'Payment confirmation rejected', 'The cash confirmation for the booking on ' . fmt_date($r['booking_date']) . ' was rejected: ' . $reason, 'payment');
        notify((int)$r['user_id'], null, 'Payment confirmation rejected', 'The provider\'s cash confirmation for the booking on ' . fmt_date($r['booking_date']) . ' was not verified: ' . $reason, 'payment');
        db_commit();
        return ['success', 'Confirmation rejected. The provider can resubmit.'];
    } catch (Throwable $e) {
        db_rollback();
        error_log('payment_admin_reject: ' . $e->getMessage());
        return ['danger', 'Something went wrong. Please try again.'];
    }
}
