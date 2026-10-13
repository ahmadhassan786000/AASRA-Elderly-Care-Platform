<?php
function actions_open(string $label = 'Actions'): void
{
    echo '<div class="dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">' . e($label) . '</button><ul class="dropdown-menu dropdown-menu-end shadow-sm">';
}
function actions_close(): void
{
    echo '</ul></div>';
}
function act_link(string $icon, string $label, string $href, string $cls = ''): void
{
    echo '<li><a class="dropdown-item ' . $cls . '" href="' . e($href) . '"><i class="bi bi-' . $icon . ' me-2"></i>' . e($label) . '</a></li>';
}
// AJAX action item. $when = [liveKey, statusValue] shows the item only when the live status equals the value.
function act_ajax(string $icon, string $label, string $action, $id, string $value = '', string $cls = '', string $confirm = '', ?array $when = null): void
{
    $w = $when ? ' data-live-when="' . e($when[0]) . '" data-when="' . e($when[1]) . '"' : '';
    $hide = ($when && isset($when[2]) && $when[2] !== $when[1]) ? ' class="d-none"' : '';
    echo '<li' . $w . $hide . '><a href="#" class="dropdown-item ' . $cls . '" data-ajax-action="' . e($action) . '" data-id="' . (int)$id . '" data-value="' . e($value) . '"' . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '><i class="bi bi-' . $icon . ' me-2"></i>' . e($label) . '</a></li>';
}
function act_divider(): void
{
    echo '<li><hr class="dropdown-divider"></li>';
}
function live_badge(string $key, string $status): string
{
    return '<span data-live-key="' . e($key) . '">' . badge($status) . '</span>';
}
function page_head(string $title, string $sub = '', string $right = ''): void
{
    echo '<div class="page-head"><div><h1>' . e($title) . '</h1>' . ($sub ? '<p>' . e($sub) . '</p>' : '') . '</div>' . $right . '</div>';
}
function filter_reset(string $path): string
{
    return '<a class="btn btn-outline-secondary" href="' . e(url($path)) . '">Reset</a>';
}
function delete_error(Throwable $e): string
{
    if (db_is_integrity_error($e)) return 'This record has related bookings, ratings or complaints and cannot be deleted. Deactivate it instead.';
    throw $e;
}

// Sidebar badges: records that need an ADMIN decision right now. One row is counted once (no joins that could repeat it).
//  verification - providers who submitted documents and are waiting for review (same rule as Admin > Verification)
//  payments     - cash confirmations from providers waiting to be verified/rejected (same rule as the Verify button)
//  complaints   - complaints not yet resolved (Pending or Under Review)
// Bookings, Categories, Users and Providers have no admin-pending state in the workflow, so they get no badge.
function admin_pending_counts(): array
{
    return [
        'verification' => (int) val("SELECT COUNT(*) FROM providers p WHERE p.verification_status='Pending' AND EXISTS (SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id)"),
        'payments'     => (int) val("SELECT COUNT(*) FROM payments pay JOIN bookings b ON b.id=pay.booking_id WHERE pay.payment_status='Verification Requested' AND b.status='Completed'"),
        'complaints'   => (int) val("SELECT COUNT(*) FROM complaints WHERE status IN ('Pending','Under Review')"),
    ];
}
