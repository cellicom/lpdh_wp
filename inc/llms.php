<?php
/**
 * LLM.txt / LLMs.txt Support for AI Search Engines
 *
 * Serves the llms.txt file dynamically at /llms.txt and /llm.txt.
 *
 * @package Bootscore Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle virtual routing for llms.txt and llm.txt requests.
 */
function lpdh_serve_llms_txt() {
    // Get the request path relative to the WordPress home URL
    $home_path = parse_url( home_url(), PHP_URL_PATH );
    $request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

    if ( $home_path ) {
        // If WordPress is installed in a subdirectory, remove it from the start of the request
        if ( strpos( $request_path, $home_path ) === 0 ) {
            $request_path = substr( $request_path, strlen( $home_path ) );
        }
    }

    $request_path = ltrim( $request_path, '/' );

    // Match either llms.txt or llm.txt (case-insensitive)
    if ( strcasecmp( $request_path, 'llms.txt' ) === 0 || strcasecmp( $request_path, 'llm.txt' ) === 0 ) {
        $file_path = get_stylesheet_directory() . '/llms.txt';

        if ( file_exists( $file_path ) ) {
            header( 'Content-Type: text/plain; charset=utf-8' );
            header( 'Access-Control-Allow-Origin: *' ); // Enable CORS for AI agents/crawlers
            readfile( $file_path );
            exit;
        }
    }
}
add_action( 'init', 'lpdh_serve_llms_txt' );
