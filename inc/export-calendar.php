<?php
/**
 * iCal / Export Calendar
 *
 * Helper functions and feed handlers for the "Page Export" template.
 * Included automatically by functions.php so functions are available
 * site-wide (e.g. for future shortcodes or REST endpoints).
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ---------------------------------------------------------------------------
// iCal Helpers
// ---------------------------------------------------------------------------

/**
 * Fold long iCal lines at 75 octets (RFC 5545 §3.1).
 *
 * Splits by Unicode code-point (not raw byte) so multibyte UTF-8
 * characters are never broken across a fold boundary.
 *
 * @param string $text Raw property line (without CRLF).
 * @return string Folded text with CRLF line endings.
 */
function lpdh_ical_fold( $text ) {
    $output   = '';
    $line_len = 0; // current line length in bytes

    // mb_str_split() splits by Unicode code-points (PHP 7.4+).
    $chars = mb_str_split( $text, 1, 'UTF-8' );

    foreach ( $chars as $char ) {
        $char_bytes = strlen( $char ); // byte-length of this code-point

        // If adding this character would exceed 75 octets, fold first.
        if ( $line_len + $char_bytes > 75 ) {
            $output  .= "\r\n ";
            $line_len = 1; // the leading space counts as 1 octet
        }

        $output   .= $char;
        $line_len += $char_bytes;
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
 * Preserves paragraph breaks and list items before stripping tags.
 * Collapses runs of blank lines to a maximum of two.
 *
 * @param string $html Raw HTML.
 * @return string Plain text (UTF-8).
 */
function lpdh_html_to_plain( $html ) {
    if ( empty( $html ) ) {
        return '';
    }

    // Block-level elements → newlines
    $html = preg_replace( '/<br\s*\/?>/i',             "\n",   $html );
    $html = preg_replace( '/<\/p\s*>/i',               "\n\n", $html );
    $html = preg_replace( '/<\/h[1-6]\s*>/i',          "\n\n", $html );
    $html = preg_replace( '/<\/li\s*>/i',              "\n",   $html );
    $html = preg_replace( '/<li[^>]*>/i',              '• ',   $html );
    $html = preg_replace( '/<\/?(ul|ol)[^>]*>/i',      "\n",   $html );
    $html = preg_replace( '/<\/?(blockquote)[^>]*>/i', "\n",   $html );

    // Strip remaining tags
    $text = wp_strip_all_tags( $html );

    // Decode HTML entities (handles &#8211;, &amp;, &nbsp;, etc.)
    $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

    // Remove non-breaking spaces and other invisible Unicode spaces
    $text = preg_replace( '/[\xc2\xa0\x{00A0}]/u', ' ', $text );

    // Trim whitespace from each line
    $lines = explode( "\n", $text );
    $lines = array_map( 'trim', $lines );
    $text  = implode( "\n", $lines );

    // Collapse more than 2 consecutive blank lines into exactly 2
    $text = preg_replace( '/\n{3,}/', "\n\n", $text );

    return trim( $text );
}

/**
 * Format a Unix timestamp as an iCal DATE-TIME value in UTC.
 *
 * @param int $timestamp Unix timestamp.
 * @return string e.g. "20260510T140000Z"
 */
function lpdh_ical_datetime( $timestamp ) {
    return gmdate( 'Ymd\THis\Z', $timestamp );
}

// ---------------------------------------------------------------------------
// Feed handlers  (called from page-export.php)
// ---------------------------------------------------------------------------

/**
 * Get dynamic calendar name based on active URL GET filters
 *
 * @return string Dynamic calendar name (e.g. "LPDH Events Palermo 2026")
 */
function lpdh_get_dynamic_calendar_name() {
    $cal_name_parts = ['LPDH Events'];
    if ( ! empty( $_GET['event_city'] ) ) {
        $cal_name_parts[] = sanitize_text_field( $_GET['event_city'] );
    }
    if ( ! empty( $_GET['event_place_id'] ) ) {
        $place = get_post( intval( $_GET['event_place_id'] ) );
        if ( $place ) {
            $cal_name_parts[] = $place->post_title;
        }
    }
    if ( ! empty( $_GET['event_year'] ) ) {
        $cal_name_parts[] = intval( $_GET['event_year'] );
    }
    return implode( ' ', $cal_name_parts );
}

/**
 * Output the iCal events feed and exit.
 *
 * Sends all HTTP headers, builds the VCALENDAR/VEVENT blocks for every
 * future event, and terminates the request.
 *
 * @return void
 */
function lpdh_export_events_ical() {

    // Pull ALL future events (no pagination limit)
    $meta_query_args = array(
        'relation' => 'AND',
        array(
            'key'     => 'event_date',
            'value'   => current_time( 'Y-m-d H:i:s' ),
            'compare' => '>=',
            'type'    => 'DATETIME',
        ),
    );

    // Apply URL filters if present
    if ( ! empty( $_GET['event_year'] ) ) {
        $filter_year = intval( $_GET['event_year'] );
        // Override the future event rule with the specific year rule (to match page behavior)
        $meta_query_args[0] = array(
            'key'     => 'event_date',
            'value'   => array( $filter_year . '-01-01 00:00:00', $filter_year . '-12-31 23:59:59' ),
            'compare' => 'BETWEEN',
            'type'    => 'DATETIME',
        );
    }
    if ( ! empty( $_GET['event_city'] ) ) {
        $meta_query_args[] = array(
            'key'     => 'event_city',
            'value'   => sanitize_text_field( $_GET['event_city'] ),
            'compare' => '=',
        );
    }
    if ( ! empty( $_GET['event_place_id'] ) ) {
        $meta_query_args[] = array(
            'key'     => 'event_place',
            'value'   => intval( $_GET['event_place_id'] ),
            'compare' => '=',
        );
    }

    $args = array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => $meta_query_args,
    );

    $events_query = new WP_Query( $args );

    // --- Output headers (must be sent before any whitespace) ---
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

    // --- Build Dynamic Calendar Name ---
    $cal_name = lpdh_get_dynamic_calendar_name();

    // VCALENDAR wrapper
    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo lpdh_ical_fold( 'PRODID:' . $prod_id );
    echo "CALSCALE:GREGORIAN\r\n";
    echo "METHOD:PUBLISH\r\n";
    echo lpdh_ical_fold( 'X-WR-CALNAME:' . lpdh_ical_escape_text( $cal_name ) );
    echo lpdh_ical_fold( 'X-WR-CALDESC:' . lpdh_ical_escape_text( 'Upcoming events from ' . $site_name . ' (' . $cal_name . ')' ) );
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

            // Skip events without a date
            if ( ! $event_date ) {
                continue;
            }

            $ts_start = strtotime( $event_date );
            $ts_end   = $ts_start + ( 4 * HOUR_IN_SECONDS ); // default: +4 hours

            // Location
            $location = '';
            if ( $place_obj ) {
                $location = $place_obj->post_title;
                $place_city = get_field( 'field_place_city', $place_obj->ID );
                $place_address = get_field( 'field_place_address', $place_obj->ID );
                if ( $place_city ) {
                    $location .= ', ' . $place_city;
                }
                if ( $place_address ) {
                    $location .= ', ' . $place_address;
                }
            }

            // Description: event URL first, then content as plain text
            $event_url   = get_permalink();
            $raw_content = get_the_content();
            $body_text   = lpdh_html_to_plain( $raw_content );

            $description = $event_url;
            if ( $body_text ) {
                $description .= "\n\n" . $body_text;
            }
            if ( $fb_link ) {
                $description .= "\n\nFacebook: " . $fb_link;
            }

            // UID: stable and unique per event
            $uid = 'event-' . $post_id . '@' . parse_url( $site_url, PHP_URL_HOST );

            // Last modified
            $modified_ts = strtotime( get_the_modified_date( 'Y-m-d H:i:s' ) );

            echo "BEGIN:VEVENT\r\n";
            echo lpdh_ical_fold( 'UID:' . $uid );
            echo lpdh_ical_fold( 'SUMMARY:' . lpdh_ical_escape_text(
                html_entity_decode( get_the_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8' )
            ) );
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
}

/**
 * Output the events feed as JSON and exit.
 *
 * @return void
 */
function lpdh_export_events_json() {

    // Pull ALL future events (no pagination limit)
    $meta_query_args = array(
        'relation' => 'AND',
        array(
            'key'     => 'event_date',
            'value'   => current_time( 'Y-m-d H:i:s' ),
            'compare' => '>=',
            'type'    => 'DATETIME',
        ),
    );

    // Apply URL filters if present
    if ( ! empty( $_GET['event_year'] ) ) {
        $filter_year = intval( $_GET['event_year'] );
        // Override the future event rule with the specific year rule (to match page behavior)
        $meta_query_args[0] = array(
            'key'     => 'event_date',
            'value'   => array( $filter_year . '-01-01 00:00:00', $filter_year . '-12-31 23:59:59' ),
            'compare' => 'BETWEEN',
            'type'    => 'DATETIME',
        );
    }
    if ( ! empty( $_GET['event_city'] ) ) {
        $meta_query_args[] = array(
            'key'     => 'event_city',
            'value'   => sanitize_text_field( $_GET['event_city'] ),
            'compare' => '=',
        );
    }
    if ( ! empty( $_GET['event_place_id'] ) ) {
        $meta_query_args[] = array(
            'key'     => 'event_place',
            'value'   => intval( $_GET['event_place_id'] ),
            'compare' => '=',
        );
    }

    $args = array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => $meta_query_args,
    );

    $events_query = new WP_Query( $args );
    
    $events_data = [];

    if ( $events_query->have_posts() ) {
        while ( $events_query->have_posts() ) {
            $events_query->the_post();

            $post_id    = get_the_ID();
            $event_date = get_field( 'field_event_date' ); 
            $place_obj  = get_field( 'field_event_place' );
            $fb_link    = get_field( 'field_event_fb_link' );

            if ( ! $event_date ) {
                continue;
            }
            
            $ts_start = strtotime( $event_date );

            $location_name = '';
            $location_city = '';
            $location_address = '';

            if ( $place_obj ) {
                $location_name = $place_obj->post_title;
                $location_city = get_field( 'field_place_city', $place_obj->ID );
                $location_address = get_field( 'field_place_address', $place_obj->ID );
            }

            // Thumbnail (Cover)
            $thumbnail_url = get_the_post_thumbnail_url( $post_id, 'full' );
            if ( ! $thumbnail_url ) {
                // Fallback image as in card-event.php
                $thumbnail_url = get_stylesheet_directory_uri() . '/assets/img/logo/logo-lpdh-ext-transparent.png';
            }

            // Extra ACF Fields
            $event_type  = get_field( 'event_type' ) ?: 'Tournament';
            $format      = get_field( 'format' ) ?: 'LPDH';
            $max_players = intval( get_field( 'max_players' ) );
            $ticket_fee  = get_field( 'ticket_fee' );

            $events_data[] = [
                'id'          => $post_id,
                'title'       => html_entity_decode( get_the_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
                'start_time'  => date( 'Y-m-d\TH:i:s', $ts_start ),
                'url'         => get_permalink(),
                'thumbnail'   => $thumbnail_url,
                'event_type'  => $event_type,
                'format'      => $format,
                'max_players' => $max_players,
                'ticket_fee'  => $ticket_fee ? sanitize_text_field( $ticket_fee ) : '',
                'fb_link'     => $fb_link ? esc_url( $fb_link ) : '',
                'location'    => [
                    'name'    => $location_name,
                    'city'    => $location_city,
                    'address' => $location_address,
                ],
                'description' => lpdh_html_to_plain( get_the_content() )
            ];
        }
        wp_reset_postdata();
    }

    $cal_name = lpdh_get_dynamic_calendar_name();
    
    $response = [
        'calendar_name' => $cal_name,
        'events'        => $events_data,
    ];

    // Restituisce l'output in JSON e termina l'esecuzione
    wp_send_json( $response );
}

/**
 * Render the Add to Calendar subscription/export buttons.
 *
 * @return void
 */
function lpdh_render_calendar_buttons() {
    $ical_url = lpdh_get_export_page_url( 'events' );
    
    if ( ! $ical_url ) {
        return;
    }

    $export_args = [];
    if ( ! empty( $_GET['event_city'] ) ) {
        $export_args['event_city'] = sanitize_text_field( $_GET['event_city'] );
    }
    if ( ! empty( $_GET['event_place_id'] ) ) {
        $export_args['event_place_id'] = intval( $_GET['event_place_id'] );
    }
    if ( ! empty( $_GET['event_year'] ) ) {
        $export_args['event_year'] = intval( $_GET['event_year'] );
    }
    if ( ! empty( $export_args ) ) {
        $ical_url = add_query_arg( $export_args, $ical_url );
    }

    $cal_name = lpdh_get_dynamic_calendar_name();

    // webcal:// variant (replaces https:// or http://)
    $webcal_url = preg_replace( '/^https?:\/\//i', 'webcal://', $ical_url );

    // Google Calendar subscription link
    $gcal_url = 'https://calendar.google.com/calendar/r?cid=' . urlencode( $webcal_url ) . '&name=' . urlencode( $cal_name );

    // JSON Feed link
    $json_url = str_replace( 'type=events', 'type=events_json', $ical_url );
    ?>
    <div class="lpdh-cal-icons d-flex justify-content-center align-items-center gap-2 mt-2 mb-1">

        <!-- Google Calendar -->
        <a href="<?php echo esc_url( $gcal_url ); ?>"
           target="_blank" rel="noopener noreferrer"
           class="lpdh-cal-icon-btn lpdh-cal-icon-gcal"
           data-bs-toggle="tooltip"
           data-bs-placement="bottom"
           data-bs-title="Add to Google Calendar"
           aria-label="Add to Google Calendar">
            <span class="lpdh-cal-icon-wrap">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="6" y="6" width="36" height="36" rx="4" fill="#fff" stroke="#dadce0" stroke-width="2"/>
                    <rect x="6" y="6" width="36" height="12" rx="4" fill="#1a73e8"/>
                    <rect x="6" y="14" width="36" height="4" fill="#1a73e8"/>
                    <circle cx="16" cy="6" r="3" fill="#1a73e8"/>
                    <circle cx="32" cy="6" r="3" fill="#1a73e8"/>
                    <rect x="14" y="24" width="6" height="6" rx="1" fill="#34a853"/>
                    <rect x="22" y="24" width="6" height="6" rx="1" fill="#fbbc04"/>
                    <rect x="30" y="24" width="6" height="6" rx="1" fill="#ea4335"/>
                    <rect x="14" y="32" width="6" height="6" rx="1" fill="#ea4335"/>
                    <rect x="22" y="32" width="6" height="6" rx="1" fill="#1a73e8"/>
                    <rect x="30" y="32" width="6" height="6" rx="1" fill="#34a853"/>
                </svg>
            </span>
        </a>

        <!-- Apple / Outlook Calendar -->
        <a href="<?php echo esc_url( $webcal_url ); ?>"
           class="lpdh-cal-icon-btn lpdh-cal-icon-apple"
           data-bs-toggle="tooltip"
           data-bs-placement="bottom"
           data-bs-title="Add to Apple / Outlook Calendar"
           aria-label="Add to Apple / Outlook Calendar">
            <span class="lpdh-cal-icon-wrap">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="4" y="8" width="40" height="36" rx="6" fill="#f5f5f7"/>
                    <rect x="4" y="8" width="40" height="14" rx="6" fill="#ff3b30"/>
                    <rect x="4" y="18" width="40" height="4" fill="#ff3b30"/>
                    <circle cx="16" cy="8" r="3.5" fill="#fff" stroke="#ff3b30" stroke-width="1.5"/>
                    <circle cx="32" cy="8" r="3.5" fill="#fff" stroke="#ff3b30" stroke-width="1.5"/>
                    <text x="24" y="38" text-anchor="middle" font-size="16" font-weight="700" font-family="Helvetica, Arial, sans-serif" fill="#1c1c1e">
                        <?php echo date_i18n( 'j' ); ?>
                    </text>
                </svg>
            </span>
        </a>

        <!-- JSON Feed -->
        <a href="<?php echo esc_url( $json_url ); ?>"
           target="_blank" rel="noopener noreferrer"
           class="lpdh-cal-icon-btn lpdh-cal-icon-json"
           data-bs-toggle="tooltip"
           data-bs-placement="bottom"
           data-bs-title="JSON Feed"
           aria-label="JSON Feed">
            <span class="lpdh-cal-icon-wrap">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="48" height="48" rx="10" fill="#f0db4f"/>
                    <text x="24" y="32" text-anchor="middle" font-size="22" font-weight="bold" font-family="monospace" fill="#323330">
                        {}
                    </text>
                </svg>
            </span>
        </a>

        <!-- Download .ics -->
        <a href="<?php echo esc_url( $ical_url ); ?>"
           download="lpdh-events.ics"
           class="lpdh-cal-icon-btn lpdh-cal-icon-ics"
           data-bs-toggle="tooltip"
           data-bs-placement="bottom"
           data-bs-title="Download .ics file"
           aria-label="Download .ics file">
            <span class="lpdh-cal-icon-wrap">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="48" height="48" rx="24" fill="#6c757d"/>
                    <path d="M24 11 L24 29 M15 23 L24 33 L33 23" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <rect x="11" y="35" width="26" height="3" rx="1.5" fill="#fff"/>
                </svg>
            </span>
        </a>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.lpdh-cal-icon-btn[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { trigger: 'hover focus' });
        });
    });
    </script>

    <style>
    /* ── Add to Calendar — compact circular icons ───────────────── */
    .lpdh-cal-icon-btn {
        display: inline-flex;
        text-decoration: none;
        color: inherit;
        line-height: 1;
    }
    .lpdh-cal-icon-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        transition: transform 0.18s ease;
        overflow: hidden;
        background: transparent;
    }
    .lpdh-cal-icon-wrap svg {
        width: 32px;
        height: 32px;
        display: block;
    }
    .lpdh-cal-icon-btn:hover .lpdh-cal-icon-wrap,
    .lpdh-cal-icon-btn:focus .lpdh-cal-icon-wrap {
        transform: translateY(-2px) scale(1.1);
    }
    </style>
    <?php
}

