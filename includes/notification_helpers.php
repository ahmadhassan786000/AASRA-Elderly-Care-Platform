<?php
/* Contextual notifications for Users and Providers only (there is deliberately no Admin recipient anywhere in this file).
   Messages are built from the real booking / payment / complaint records. Event keys make every event single-shot per
   recipient (notifications.event_key is unique), so refreshes and double submits cannot create duplicates. */

function notif_booking(int $bookingId): ?array
{
    $r = row('SELECT b.id,b.user_id,b.provider_id,b.booking_date,b.booking_time,b.charges,b.status,u.name user_name,p.name provider_name,' . BOOKING_SERVICE_NAME_SQL . ' AS service_name
        FROM bookings b JOIN users u ON u.id=b.user_id JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id' . BOOKING_SERVICE_JOIN . 'WHERE b.id=?', [$bookingId]);
    return $r ?: null;
}
function notif_when(array $b, bool $withTime = true): string
{
    return fmt_date($b['booking_date']) . ($withTime && !empty($b['booking_time']) ? ' at ' . date('g:i A', strtotime((string)$b['booking_time'])) : '');
}

// New booking request -> the PROVIDER.
function notify_booking_requested(int $bookingId): void
{
    $b = notif_booking($bookingId);
    if (!$b) return;
    notify(null, (int)$b['provider_id'], 'New booking request',
        'You received a new booking request #' . $b['id'] . ' from ' . $b['user_name'] . ' for ' . $b['service_name'] . ' on ' . notif_when($b) . '.',
        'booking', 'booking', (int)$b['id'], 'p:booking:' . $b['id'] . ':requested');
}

// A real status change of a booking. $actor is who did it: 'provider' or 'user'. The other party is notified.
function notify_booking_status(int $bookingId, string $status, string $actor): void
{
    $b = notif_booking($bookingId);
    if (!$b) return;
    $id = (int)$b['id'];
    $key = strtolower($status);
    if ($actor === 'provider') {
        $uid = (int)$b['user_id'];
        switch ($status) {
            case 'Accepted':
                notify($uid, null, 'Booking accepted', 'Your booking #' . $id . ' for ' . $b['service_name'] . ' with ' . $b['provider_name'] . ' on ' . notif_when($b) . ' has been accepted.', 'booking', 'booking', $id, "u:booking:$id:$key");
                break;
            case 'Rejected':
                notify($uid, null, 'Booking rejected', 'Your booking #' . $id . ' for ' . $b['service_name'] . ' with ' . $b['provider_name'] . ' has been rejected.', 'booking', 'booking', $id, "u:booking:$id:$key");
                break;
            case 'Completed':
                notify($uid, null, 'Booking completed', $b['provider_name'] . ' marked booking #' . $id . ' for ' . $b['service_name'] . ' as Completed.', 'booking', 'booking', $id, "u:booking:$id:$key");
                break;
            case 'Cancelled':
                notify($uid, null, 'Booking cancelled', 'Your booking #' . $id . ' for ' . $b['service_name'] . ' with ' . $b['provider_name'] . ' on ' . fmt_date($b['booking_date']) . ' has been cancelled by the provider.', 'booking', 'booking', $id, "u:booking:$id:$key");
                break;
        }
    } elseif ($actor === 'user' && $status === 'Cancelled') {
        notify(null, (int)$b['provider_id'], 'Booking cancelled', 'Booking #' . $id . ' from ' . $b['user_name'] . ' for ' . $b['service_name'] . ' on ' . fmt_date($b['booking_date']) . ' has been cancelled.', 'booking', 'booking', $id, "p:booking:$id:$key");
    }
}

// The provider confirmed the cash. Call only after the payment really became Paid.
function notify_payment_confirmed(int $bookingId): void
{
    $b = notif_booking($bookingId);
    $pay = row('SELECT id,amount,payment_status FROM payments WHERE booking_id=?', [$bookingId]);
    if (!$b || !$pay || $pay['payment_status'] !== 'Paid') return;
    $id = (int)$b['id'];
    $amt = money($pay['amount']);
    notify((int)$b['user_id'], null, 'Cash payment confirmed',
        $b['provider_name'] . ' confirmed receiving your cash payment of ' . $amt . ' for booking #' . $id . ' (' . $b['service_name'] . '). Payment status: Paid.',
        'payment', 'payment', (int)$pay['id'], "u:payment:$id:paid");
    notify(null, (int)$b['provider_id'], 'Payment marked Paid',
        'Payment for booking #' . $id . ' from ' . $b['user_name'] . ' (' . $b['service_name'] . ') has been marked Paid. Amount: ' . $amt . '.',
        'payment', 'payment', (int)$pay['id'], "p:payment:$id:paid");
}

// Complaint status/response changed by the Admin -> the complaining USER (and the provider the complaint names, if any).
function notify_complaint_update(array $c, string $status, ?string $response = null): void
{
    $cid = (int)$c['id'];
    $bid = !empty($c['booking_id']) ? (int)$c['booking_id'] : 0;
    $msg = 'Your complaint #' . $cid . ($bid ? ' regarding booking #' . $bid : '') . ' has been updated. Status: ' . $status . '.';
    if ($response !== null && trim($response) !== '') $msg .= ' Response: ' . mb_substr(trim($response), 0, 300);
    notify((int)$c['user_id'], null, 'Complaint update', $msg, 'complaint', 'complaint', $cid);
    if (!empty($c['provider_id']))
        notify(null, (int)$c['provider_id'], 'Complaint update', 'Complaint #' . $cid . ($bid ? ' related to booking #' . $bid : '') . ' has been updated. Status: ' . $status . '.', 'complaint', 'complaint', $cid);
}

// Verification status changed by the Admin -> the PROVIDER.
function notify_verification_update(int $providerId, string $status, ?string $note = null): void
{
    $why = $status === 'Approved' ? 'Your account is active and you are visible to users.'
        : ($status === 'Rejected' ? ($note ? 'Reason: ' . $note : 'Please upload corrected documents.') : 'Your documents are awaiting review.');
    notify(null, $providerId, 'Verification ' . strtolower($status), 'Your provider verification status has changed to ' . $status . '. ' . $why, 'verification', 'verification', $providerId);
}

/* ---------- links ----------
   Destinations are the existing clean-URL list pages, which only ever list the signed-in recipient's own records. Each link
   is additionally ownership-checked here: if the linked record is not the recipient's, no link is shown. */
function notification_links(array $list, string $role, int $ownerId): array
{
    $links = [];
    $ids = ['booking' => [], 'payment' => [], 'complaint' => []];
    foreach ($list as $n) if (!empty($n['related_type']) && isset($ids[$n['related_type']]) && !empty($n['related_id'])) $ids[$n['related_type']][] = (int)$n['related_id'];
    $own = ['booking' => [], 'payment' => [], 'complaint' => []];
    $col = $role === 'provider' ? 'provider_id' : 'user_id';
    if ($ids['booking']) foreach (rows("SELECT id,status FROM bookings WHERE $col=? AND id IN (" . implode(',', array_unique($ids['booking'])) . ')', [$ownerId]) as $r) $own['booking'][(int)$r['id']] = $r['status'];
    if ($ids['payment']) foreach (rows("SELECT pay.id,pay.booking_id FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE b.$col=? AND pay.id IN (" . implode(',', array_unique($ids['payment'])) . ')', [$ownerId]) as $r) $own['payment'][(int)$r['id']] = (int)$r['booking_id'];
    if ($role === 'user' && $ids['complaint']) foreach (rows('SELECT id FROM complaints WHERE user_id=? AND id IN (' . implode(',', array_unique($ids['complaint'])) . ')', [$ownerId]) as $r) $own['complaint'][(int)$r['id']] = true;
    foreach ($list as $n) {
        $nid = (int)$n['id'];
        $t = $n['related_type'] ?? null;
        $rid = (int)($n['related_id'] ?? 0);
        $url = null;
        if ($t === 'booking' && isset($own['booking'][$rid]))
            $url = $role === 'provider' ? ($own['booking'][$rid] === 'Pending' ? 'provider/requests' : 'provider/bookings') . '#booking-' . $rid : 'user/bookings#booking-' . $rid;
        elseif ($t === 'payment' && isset($own['payment'][$rid]))
            $url = ($role === 'provider' ? 'provider/payments' : 'user/payments') . '#booking-' . $own['payment'][$rid];
        elseif ($t === 'complaint' && isset($own['complaint'][$rid]))
            $url = 'user/complaints#complaint-' . $rid;
        elseif ($t === 'verification' && $role === 'provider')
            $url = 'provider/verification';
        elseif (!$t) { // older notification without a linked record: send to the matching list page
            $ty = (string)($n['type'] ?? '');
            if ($role === 'provider') $url = ['booking' => stripos((string)$n['title'], 'request') !== false ? 'provider/requests' : 'provider/bookings', 'payment' => 'provider/payments', 'verification' => 'provider/verification', 'rating' => 'provider/reviews'][$ty] ?? null;
            else $url = ['booking' => 'user/bookings', 'payment' => 'user/payments', 'complaint' => 'user/complaints'][$ty] ?? null;
        }
        $links[$nid] = $url;
    }
    return $links;
}

// Shared list for /user/notifications and /provider/notifications.
function render_notification_list(array $list, array $links): void
{ ?>
    <div class="card-soft flush notif-list"><?php foreach ($list as $n): $url = $links[(int)$n['id']] ?? null; ?>
            <div class="notif-item p-3 border-bottom <?= $n['is_read'] ? '' : 'bg-success-subtle notif-unread' ?>">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <b><?php if (!$n['is_read']): ?><span class="notif-dot" aria-hidden="true"></span><span class="visually-hidden">Unread: </span><?php endif; ?><?= e($n['title']) ?></b>
                    <small class="text-secondary text-nowrap"><?= e(fmt_date($n['created_at'], 'd M Y, g:i A')) ?></small>
                </div>
                <div class="text-secondary notif-msg"><?= e($n['message']) ?></div>
                <?php if ($url): ?><a class="small" href="<?= e(url($url)) ?>">View Details <i class="bi bi-arrow-right"></i></a><?php endif; ?>
            </div><?php endforeach; ?>
        <?php if (!$list): ?><div class="empty-state"><i class="bi bi-bell"></i>
                <p class="mb-0 mt-2">Nothing new.</p>
            </div><?php endif; ?></div>
<?php }
