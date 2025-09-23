<?php
// Shared helper for booking status codes and labels
if (!defined('BOOKING_STATUS_PENDING')) {
    define('BOOKING_STATUS_PENDING', 1);
    define('BOOKING_STATUS_CONFIRMED', 2);
    define('BOOKING_STATUS_COMPLATE', 3);
    define('BOOKING_STATUS_CANCELLED', 4);
}

if (!function_exists('booking_status_code')) {
    function booking_status_code($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        $normalized = strtolower(trim((string)$value));
        switch ($normalized) {
            case '1':
            case 'pending':
                return BOOKING_STATUS_PENDING;
            case '2':
            case 'confirmed':
            case 'confirm':
                return BOOKING_STATUS_CONFIRMED;
            case '3':
            case 'complate':
            case 'completed':
            case 'complete':
                return BOOKING_STATUS_COMPLATE;
            case '4':
            case 'cancelled':
            case 'canceled':
            case 'cancel':
            case 'cancle':
            case 'rejected':
                return BOOKING_STATUS_CANCELLED;
            default:
                return null;
        }
    }
}

if (!function_exists('booking_status_slug')) {
    function booking_status_slug($value)
    {
        switch (booking_status_code($value)) {
            case BOOKING_STATUS_PENDING:
                return 'pending';
            case BOOKING_STATUS_CONFIRMED:
                return 'confirmed';
            case BOOKING_STATUS_COMPLATE:
                return 'complate';
            case BOOKING_STATUS_CANCELLED:
                return 'cancelled';
            default:
                return '';
        }
    }
}

if (!function_exists('booking_status_label')) {
    function booking_status_label($value)
    {
        switch (booking_status_code($value)) {
            case BOOKING_STATUS_PENDING:
                return 'Pending';
            case BOOKING_STATUS_CONFIRMED:
                return 'Confirmed';
            case BOOKING_STATUS_COMPLATE:
                return 'Completed';
            case BOOKING_STATUS_CANCELLED:
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    }
}

if (!function_exists('booking_status_badge_class')) {
    function booking_status_badge_class($value)
    {
        switch (booking_status_code($value)) {
            case BOOKING_STATUS_CONFIRMED:
                return 'bg-success';
            case BOOKING_STATUS_COMPLATE:
                return 'bg-primary';
            case BOOKING_STATUS_CANCELLED:
                return 'bg-danger';
            case BOOKING_STATUS_PENDING:
                return 'bg-warning text-dark';
            default:
                return 'bg-secondary';
        }
    }
}

if (!function_exists('booking_status_options')) {
    function booking_status_options()
    {
        return [
            BOOKING_STATUS_PENDING   => 'Pending',
            BOOKING_STATUS_CONFIRMED => 'Confirmed',
            BOOKING_STATUS_COMPLATE  => 'Completed',
            BOOKING_STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
