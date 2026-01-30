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
                    <a href="#achievements" style="font-weight: bold; color: #2271b1;">3. Achievements System ⭐</a>
                    <a href="#roulette" style="color: #6b21a8;">4. Commander Roulette</a>
                    <a href="#articles">5. Articles & News</a>
                    <a href="#pages">6. Pages & ACF Boxes</a>
                    <a href="#banned">7. Banned Cards</a>
                    <a href="#leaderboards">8. Leaderboards</a>
                    <a href="#stats">9. Statistics & ELO</a>
                    <a href="#profiles">10. Profiles & Preferences</a>
                    <a href="#roles">11. Roles & Security</a>
                    <a href="#settings">12. Theme Settings</a>
                    <a href="#easter-eggs" style="background: #fff5f5; color: #d63638;">13. Easter Eggs ✨</a>
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
                    <li><strong>Private Deck:</strong>
                        <div
                            style="background: #fff8e5; padding: 10px; border-radius: 4px; margin-top: 5px; border-left: 3px solid #ffb900;">
                            <strong>Logic:</strong> If enabled, the deck will only be visible to the author and site
                            administrators. It will be hidden from public lists and search results.
                        </div>
                    </li>
                </ul>
            </section>

            <!-- SECTION: ACHIEVEMENTS -->
            <section id="achievements" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-awards"></span> 3. Achievements System
                </h2>
                <p>Track player milestones, deck variety, and seasonal competitive effort.</p>

                <h4 style="margin-bottom: 10px;">Managing Achievements (CPT):</h4>
                <p>Every achievement is a post in the <i>Achievements</i> CPT. Key ACF fields include:</p>
                <table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Field Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Condition Type</strong></td>
                            <td>The metric to track (Wins, Events, Elo, Specific Commander, etc.).</td>
                        </tr>
                        <tr>
                            <td><strong>Operator / Value</strong></td>
                            <td>Example: <i>Wins (Condition) > (Operator) 10 (Value)</i>.</td>
                        </tr>
                        <tr>
                            <td><strong>Yearly / Year</strong></td>
                            <td>If enabled, the badge is tied to a specific year and shows a ribbon overlay.</td>
                        </tr>
                        <tr>
                            <td><strong>Icon Selection</strong></td>
                            <td>Choose between a simple FontAwesome icon or a custom Uploaded Image.</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-bottom: 10px;">Special Admin Tools:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 8px;">
                        <h5 style="margin-top: 0; color: #2271b1;"><span class="dashicons dashicons-admin-users"></span>
                            Manage User Achievements</h5>
                        <p class="small">Accessed via the <strong>Achievements menu</strong>. Search for a user to see a
                            toggle-grid of all achievements. You can manually grant/revoke badges or use the
                            <strong>"Check"</strong> microscope to verify if the user meets the requirements based on their
                            current stats.
                        </p>
                    </div>
                    <div style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 8px;">
                        <h5 style="margin-top: 0; color: #46b450;"><span class="dashicons dashicons-calendar"></span>
                            Duplicate for Next Year</h5>
                        <p class="small">In the <strong>Achievements List</strong>, select badges and use Bulk Actions >
                            <i>Duplicate for next year</i>. The system clones the badges, increments the year field, and
                            updates title automatically.
                        </p>
                    </div>
                </div>

                <div
                    style="background: #e7f5fe; border-left: 5px solid #0073aa; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <strong>Automatic Unlocking:</strong> Achievements are checked and granted automatically whenever an
                    event is synchronized via the <strong>Sync Player</strong> tool. Manual overrides are always possible
                    through the <i>Manage Achievements</i> page.
                </div>
            </section>

            <!-- SECTION: ROULETTE -->
            <section id="roulette" style="margin-bottom: 60px;">
                <h2
                    style="color: #6b21a8; background: #f3e8ff; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-update"></span> 4. Commander Roulette
                </h2>
                <p>An interactive feature where players receive a random Commander suggestion from a curated list.</p>

                <h4 style="margin-bottom: 10px;">Key Logic:</h4>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Rate Limiting:</strong> Regular players receive <strong>3 tokens per day</strong>. A counter
                        is shown next to the spin button.</li>
                    <li><strong>Admin Bypass:</strong> Site administrators have <strong>infinite tokens</strong> for testing
                        purposes.</li>
                    <li><strong>Pool Selection:</strong> The system picks from <strong>Uncommon Legendary Creatures</strong>
                        (LPDH legal), automatically excluding cards currently in the <i>Banned List</i> CPT.</li>
                </ul>
            </section>

            <!-- SECTION: ARTICLES -->
            <section id="articles" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-post"></span> 5. Articles (WordPress Posts)
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
                    <span class="dashicons dashicons-admin-page"></span> 6. Pages & ACF Boxes
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
                    <span class="dashicons dashicons-dismiss"></span> 7. Banned Cards
                </h2>

                <h4 style="margin-bottom: 10px;">Managing the List:</h4>
                <p>Use the <i>Banned Cards</i> CPT. Each entry requires a name and a <strong>Scryfall Link</strong>.</p>
                <div
                    style="background: #fffcf5; padding: 15px; border: 1px solid #faecd8; border-radius: 6px; margin-bottom: 15px;">
                    <strong>🚀 Tool: Admin Autocomplete</strong><br>
                    When typing a card name in the editor, the system suggests official names from Scryfall. Selecting one
                    **automatically** populates the Scryfall URL and basic card metadata.
                </div>
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
                    <span class="dashicons dashicons-awards"></span> 8. Leaderboards
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
                    <span class="dashicons dashicons-chart-area"></span> 9. Statistics & ELO
                </h2>
                <p>Starting ELO is <?php echo LPDH_DEFAULT_ELO; ?>. Stats are calculated based on synced player names in
                    Event rankings.</p>

                <h4 style="margin-top: 25px; margin-bottom: 10px;">ELO Calculation Logic:</h4>
                <p>The system uses a modified ELO formula that accounts for match results and overall final position in the
                    ranking:</p>
                <pre
                    style="margin: 0; color: #adbac7; font-size: 13px; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; overflow-x: auto;">
                                                // Centralized ELO Calculation logic in functions.php
                                                function lpdh_calculate_elo($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players) {
                                                    $games_played = $wins + $draws + $losses;
                                            
                                                    if ($games_played <= 0) {
                                                        return array(
                                                            'new_elo' => $current_elo,
                                                            'k_factor' => 0,
                                                            'expected_score' => 0,
                                                            'position_adjustment' => 0
                                                        );
                                                    }

                                                    $actual_score = $wins + ($draws * 0.5);
                                                    $expected_score_rate = 1 / (1 + pow(10, ($avg_elo - $current_elo) / 400));
                                                    $expected_score = $expected_score_rate * $games_played;

                                                    // K-factor logic based on theme setting
                                                    $k_factor_divide = get_option('lpdh_elo_k_factor_divide_by_game', 1);
                                                    $k_factor = ($k_factor_divide) ? 32 / $games_played : 32;

                                                    // Position Adjustment (rewarding top finishes)
                                                    $rank_score = ($total_players > 1) ? ($total_players - $pos) / ($total_players - 1) : 1;
                                                    $position_adjustment = 20 * ($rank_score - 0.5);

                                                    $new_elo = $current_elo + $k_factor * ($actual_score - $expected_score) + $position_adjustment;

                                                    return array(
                                                        'new_elo' => $new_elo,
                                                        'k_factor' => $k_factor,
                                                        'expected_score' => $expected_score,
                                                        'position_adjustment' => $position_adjustment
                                                    );
                                                }</pre>
            </section>

            <!-- SECTION: PROFILES -->
            <section id="profiles" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-users"></span> 10. Profiles & Preferences
                </h2>
                <p>Manage user visibility, achievement privacy, and data settings.</p>

                <h4 style="margin-bottom: 10px;">User Security & Privacy:</h4>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Secret Achievements:</strong> By default, locked achievements show titles/desc. However,
                        when viewing **another user's profile**, these are masked as "Secret Achievement" unless the visitor
                        has also unlocked them.</li>
                    <li><strong>Private Profile:</strong> Hides the User Detail page from the public.</li>
                    <li><strong>Account Deletion:</strong> Permanent removal of profile and assigned decks.</li>
                </ul>
            </section>

            <!-- SECTION: ROLES -->
            <section id="roles" style="margin-bottom: 60px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-shield"></span> 11. Roles & Security
                </h2>
                <p><strong>Administrator:</strong> Full access. <br> <strong>Player:</strong> Limited to deck management.
                </p>
            </section>

            <!-- SECTION: SETTINGS -->
            <section id="settings" style="margin-bottom: 40px;">
                <h2
                    style="color: #2271b1; background: #f6f7f7; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-generic"></span> 12. Theme Settings
                </h2>
                <p>Configure aesthetics and mapping under <strong>Appearance > Theme Settings</strong>.</p>

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
                            <td>Switches global design (Default, Vaporwave, Lost Wood, etc.).</td>
                        </tr>
                        <tr>
                            <td><strong>Cookie Consent</strong></td>
                            <td>Configure the "Cookie Bar" text and branding for GDPR/privacy compliance.</td>
                        </tr>
                        <tr>
                            <td><strong>Social Links</strong></td>
                            <td>Configure Instagram, Discord, Facebook, and X links for the footer.</td>
                        </tr>
                        <tr>
                            <td><strong>Calculate ELO: K Factor</strong></td>
                            <td>Toggle whether to divide the K-factor by the number of games played (32 / games) or use a
                                flat value (32).</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- SECTION: EASTER EGGS -->
            <section id="easter-eggs" style="margin-bottom: 40px;">
                <h2
                    style="color: #d63638; background: #fff5f5; padding: 10px 15px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <span class="dashicons dashicons-admin-appearance"></span> 13. Easter Eggs & Hidden Features
                </h2>
                <p>Curated secret animations and interactions for the LPDH community.</p>

                <details
                    style="border: 1px solid #fecaca; border-radius: 8px; background: #fff5f5; overflow: hidden; margin-top: 20px;">
                    <summary
                        style="padding: 15px; cursor: pointer; color: #d63638; font-weight: bold; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                        <span>Click to view Easter Eggs (Spoiler Warning!)</span>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </summary>
                    <div style="padding: 20px; background: #fff; border-top: 1px solid #fecaca;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                            <!-- Point 1 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #1e40af;">1. Counterspell Search</h5>
                                <p class="small">Searching for <strong>"counterspell"</strong> or
                                    <strong>"contromagia"</strong> in
                                    the site search bar triggers a blue burst visual effect.
                                </p>
                            </div>

                            <!-- Point 2 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #7c2d12;">2. Daybound / Nightbound</h5>
                                <p class="small">Pressing the keys <strong>"D"</strong> then <strong>"N"</strong> in
                                    sequence
                                    toggles the site theme with a sun/moon rising animation.</p>
                            </div>

                            <!-- Point 3 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #d63638;">3. Forbidden Cards</h5>
                                <p class="small">Searching for "expensive" cards (like <strong>Black Lotus</strong>)
                                    triggers a
                                    rejection screen and a random redirect.</p>
                            </div>

                            <!-- Point 4 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #111827;">4. Console Lives</h5>
                                <p class="small">Opening the browser's <strong>Inspect Console</strong> reveals a greeting
                                    for the
                                    LPDH gods Gut and Balmor.</p>
                            </div>

                            <!-- Point 5 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #b45309;">5. Commander Tax</h5>
                                <p class="small">In profile pages, clicking the <strong>User Avatar</strong> adds +2
                                    "Commander Tax"
                                    to a floating badge.</p>
                            </div>

                            <!-- Point 6 -->
                            <div style="border: 1px solid #fecaca; padding: 15px; border-radius: 8px; background: #fff;">
                                <h5 style="margin-top: 0; color: #2563eb;">6. Countered Commander</h5>
                                <p class="small">Clicking the <strong>Commander Image</strong> on a deck page reveals a
                                    random artwork from various <strong>Counterspells</strong> or <strong>Mana
                                        Tithes</strong>.</p>
                            </div>
                        </div>
                    </div>
                </details>
            </section>

            <footer
                style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #f0f0f1; text-align: center; font-style: italic; color: #646970;">
                <p>Document updated: <?php echo date('F j, Y'); ?> - Version 1.9.6</p>
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

        details summary::-webkit-details-marker {
            display: none;
        }

        details[open] summary .dashicons-arrow-down-alt2 {
            transform: rotate(180deg);
        }

        details summary .dashicons-arrow-down-alt2 {
            transition: transform 0.2s ease;
        }
    </style>
    <?php
}
