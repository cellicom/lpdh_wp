<?php
/**
 * Admin Help Guide Page - GitHub Dark Theme Version
 *
 * @package Bootscore Child
 * @version 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Register the Help Guide admin page as submenu under LPDH
 */
function register_help_guide_admin_page()
{
    add_submenu_page(
        'lpdh-main',
        __('Help Guide', 'text_domain'),
        __('Help Guide', 'text_domain'),
        'view_lpdh_help_guide',
        'lpdh-help-guide',
        'render_help_guide_page'
    );
}
add_action('admin_menu', 'register_help_guide_admin_page', 10);

/**
 * Render the Help Guide page content
 */
function render_help_guide_page()
{
    ?>
    <div class="wrap help-guide-wrap">
        <h1 class="hg-main-title">
            <span class="dashicons dashicons-editor-help"></span>
            <?php _e('LPDH Advanced Management Guide', 'text_domain'); ?>
        </h1>

        <div class="help-guide-container">

            <div class="hg-alert hg-alert-info">
                <h3><span class="dashicons dashicons-info"></span> Admin Overview</h3>
                <p>This site uses <strong>Custom Post Types (CPT)</strong> and <strong>Advanced Custom Fields (ACF)</strong> to manage tournament data. This guide detail every field and tool available to you as an Administrator.</p>
            </div>

            <nav class="help-guide-nav">
                <h3><?php _e('Quick Navigation', 'text_domain'); ?></h3>
                <div class="hg-nav-grid">
                    <a href="#events">1. Events & Rankings</a>
                    <a href="#decks">2. Decks Management</a>
                    <a href="#achievements" class="hg-highlight">3. Achievements System ⭐</a>
                    <a href="#roulette" class="hg-purple">4. Commander Roulette</a>
                    <a href="#articles">5. Articles & News</a>
                    <a href="#pages">6. Pages & ACF Boxes</a>
                    <a href="#banned">7. Banned Cards</a>
                    <a href="#leaderboards">8. Leaderboards</a>
                    <a href="#stats">9. Statistics & ELO</a>
                    <a href="#instagram" class="hg-pink">10. Instagram Generator 📸</a>
                    <a href="#profiles">11. Profiles & Preferences</a>
                    <a href="#roles">12. Roles & Security</a>
                    <a href="#settings">13. Theme Settings</a>
                    <a href="#emails">14. Email System 📧</a>
                    <a href="#easter-eggs" class="hg-red">15. Easter Eggs ✨</a>
                </div>
            </nav>

            <!-- SECTION: EVENTS -->
            <section id="events" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-calendar-alt"></span> 1. Events & Rankings
                </h2>
                <p>Manage tournaments and results. Every event updates global player statistics.</p>

                <h4>Custom Fields (ACF):</h4>
                <table class="wp-list-table widefat fixed striped hg-table">
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
                                <div class="hg-tip">
                                    <strong>Tool:</strong> Use the <strong>Update Survey</strong> button to automatically sync players from the ranking table to the survey list.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h4>Custom Tools (Metaboxes):</h4>
                <div class="hg-grid-2">
                    <div class="hg-card">
                        <h5 class="hg-text-red"><span class="dashicons dashicons-upload"></span> OCR Ranking Generator</h5>
                        <p class="small">Upload a screenshot of the results. The system uses <i>Tesseract.js</i> to extract text and format it into the <strong>Rankings JSON</strong> field.</p>
                    </div>
                    <div class="hg-card">
                        <h5 class="hg-text-blue"><span class="dashicons dashicons-admin-users"></span> Sync Player Tool</h5>
                        <p class="small">After populating rankings, click "Sync Player". The site will match the text names with registered <strong>Player</strong> users to link their ELO history automatically.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION: DECKS -->
            <section id="decks" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-saved"></span> 2. Decks Management
                </h2>
                <p>Decks are created by players but overseen by administrators.</p>

                <h4>Key Fields:</h4>
                <ul class="hg-list">
                    <li><strong>Commander / Partner:</strong> Dynamic fields to specify the deck's leaders.</li>
                    <li><strong>Featured Image:</strong> Used for the Commander's art.
                        <div class="hg-tip">
                            <strong>Note:</strong> If no Featured Image is provided, the system automatically pulls the official card art from <strong>Scryfall</strong> based on the <i>Commander</i> or <i>Partner</i> name.
                        </div>
                    </li>
                    <li><strong>Decklist URL:</strong> External link (Moxfield, deckstats.net, etc.).</li>
                    <li><strong>Decklist Text:</strong> Optional field for raw card list pasting.</li>
                    <li><strong>Private Deck:</strong>
                        <div class="hg-warning">
                            <strong>Logic:</strong> If enabled, the deck will only be visible to the author and site administrators. It will be hidden from public lists and search results.
                        </div>
                    </li>
                </ul>

                <h4>Deck Creation Tools:</h4>
                <div class="hg-box hg-box-yellow">
                    <strong>🚀 Tool: Admin Autocomplete</strong><br>
                    When typing a card name in the <strong>Commander</strong> or <strong>Partner</strong> fields during deck creation, the system suggests official names from Scryfall. Selecting one **automatically** populates the Scryfall URL and basic card metadata.
                </div>
            </section>

            <!-- SECTION: ACHIEVEMENTS -->
            <section id="achievements" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-awards"></span> 3. Achievements System
                </h2>
                <p>Track player milestones, deck variety, and seasonal competitive effort.</p>

                <h4>Managing Achievements (CPT):</h4>
                <p>Every achievement is a post in the <i>Achievements</i> CPT. Key ACF fields include:</p>
                <table class="wp-list-table widefat fixed striped hg-table">
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

                <h4>Special Admin Tools:</h4>
                <div class="hg-grid-2">
                    <div class="hg-card">
                        <h5 class="hg-text-blue"><span class="dashicons dashicons-admin-users"></span> Manage User Achievements</h5>
                        <p class="small">Accessed via the <strong>Achievements menu</strong>. Search for a user to see a toggle-grid of all achievements. You can manually grant/revoke badges or use the <strong>"Check"</strong> microscope to verify if the user meets the requirements based on their current stats.</p>
                    </div>
                    <div class="hg-card">
                        <h5 class="hg-text-green"><span class="dashicons dashicons-calendar"></span> Duplicate for Next Year</h5>
                        <p class="small">In the <strong>Achievements List</strong>, select badges and use Bulk Actions > <i>Duplicate for next year</i>. The system clones the badges, increments the year field, and updates title automatically.</p>
                    </div>
                </div>

                <div class="hg-tip hg-mt-20">
                    <strong>Automatic Unlocking:</strong> Achievements are checked and granted automatically whenever an event is synchronized via the <strong>Sync Player</strong> tool. Manual overrides are always possible through the <i>Manage Achievements</i> page.
                </div>
            </section>

            <!-- SECTION: ROULETTE -->
            <section id="roulette" class="hg-section">
                <h2 class="hg-section-title hg-title-purple">
                    <span class="dashicons dashicons-update"></span> 4. Commander Roulette
                </h2>
                <p>An interactive feature where players receive a random Commander suggestion from a curated list.</p>

                <h4>Key Logic:</h4>
                <ul class="hg-list">
                    <li><strong>Rate Limiting:</strong> Regular players receive <strong>3 tokens per day</strong>. A counter is shown next to the spin button.</li>
                    <li><strong>Admin Bypass:</strong> Site administrators have <strong>infinite tokens</strong> for testing purposes.</li>
                    <li><strong>Pool Selection:</strong> The system picks from <strong>Uncommon Legendary Creatures</strong> (LPDH legal), automatically excluding cards currently in the <i>Banned List</i> CPT.</li>
                </ul>
            </section>

            <!-- SECTION: ARTICLES -->
            <section id="articles" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-admin-post"></span> 5. Articles (WordPress Posts)
                </h2>
                <p>Use standard WordPress <strong>Posts</strong> for news, announcements, and tournament reports.</p>

                <h4>Best Practices:</h4>
                <ul class="hg-list">
                    <li><strong>Categories:</strong> Organize your posts (e.g., "Tournament Reports").</li>
                    <li><strong>Featured Image:</strong> Always set a high-quality featured image for home page sliders.</li>
                </ul>

                <h4>Enhanced Features for Posts:</h4>
                <div class="hg-box hg-box-yellow">
                    <h5 class="hg-text-yellow"><span class="dashicons dashicons-admin-tools"></span> Banned Card Shortcode Generator</h5>
                    <p class="small">In the sidebar of the <strong>Post Editor</strong>, you can search for any card in the ban list and click <strong>Add to Content</strong> to insert the interactive card box.</p>
                </div>
            </section>

            <!-- SECTION: PAGES & ACF BOXES -->
            <section id="pages" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-admin-page"></span> 6. Pages & ACF Boxes
                </h2>
                <p>Pages are the structural backbone of the site. Most use the <strong>No Sidebar With ACF Boxes</strong> template for modular content.</p>

                <h4>Master Content Structure (ACF):</h4>
                <p>The main tool for managing page content is the <strong>Sections</strong> field, which is a <i>Flexible Content</i> area composed of individual rows.</p>

                <table class="wp-list-table widefat fixed striped hg-table">
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
                            <td>Inside each section, you add one or more <strong>Boxes</strong> from a list of predefined layouts.</td>
                        </tr>
                    </tbody>
                </table>

                <h4>Available Box Layouts:</h4>
                <div class="hg-grid-3">
                    <div class="hg-card-mini">
                        <strong>Title, Subtitle & Text</strong>
                        <p class="small">Classic text block. Perfect for "Welcome" messages or descriptions. <br><i>Fields: Title, Subtitle, Text (WYSIWYG).</i></p>
                    </div>
                    <div class="hg-card-mini">
                        <strong>Box FAQ</strong>
                        <p class="small">An interactive accordion list. <br><i>Fields: Titolo, FAQ Repeater (Domanda, Risposta).</i></p>
                    </div>
                    <div class="hg-card-mini">
                        <strong>Links List</strong>
                        <p class="small">A grid of cards linking to external or internal resources. <br><i>Fields: Links Repeater (Icon, Title, Subtitle, URL).</i></p>
                    </div>
                    <div class="hg-card-mini">
                        <strong>Action Button</strong>
                        <p class="small">A centered call-to-action button. <br><i>Fields: Icon, Label, Link.</i></p>
                    </div>
                    <div class="hg-card-mini">
                        <strong>Title and 3 Cards</strong>
                        <p class="small">A fixed 3-column layout for features or services. <br><i>Fields: Title, Cards Repeater (Icon, Title, Text).</i></p>
                    </div>
                    <div class="hg-card-mini">
                        <strong>About List</strong>
                        <p class="small">For staff or team pages. Can link to WP Profiles. <br><i>Fields: People Repeater (Profile, Icon, Title, Nickname, Subtitle, Text, URL).</i></p>
                    </div>
                </div>
            </section>

             <!-- SECTION: BANNED -->
             <section id="banned" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-dismiss"></span> 7. Banned Cards
                </h2>

                <h4>Managing the List:</h4>
                <p>Use the <i>Banned Cards</i> CPT. Each entry requires a name and a <strong>Scryfall Link</strong>.</p>
                <div class="hg-tip">
                    <strong>Image Fallback:</strong> The <i>Featured Image</i> should be the card scan. If you don't upload one, the system will automatically fetch it from <strong>Scryfall</strong>.
                </div>

                <h4 style="margin-top: 20px;">Shortcode Integration:</h4>
                <div class="hg-box hg-box-orange">
                    <p><strong>Banned Card Shortcode (Metabox):</strong> Located in the sidebar of the Banned Card editor. It provides the unique code <code>[banned_card id="XX"]</code>.</p>
                </div>
            </section>

            <!-- SECTION: LEADERBOARDS -->
            <section id="leaderboards" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-awards"></span> 8. Leaderboards
                </h2>
                <p>Leaderboards represent the stabilized rankings for a specific competitive year.</p>
                <ul class="hg-list">
                    <li><strong>Year:</strong> Select the target year for the ranking.</li>
                    <li><strong>Rankings JSON:</strong> Stores the finalized list of participants.
                        <div class="hg-warning">
                            <strong>Crucial:</strong> This field is <strong>calculated manually</strong> by clicking the <strong>Update Leaderboard</strong> button after selecting the year.
                        </div>
                    </li>
                </ul>
            </section>

            <!-- SECTION: STATS -->
            <section id="stats" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-chart-area"></span> 9. Statistics & ELO
                </h2>
                <p>Starting ELO is <?php echo LPDH_DEFAULT_ELO; ?>. Stats are calculated based on synced player names in Event rankings.</p>

                <h4 style="margin-top: 25px;">ELO Calculation Logic:</h4>
                <p>The system uses a modified ELO formula that accounts for match results and overall final position in the ranking:</p>
                <div class="hg-code-block">
                    <pre><span style="color: #8b949e;">/**
 * Calculate ELO for a player based on match results and tournament position.
 *
 * @param float $current_elo Current ELO of the player.
 * @param int $wins Number of wins in the event.
 * @param int $draws Number of draws in the event.
 * @param int $losses Number of losses in the event.
 * @param float $avg_elo Average ELO of the event participants.
 * @param int $pos Final position of the player in the event.
 * @param int $total_players Total number of players in the event.
 * @return array Array containing 'new_elo', 'k_factor', 'expected_score', and 'position_adjustment'.
 */</span>
<span style="color: #ff7b72;">function</span> <span style="color: #d2a8ff;">lpdh_calculate_elo</span>(<span style="color: #79c0ff;">$current_elo</span>, <span style="color: #79c0ff;">$wins</span>, <span style="color: #79c0ff;">$draws</span>, <span style="color: #79c0ff;">$losses</span>, <span style="color: #79c0ff;">$avg_elo</span>, <span style="color: #79c0ff;">$pos</span>, <span style="color: #79c0ff;">$total_players</span>)
{
    <span style="color: #79c0ff;">$games_played</span> = <span style="color: #79c0ff;">$wins</span> + <span style="color: #79c0ff;">$draws</span> + <span style="color: #79c0ff;">$losses</span>;

    <span style="color: #ff7b72;">if</span> (<span style="color: #79c0ff;">$games_played</span> <= <span style="color: #79c0ff;">0</span>) {
        <span style="color: #ff7b72;">return</span> <span style="color: #ff7b72;">array</span>(
            <span style="color: #a5d6ff;">'new_elo'</span> => <span style="color: #79c0ff;">$current_elo</span>,
            <span style="color: #a5d6ff;">'k_factor'</span> => <span style="color: #79c0ff;">0</span>,
            <span style="color: #a5d6ff;">'expected_score'</span> => <span style="color: #79c0ff;">0</span>,
            <span style="color: #a5d6ff;">'position_adjustment'</span> => <span style="color: #79c0ff;">0</span>
        );
    }

    <span style="color: #79c0ff;">$elo_result</span> = <span style="color: #d2a8ff;">lpdh_perform_elo_math</span>(<span style="color: #79c0ff;">$current_elo</span>, <span style="color: #79c0ff;">$wins</span>, <span style="color: #79c0ff;">$draws</span>, <span style="color: #79c0ff;">$losses</span>, <span style="color: #79c0ff;">$avg_elo</span>, <span style="color: #79c0ff;">$pos</span>, <span style="color: #79c0ff;">$total_players</span>);

    <span style="color: #ff7b72;">return</span> <span style="color: #79c0ff;">$elo_result</span>;
}

<span style="color: #8b949e;">/**
 * Internal helper for ELO math to keep lpdh_calculate_elo clean.
 */</span>
<span style="color: #ff7b72;">function</span> <span style="color: #d2a8ff;">lpdh_perform_elo_math</span>(<span style="color: #79c0ff;">$current_elo</span>, <span style="color: #79c0ff;">$wins</span>, <span style="color: #79c0ff;">$draws</span>, <span style="color: #79c0ff;">$losses</span>, <span style="color: #79c0ff;">$avg_elo</span>, <span style="color: #79c0ff;">$pos</span>, <span style="color: #79c0ff;">$total_players</span>)
{
    <span style="color: #79c0ff;">$games_played</span> = <span style="color: #79c0ff;">$wins</span> + <span style="color: #79c0ff;">$draws</span> + <span style="color: #79c0ff;">$losses</span>;
    <span style="color: #79c0ff;">$actual_score</span> = <span style="color: #79c0ff;">$wins</span> + (<span style="color: #79c0ff;">$draws</span> * <span style="color: #79c0ff;">0.5</span>);
    <span style="color: #79c0ff;">$expected_score_rate</span> = <span style="color: #79c0ff;">1</span> / (<span style="color: #79c0ff;">1</span> + <span style="color: #d2a8ff;">pow</span>(<span style="color: #79c0ff;">10</span>, (<span style="color: #79c0ff;">$avg_elo</span> - <span style="color: #79c0ff;">$current_elo</span>) / <span style="color: #79c0ff;">400</span>));
    <span style="color: #79c0ff;">$expected_score</span> = <span style="color: #79c0ff;">$expected_score_rate</span> * <span style="color: #79c0ff;">$games_played</span>;

    <span style="color: #8b949e;">// K-factor logic based on theme setting</span>
    <span style="color: #79c0ff;">$k_factor_divide</span> = <span style="color: #d2a8ff;">get_option</span>(<span style="color: #a5d6ff;">'lpdh_elo_k_factor_divide_by_game'</span>, <span style="color: #79c0ff;">1</span>);
    <span style="color: #79c0ff;">$k_factor</span> = (<span style="color: #79c0ff;">$k_factor_divide</span>) ? <span style="color: #79c0ff;">32</span> / <span style="color: #79c0ff;">$games_played</span> : <span style="color: #79c0ff;">32</span>;

    <span style="color: #8b949e;">// Position Adjustment (rewarding top finishes)</span>
    <span style="color: #79c0ff;">$rank_score</span> = (<span style="color: #79c0ff;">$total_players</span> > <span style="color: #79c0ff;">1</span>) ? (<span style="color: #79c0ff;">$total_players</span> - <span style="color: #79c0ff;">$pos</span>) / (<span style="color: #79c0ff;">$total_players</span> - <span style="color: #79c0ff;">1</span>) : <span style="color: #79c0ff;">1</span>;
    <span style="color: #79c0ff;">$position_adjustment</span> = <span style="color: #79c0ff;">20</span> * (<span style="color: #79c0ff;">$rank_score</span> - <span style="color: #79c0ff;">0.5</span>);

    <span style="color: #79c0ff;">$new_elo</span> = <span style="color: #79c0ff;">$current_elo</span> + <span style="color: #79c0ff;">$k_factor</span> * (<span style="color: #79c0ff;">$actual_score</span> - <span style="color: #79c0ff;">$expected_score</span>) + <span style="color: #79c0ff;">$position_adjustment</span>;

    <span style="color: #ff7b72;">return</span> <span style="color: #ff7b72;">array</span>(
        <span style="color: #a5d6ff;">'new_elo'</span> => <span style="color: #79c0ff;">$new_elo</span>,
        <span style="color: #a5d6ff;">'k_factor'</span> => <span style="color: #79c0ff;">$k_factor</span>,
        <span style="color: #a5d6ff;">'expected_score'</span> => <span style="color: #79c0ff;">$expected_score</span>,
        <span style="color: #a5d6ff;">'position_adjustment'</span> => <span style="color: #79c0ff;">$position_adjustment</span>
    );
}</pre>
                </div>
            </section>

            <!-- SECTION: INSTAGRAM GENERATOR -->
            <section id="instagram" class="hg-section">
                <h2 class="hg-section-title hg-title-pink">
                    <span class="dashicons dashicons-instagram"></span> 10. Instagram Generator
                </h2>
                <p>Powerful tool to create promotional images for social media directly from event data.</p>

                <h4>Accessing the Generator:</h4>
                <p>Go to any <strong>Event</strong> post and look for the <i>Instagram Generator</i> button in the admin sidebar or frontend event page.</p>

                <table class="wp-list-table widefat fixed striped hg-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Selector Field</th>
                            <th>Features & Visual Logic</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Select Theme</strong></td>
                            <td>
                                🏰 <strong>Epic Fantasy:</strong> Classic template with dark parchment and ornate frames.<br>
                                🌸 <strong>Vaporwave:</strong> Retro-neon style with custom backgrounds and glow effects.<br>
                                💚 <strong>Vaporwave Green:</strong> Specialized neon-green variant for specific community branding.<br>
                                🌲 <strong>Lost Wood:</strong> Forest-themed aesthetic with organic frames.<br>
                                📘 <strong>Bootstrap Classic:</strong> Clean, minimal professional layout.
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Select Type</strong></td>
                            <td>
                                🥉 <strong>Top 3:</strong> Focuses on the podium finishers.<br>
                                🏅 <strong>Top 4:</strong> Default layout for standard local tournaments.<br>
                                🏆 <strong>Top 8:</strong> Advanced 1+2+5 hierarchy for major competitive events.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="hg-box hg-box-pink">
                    <p>When clicking <strong>Download Image</strong>, the system generates a high-resolution PNG with a standardized filename:</p>
                    <code><strong>IG-[TYPE]-[THEME]-[EVENT-NAME].png</strong></code>
                </div>

                <div class="hg-tip hg-mt-20">
                    <strong>Tech Note:</strong> The generator uses <i>html2canvas</i> and local image caching to ensure card art is rendered quickly without triggering Scryfall rate limits.
                </div>
            </section>

             <!-- SECTION: PROFILES -->
             <section id="profiles" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-admin-users"></span> 11. Profiles & Preferences
                </h2>
                <p>Manage user visibility, achievement privacy, and data settings.</p>

                <h4>User Security & Privacy:</h4>
                <ul class="hg-list">
                    <li><strong>Secret Achievements:</strong> By default, locked achievements show titles/desc. However, when viewing **another user's profile**, these are masked as "Secret Achievement" unless the visitor has also unlocked them.</li>
                    <li><strong>Private Profile:</strong> Hides the User Detail page from the public.</li>
                    <li><strong>Account Deletion:</strong> Permanent removal of profile and assigned decks.</li>
                </ul>
            </section>

            <!-- SECTION: ROLES -->
            <section id="roles" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-shield"></span> 12. Roles & Security
                </h2>
                <div class="hg-grid-3">
                    <div class="hg-card">
                        <h5 class="hg-text-red"><span class="dashicons dashicons-shield"></span> Administrator</h5>
                        <p class="small">Full, unrestricted access to the entire site: themes, plugins, users, settings, and code.</p>
                    </div>
                    <div class="hg-card">
                        <h5 class="hg-text-blue"><span class="dashicons dashicons-admin-users"></span> Co-Administrator</h5>
                        <p class="small">Full management of all content types (Events, Rankings, Achievements, Decks, Pages).<br>
                        <strong>Restricted from:</strong> Themes, Plugins, Site Settings, ACF configuration, and System Tools.</p>
                    </div>
                    <div class="hg-card">
                        <h5 class="hg-text-gray"><span class="dashicons dashicons-id"></span> Player</h5>
                        <p class="small">Can manage their personal profile and their own decklists only.</p>
                    </div>
                </div>
            </section>

            <!-- SECTION: SETTINGS -->
            <section id="settings" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-admin-generic"></span> 13. Theme Settings
                </h2>
                <p>Configure aesthetics and mapping under <strong>LPDH > Theme Settings</strong>.</p>

                <table class="wp-list-table widefat fixed striped hg-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Setting Name</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Active Theme</strong></td>
                            <td>Switches global design (Bootscore Default, Vaporwave, Vaporwave Green, Lost Wood).</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="hg-table-header">Pages Configuration</td>
                        </tr>
                        <tr>
                            <td><strong>Deck Editor Page</strong></td>
                            <td>Select the page using the "Deck Editor" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Events Archive Page</strong></td>
                            <td>Select the page using the "Events" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Roulette Page</strong></td>
                            <td>Select the page using the "Roulette" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Player Stats Page</strong></td>
                            <td>Select the page using the "Player Stats" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Profile Editor Page</strong></td>
                            <td>Select the page using the "Profile Editor" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Login/Register Page</strong></td>
                            <td>Select the page using the "Registration Page" template.</td>
                        </tr>
                        <tr>
                            <td><strong>Enable Admin custom login</strong></td>
                            <td>Toggle to activate the custom split-screen login page styling (replaces default wp-login).</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="hg-table-header">Branding</td>
                        </tr>
                        <tr>
                            <td><strong>Custom Logo</strong></td>
                            <td>Upload a custom logo for your site.</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="hg-table-header">Socials</td>
                        </tr>
                        <tr>
                            <td><strong>Social Links</strong></td>
                            <td>Configure Instagram, Discord, Facebook, and X links for the footer.</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="hg-table-header">ELO Settings</td>
                        </tr>
                        <tr>
                            <td><strong>K Factor / Game Played</strong></td>
                            <td>Toggle whether to divide the K-factor.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- SECTION: EMAIL SYSTEM -->
            <section id="emails" class="hg-section">
                <h2 class="hg-section-title">
                    <span class="dashicons dashicons-email-alt"></span> 14. Email System & Testing
                </h2>
                <p>The theme includes a custom-styled email system that adapts to your active theme's aesthetic.</p>

                <div class="hg-grid-2">
                    <div class="hg-card">
                        <h5><span class="dashicons dashicons-art"></span> Dynamic Styling</h5>
                        <p class="small">Emails automatically inherit colors, logos, and backgrounds from the active theme (e.g., Vaporwave emails feature neon gradients and grid backgrounds).</p>
                    </div>
                    <div class="hg-card">
                        <h5><span class="dashicons dashicons-admin-settings"></span> Testing Dashboard</h5>
                        <p class="small">Access the <strong>Email Test Page</strong> to preview templates and send test emails to your inbox before they go live.</p>
                    </div>
                </div>

                <h4>Available Templates:</h4>
                <ul class="hg-list">
                    <li><strong>New User Welcome:</strong> Sent to players upon registration. Includes personalized greetings and platform links.</li>
                    <li><strong>Admin Notification:</strong> Alerts administrators when a new player joins the league.</li>
                </ul>

                <div class="hg-tip hg-mt-20">
                    <strong>Admin Tip:</strong> To use the tester, create a page with the <strong>Email Test Page</strong> template and visit it while logged in as an Administrator.
                </div>
            </section>

             <!-- SECTION: EASTER EGGS -->
             <section id="easter-eggs" class="hg-section">
                <h2 class="hg-section-title hg-title-red">
                    <span class="dashicons dashicons-admin-appearance"></span> 15. Easter Eggs & Hidden Features
                </h2>
                <p>Curated secret animations and interactions for the LPDH community.</p>

                <details class="hg-details">
                    <summary class="hg-summary">
                        <span>Click to view Easter Eggs (Spoiler Warning!)</span>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </summary>
                    <div class="hg-details-content">
                        <div class="hg-grid-2">
                             <div class="hg-card-mini">
                                <h5 class="hg-text-blue">1. Counterspell Search</h5>
                                <p class="small">Searching for <strong>"counterspell"</strong> triggers a blue burst visual effect.</p>
                            </div>
                             <div class="hg-card-mini">
                                <h5 class="hg-text-red">2. Daybound / Nightbound</h5>
                                <p class="small">Pressing <strong>"D"</strong> then <strong>"N"</strong> toggles the theme.</p>
                            </div>
                            <div class="hg-card-mini">
                                <h5 class="hg-text-red">3. Forbidden Cards</h5>
                                <p class="small">Searching for "expensive" cards triggers a rejection screen.</p>
                            </div>
                            <div class="hg-card-mini">
                                <h5 class="hg-text-white">4. Console Lives</h5>
                                <p class="small">Browser's <strong>Inspect Console</strong> reveals a greeting.</p>
                            </div>
                            <div class="hg-card-mini">
                                <h5 class="hg-text-yellow">5. Commander Tax</h5>
                                <p class="small">Clicking <strong>User Avatar</strong> adds +2 "Commander Tax".</p>
                            </div>
                        </div>
                    </div>
                </details>
            </section>

            <footer class="hg-footer">
                <p>Document updated: <?php echo date('F j, Y'); ?> - Version 2.0.0 (GitHub Dark Theme)</p>
            </footer>
        </div>
    </div>

    <style>
        :root {
            --gh-bg: #0d1117;
            --gh-container-bg: #161b22;
            --gh-border: #30363d;
            --gh-text-main: #c9d1d9;
            --gh-text-muted: #8b949e;
            --gh-link: #58a6ff;
            --gh-header: #f0f6fc;
            --gh-btn-bg: #21262d;
            --gh-btn-hover: #30363d;
            --gh-code-bg: #161b22;
            --gh-accent-blue: #1f6feb;
            --gh-alert-info-bg: rgba(56, 139, 253, 0.15);
            --gh-alert-info-border: rgba(56, 139, 253, 0.4);
            --gh-alert-warn-bg: rgba(187, 128, 9, 0.15);
            --gh-alert-warn-border: rgba(187, 128, 9, 0.4);
        }

        .help-guide-wrap .hg-main-title {
            display: flex; 
            align-items: center; 
            gap: 10px;
            color: var(--gh-text-main);
        }

        .help-guide-container {
            max-width: 1100px;
            padding: 40px;
            border-radius: 6px;
            border: 1px solid var(--gh-border);
            background: var(--gh-bg);
            margin-top: 25px;
            line-height: 1.6;
            color: var(--gh-text-main);
            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";
        }

        .help-guide-container h1, 
        .help-guide-container h2, 
        .help-guide-container h3, 
        .help-guide-container h4, 
        .help-guide-container h5 {
            color: var(--gh-header);
        }

        .help-guide-container a {
            color: var(--gh-link);
            text-decoration: none;
        }
        .help-guide-container a:hover {
            text-decoration: underline;
        }

        .hg-alert {
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
            border: 1px solid var(--gh-border);
        }

        .hg-alert-info {
            background: var(--gh-alert-info-bg);
            border-color: var(--gh-alert-info-border);
        }
        .hg-alert-info h3 {
            color: #58a6ff !important;
            margin-top: 0;
            display: flex; align-items: center; gap: 8px;
        }

        /* Nav */
        .help-guide-nav h3 {
            border-bottom: 1px solid var(--gh-border);
            padding-bottom: 10px;
        }
        .hg-nav-grid {
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 10px;
        }
        .help-guide-nav a {
            display: block;
            padding: 8px 16px;
            background: var(--gh-btn-bg);
            border: 1px solid var(--gh-border);
            color: var(--gh-text-main);
            border-radius: 6px;
            font-size: 14px;
            transition: 0.2s;
        }
        .help-guide-nav a:hover {
            background: var(--gh-btn-hover);
            border-color: #8b949e;
            text-decoration: none;
        }
        .hg-highlight { color: #e3b341 !important; font-weight: bold; }
        .hg-purple { color: #d2a8ff !important; }
        .hg-pink { color: #ff7b72 !important; }
        .hg-red { color: #ffa198 !important; }

        /* Sections */
        .hg-section { margin-bottom: 60px; }
        .hg-section-title {
            background: var(--gh-container-bg);
            padding: 10px 15px;
            border-radius: 6px;
            display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid var(--gh-border);
        }
        .hg-title-purple { color: #d2a8ff !important; }
        .hg-title-pink { color: #ff7b72 !important; }
        .hg-title-red { color: #ffa198 !important; }

        /* Tables (WordPress Override) */
        .help-guide-container .wp-list-table {
            background: var(--gh-container-bg);
            border: 1px solid var(--gh-border);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: none;
        }
        .help-guide-container .wp-list-table th, 
        .help-guide-container .wp-list-table td {
            background: var(--gh-container-bg) !important;
            color: var(--gh-text-main) !important;
            border-bottom: 1px solid var(--gh-border);
        }
        .help-guide-container .wp-list-table th {
            font-weight: 600;
            color: var(--gh-text-muted) !important;
        }
        .hg-table-header {
            background: var(--gh-btn-bg) !important;
            font-weight: bold;
            color: var(--gh-header) !important;
        }

        /* Tips & Boxes */
        .hg-tip {
            margin-top: 8px;
            background: rgba(56, 139, 253, 0.1);
            border-left: 3px solid #58a6ff;
            padding: 10px;
            border-radius: 3px;
            font-size: 13px;
        }
        .hg-warning {
            margin-top: 8px;
            background: rgba(187, 128, 9, 0.1);
            border-left: 3px solid #d29922;
            padding: 10px;
            border-radius: 3px;
            font-size: 13px;
        }
        .hg-box {
            padding: 15px; 
            border: 1px solid var(--gh-border); 
            border-radius: 6px; 
            margin-bottom: 15px;
            background: var(--gh-container-bg);
        }
        .hg-box-yellow { border-color: rgba(187, 128, 9, 0.4); }
        .hg-box-orange { border-color: rgba(219, 109, 40, 0.4); }
        .hg-box-pink { border-color: rgba(247, 120, 186, 0.4); }

        /* Grids & Cards */
        .hg-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .hg-grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }

        .hg-card, .hg-card-mini {
            border: 1px solid var(--gh-border);
            padding: 15px;
            border-radius: 6px;
            background: var(--gh-container-bg);
        }
        .hg-card h5, .hg-card-mini h5 { margin-top: 0; margin-bottom: 8px; }
        .hg-text-blue { color: #58a6ff !important; }
        .hg-text-green { color: #3fb950 !important; }
        .hg-text-red { color: #ff7b72 !important; }
        .hg-text-yellow { color: #d29922 !important; }
        .hg-text-gray { color: #8b949e !important; }
        .hg-text-white { color: #f0f6fc !important; }

        .small { font-size: 13px; color: var(--gh-text-muted); }

        /* Code */
        .hg-code-block {
            background: #0d1117;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            border: 1px solid var(--gh-border);
        }
        pre { color: var(--gh-text-main); font-family: monospace; }
        code { background: rgba(110,118,129,0.4); padding: 0.2em 0.4em; border-radius: 6px; }

        /* Details */
        .hg-details {
            border: 1px solid var(--gh-border);
            border-radius: 6px;
            background: var(--gh-container-bg);
            overflow: hidden;
        }
        .hg-summary {
            padding: 15px;
            cursor: pointer;
            color: var(--gh-header);
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            background: var(--gh-btn-bg);
        }
        .hg-details-content {
            padding: 20px;
            border-top: 1px solid var(--gh-border);
        }

        /* Footer */
        .hg-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--gh-border);
            text-align: center;
            font-style: italic;
            color: var(--gh-text-muted);
        }

        /* Dashicons override */
        .dashicons { opacity: 0.9; }
        
        /* List */
        .hg-list { list-style: disc; margin-left: 20px; }
        .hg-mt-20 { margin-top: 20px; }
    </style>
    <?php
}
