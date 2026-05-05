<?php
/**
 * Template Name: Page Export
 *
 * Serves machine-readable export feeds based on the `type` query parameter.
 * Usage: /page-export-slug/?type=events
 *
 * Supported types:
 *   - events  →  iCal feed (RFC 5545) of all upcoming events
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Fold long iCal lines at 75 octets (RFC 5545 §3.1).
 *
 * @param string $text Raw property line (without CRLF).
 * @return string Folded text with CRLF line endings.
 */
function lpdh_ical_fold( $text ) {
    $output   = '';
    $line_len = 0;

    // Work byte-by-byte to respect the 75-octet limit
    $bytes = str_split( $text );
    foreach ( $bytes as $byte ) {
        if ( $line_len >= 75 ) {
            $output  .= "\r\n ";
            $line_len = 1; // the space counts
        }
        $output  .= $byte;
        $line_len++;
    }
    return $output . "\r\n";
}

/**
 * Escape iCal text values (commas, semicolons, backslashes, newlines).
 *
 * @param string $text Raw value.
 * @return string Escaped value.
 */
function lpdh_ical_escape_text( $text ) {
    $text = str_replace( '\\', '\\\\', $text );
    $text = str_replace( ';', '\;', $text );
    $text = str_replace( ',', '\,', $text );
    $text = str_replace( "\r\n", '\n', $text );
    $text = str_replace( "\n", '\n', $text );
    $text = str_replace( "\r", '\n', $text );
    return $text;
}

/**
 * Convert an HTML string to a readable plain-text approximation.
 *
 * Preserves paragraph breaks and list items in a human-readable way
 * before stripping remaining tags.
 *
 * @param string $html Raw HTML.
 * @return string Plain text.
 */
function lpdh_html_to_plain( $html ) {
    if ( empty( $html ) ) {
        return '';
    }

    // Block-level elements → newlines
    $html = preg_replace( '/<br\s*\/?>/i',             "\n",      $html );
    $html = preg_replace( '/<\/p\s*>/i',               "\n\n",    $html );
    $html = preg_replace( '/<\/h[1-6]\s*>/i',          "\n\n",    $html );
    $html = preg_replace( '/<\/li\s*>/i',              "\n",      $html );
    $html = preg_replace( '/<li[^>]*>/i',              "• ",      $html );
    $html = preg_replace( '/<\/?(ul|ol)[^>]*>/i',      "\n",      $html );
    $html = preg_replace( '/<\/?(blockquote)[^>]*>/i', "\n",      $html );

    // Strip remaining tags
    $text = wp_strip_all_tags( $html );

    // Decode HTML entities
    $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

    // Collapse more than two consecutive newlines
    $text = preg_replace( '/\n{3,}/', "\n\n", $text );

    return trim( $text );
}

/**
 * Format a timestamp as an iCal DATE-TIME value (UTC).
 *
 * @param int $timestamp Unix timestamp.
 * @return string e.g. "20260510T140000Z"
 */
function lpdh_ical_datetime( $timestamp ) {
    return gmdate( 'Ymd\THis\Z', $timestamp );
}

// ---------------------------------------------------------------------------
// Route the request
// ---------------------------------------------------------------------------

$feed_type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : '';

switch ( $feed_type ) {

    // -----------------------------------------------------------------------
    case 'events':
    // -----------------------------------------------------------------------

        // Pull ALL future events (no pagination limit)
        $args = array(
            'post_type'      => 'event',
            'posts_per_page' => -1,
            'meta_key'       => 'event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => 'event_date',
                    'value'   => current_time( 'Y-m-d H:i:s' ),
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ),
            ),
        );

        $events_query = new WP_Query( $args );

        // --- Output headers ---
        // Must be sent before any whitespace
        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: inline; filename="lpdh-events.ics"' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // --- Build iCal ---
        $site_name   = get_bloginfo( 'name' );
        $site_url    = get_site_url();
        $prod_id     = '-//' . $site_name . '//Events Calendar//IT';
        $calendar_id = sanitize_title( $site_name ) . '-events@' . parse_url( $site_url, PHP_URL_HOST );

        // VCALENDAR wrapper
        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo lpdh_ical_fold( 'PRODID:' . $prod_id );
        echo "CALSCALE:GREGORIAN\r\n";
        echo "METHOD:PUBLISH\r\n";
        echo lpdh_ical_fold( 'X-WR-CALNAME:LPDH Events' );
        echo lpdh_ical_fold( 'X-WR-CALDESC:' . lpdh_ical_escape_text( 'Upcoming events from ' . $site_name ) );
        echo "X-WR-TIMEZONE:Europe/Rome\r\n";

        // VTIMEZONE block for Europe/Rome (CET/CEST)
        echo "BEGIN:VTIMEZONE\r\n";
        echo "TZID:Europe/Rome\r\n";
        echo "BEGIN:STANDARD\r\n";
        echo "DTSTART:19701025T030000\r\n";
        echo "RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10\r\n";
        echo "TZOFFSETFROM:+0200\r\n";
        echo "TZOFFSETTO:+0100\r\n";
        echo "TZNAME:CET\r\n";
        echo "END:STANDARD\r\n";
        echo "BEGIN:DAYLIGHT\r\n";
        echo "DTSTART:19700329T020000\r\n";
        echo "RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3\r\n";
        echo "TZOFFSETFROM:+0100\r\n";
        echo "TZOFFSETTO:+0200\r\n";
        echo "TZNAME:CEST\r\n";
        echo "END:DAYLIGHT\r\n";
        echo "END:VTIMEZONE\r\n";

        if ( $events_query->have_posts() ) {
            while ( $events_query->have_posts() ) {
                $events_query->the_post();

                $post_id    = get_the_ID();
                $event_date = get_field( 'field_event_date' ); // e.g. "2026-05-10 14:00:00"
                $place_obj  = get_field( 'field_event_place' );
                $fb_link    = get_field( 'field_event_fb_link' );

                // Parse event start
                if ( ! $event_date ) {
                    continue; // Skip events without a date
                }

                $ts_start = strtotime( $event_date );
                $ts_end   = $ts_start + ( 4 * HOUR_IN_SECONDS ); // default: +4 hours

                // Location
                $location = '';
                if ( $place_obj ) {
                    $location = $place_obj->post_title;
                    // Try to get address ACF field from the place CPT if available
                    $place_address = get_field( 'field_place_address', $place_obj->ID );
                    if ( $place_address ) {
                        $location .= ', ' . $place_address;
                    }
                }

                // Description: use post content, convert from HTML to plain text
                $raw_content = get_the_content();
                $description = lpdh_html_to_plain( $raw_content );

                // Append Facebook link if present
                if ( $fb_link ) {
                    $description .= ( $description ? "\n\n" : '' ) . 'Facebook: ' . $fb_link;
                }

                // UID: stable, unique per-event
                $uid = 'event-' . $post_id . '@' . parse_url( $site_url, PHP_URL_HOST );

                // Last modified
                $modified_ts = strtotime( get_the_modified_date( 'Y-m-d H:i:s' ) );

                echo "BEGIN:VEVENT\r\n";
                echo lpdh_ical_fold( 'UID:' . $uid );
                echo lpdh_ical_fold( 'SUMMARY:' . lpdh_ical_escape_text( get_the_title() ) );
                echo lpdh_ical_fold( 'DTSTART;TZID=Europe/Rome:' . date( 'Ymd\THis', $ts_start ) );
                echo lpdh_ical_fold( 'DTEND;TZID=Europe/Rome:' . date( 'Ymd\THis', $ts_end ) );
                echo lpdh_ical_fold( 'DTSTAMP:' . lpdh_ical_datetime( time() ) );
                echo lpdh_ical_fold( 'LAST-MODIFIED:' . lpdh_ical_datetime( $modified_ts ) );
                echo lpdh_ical_fold( 'URL:' . get_permalink() );

                if ( $location ) {
                    echo lpdh_ical_fold( 'LOCATION:' . lpdh_ical_escape_text( $location ) );
                }

                if ( $description ) {
                    echo lpdh_ical_fold( 'DESCRIPTION:' . lpdh_ical_escape_text( $description ) );
                }

                echo "END:VEVENT\r\n";
            }
            wp_reset_postdata();
        }

        echo "END:VCALENDAR\r\n";

        exit; // Stop WordPress from rendering the theme

    // -----------------------------------------------------------------------
    default:
    // -----------------------------------------------------------------------
        // Unknown type — return a simple 400
        status_header( 400 );
        header( 'Content-Type: text/plain; charset=utf-8' );
        echo 'Feed type not recognised. Supported types: events';
        exit;
}
