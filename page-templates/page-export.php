<?php
/**
 * Template Name: Page Export
 *
 * Serves machine-readable export feeds based on the `type` query parameter.
 * All logic and helpers live in inc/export-calendar.php.
 *
 * Supported types:
 *   - events  →  iCal feed (RFC 5545) of all upcoming events
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

$feed_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';

switch ( $feed_type ) {

    case 'events':
        lpdh_export_events_ical(); // defined in inc/export-calendar.php — exits
        break;

    case 'events_json':
        lpdh_export_events_json(); // defined in inc/export-calendar.php — exits
        break;

    default:
        // Unknown type — redirect to homepage
        wp_redirect( home_url( '/' ), 302 );
        exit;
}
