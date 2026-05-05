<?php
/**
 * Template Name: Page Events
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4 text-center">
            <?php
            // Build the iCal feed URL from the Theme Settings option (lpdh_feed_page_id)
            $ical_url = lpdh_get_export_page_url( 'events' );

            // webcal:// variant (replaces https:// or http://)
            $webcal_url = $ical_url ? preg_replace( '/^https?:\/\//i', 'webcal://', $ical_url ) : '';

            // Google Calendar subscription link
            // The &name= parameter sets the calendar display name in Google Calendar
            $gcal_url = $ical_url
                ? 'https://calendar.google.com/calendar/r?cid=' . urlencode( $webcal_url ) . '&name=' . urlencode( 'LPDH Events' )
                : '';
            ?>

            <h1 class="page-title"><?php the_title(); ?></h1>

            <?php if ( $ical_url ) : ?>
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
            <?php endif; ?>

            <?php the_content(); ?>
        </header>

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
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 1px 5px rgba(0,0,0,.14);
            overflow: hidden;
            background: #fff;
        }
        .lpdh-cal-icon-wrap svg {
            width: 32px;
            height: 32px;
            display: block;
        }
        .lpdh-cal-icon-btn:hover .lpdh-cal-icon-wrap,
        .lpdh-cal-icon-btn:focus .lpdh-cal-icon-wrap {
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,.2);
        }
        /* Circular background tint on hover for download icon */
        .lpdh-cal-icon-ics .lpdh-cal-icon-wrap { background: #6c757d; }
        </style>

        <div class="container pb-5">
            <?php
            $today = date('Y-m-d H:i:s');
            $args = array(
                'post_type' => 'event',
                'posts_per_page' => 12,
                'meta_key' => 'event_date',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'event_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATETIME'
                    )
                )
            );
            $events_query = new WP_Query($args);

            if ($events_query->have_posts()): ?>
                <div class="event-archive-grid">
                    <?php while ($events_query->have_posts()):
                        $events_query->the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php esc_html_e('No upcoming events found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-primary btn-lg">
                    <?php esc_html_e('See all events', 'bootscore'); ?>
                </a>
            </div>

            <div class="places-section mt-5 pt-5 border-top">
                <h2 class="text-center mb-5"><?php esc_html_e('Where to play', 'bootscore'); ?></h2>

                <?php
                $places_args = array(
                    'post_type' => 'place',
                    'posts_per_page' => -1,
                    'orderby' => 'date',
                    'order' => 'ASC',
                );
                $places_query = new WP_Query($places_args);

                if ($places_query->have_posts()): ?>
                    <div class="places-list">
                        <?php while ($places_query->have_posts()):
                            $places_query->the_post(); ?>
                            <?php get_template_part('template-parts/card', 'place'); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>



<?php get_footer(); ?>