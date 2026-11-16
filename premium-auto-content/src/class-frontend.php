<?php
/**
 * Frontend Class for Premium Auto Content Generator
 */

class Premium_Auto_Content_Frontend {

    private $settings;

    public function __construct() {
        $this->settings = get_option('premium_auto_content_settings', []);
    }

    public function enqueue_scripts() {
        // Enqueue frontend assets
        wp_enqueue_style('premium-auto-content-frontend');
        wp_enqueue_script('premium-auto-content-frontend');
    }

    public function render_generator_form($atts = []) {
        $atts = shortcode_atts([
            'template_id' => '',
            'show_title' => 'true',
            'button_text' => 'Generate Content',
        ], $atts);

        if (!is_user_logged_in() || !current_user_can('edit_posts')) {
            return '<div class="premium-auto-content-error">' . __('You must be logged in with appropriate permissions to use this feature.', 'premium-auto-content') . '</div>';
        }

        ob_start();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/frontend-generator-form.php';
        return ob_get_clean();
    }

    public function render_content_display($atts = []) {
        $atts = shortcode_atts([
            'post_id' => '',
            'template' => 'default',
            'show_seo' => 'true',
            'show_schema' => 'false',
        ], $atts);

        $post_id = $atts['post_id'] ? absint($atts['post_id']) : get_the_ID();
        
        if (!$post_id) {
            return '';
        }

        $post = get_post($post_id);
        if (!$post) {
            return '';
        }

        // Check if this is an auto-generated post
        $is_auto_generated = get_post_meta($post_id, '_premium_auto_generated', true);
        
        if (!$is_auto_generated && $atts['template'] !== 'force') {
            // Not an auto-generated post, just return normal content
            return '';
        }

        ob_start();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/frontend-content-display.php';
        return ob_get_clean();
    }

    public function add_schema_markup($post_id = 0) {
        $post_id = $post_id ? absint($post_id) : get_the_ID();
        
        $schema = get_post_meta($post_id, '_schema_markup', true);
        $premium_schema = get_post_meta($post_id, '_premium_auto_schema', true);
        
        $final_schema = $premium_schema ?: $schema;
        
        if ($final_schema) {
            echo '<script type="application/ld+json">' . "\n";
            echo $final_schema . "\n";
            echo '</script>' . "\n";
        }
    }

    public function add_meta_tags($post_id = 0) {
        $post_id = $post_id ? absint($post_id) : get_the_ID();
        
        $meta_desc = get_post_meta($post_id, '_premium_auto_meta_desc', true);
        $keywords = get_post_meta($post_id, '_premium_auto_seo_keywords', true);
        
        if ($meta_desc) {
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
        }
        
        if ($keywords) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
        }
        
        // Open Graph tags
        $title = get_the_title($post_id);
        $excerpt = $meta_desc ?: get_the_excerpt($post_id);
        $image = get_the_post_thumbnail_url($post_id, 'large') ?: get_site_icon_url(512);
        $url = get_permalink($post_id);
        
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($excerpt) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
        }
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($excerpt) . '" />' . "\n";
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }
    }

    public function filter_content($content) {
        if (!is_singular()) return $content;
        
        $post_id = get_the_ID();
        $is_auto_generated = get_post_meta($post_id, '_premium_auto_generated', true);
        
        if (!$is_auto_generated) return $content;
        
        // Add SEO meta tags to head
        add_action('wp_head', [$this, 'add_meta_tags']);
        
        // Add schema markup to footer
        add_action('wp_footer', [$this, 'add_schema_markup']);
        
        return $content;
    }

    public function rest_generate_content($request) {
        if (!current_user_can('edit_posts')) {
            return new WP_Error('permission_denied', __('Insufficient permissions', 'premium-auto-content'), ['status' => 403]);
        }

        $generator = new Premium_Content_Generator();
        $result = $generator->generate([
            'prompt' => sanitize_textarea_field($request->get_param('prompt')),
            'content_type' => sanitize_text_field($request->get_param('content_type') ?? 'post'),
            'length' => sanitize_text_field($request->get_param('length') ?? 'medium'),
            'seo_keywords' => sanitize_textarea_field($request->get_param('seo_keywords') ?? ''),
            'language' => sanitize_text_field($request->get_param('language') ?? 'en_US'),
        ]);

        if (isset($result['error'])) {
            return new WP_Error('generation_failed', $result['error'], ['status' => 500]);
        }

        return rest_ensure_response($result);
    }

    public function rest_get_templates($request) {
        if (!current_user_can('edit_posts')) {
            return new WP_Error('permission_denied', __('Insufficient permissions', 'premium-auto-content'), ['status' => 403]);
        }

        $templates = get_posts([
            'post_type' => 'premium_content_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => '_is_active',
            'meta_value' => 1,
        ]);

        $data = [];
        foreach ($templates as $template) {
            $data[] = [
                'id' => $template->ID,
                'name' => $template->post_title,
                'description' => $template->post_content,
                'content_type' => get_post_meta($template->ID, '_content_type', true),
                'prompt_template' => get_post_meta($template->ID, '_prompt_template', true),
                'seo_keywords' => get_post_meta($template->ID, '_seo_keywords', true),
                'default_length' => get_post_meta($template->ID, '_default_length', true),
            ];
        }

        return rest_ensure_response($data);
    }
}