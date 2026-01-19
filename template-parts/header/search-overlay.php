<?php

/**
 * Template part for displaying the full-screen search overlay
 *
 * @package Bootscore
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<div id="search-overlay" class="search-overlay">
    <button type="button" class="search-overlay-close" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
    </button>

    <div class="search-overlay-content container">
        <form role="search" method="get" class="search-form-overlay" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="input-group input-group-lg">
                <input type="search" class="form-control bg-transparent border-0 border-bottom form-control-lg"
                    placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder', 'bootscore'); ?>"
                    value="<?php echo get_search_query(); ?>" name="s" id="search-overlay-input" />
                <button class="btn btn-link border-0 border-bottom p-0 ms-2" type="submit">
                    <i class="fa-solid fa-magnifying-glass fa-2x"></i>
                </button>
            </div>
        </form>
    </div>
</div>