<?php
/**
 * Email Template Base Class
 * 
 * Handles email template rendering with theme-based styling and logo integration
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

class LPDH_Email_Template
{
    /**
     * Active theme name
     */
    private $theme;

    /**
     * Theme color configuration
     */
    private $colors;

    /**
     * Site logo HTML
     */
    private $logo_html;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->theme = get_option('lpdh_active_theme', 'default');
        $this->colors = $this->get_theme_colors();
        $this->logo_html = $this->get_logo_html();
    }

    /**
     * Get theme-specific color palette
     * 
     * @return array Theme colors
     */
    private function get_theme_colors()
    {
        $palettes = array(
            'default' => array(
                'primary' => '#007bff',
                'primary_dark' => '#0056b3',
                'secondary' => '#6c757d',
                'success' => '#28a745',
                'background' => '#f8f9fa',
                'text' => '#333333',
                'border' => '#dddddd',
            ),
            'vaporwave' => array(
                'primary' => '#ff6ec7',
                'primary_dark' => '#e91e84',
                'secondary' => '#7928ca',
                'success' => '#00d4ff',
                'background' => '#1a0033',
                'text' => '#ffffff',
                'border' => '#ff6ec7',
            ),
            'vaporwave-green' => array(
                'primary' => '#39ff14',
                'primary_dark' => '#2dd10f',
                'secondary' => '#7928ca',
                'success' => '#00d4ff',
                'background' => '#0a0a0a',
                'text' => '#ffffff',
                'border' => '#39ff14',
            ),
            'lost-wood' => array(
                'primary' => '#2d5016',
                'primary_dark' => '#1f3810',
                'secondary' => '#8b4513',
                'success' => '#228b22',
                'background' => '#f5f5dc',
                'text' => '#2d2d2d',
                'border' => '#8b7355',
            ),
        );

        return isset($palettes[$this->theme]) ? $palettes[$this->theme] : $palettes['default'];
    }

    /**
     * Get site logo HTML
     * 
     * @return string Logo HTML or site name
     */
    private function get_logo_html()
    {
        $custom_logo_id = get_theme_mod('custom_logo');

        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                // Ensure absolute URL for email compatibility
                if (strpos($logo_url, 'http') !== 0) {
                    $logo_url = home_url($logo_url);
                }

                return sprintf(
                    '<img src="%s" alt="%s" style="max-width: 80%; height: auto; display: block; margin: 0 auto;">',
                    esc_url($logo_url),
                    esc_attr(get_bloginfo('name'))
                );
            }
        }

        // Fallback to site name
        return sprintf(
            '<h1 style="margin: 0; color: %s; font-size: 28px; text-align: center;">%s</h1>',
            $this->colors['primary'],
            esc_html(get_bloginfo('name'))
        );
    }

    /**
     * Generate email header HTML
     * 
     * @param string $title Email title
     * @return string Header HTML
     */
    public function get_header($title = '')
    {
        $header_bg = $this->theme === 'vaporwave' || $this->theme === 'vaporwave-green'
            ? 'background: linear-gradient(135deg, ' . $this->colors['primary'] . ', ' . $this->colors['secondary'] . ');'
            : 'background: linear-gradient(135deg, ' . $this->colors['primary'] . ', ' . $this->colors['primary_dark'] . ');';

        return sprintf(
            '<div style="%s padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                %s
                %s
            </div>',
            $header_bg,
            $this->logo_html,
            $title ? '<h2 style="color: white; margin: 20px 0 0 0; font-size: 20px;">' . esc_html($title) . '</h2>' : ''
        );
    }

    /**
     * Generate email footer HTML
     * 
     * @return string Footer HTML
     */
    public function get_footer()
    {
        return sprintf(
            '<div style="text-align: center; padding: 20px; color: #666666; font-size: 12px; border-top: 1px solid %s;">
                <p style="margin: 0;">This is an automated email, please do not reply to this message.</p>
                <p style="margin: 5px 0 0 0;">&copy; %s %s. All rights reserved.</p>
            </div>',
            $this->colors['border'],
            date('Y'),
            esc_html(get_bloginfo('name'))
        );
    }

    /**
     * Generate button HTML
     * 
     * @param string $url Button URL
     * @param string $text Button text
     * @return string Button HTML
     */
    public function get_button($url, $text)
    {
        $button_style = $this->theme === 'vaporwave' || $this->theme === 'vaporwave-green'
            ? 'box-shadow: 0 0 15px ' . $this->colors['primary'] . '; text-shadow: 0 0 10px rgba(255,255,255,0.8);'
            : '';

        return sprintf(
            '<a href="%s" style="display: inline-block; background: %s; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px; %s">%s</a>',
            esc_url($url),
            $this->colors['primary'],
            $button_style,
            esc_html($text)
        );
    }

    /**
     * Generate info box HTML
     * 
     * @param array $items Key-value pairs to display
     * @return string Info box HTML
     */
    public function get_info_box($items)
    {
        $html = sprintf(
            '<div style="background: %s; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid %s;">',
            $this->colors['background'],
            $this->colors['border']
        );

        foreach ($items as $label => $value) {
            $html .= sprintf(
                '<p style="margin: 8px 0; color: %s;"><strong>%s:</strong> %s</p>',
                $this->colors['text'],
                esc_html($label),
                esc_html($value)
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Wrap content in email container
     * 
     * @param string $header_title Header title
     * @param string $content Body content
     * @return string Complete email HTML
     */
    public function wrap($header_title, $content)
    {
        $text_color = $this->theme === 'vaporwave' || $this->theme === 'vaporwave-green' || $this->theme === 'lost-wood'
            ? $this->colors['text']
            : '#333333';

        // Vaporwave background images
        $body_bg = '#f4f4f4';
        $body_extra_styles = '';

        if ($this->theme === 'vaporwave') {
            $body_bg = '#1a0033';
            $bg_image_url = get_stylesheet_directory_uri() . '/assets/img/bg/vaporwave.jpg';
            $body_extra_styles = sprintf(
                'background-image: url(%s); background-size: cover; background-position: center; background-attachment: fixed;',
                esc_url($bg_image_url)
            );
        } elseif ($this->theme === 'vaporwave-green') {
            $body_bg = '#0a0a0a';
            $bg_image_url = get_stylesheet_directory_uri() . '/assets/img/bg/vaporwave-green.jpg';
            $body_extra_styles = sprintf(
                'background-image: url(%s); background-size: cover; background-position: center; background-attachment: fixed;',
                esc_url($bg_image_url)
            );
        }

        // Card background for vaporwave themes
        $card_bg = ($this->theme === 'vaporwave' || $this->theme === 'vaporwave-green')
            ? 'rgba(26, 0, 51, 0.9)'
            : 'white';

        return sprintf(
            '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>%s</title>
            </head>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: %s; margin: 0; padding: 20px 0px; background-color: %s; %s">
                <div style="max-width: 600px; margin: 20px auto; background: %s; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                    %s
                    <div style="padding: 30px; color: %s;">
                        %s
                    </div>
                    %s
                </div>
            </body>
            </html>',
            esc_html($header_title),
            $text_color,
            $body_bg,
            $body_extra_styles,
            $card_bg,
            $this->get_header($header_title),
            $text_color,
            $content,
            $this->get_footer()
        );
    }

    /**
     * Get theme name for display
     * 
     * @return string Theme display name
     */
    public function get_theme_name()
    {
        return ucwords(str_replace('-', ' ', $this->theme));
    }
}
