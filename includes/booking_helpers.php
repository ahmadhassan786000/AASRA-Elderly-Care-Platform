<?php
/* Booking history (audit trail). Table: booking_history, created by database/migrations/003_add_booking_history.sql.
   One row is written per real status change, in the same transaction as the bookings.status UPDATE.
   Bookings created before the table existed have no rows and are shown as "No history available"; nothing is back-filled. */

const BOOKING_STATUS_FLOW = [
    // action => [new status, allowed current statuses, history event]
    'accept'   => ['Accepted',  ['Pending'],                'accepted'],
    'reject'   => ['Rejected',  ['Pending'],                'rejected'],
    'complete' => ['Completed', ['Accepted', 'Confirmed'],  'completed'],
    'cancel'   => ['Cancelled', ['Accepted', 'Confirmed'],  'cancelled'],
];

// Appends one history row. Never lets a missing/old table break the booking action itself.
function booking_history_add(int $bookingId, string $event, ?string $from, string $to, string $role, ?int $actorUserId, ?string $note = null): void
{
    try {
        q('INSERT INTO booking_history(booking_id,event,from_status,to_status,actor_role,actor_user_id,note) VALUES(?,?,?,?,?,?,?)', [$bookingId, $event, $from, $to, $role, $actorUserId, $note]);
    } catch (Throwable $e) {
        error_log('booking history skipped (run database/migrations/003_add_booking_history.sql): ' . $e->getMessage());
    }
}

// Changes a booking's status only if it is still in one of $allowedFrom (so a double click or a stale page cannot apply
// the same change twice), and records the history row. Returns true when the change was applied.
function booking_change_status(int $bookingId, string $to, array $allowedFrom, string $event, string $role, ?int $actorUserId, ?string $note = null): bool
{
    db_begin();
    try {
        $cur = row('SELECT status FROM bookings WHERE id=? FOR UPDATE', [$bookingId]);
        if (!$cur || !in_array($cur['status'], $allowedFrom, true)) { db_rollback(); return false; }
        if ($note !== null) q('UPDATE bookings SET status=?,cancellation_reason=? WHERE id=?', [$to, $note, $bookingId]);
        else q('UPDATE bookings SET status=? WHERE id=?', [$to, $bookingId]);
        booking_history_add($bookingId, $event, $cur['status'], $to, $role, $actorUserId);
        db_commit();
        return true;
    } catch (Throwable $e) {
        db_rollback();
        throw $e;
    }
}

// [bookingId => [rows oldest first]] for the admin table. Returns [] if the table does not exist yet.
function booking_histories(array $bookingIds): array
{
    $ids = array_values(array_filter(array_map('intval', $bookingIds)));
    if (!$ids) return [];
    $in = implode(',', $ids); // integers only
    $out = [];
    try {
        foreach (rows("SELECT h.*,u.name actor_name FROM booking_history h LEFT JOIN users u ON u.id=h.actor_user_id WHERE h.booking_id IN ($in) ORDER BY h.created_at ASC, h.id ASC") as $r) $out[(int)$r['booking_id']][] = $r;
    } catch (Throwable $e) {
        error_log('booking_histories: ' . $e->getMessage());
    }
    return $out;
}

function booking_event_label(array $h): string
{
    $who = $h['actor_role'] === 'provider' ? 'Provider' : ($h['actor_role'] === 'user' ? 'User' : 'System');
    switch ($h['event']) {
        case 'created':   return 'Booking created';
        case 'accepted':  return 'Provider accepted the booking';
        case 'rejected':  return 'Provider rejected the booking';
        case 'cancelled': return $who . ' cancelled the booking';
        case 'completed': return 'Booking completed';
    }
    return 'Booking status changed';
}

// Admin History column: expandable, read-only list.
function booking_history_html(array $events): string
{
    $h = '<details><summary class="small text-success" style="cursor:pointer">View History' . ($events ? ' (' . count($events) . ')' : '') . '</summary>';
    if (!$events) return $h . '<div class="small text-secondary mt-1">No history available</div></details>';
    $h .= '<ul class="small list-unstyled mb-0 mt-2" style="min-width:14rem">';
    foreach ($events as $ev) {
        $h .= '<li class="mb-2"><b>' . e(booking_event_label($ev)) . '</b>';
        if ($ev['from_status'] || $ev['to_status']) $h .= '<br><span class="text-secondary">' . ($ev['from_status'] ? e($ev['from_status']) . ' â†’ ' : '') . e($ev['to_status']) . '</span>';
        $h .= '<br><span class="text-secondary">' . e(fmt_date($ev['created_at'], 'd M Y, g:i A')) . '</span></li>';
    }
    return $h . '</ul></details>';
}

/* ---------- duplicate booking prevention ----------
   Rule: one user cannot hold two active bookings for the same provider + same specific service (provider_offerings.id)
   + same appointment date and time. Different providers, different services or different times are never blocked, and
   Cancelled / Rejected bookings do not block rebooking. bookings.offering_id comes from
   database/migrations/004_add_booking_offering_id.sql; until it is applied the check falls back to the category. */
function booking_has_offering_column(): bool
{
    static $has = null;
    if ($has === null) {
        try {
            $has = (int)val("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='bookings' AND COLUMN_NAME='offering_id'") > 0;
        } catch (Throwable $e) {
            $has = false;
        }
    }
    return $has;
}

// Call inside a transaction that already holds the per-user lock (see pages/user/book.php). Locking read, so it sees the latest committed rows.
function booking_duplicate_exists(int $userId, int $providerId, int $offeringId, int $categoryId, string $date, string $time): bool
{
    $appointment = strtotime($date . ' ' . $time);
    if ($appointment === false) {
        return true;
    }

    $start = date('Y-m-d H:i:s', $appointment - 3600);
    $end   = date('Y-m-d H:i:s', $appointment + 3600);

    if (booking_has_offering_column()) {
        return (bool)row(
            "SELECT id FROM bookings
             WHERE user_id=? AND provider_id=? AND offering_id=?
             AND TIMESTAMP(booking_date, booking_time) BETWEEN ? AND ?
             AND status NOT IN ('Cancelled','Rejected')
             LIMIT 1 FOR UPDATE",
            [$userId, $providerId, $offeringId, $start, $end]
        );
    }

    return (bool)row(
        "SELECT id FROM bookings
         WHERE user_id=? AND provider_id=? AND category_id=?
         AND TIMESTAMP(booking_date, booking_time) BETWEEN ? AND ?
         AND status NOT IN ('Cancelled','Rejected')
         LIMIT 1 FOR UPDATE",
        [$userId, $providerId, $categoryId, $start, $end]
    );
}
