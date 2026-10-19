<?php
/* Complaints: one place for how a complaint's related booking is read and shown, so the User, Admin and notification
   texts all describe the booking in the same way.
   - A complaint must belong to one of the user's own bookings (booking_id is required; checked on the server).
   - The booking is identified by its number and the exact SERVICE name (bookings.service_name snapshot, else the current
     offering title). The service CATEGORY name is never shown here. Older bookings that never recorded a service name
     show "Service not recorded" instead of the category.
   - No schema change: complaints.booking_id / provider_id already exist. Older complaints without a booking stay readable
     and show "No booking linked". Aliases used by the SQL below: c = complaints, b = bookings, o = provider_offerings. */

const COMPLAINT_SERVICE_NAME_SQL = "COALESCE(NULLIF(b.service_name,''), o.title)";
const COMPLAINT_BOOKING_COLS = 'b.id AS bk_id, b.booking_date AS bk_date, b.status AS bk_status, ' . COMPLAINT_SERVICE_NAME_SQL . ' AS service_name';
const COMPLAINT_BOOKING_JOIN = ' LEFT JOIN bookings b ON b.id=c.booking_id LEFT JOIN provider_offerings o ON o.id=b.offering_id ';

function complaint_service_name(array $c): string
{
    $n = trim((string)($c['service_name'] ?? ''));
    return $n !== '' ? $n : 'Service not recorded';
}

// Booking details shown with a complaint. $c needs bk_id, service_name, bk_date, bk_status (see COMPLAINT_BOOKING_COLS) and,
// for the detail line, provider_name. $compact = just "Booking #ID · Service" (used in table cells).
function complaint_booking_html(array $c, bool $compact = false): string
{
    if (empty($c['bk_id'])) return '<span class="text-secondary">No booking linked</span>';
    $h = '<div class="complaint-booking"><div class="complaint-booking__main"><b>Booking #' . (int)$c['bk_id'] . '</b> · ' . e(complaint_service_name($c)) . '</div>';
    if (!$compact) {
        $bits = [];
        if (!empty($c['provider_name'])) $bits[] = (string)$c['provider_name'];
        if (!empty($c['bk_date'])) $bits[] = fmt_date($c['bk_date']);
        if (!empty($c['bk_status'])) $bits[] = (string)$c['bk_status'];
        if ($bits) $h .= '<div class="small text-secondary">' . e(implode(' · ', $bits)) . '</div>';
    }
    return $h . '</div>';
}

// Text shown to the user while choosing the booking in the complaint form.
function complaint_booking_option_label(array $b): string
{
    return 'Booking #' . (int)$b['id'] . ' — ' . complaint_service_name($b) . ' · ' . $b['provider_name'] . ' · ' . fmt_date($b['booking_date']);
}

// The user's own bookings, newest first, for the complaint form.
function complaint_user_bookings(int $userId): array
{
    return rows('SELECT b.id,b.booking_date,p.name provider_name,' . COMPLAINT_SERVICE_NAME_SQL . ' AS service_name
        FROM bookings b JOIN providers p ON p.id=b.provider_id LEFT JOIN provider_offerings o ON o.id=b.offering_id
        WHERE b.user_id=? ORDER BY b.created_at DESC, b.id DESC', [$userId]);
}

// Server-side booking check for a new complaint. Returns [bookingRow|null, errorMessage|null]. The booking must exist and
// belong to $userId; the provider is taken from the booking, never from the request.
function complaint_check_booking($raw, int $userId): array
{
    if (!is_string($raw) || !ctype_digit(trim($raw)) || (int)trim($raw) < 1) return [null, 'Please select the booking this complaint is about.'];
    $b = row('SELECT id,provider_id FROM bookings WHERE id=? AND user_id=?', [(int)trim($raw), $userId]);
    return $b ? [$b, null] : [null, 'The selected booking was not found in your account. Please choose one of your own bookings.'];
}

// Service name for notification texts; '' when the booking never recorded one.
function complaint_booking_service_name(int $bookingId): string
{
    $n = val('SELECT ' . COMPLAINT_SERVICE_NAME_SQL . ' FROM bookings b LEFT JOIN provider_offerings o ON o.id=b.offering_id WHERE b.id=?', [$bookingId]);
    return trim((string)$n);
}

// Long text with a "See More" / "See Less" toggle. Re-uses the existing .req-box / .req-text / .req-toggle markup and the
// handler in assets/js/app.js: the full text is always in the page, only the preview is clipped (3 lines) and the button
// is revealed only when the text really overflows, so short text gets no control. $domId must be unique on the page.
function expandable_text_html(string $domId, ?string $text, string $extraClass = ''): string
{
    $text = trim((string)$text);
    if ($text === '') return '';
    return '<div class="' . trim('req-box complaint-text ' . e($extraClass)) . '"><div class="req-text clamp-3" data-clamp="clamp-3" id="' . e($domId) . '">' . e($text) . '</div>'
        . '<button type="button" class="btn btn-link btn-sm p-0 req-toggle" hidden aria-expanded="false" aria-controls="' . e($domId) . '" data-more="See More" data-less="See Less">See More</button></div>';
}

// Admin's answer, shown as a normal readable section (not an alert box). Empty string when there is no response yet.
function complaint_response_html(int $complaintId, ?string $response): string
{
    $body = expandable_text_html('cresp-' . $complaintId, $response);
    if ($body === '') return '';
    return '<section class="complaint-response" aria-label="Admin response"><h3 class="complaint-section-label">Admin response</h3>' . $body . '</section>';
}
