<?php
/**
 * Admin Class for Premium Auto Content Generator
 */

class Premium_Auto_Content_Admin {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function enqueue_scripts($hook) {
        // Already handled in main plugin class
    }

    public function render_dashboard() {
        $stats = $this->get_dashboard_stats();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    public function render_settings() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    public function render_generator() {
        $templates = $this->get_templates();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-generator.php';
    }

    public function render_templates() {
        $templates = $this->get_templates();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-templates.php';
    }

    public function render_log() {
        $logs = $this->get_logs();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-log.php';
    }

    public function render_seo() {
        $analytics = $this->get_seo_analytics();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-seo.php';
    }

    public function render_scheduler() {
        $queue = $this->get_scheduled_queue();
        $templates = $this->get_templates();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-scheduler.php';
    }

    public function render_license() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-license.php';
    }

    private function get_dashboard_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $stats = [];
        $stats['total_generated'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'completed'");
        $stats['total_failed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");
        $stats['total_tokens'] = $wpdb->get_var("SELECT SUM(api_tokens_used) FROM $table");
        $stats['avg_seo_score'] = $wpdb->get_var("SELECT AVG(seo_score) FROM $table WHERE status = 'completed'");
        $stats['this_week'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        ));
        $stats['this_month'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));
        
        // Recent activity
        $stats['recent'] = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY created_at DESC LIMIT 10"
        ));
        
        return $stats;
    }

    private function get_templates() {
        return get_posts([
            'post_type' => 'premium_content_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
    }

    private function get_logs($page = 1, $per_page = 20, $status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $offset = ($page - 1) * $per_page;
        $where = $status ? $wpdb->prepare("WHERE status = %s", $status) : '';
        
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table $where",
            $status ? [$status] : []
        ));
        
        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ];
    }

    private function get_seo_analytics() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $analytics = [];
        
        // SEO score distribution
        $analytics['score_distribution'] = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN seo_score >= 90 THEN '90-100'
                    WHEN seo_score >= 80 THEN '80-89'
                    WHEN seo_score >= 70 THEN '70-79'
                    WHEN seo_score >= 60 THEN '60-69'
                    ELSE 'Below 60'
                END as range,
                COUNT(*) as count
             FROM $table 
             WHERE status = 'completed' 
             GROUP BY range 
             ORDER BY range DESC"
        );
        
        // Top performing content
        $analytics['top_content'] = $wpdb->get_results($wpdb->prepare(
            "SELECT title, seo_score, api_tokens_used, created_at 
             FROM $table 
             WHERE status = 'completed' 
             ORDER BY seo_score DESC, created_at DESC 
             LIMIT 10"
        ));
        
        // Content type performance
        $analytics['type_performance'] = $wpdb->get_results(
            "SELECT content_type, COUNT(*) as count, AVG(seo_score) as avg_score, AVG(api_tokens_used) as avg_tokens
             FROM $table 
             WHERE status = 'completed' 
             GROUP BY content_type"
        );
        
        // Monthly trends
        $analytics['monthly_trends'] = $wpdb->get_results(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                    COUNT(*) as generated,
                    AVG(seo_score) as avg_score,
                    SUM(api_tokens_used) as total_tokens
             FROM $table 
             WHERE status = 'completed' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month 
             ORDER BY month"
        );
        
        return $analytics;
    }

    private function get_scheduled_queue() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        return $wpdb->get_results(
            "SELECT * FROM $table 
             ORDER BY scheduled_at ASC"
        );
    }

    public function save_template($data) {
        $post_data = [
            'post_title' => sanitize_text_field($data['name']),
            'post_content' => sanitize_textarea_field($data['description']),
            'post_type' => 'premium_content_template',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return ['error' => $post_id->get_error_message()];
        }
        
        // Save template meta
        update_post_meta($post_id, '_content_type', sanitize_text_field($data['content_type']));
        update_post_meta($post_id, '_prompt_template', sanitize_textarea_field($data['prompt_template']));
        update_post_meta($post_id, '_seo_keywords', sanitize_textarea_field($data['seo_keywords']));
        update_post_meta($post_id, '_default_length', sanitize_text_field($data['default_length']));
        update_post_meta($post_id, '_is_active', isset($data['is_active']) ? 1 : 0);
        
        return ['success' => true, 'id' => $post_id];
    }

    public function update_template($post_id, $data) {
        $post_data = [
            'ID' => $post_id,
            'post_title' => sanitize_text_field($data['name']),
            'post_content' => sanitize_textarea_field($data['description']),
        ];
        
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return ['error' => $result->get_error_message()];
        }
        
        update_post_meta($post_id, '_content_type', sanitize_text_field($data['content_type']));
        update_post_meta($post_id, '_prompt_template', sanitize_textarea_field($data['prompt_template']));
        update_post_meta($post_id, '_seo_keywords', sanitize_textarea_field($data['seo_keywords']));
        update_post_meta($post_id, '_default_length', sanitize_text_field($data['default_length']));
        update_post_meta($post_id, '_is_active', isset($data['is_active']) ? 1 : 0);
        
        return ['success' => true];
    }

    public function delete_template($post_id) {
        $result = wp_delete_post($post_id, true);
        
        if (!$result) {
            return ['error' => 'Failed to delete template'];
        }
        
        return ['success' => true];
    }

    public function schedule_generation($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $scheduled_at = sanitize_text_field($data['scheduled_at']);
        if (empty($scheduled_at)) {
            $scheduled_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        }
        
        $result = $wpdb->insert($table, [
            'template_id' => absint($data['template_id']),
            'content_type' => sanitize_text_field($data['content_type']),
            'title' => sanitize_text_field($data['title']),
            'prompt' => sanitize_textarea_field($data['prompt']),
            'seo_keywords' => sanitize_textarea_field($data['seo_keywords']),
            'scheduled_at' => $scheduled_at,
            'status' => 'pending',
            'priority' => absint($data['priority'] ?? 0),
        ]);
        
        if ($result === false) {
            return ['error' => 'Failed to schedule generation'];
        }
        
        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public function cancel_scheduled($queue_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $result = $wpdb->delete($table, ['id' => absint($queue_id)]);
        
        if ($result === false) {
            return ['error' => 'Failed to cancel scheduled generation'];
        }
        
        return ['success' => true];
    }

    public function verify_license($license_key) {
        // In a real implementation, this would verify with LuxWP license server
        // For now, return mock verification
        if (empty($license_key)) {
            return ['valid' => false, 'message' => 'License key is required'];
        }
        
        // Mock validation - in production, verify with API
        if (strpos($license_key, 'LUXWP-') === 0 && strlen($license_key) >= 20) {
            update_option('premium_auto_content_license', [
                'key' => $license_key,
                'status' => 'valid',
                'verified_at' => current_time('mysql'),
                'expires_at' => date('Y-m-d', strtotime('+1 year')),
            ]);
            return ['valid' => true, 'message' => 'License verified successfully'];
        }
        
        return ['valid' => false, 'message' => 'Invalid license key format'];
    }

    public function get_license_status() {
        $license = get_option('premium_auto_content_license', []);
        return [
            'valid' => !empty($license['status']) && $license['status'] === 'valid',
            'key' => $license['key'] ?? '',
            'expires' => $license['expires_at'] ?? '',
            'verified' => $license['verified_at'] ?? '',
        ];
    }
}