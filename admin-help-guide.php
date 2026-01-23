<?php
/**
 * Admin Help Guide Page - Full Version
 *
 * @package Bootscore Child
 * @version 1.7.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Register the Help Guide admin page
 */
function register_help_guide_admin_page()
{
    add_menu_page(
        __('Help Guide', 'text_domain'),
        __('Help Guide', 'text_domain'),
        'administrator',
        'help-guide',
        'render_help_guide_page',
        'dashicons-editor-help',
        2
    );
}
add_action('admin_menu', 'register_help_guide_admin_page');

/**
 * Render the Help Guide page content
 */
function render_help_guide_page()
{
    ?>
    <div class="wrap help-guide-wrap">
        <h1 style="display: flex; align-items: center; gap: 10px;">
            <span class="dashicons dashicons-editor-help" style="font-size: 32px; width: 32px; height: 32px;"></span>
            <?php _e('LPDH Advanced Management Guide', 'text_domain'); ?>
        </h1>

        <div class="help-guide-container"
            style="max-width: 1100px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-top: 25px; line-height: 1.6; color: #3c434a;">

            <div
                style="background: #e7f5fe; border-left: 5px solid #0073aa; padding: 20px; border-radius: 0 8px 8px 0; margin-bottom: 40px;">
                <h3 style="margin-top: 0; color: #0073aa;"><span class="dashicons dashicons-info"></span> Admin Overview
                </h3>
                <p style="margin-bottom: 0;">This site uses <strong>Custom Post Types (CPT)</strong> and <strong>Advanced
                        Custom Fields (ACF)</strong> to manage tournament data. This guide detail every field and tool
                    available to you as an Administrator.</p>
            </div>

            <nav class="help-guide-nav" style="margin-bottom: 50px;">
                <h3 style="border-bottom: 1px solid #ddd; padding-bottom: 10px;">
                    <?php _e('Quick Navigation', 'text_domain'); ?>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <a href="#events">1. Events & Rankings</a>
                    <a href="#decks">2. Decks Management</a>
                    <a href="#articles">3. Articles & News</a>
                    <a href="#pages">4. Pages & ACF Boxes</a>
                    <a href="#banned">5. Banned Cards</a>
                    <a href="#leaderboards">6. Leaderboards</a>
                    <a href="#stats">7. Statistics & ELO</a>
                    <a href="#roles">8. Roles & Security</a>
                    <a href="#settings">9. Theme Settings</a>
                    <a href="#easter-eggs" style="background: #fff5f5; color: #d63638;">10. Easter Eggs ✨</a>
                </div>
            </nav>

            <!-- SECTION: EVENTS -->
            <section id="events" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-calendar-alt"></span> 1. Events & Rankings
                </h2>
                <p>Manage tournaments and results. Every event updates global player statistics.</p>

                <h4 style="margin-bottom: 10px;">Custom Fields (ACF):</h4>
                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Field Name</th>
                            <th>Description / Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Place</strong></td>
                            <td>Link to a post in the <i>Places</i> CPT. Determines the venue.</td>
                        </tr>
                        <tr>
                            <td><strong>Date</strong></td>
                            <td>Date and time of the event (used for chronological ELO tracking).</td>
                        </tr>
                        <tr>
                            <td><strong>Facebook Link</strong></td>
                            <td>URL of the official Facebook event page.</td>
                        </tr>
                        <tr>
                            <td><strong>Rankings JSON</strong></td>
                            <td>Paste your scoring software JSON here. Used by the <strong>Populate</strong> button.</td>
                        </tr>
                        <tr>
                            <td><strong>Ranking (Repeater)</strong></td>
                            <td>Individual player results: Position, Pts, W-L-D, and linked Deck ID.</td>
                        </tr>
                        <tr>
                            <td><strong>Survey</strong></td>
                            <td>List of users who participated (used for attendance stats).
                                <div
                                    style="background: #e7f5fe; padding: 5px 10px; border-radius: 4px; margin-top: 5px; font-size: 13px; border-left: 3px solid #0073aa;">
                                    <strong>Tool:</strong> Use the <strong>Update Survey</strong> button to automatically
                                    sync players from the ranking table to the survey list.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-bottom: 10px;">Custom Tools (Metaboxes):</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 8px;">
                        <h5 style="margin-top: 0; color: #d63638;"><span class="dashicons dashicons-upload"></span> OCR
                            Ranking Generator</h5>
                        <p class="small">Upload a screenshot of the results. The system uses <i>Tesseract.js</i> to extract
                            text and format it into the <strong>Rankings JSON</strong> field.</p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 8px;">
                        <h5 style="margin-top: 0; color: #2271b1;"><span class="dashicons dashicons-admin-users"></span>
                            Sync Player Tool</h5>
                        <p class="small">After populating rankings, click "Sync Player". The site will match the text names
                            with registered <strong>Player</strong> users to link their ELO history automatically.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION: DECKS -->
            <section id="decks" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-saved"></span> 2. Decks Management
                </h2>
                <p>Decks are created by players but overseen by administrators.</p>

                <h4 style="margin-bottom: 10px;">Key Fields:</h4>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Commander / Partner:</strong> Dynamic fields to specify the deck's leaders.</li>
                    <li><strong>Featured Image:</strong> Used for the Commander's art.
                        <div
                            style="background: #e7f5fe; padding: 10px; border-radius: 4px; margin-top: 5px; border-left: 3px solid #0073aa;">
                            <strong>Note:</strong> If no Featured Image is provided, the system automatically pulls the
                            official card art from <strong>Scryfall</strong> based on the <i>Commander</i> or <i>Partner</i>
                            name.
                        </div>
                    </li>
                    <li><strong>Decklist URL:</strong> External link (Moxfield, deckstats.net, etc.).</li>
                    <li><strong>Decklist Text:</strong> Optional field for raw card list pasting.</li>
                </ul>
            </section>

            <!-- SECTION: ARTICLES -->
            <section id="articles" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-post"></span> 3. Articles (WordPress Posts)
                </h2>
                <p>Use standard WordPress <strong>Posts</strong> for news, announcements, and tournament reports.</p>

                <h4 style="margin-bottom: 10px;">Best Practices:</h4>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Categories:</strong> Organize your posts (e.g., "Tournament Reports").</li>
                    <li><strong>Featured Image:</strong> Always set a high-quality featured image for home page sliders.
                    </li>
                </ul>

                <h4 style="margin-bottom: 10px;">Enhanced Features for Posts:</h4>
                <div style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 8px; background: #fffcf5;">
                    <h5 style="margin-top: 0; color: #b58105;"><span class="dashicons dashicons-admin-tools"></span> Banned
                        Card Shortcode Generator</h5>
                    <p class="small">In the sidebar of the <strong>Post Editor</strong>, you can search for any card in the
                        ban list and click <strong>Add to Content</strong> to insert the interactive card box.</p>
                </div>
            </section>

            <!-- SECTION: PAGES & ACF BOXES -->
            <section id="pages" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-page"></span> 4. Pages & ACF Boxes
                </h2>
                <p>Pages are the structural backbone of the site. Most use the <strong>No Sidebar With ACF Boxes</strong>
                    template for modular content.</p>

                <h4 style="margin-bottom: 10px;">Master Content Structure (ACF):</h4>
                <p>The main tool for managing page content is the <strong>Sections</strong> field, which is a <i>Flexible
                        Content</i> area composed of individual rows.</p>

                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Section Component</th>
                            <th>Fields & Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Section Header</strong></td>
                            <td><strong>Title:</strong> A large centered heading that introduces the entire section.</td>
                        </tr>
                        <tr>
                            <td><strong>Box (Layouts)</strong></td>
                            <td>Inside each section, you add one or more <strong>Boxes</strong> from a list of predefined
                                layouts.</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-bottom: 10px;">Available Box Layouts:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>Title, Subtitle & Text</strong>
                        <p class="small">Classic text block. Perfect for "Welcome" messages or descriptions. <br><i>Fields:
                                Title, Subtitle, Text (WYSIWYG).</i></p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>Box FAQ</strong>
                        <p class="small">An interactive accordion list. <br><i>Fields: Titolo, FAQ Repeater (Domanda,
                                Risposta).</i></p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>Links List</strong>
                        <p class="small">A grid of cards linking to external or internal resources. <br><i>Fields: Links
                                Repeater (Icon, Title, Subtitle, URL).</i></p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>Action Button</strong>
                        <p class="small">A centered call-to-action button. <br><i>Fields: Icon, Label, Link.</i></p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>Title and 3 Cards</strong>
                        <p class="small">A fixed 3-column layout for features or services. <br><i>Fields: Title, Cards
                                Repeater (Icon, Title, Text).</i></p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 12px; border-radius: 6px; background: #fff;">
                        <strong>About List</strong>
                        <p class="small">For staff or team pages. Can link to WP Profiles. <br><i>Fields: People Repeater
                                (Profile, Icon, Title, Nickname, Subtitle, Text, URL).</i></p>
                    </div>
                </div>
            </section>

            <!-- SECTION: BANNED -->
            <section id="banned" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-dismiss"></span> 5. Banned Cards
                </h2>

                <h4 style="margin-bottom: 10px;">Managing the List:</h4>
                <p>Use the <i>Banned Cards</i> CPT. Each entry requires a name and a <strong>Scryfall Link</strong>.</p>
                <div
                    style="background: #e7f5fe; padding: 10px; border-radius: 4px; margin-top: 5px; border-left: 3px solid #0073aa;">
                    <strong>Image Fallback:</strong> The <i>Featured Image</i> should be the card scan. If you don't upload
                    one, the system will automatically fetch it from <strong>Scryfall</strong>.
                </div>

                <h4 style="margin-top: 20px; margin-bottom: 10px;">Shortcode Integration:</h4>
                <div style="background: #fdf6ec; padding: 20px; border-radius: 8px; border: 1px solid #faecd8;">
                    <p><strong>Banned Card Shortcode (Metabox):</strong> Located in the sidebar of the Banned Card editor.
                        It provides the unique code <code>[banned_card id="XX"]</code>.</p>
                </div>
            </section>

            <!-- SECTION: LEADERBOARDS -->
            <section id="leaderboards" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-awards"></span> 6. Leaderboards
                </h2>
                <p>Leaderboards represent the stabilized rankings for a specific competitive year.</p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Year:</strong> Select the target year for the ranking.</li>
                    <li><strong>Rankings JSON:</strong> Stores the finalized list of participants.
                        <div
                            style="background: #fff8e5; padding: 10px; border-radius: 4px; margin-top: 5px; border-left: 3px solid #ffb900;">
                            <strong>Crucial:</strong> This field is <strong>calculated manually</strong> by clicking the
                            <strong>Update Leaderboard</strong> button after selecting the year.
                        </div>
                    </li>
                </ul>
            </section>

            <!-- SECTION: STATS -->
            <section id="stats" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-chart-area"></span> 7. Statistics & ELO
                </h2>
                <p>Starting ELO is 1200. Stats are calculated based on synced player names in Event rankings.</p>

                <h4 style="margin-top: 25px; margin-bottom: 10px;">ELO Calculation Logic:</h4>
                <p>The system uses a modified ELO formula that accounts for match results and overall final position in the
                    ranking:</p>
                <div
                    style="background: #2d333b; padding: 20px; border-radius: 8px; border: 1px solid #444c56; margin-top: 15px;">
                    <pre
                        style="margin: 0; color: #adbac7; font-size: 13px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; overflow-x: auto;">
            // Core Calculation Snippet
            $actual_score = $wins + ($draws * 0.5);
            $expected_score_rate = 1 / (1 + pow(10, ($avg_elo - $current_elo) / 400));
            $expected_score = $expected_score_rate * $games_played;
            $k_factor = 32 / ($games_played);

            // Position Adjustment
            $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
            $rank_score = ($total_players > 1) ? ($total_players - $pos) / ($total_players - 1) : 1;
            $position_adjustment = 20 * ($rank_score - 0.5);

            // New ELO Result
            $new_elo = $current_elo + $k_factor * ($actual_score - $expected_score) + $position_adjustment;</pre>
                </div>
            </section>

            <!-- SECTION: ROLES -->
            <section id="roles" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-shield"></span> 8. Roles & Security
                </h2>
                <p><strong>Administrator:</strong> Unrestricted access. <br> <strong>Player:</strong> Limited to personal
                    Deck management with automatic redirection from restricted areas.</p>
            </section>

            <!-- SECTION: SETTINGS -->
            <section id="settings" style="margin-bottom: 40px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-generic"></span> 9. Theme Settings
                </h2>
                <p>Configure the site's aesthetics and technical mapping under <strong>Appearance > Theme Settings</strong>.
                </p>

                <h4 style="margin-bottom: 10px;">Configuration Options:</h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Setting Name</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Active Theme</strong></td>
                            <td>Switches the global design (e.g., Default, Vaporwave, Lost Wood).</td>
                        </tr>
                        <tr>
                            <td><strong>Deck Editor Page</strong></td>
                            <td>Links the custom Deck Editor template to a site page.</td>
                        </tr>
                        <tr>
                            <td><strong>Profile Editor Page</strong></td>
                            <td>Links the User Profile management template.</td>
                        </tr>
                        <tr>
                            <td><strong>Statistics Page</strong></td>
                            <td>Links the global ELO and performance tracking page.</td>
                        </tr>
                        <tr>
                            <td><strong>Login/Register Page</strong></td>
                            <td>Sets the destination for the custom authentication screens.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- SECTION: EASTER EGGS -->
            <section id="easter-eggs" style="margin-bottom: 40px;">
                <h2
                    style="color: #d63638; background: #fff5f5; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-appearance"></span> 10. Easter Eggs & Hidden Features
                </h2>
                <p>Curated secret animations and interactions for the LPDH community.</p>

                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                    <!-- Point 1 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #1e40af;">1. Counterspell Search</h5>
                        <p class="small">Searching for <strong>"counterspell"</strong> or <strong>"contromagia"</strong> in
                            the site search bar triggers a blue burst visual effect.</p>
                    </div>

                    <!-- Point 2 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #7c2d12;">2. Daybound / Nightbound</h5>
                        <p class="small">Pressing the keys <strong>"D"</strong> then <strong>"N"</strong> in sequence
                            toggles the site theme with a sun/moon rising animation.</p>
                    </div>

                    <!-- Point 3 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #d63638;">3. Forbidden Cards</h5>
                        <p class="small">Searching for "expensive" cards (like <strong>Black Lotus</strong>) triggers a
                            rejection screen and a random redirect.</p>
                    </div>

                    <!-- Point 4 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #111827;">4. Console Lives</h5>
                        <p class="small">Opening the browser's <strong>Inspect Console</strong> reveals a greeting for the
                            LPDH gods Gut and Balmor.</p>
                    </div>

                    <!-- Point 5 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #b45309;">5. Commander Tax</h5>
                        <p class="small">In profile pages, clicking the <strong>User Avatar</strong> adds +2 "Commander Tax"
                            to a
                            floating badge.</p>
                    </div>

                    <!-- Point 6 -->
                    <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                        <h5 style="margin-top: 0; color: #2563eb;">6. Countered Commander</h5>
                        <p class="small">Clicking the <strong>Commander Image</strong> on a deck page reveals one of 79
                            different Counterspell artworks at random.</p>
                    </div>
                </div>
            </section>

            <footer
                style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #f0f0f1; text-align: center; font-style: italic; color: #646970;">
                <p>Document updated: <?php echo date('F j, Y'); ?> - Version 1.8.0</p>
            </footer>
        </div>
    </div>

    <style>
        .help-guide-wrap .wp-list-table th {
            font-weight: 700;
            color: #2c3338;
        }

        .help-guide-nav a {
            display: block;
            padding: 12px 15px;
            background: #f6f7f7;
            color: #2271b1;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .help-guide-nav a:hover {
            background: #fff;
            border-color: #2271b1;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .help-guide-container h2 {
            margin-top: 0;
        }

        .small {
            font-size: 13px;
            color: #646970;
        }
    </style>
    <?php
}
