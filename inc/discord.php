<?php
/**
 * Discord Integration for Events
 *
 * Handles Discord notifications for new events and manual pushes.
 *
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Register Meta Box for Discord Notification
 */
function lpdh_discord_meta_box()
{
    add_meta_box(
        'lpdh_discord_notification',
        'Discord Notification',
        'lpdh_discord_meta_box_callback',
        'event',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'lpdh_discord_meta_box');

/**
 * Render Meta Box Content
 */
function lpdh_discord_meta_box_callback($post)
{
    // Nonce field for security
    wp_nonce_field('lpdh_discord_action', 'lpdh_discord_nonce');

    // Retrieve settings
    $webhook_url = get_option('lpdh_discord_webhook_url');

    ?>
    <div style="text-align: center; margin-top: 10px;">
        <?php if ($webhook_url): ?>
            
            <div style="margin-bottom: 15px; text-align: left; background: #f0f0f1; padding: 10px; border-radius: 4px;">
                <label for="lpdh_discord_create_poll" style="font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="lpdh_discord_create_poll" value="1" checked>
                    Create Poll: Will you participate?
                </label>
                <p class="description" style="margin: 5px 0 0 25px;">Adds a poll (Yes/No/Maybe) to the message.</p>
            </div>

            <button type="button" id="send_discord_notification" class="button button-primary button-large" style="width: 100%;">
                <span class="dashicons dashicons-megaphone" style="margin-top: 3px;"></span>
                Send event on Discord server
            </button>
            <p class="description" style="margin-top: 10px;">
                Click to send a formatted announcement to the configured Discord channel.
            </p>
            <div id="discord_response" style="margin-top: 10px; font-weight: bold;"></div>
        <?php else: ?>
            <p style="color: #d63638;">
                <strong>Missing Webhook URL!</strong><br>
                Please configure the Discord Webhook in <a href="<?php echo admin_url('admin.php?page=lpdh-theme-settings'); ?>">Theme Settings</a>.
            </p>
        <?php endif; ?>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#send_discord_notification').click(function() {
                var button = $(this);
                button.prop('disabled', true).text('Sending...');
                $('#discord_response').html('');

                var data = {
                    'action': 'lpdh_send_discord_notification',
                    'post_id': <?php echo $post->ID; ?>,
                    'create_poll': $('#lpdh_discord_create_poll').is(':checked') ? 1 : 0,
                    'security': '<?php echo wp_create_nonce("lpdh_discord_ajax_nonce"); ?>'
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        $('#discord_response').css('color', '#00a32a').html(response.data);
                        button.text('Sent Successfully!');
                    } else {
                        $('#discord_response').css('color', '#d63638').html(response.data);
                        button.prop('disabled', false).html('<span class="dashicons dashicons-megaphone" style="margin-top: 3px;"></span> Send event on Discord server');
                    }
                }).fail(function() {
                    $('#discord_response').css('color', '#d63638').html('Server Error. Please try again.');
                    button.prop('disabled', false).html('<span class="dashicons dashicons-megaphone" style="margin-top: 3px;"></span> Send event on Discord server');
                });
            });
        });
    </script>
    <?php
}

/**
 * Render Discord Settings Row in Theme Settings
 * Hooked to: lpdh_after_theme_settings_row
 */
function lpdh_render_discord_settings_row()
{
    $discord_webhook = get_option('lpdh_discord_webhook_url', '');
    $discord_bot_name = get_option('lpdh_discord_bot_name', 'LPDH Bot');
    $discord_role_id = get_option('lpdh_discord_role_to_mention', '');
    ?>
    <h2>Discord Integration</h2>
    <table class="form-table">
        <tr>
            <th scope="row">Webhook URL</th>
            <td>
                <input type="url" name="lpdh_discord_webhook_url" id="lpdh_discord_webhook_url" value="<?php echo esc_attr($discord_webhook); ?>" class="regular-text" placeholder="https://discord.com/api/webhooks/...">
                <p class="description">Paste the Webhook URL from your Discord Server Settings > Integrations.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Bot Name</th>
            <td>
                <input type="text" name="lpdh_discord_bot_name" id="lpdh_discord_bot_name" value="<?php echo esc_attr($discord_bot_name); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th scope="row">Role ID to Mention (Optional)</th>
            <td>
                <input type="text" name="lpdh_discord_role_to_mention" id="lpdh_discord_role_to_mention" value="<?php echo esc_attr($discord_role_id); ?>" class="regular-text" placeholder="e.g. 123456789012345678">
                <p class="description">Copy the Role ID (Enable Developer Mode in Discord) to mention a specific role (e.g. @Player). Format in message will be &lt;@&ID&gt;.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Footer Text</th>
            <td>
                <input type="text" name="lpdh_discord_footer_text" id="lpdh_discord_footer_text" value="<?php echo esc_attr(get_option('lpdh_discord_footer_text', 'LPDH Tournament System')); ?>" class="regular-text">
                <p class="description">Custom text to display in the footer of the Discord embed.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('lpdh_after_theme_settings_row', 'lpdh_render_discord_settings_row');

/**
 * Save Discord Options
 * Hooked to: lpdh_after_theme_settings_save
 */
function lpdh_save_discord_options()
{
    // Security check is already handled in the parent function 'lpdh_theme_settings_render' via check_admin_referer('lpdh_theme_settings_save')
    
    if (isset($_POST['lpdh_discord_webhook_url'])) {
        update_option('lpdh_discord_webhook_url', esc_url_raw($_POST['lpdh_discord_webhook_url']));
    }
    
    if (isset($_POST['lpdh_discord_bot_name'])) {
        update_option('lpdh_discord_bot_name', sanitize_text_field($_POST['lpdh_discord_bot_name']));
    }

    if (isset($_POST['lpdh_discord_role_to_mention'])) {
        update_option('lpdh_discord_role_to_mention', sanitize_text_field($_POST['lpdh_discord_role_to_mention']));
    }

    if (isset($_POST['lpdh_discord_footer_text'])) {
        update_option('lpdh_discord_footer_text', sanitize_text_field($_POST['lpdh_discord_footer_text']));
    }
}
add_action('lpdh_after_theme_settings_save', 'lpdh_save_discord_options');

/**
 * AJAX Handler to Send Discord Notification
 */
function ajax_send_discord_event_notification()
{
    check_ajax_referer('lpdh_discord_ajax_nonce', 'security');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Insufficient permissions.');
    }

    $post_id = intval($_POST['post_id']);
    if (!$post_id) {
        wp_send_json_error('Invalid Post ID.');
    }

    $webhook_url = get_option('lpdh_discord_webhook_url');
    if (!$webhook_url) {
        wp_send_json_error('Webhook URL not configured.');
    }

    // Get Event Details
    $event_title = html_entity_decode(get_the_title($post_id), ENT_QUOTES, 'UTF-8');
    $event_link = get_permalink($post_id);
    $event_date_raw = get_field('event_date', $post_id);
    $event_date = $event_date_raw ? date_i18n('d/m/Y H:i', strtotime($event_date_raw)) : 'TBA';
    $event_place_id = get_field('event_place', $post_id); // Assuming relation to Place CPT
    
    $place_name = 'TBD';
    $place_link = '';
    $place_thumbnail = '';

    if ($event_place_id) {
        $place_name = html_entity_decode(get_the_title($event_place_id), ENT_QUOTES, 'UTF-8');
        $place_link = get_permalink($event_place_id);
        if (has_post_thumbnail($event_place_id)) {
            $place_thumbnail = get_the_post_thumbnail_url($event_place_id, 'medium');
        }
    }

    $facebook_link = get_field('event_facebook_link', $post_id);
    
    $role_id = get_option('lpdh_discord_role_to_mention');
    $mention_text = $role_id ? "<@&{$role_id}>" : "";
    $footer_text = get_option('lpdh_discord_footer_text', 'LPDH Tournament System');

    // Determine Icon URL
    $icon_url = get_site_icon_url();
    if (empty($icon_url)) {
        $icon_url = lpdh_get_logo(); // Fallback to theme logo
    }

    // Prepare Discord Message payload
    // Using embeds for a nicer look
    $payload = [
        'username' => get_option('lpdh_discord_bot_name', 'LPDH Bot'),
        'avatar_url' => $icon_url,
        'content' => "📢 **New Event Announcement!** $mention_text",
        'embeds' => [
            [
                'title' => $event_title,
                'description' => "A new tournament has been announced! Check the details below and get ready.",
                'url' => $event_link,
                'color' => hexdec("FFD700"), // Gold color
                'fields' => [
                    [
                        'name' => '📅 Date',
                        'value' => $event_date ? $event_date : 'TBA',
                        'inline' => true
                    ],
                    [
                        'name' => '📍 Location',
                        'value' => $place_link ? "[{$place_name}]({$place_link})" : $place_name,
                        'inline' => true
                    ]
                ]
            ]
        ]
    ];

    // Add Event Code if exists
    $event_code = get_field('field_event_code', $post_id);
    if ($event_code) {
        $payload['embeds'][0]['fields'][] = [
            'name' => '🔢 Event Code',
            'value' => "`{$event_code}`",
            'inline' => true
        ];
    }

    // Add Footer
    $payload['embeds'][0]['footer'] = [
        'text' => $footer_text,
        'icon_url' => $icon_url
    ];
    
    // Add thumbnail if place has one
    if ($place_thumbnail) {
        $payload['embeds'][0]['thumbnail'] = ['url' => $place_thumbnail];
    }

    // Add Facebook Link if exists
    if ($facebook_link) {
        $payload['embeds'][0]['fields'][] = [
            'name' => '🔗 Facebook Event',
            'value' => "[Click Here]({$facebook_link})",
            'inline' => false
        ];
    }

    // Add Poll (Text-Based Fallback) if requested
    if (isset($_POST['create_poll']) && $_POST['create_poll'] == '1') {
        $payload['embeds'][0]['fields'][] = [
            'name' => '📊 Will you participate?',
            'value' => "React to answer:\n✅ Yes\n❌ No\n🤔 Maybe",
            'inline' => false
        ];
    }

    // Send Request (Single Message)
    $response = wp_remote_post($webhook_url, [
        'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        'headers' => ['Content-Type' => 'application/json'],
        'method' => 'POST',
        'data_format' => 'body',
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('Discord API Error: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code >= 200 && $response_code < 300) {
        update_post_meta($post_id, 'lpdh_discord_notified', current_time('mysql'));
        wp_send_json_success('Notification sent to Discord!');
    } else {
        $body = wp_remote_retrieve_body($response);
        wp_send_json_error('Discord rejected. Code: ' . $response_code . ' - ' . $body);
    }
}
add_action('wp_ajax_lpdh_send_discord_notification', 'ajax_send_discord_event_notification');
