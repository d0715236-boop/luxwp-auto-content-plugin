<?php
/**
 * Plugin Name: Premium Auto Content Generator
 * Plugin URI: https://luxwp.com/premium-auto-content-generator
 * Description: AI-powered premium WordPress plugin that automatically generates high-quality content for your website using Google Gemini API. Features intelligent content creation, SEO optimization, multi-language support, and seamless integration with premium themes and plugins from LuxWP.
 * Version: 1.0.0
 * Author: LuxWP Development Team
 * Author URI: https://luxwp.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: premium-auto-content
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Tested up to: 6.4
 * WC requires at least: 7.0
 * WC tested up to: 8.5
 * Network: false
 * Update URI: https://luxwp.com/api/plugins/premium-auto-content
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PREMIUM_AUTO_CONTENT_VERSION', '1.0.0');
define('PREMIUM_AUTO_CONTENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PREMIUM_AUTO_CONTENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PREMIUM_AUTO_CONTENT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Premium Auto Content Generator Class
 */
class Premium_Auto_Content_Generator {

    /**
     * Plugin instance
     * @var Premium_Auto_Content_Generator|null
     */
    private static $instance = null;

    /**
     * Plugin settings
     * @var array
     */
    private $settings = [];

    /**
     * Get plugin instance
     * @return Premium_Auto_Content_Generator
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->load_settings();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-settings.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-gemini-api.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-content-generator.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-admin.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-frontend.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'src/class-scheduler.php';
        require_once PREMIUM_AUTO_CONTENT_PLUGIN_DIR 'src/class-seo-optimizer.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Plugin activation/deactivation
        register_activation_hook(PREMIUM_AUTO_CONTENT_PLUGIN_BASENAME, [$this, 'activate']);
        register_deactivation_hook(PREMIUM_AUTO_CONTENT_PLUGIN_BASENAME, [$this, 'deactivate']);

        // Admin initialization
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // Frontend initialization
        add_action('wp_enqueue_scripts', [$this, 'frontend_enqueue_scripts']);
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);

        // REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // AJAX handlers
        add_action('wp_ajax_premium_auto_content_generate', [$this, 'ajax_generate_content']);
        add_action('wp_ajax_premium_auto_content_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_premium_auto_content_test_api', [$this, 'ajax_test_api']);
        add_action('wp_ajax_premium_auto_content_bulk_generate', [$this, 'ajax_bulk_generate']);

        // Cron jobs
        add_action('premium_auto_content_cron', [$this, 'scheduled_content_generation']);
        add_action('premium_auto_content_cleanup_cron', [$this, 'cleanup_old_data']);

        // Shortcodes
        add_shortcode('premium_auto_content', [$this, 'shortcode_content_display']);
        add_shortcode('premium_auto_content_form', [$this, 'shortcode_content_form']);

        // Gutenberg blocks
        add_action('init', [$this, 'register_gutenberg_blocks']);
    }

    /**
     * Load plugin settings
     */
    private function load_settings() {
        $this->settings = get_option('premium_auto_content_settings', [
            'gemini_api_key' => '',
            'gemini_model' => 'gemini-1.5-pro',
            'default_language' => 'en_US',
            'content_length' => 'medium',
            'seo_optimization' => true,
            'auto_publish' => false,
            'content_types' => ['post', 'page'],
            'custom_prompts' => [],
            'schedule_enabled' => false,
            'schedule_frequency' => 'daily',
            'schedule_time' => '02:00',
            'max_posts_per_run' => 5,
            'featured_image_generation' => true,
            'internal_linking' => true,
            'schema_markup' => true,
        ]);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('premium_auto_content_cron')) {
            wp_schedule_event(time(), 'daily', 'premium_auto_content_cron');
        }
        if (!wp_next_scheduled('premium_auto_content_cleanup_cron')) {
            wp_schedule_event(time(), 'weekly', 'premium_auto_content_cleanup_cron');
        }

        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        add_option('premium_auto_content_version', PREMIUM_AUTO_CONTENT_VERSION);
        
        do_action('premium_auto_content_activated');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('premium_auto_content_cron');
        wp_clear_scheduled_hook('premium_auto_content_cleanup_cron');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('premium_auto_content_deactivated');
    }

    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Content generation log table
        $table_log = $wpdb->prefix . 'premium_auto_content_log';
        $sql_log = "CREATE TABLE $table_log (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) DEFAULT 0,
            content_type varchar(50) NOT NULL,
            title text NOT NULL,
            prompt text NOT NULL,
            generated_content longtext NOT NULL,
            seo_score int(3) DEFAULT 0,
            status varchar(20) DEFAULT 'pending',
            error_message text,
            api_tokens_used int(11) DEFAULT 0,
            generation_time float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Content templates table
        $table_templates = $wpdb->prefix . 'premium_auto_content_templates';
        $sql_templates = "CREATE TABLE $table_templates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            content_type varchar(50) NOT NULL,
            prompt_template longtext NOT NULL,
            seo_keywords text,
            default_length varchar(20) DEFAULT 'medium',
            is_active tinyint(1) DEFAULT 1,
            created_by bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY content_type (content_type),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Scheduled generation queue
        $table_queue = $wpdb->prefix . 'premium_auto_content_queue';
        $sql_queue = "CREATE TABLE $table_queue (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            template_id bigint(20) DEFAULT 0,
            content_type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            prompt text NOT NULL,
            seo_keywords text,
            scheduled_at datetime NOT NULL,
            status varchar(20) DEFAULT 'pending',
            priority int(3) DEFAULT 0,
            retry_count int(3) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY scheduled_at (scheduled_at),
            KEY status (status)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_log);
        dbDelta($sql_templates);
        dbDelta($sql_queue);
    }

    /**
     * Admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('Premium Auto Content', 'premium-auto-content'),
            __('Auto Content AI', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content',
            [$this, 'admin_dashboard_page'],
            'dashicons-editor-expand',
            25
        );

        add_submenu_page(
            'premium-auto-content',
            __('Settings', 'premium-auto-content'),
            __('Settings', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-settings',
            [$this, 'admin_settings_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('Content Generator', 'premium-auto-content'),
            __('Generator', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-generator',
            [$this, 'admin_generator_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('Templates', 'premium-auto-content'),
            __('Templates', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-templates',
            [$this, 'admin_templates_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('Generation Log', 'premium-auto-content'),
            __('Log', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-log',
            [$this, 'admin_log_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('SEO Analytics', 'premium-auto-content'),
            __('SEO Analytics', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-seo',
            [$this, 'admin_seo_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('Scheduler', 'premium-auto-content'),
            __('Scheduler', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-scheduler',
            [$this, 'admin_scheduler_page']
        );

        add_submenu_page(
            'premium-auto-content',
            __('License', 'premium-auto-content'),
            __('License', 'premium-auto-content'),
            'manage_options',
            'premium-auto-content-license',
            [$this, 'admin_license_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('premium_auto_content_settings', 'premium_auto_content_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => [],
        ]);

        // API Settings Section
        add_settings_section(
            'premium_auto_content_api_section',
            __('API Configuration', 'premium-auto-content'),
            [$this, 'api_section_callback'],
            'premium-auto-content-settings'
        );

        add_settings_field(
            'gemini_api_key',
            __('Gemini API Key', 'premium-auto-content'),
            [$this, 'gemini_api_key_field'],
            'premium-auto-content-settings',
            'premium_auto_content_api_section'
        );

        add_settings_field(
            'gemini_model',
            __('Gemini Model', 'premium-auto-content'),
            [$this, 'gemini_model_field'],
            'premium-auto-content-settings',
            'premium_auto_content_api_section'
        );

        // Content Settings Section
        add_settings_section(
            'premium_auto_content_generation_section',
            __('Content Generation', 'premium-auto-content'),
            [$this, 'generation_section_callback'],
            'premium-auto-content-settings'
        );

        add_settings_field(
            'default_language',
            __('Default Language', 'premium-auto-content'),
            [$this, 'default_language_field'],
            'premium-auto-content-settings',
            'premium_auto_content_generation_section'
        );

        add_settings_field(
            'content_length',
            __('Default Content Length', 'premium-auto-content'),
            [$this, 'content_length_field'],
            'premium-auto-content-settings',
            'premium_auto_content_generation_section'
        );

        add_settings_field(
            'content_types',
            __('Content Types', 'premium-auto-content'),
            [$this, 'content_types_field'],
            'premium-auto-content-settings',
            'premium_auto_content_generation_section'
        );

        // SEO Settings Section
        add_settings_section(
            'premium_auto_content_seo_section',
            __('SEO Optimization', 'premium-auto-content'),
            [$this, 'seo_section_callback'],
            'premium-auto-content-settings'
        );

        add_settings_field(
            'seo_optimization',
            __('Enable SEO Optimization', 'premium-auto-content'),
            [$this, 'seo_optimization_field'],
            'premium-auto-content-settings',
            'premium_auto_content_seo_section'
        );

        add_settings_field(
            'internal_linking',
            __('Auto Internal Linking', 'premium-auto-content'),
            [$this, 'internal_linking_field'],
            'premium-auto-content-settings',
            'premium_auto_content_seo_section'
        );

        add_settings_field(
            'schema_markup',
            __('Schema Markup Generation', 'premium-auto-content'),
            [$this, 'schema_markup_field'],
            'premium-auto-content-settings',
            'premium_auto_content_seo_section'
        );

        // Publishing Settings Section
        add_settings_section(
            'premium_auto_content_publishing_section',
            __('Publishing Options', 'premium-auto-content'),
            [$this, 'publishing_section_callback'],
            'premium-auto-content-settings'
        );

        add_settings_field(
            'auto_publish',
            __('Auto Publish Generated Content', 'premium-auto-content'),
            [$this, 'auto_publish_field'],
            'premium-auto-content-settings',
            'premium_auto_content_publishing_section'
        );

        add_settings_field(
            'featured_image_generation',
            __('Generate Featured Images', 'premium-auto-content'),
            [$this, 'featured_image_generation_field'],
            'premium-auto-content-settings',
            'premium_auto_content_publishing_section'
        );

        // Scheduler Settings Section
        add_settings_section(
            'premium_auto_content_scheduler_section',
            __('Automated Scheduler', 'premium-auto-content'),
            [$this, 'scheduler_section_callback'],
            'premium-auto-content-settings'
        );

        add_settings_field(
            'schedule_enabled',
            __('Enable Scheduler', 'premium-auto-content'),
            [$this, 'schedule_enabled_field'],
            'premium-auto-content-settings',
            'premium_auto_content_scheduler_section'
        );

        add_settings_field(
            'schedule_frequency',
            __('Generation Frequency', 'premium-auto-content'),
            [$this, 'schedule_frequency_field'],
            'premium-auto-content-settings',
            'premium_auto_content_scheduler_section'
        );

        add_settings_field(
            'schedule_time',
            __('Generation Time', 'premium-auto-content'),
            [$this, 'schedule_time_field'],
            'premium-auto-content-settings',
            'premium_auto_content_scheduler_section'
        );

        add_settings_field(
            'max_posts_per_run',
            __('Max Posts Per Run', 'premium-auto-content'),
            [$this, 'max_posts_per_run_field'],
            'premium-auto-content-settings',
            'premium_auto_content_scheduler_section'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        $sanitized['gemini_api_key'] = sanitize_text_field($input['gemini_api_key'] ?? '');
        $sanitized['gemini_model'] = sanitize_text_field($input['gemini_model'] ?? 'gemini-1.5-pro');
        $sanitized['default_language'] = sanitize_text_field($input['default_language'] ?? 'en_US');
        $sanitized['content_length'] = sanitize_text_field($input['content_length'] ?? 'medium');
        $sanitized['seo_optimization'] = isset($input['seo_optimization']) ? true : false;
        $sanitized['auto_publish'] = isset($input['auto_publish']) ? true : false;
        $sanitized['content_types'] = array_map('sanitize_text_field', $input['content_types'] ?? ['post']);
        $sanitized['custom_prompts'] = array_map('sanitize_textarea_field', $input['custom_prompts'] ?? []);
        $sanitized['schedule_enabled'] = isset($input['schedule_enabled']) ? true : false;
        $sanitized['schedule_frequency'] = sanitize_text_field($input['schedule_frequency'] ?? 'daily');
        $sanitized['schedule_time'] = sanitize_text_field($input['schedule_time'] ?? '02:00');
        $sanitized['max_posts_per_run'] = absint($input['max_posts_per_run'] ?? 5);
        $sanitized['featured_image_generation'] = isset($input['featured_image_generation']) ? true : false;
        $sanitized['internal_linking'] = isset($input['internal_linking']) ? true : false;
        $sanitized['schema_markup'] = isset($input['schema_markup']) ? true : false;
        return $sanitized;
    }

    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'premium-auto-content') === false) {
            return;
        }

        wp_enqueue_style('premium-auto-content-admin', PREMIUM_AUTO_CONTENT_PLUGIN_URL . 'assets/css/admin.css', [], PREMIUM_AUTO_CONTENT_VERSION);
        wp_enqueue_script('premium-auto-content-admin', PREMIUM_AUTO_CONTENT_PLUGIN_URL . 'assets/js/admin.js', ['jquery', 'wp-api'], PREMIUM_AUTO_CONTENT_VERSION, true);

        wp_localize_script('premium-auto-content-admin', 'premiumAutoContent', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('premium_auto_content_nonce'),
            'rest_url' => rest_url('premium-auto-content/v1/'),
            'strings' => [
                'generating' => __('Generating content...', 'premium-auto-content'),
                'success' => __('Content generated successfully!', 'premium-auto-content'),
                'error' => __('An error occurred. Please try again.', 'premium-auto-content'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'premium-auto-content'),
            ],
        ]);
    }

    /**
     * Frontend enqueue scripts
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('premium-auto-content-frontend', PREMIUM_AUTO_CONTENT_PLUGIN_URL . 'assets/css/frontend.css', [], PREMIUM_AUTO_CONTENT_VERSION);
        wp_enqueue_script('premium-auto-content-frontend', PREMIUM_AUTO_CONTENT_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], PREMIUM_AUTO_CONTENT_VERSION, true);
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Content Template post type
        register_post_type('premium_content_template', [
            'labels' => [
                'name' => __('Content Templates', 'premium-auto-content'),
                'singular_name' => __('Content Template', 'premium-auto-content'),
                'add_new' => __('Add New Template', 'premium-auto-content'),
                'add_new_item' => __('Add New Content Template', 'premium-auto-content'),
                'edit_item' => __('Edit Content Template', 'premium-auto-content'),
                'new_item' => __('New Content Template', 'premium-auto-content'),
                'view_item' => __('View Content Template', 'premium-auto-content'),
                'search_items' => __('Search Content Templates', 'premium-auto-content'),
                'not_found' => __('No templates found', 'premium-auto-content'),
                'not_found_in_trash' => __('No templates found in trash', 'premium-auto-content'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'premium-auto-content',
            'supports' => ['title', 'editor', 'revisions'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'has_archive' => false,
            'rewrite' => false,
            'menu_icon' => 'dashicons-editor-expand',
        ]);

        // Generation Log post type
        register_post_type('premium_generation_log', [
            'labels' => [
                'name' => __('Generation Logs', 'premium-auto-content'),
                'singular_name' => __('Generation Log', 'premium-auto-content'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'premium-auto-content',
            'supports' => ['title', 'revisions'],
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'has_archive' => false,
            'rewrite' => false,
            'menu_icon' => 'dashicons-history',
        ]);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        register_taxonomy('content_category', ['premium_content_template'], [
            'labels' => [
                'name' => __('Content Categories', 'premium-auto-content'),
                'singular_name' => __('Content Category', 'premium-auto-content'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'content-category'],
        ]);

        register_taxonomy('content_tag', ['premium_content_template'], [
            'labels' => [
                'name' => __('Content Tags', 'premium-auto-content'),
                'singular_name' => __('Content Tag', 'premium-auto-content'),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'content-tag'],
        ]);
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('premium-auto-content/v1', '/generate', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_generate_content'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'prompt' => ['required' => true, 'type' => 'string'],
                'content_type' => ['required' => false, 'type' => 'string', 'default' => 'post'],
                'length' => ['required' => false, 'type' => 'string', 'default' => 'medium'],
                'seo_keywords' => ['required' => false, 'type' => 'string'],
                'language' => ['required' => false, 'type' => 'string', 'default' => 'en_US'],
            ],
        ]);

        register_rest_route('premium-auto-content/v1', '/templates', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_templates'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        register_rest_route('premium-auto-content/v1', '/log', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_log'],
            'permission_callback' => [$this, 'check_admin_permission'],
            'args' => [
                'page' => ['required' => false, 'type' => 'integer', 'default' => 1],
                'per_page' => ['required' => false, 'type' => 'integer', 'default' => 20],
                'status' => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route('premium-auto-content/v1', '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_settings'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);

        register_rest_route('premium-auto-content/v1', '/test-api', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_test_api'],
            'permission_callback' => [$this, 'check_admin_permission'],
        ]);
    }

    /**
     * Check admin permission for REST API
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * REST API - Generate content
     */
    public function rest_generate_content($request) {
        $generator = new Premium_Content_Generator();
        $result = $generator->generate([
            'prompt' => $request->get_param('prompt'),
            'content_type' => $request->get_param('content_type'),
            'length' => $request->get_param('length'),
            'seo_keywords' => $request->get_param('seo_keywords'),
            'language' => $request->get_param('language'),
        ]);

        return rest_ensure_response($result);
    }

    /**
     * REST API - Get templates
     */
    public function rest_get_templates($request) {
        $templates = get_posts([
            'post_type' => 'premium_content_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
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

    /**
     * REST API - Get generation log
     */
    public function rest_get_log($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $status = $request->get_param('status');
        $offset = ($page - 1) * $per_page;

        $where = '';
        if ($status) {
            $where = $wpdb->prepare("WHERE status = %s", $status);
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table $where",
            $status ? [$status] : []
        ));

        return rest_ensure_response([
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ]);
    }

    /**
     * REST API - Get settings
     */
    public function rest_get_settings($request) {
        $settings = $this->settings;
        // Don't expose API key in REST API
        unset($settings['gemini_api_key']);
        return rest_ensure_response($settings);
    }

    /**
     * REST API - Test API connection
     */
    public function rest_test_api($request) {
        $api = new Premium_Gemini_API($this->settings['gemini_api_key']);
        $result = $api->test_connection();
        return rest_ensure_response($result);
    }

    /**
     * AJAX - Generate content
     */
    public function ajax_generate_content() {
        check_ajax_referer('premium_auto_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'premium-auto-content')]);
        }

        $generator = new Premium_Content_Generator();
        $result = $generator->generate([
            'prompt' => sanitize_textarea_field($_POST['prompt'] ?? ''),
            'content_type' => sanitize_text_field($_POST['content_type'] ?? 'post'),
            'length' => sanitize_text_field($_POST['length'] ?? 'medium'),
            'seo_keywords' => sanitize_textarea_field($_POST['seo_keywords'] ?? ''),
            'language' => sanitize_text_field($_POST['language'] ?? 'en_US'),
        ]);

        if (isset($result['error'])) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    /**
     * AJAX - Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('premium_auto_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'premium-auto-content')]);
        }

        $settings = $this->sanitize_settings($_POST['settings'] ?? []);
        update_option('premium_auto_content_settings', $settings);
        $this->settings = $settings;

        wp_send_json_success(['message' => __('Settings saved successfully', 'premium-auto-content')]);
    }

    /**
     * AJAX - Test API connection
     */
    public function ajax_test_api() {
        check_ajax_referer('premium_auto_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'premium-auto-content')]);
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $api = new Premium_Gemini_API($api_key);
        $result = $api->test_connection();
        
        if (isset($result['error'])) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    /**
     * AJAX - Bulk generate content
     */
    public function ajax_bulk_generate() {
        check_ajax_referer('premium_auto_content_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'premium-auto-content')]);
        }

        $template_ids = array_map('absint', $_POST['template_ids'] ?? []);
        $count = absint($_POST['count'] ?? 1);
        
        $generator = new Premium_Content_Generator();
        $results = [];
        
        foreach ($template_ids as $template_id) {
            $template = get_post($template_id);
            if (!$template) continue;
            
            for ($i = 0; $i < $count; $i++) {
                $result = $generator->generate([
                    'prompt' => get_post_meta($template_id, '_prompt_template', true),
                    'content_type' => get_post_meta($template_id, '_content_type', true),
                    'length' => get_post_meta($template_id, '_default_length', true),
                    'seo_keywords' => get_post_meta($template_id, '_seo_keywords', true),
                    'language' => $this->settings['default_language'],
                ]);
                $results[] = $result;
            }
        }

        wp_send_json_success(['results' => $results]);
    }

    /**
     * Scheduled content generation
     */
    public function scheduled_content_generation() {
        if (!$this->settings['schedule_enabled']) {
            return;
        }

        $generator = new Premium_Content_Generator();
        $scheduler = new Premium_Scheduler();
        $scheduler->run_scheduled_generation($generator, $this->settings);
    }

    /**
     * Cleanup old data
     */
    public function cleanup_old_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        // Delete logs older than 90 days
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        ));

        // Clean up queue
        $queue_table = $wpdb->prefix . 'premium_auto_content_queue';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $queue_table WHERE status = 'completed' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));
    }

    /**
     * Register Gutenberg blocks
     */
    public function register_gutenberg_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_register_script(
            'premium-auto-content-blocks',
            PREMIUM_AUTO_CONTENT_PLUGIN_URL . 'assets/js/blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-api'],
            PREMIUM_AUTO_CONTENT_VERSION,
            true
        );

        register_block_type('premium-auto-content/generator', [
            'editor_script' => 'premium-auto-content-blocks',
            'render_callback' => [$this, 'render_generator_block'],
        ]);

        register_block_type('premium-auto-content/display', [
            'editor_script' => 'premium-auto-content-blocks',
            'render_callback' => [$this, 'render_display_block'],
        ]);
    }

    /**
     * Render generator block
     */
    public function render_generator_block($attributes) {
        ob_start();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/block-generator.php';
        return ob_get_clean();
    }

    /**
     * Render display block
     */
    public function render_display_block($attributes) {
        ob_start();
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/block-display.php';
        return ob_get_clean();
    }

    /**
     * Shortcode - Content display
     */
    public function shortcode_content_display($atts) {
        $atts = shortcode_atts([
            'post_id' => '',
            'template' => '',
            'show_seo' => 'true',
        ], $atts);

        // Implementation for displaying generated content
        return '<div class="premium-auto-content-display">Content display shortcode</div>';
    }

    /**
     * Shortcode - Content generation form
     */
    public function shortcode_content_form($atts) {
        $atts = shortcode_atts([
            'template_id' => '',
        ], $atts);

        // Implementation for frontend generation form
        return '<div class="premium-auto-content-form">Generation form shortcode</div>';
    }

    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Admin settings page
     */
    public function admin_settings_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Admin generator page
     */
    public function admin_generator_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-generator.php';
    }

    /**
     * Admin templates page
     */
    public function admin_templates_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-templates.php';
    }

    /**
     * Admin log page
     */
    public function admin_log_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-log.php';
    }

    /**
     * Admin SEO page
     */
    public function admin_seo_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-seo.php';
    }

    /**
     * Admin scheduler page
     */
    public function admin_scheduler_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-scheduler.php';
    }

    /**
     * Admin license page
     */
    public function admin_license_page() {
        include PREMIUM_AUTO_CONTENT_PLUGIN_DIR . 'templates/admin-license.php';
    }

    // Settings field callbacks
    public function api_section_callback() {
        echo '<p>' . __('Configure your Google Gemini API credentials to enable AI-powered content generation.', 'premium-auto-content') . '</p>';
    }

    public function gemini_api_key_field() {
        $value = $this->settings['gemini_api_key'] ?? '';
        echo '<input type="password" name="premium_auto_content_settings[gemini_api_key]" value="' . esc_attr($value) . '" class="regular-text" placeholder="Enter your Gemini API Key" />';
        echo '<p class="description">' . __('Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>', 'premium-auto-content') . '</p>';
    }

    public function gemini_model_field() {
        $models = [
            'gemini-1.5-pro' => 'Gemini 1.5 Pro (Latest)',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash (Fast)',
            'gemini-1.0-pro' => 'Gemini 1.0 Pro',
        ];
        $value = $this->settings['gemini_model'] ?? 'gemini-1.5-pro';
        echo '<select name="premium_auto_content_settings[gemini_model]" class="regular-text">';
        foreach ($models as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function generation_section_callback() {
        echo '<p>' . __('Configure default content generation parameters.', 'premium-auto-content') . '</p>';
    }

    public function default_language_field() {
        $languages = [
            'en_US' => 'English (US)',
            'en_GB' => 'English (UK)',
            'es_ES' => 'Spanish',
            'fr_FR' => 'French',
            'de_DE' => 'German',
            'it_IT' => 'Italian',
            'pt_BR' => 'Portuguese (Brazil)',
            'ru_RU' => 'Russian',
            'zh_CN' => 'Chinese (Simplified)',
            'ja_JP' => 'Japanese',
            'ko_KR' => 'Korean',
            'ar_SA' => 'Arabic',
            'hi_IN' => 'Hindi',
        ];
        $value = $this->settings['default_language'] ?? 'en_US';
        echo '<select name="premium_auto_content_settings[default_language]" class="regular-text">';
        foreach ($languages as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function content_length_field() {
        $lengths = [
            'short' => __('Short (~500 words)', 'premium-auto-content'),
            'medium' => __('Medium (~1500 words)', 'premium-auto-content'),
            'long' => __('Long (~3000 words)', 'premium-auto-content'),
            'comprehensive' => __('Comprehensive (~5000+ words)', 'premium-auto-content'),
        ];
        $value = $this->settings['content_length'] ?? 'medium';
        echo '<select name="premium_auto_content_settings[content_length]" class="regular-text">';
        foreach ($lengths as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function content_types_field() {
        $post_types = get_post_types(['public' => true], 'objects');
        $value = $this->settings['content_types'] ?? ['post'];
        echo '<div class="checkbox-group">';
        foreach ($post_types as $type) {
            if (in_array($type->name, ['attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation'])) {
                continue;
            }
            $checked = in_array($type->name, $value) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="premium_auto_content_settings[content_types][]" value="' . esc_attr($type->name) . '" ' . $checked . ' /> ' . esc_html($type->label) . '</label><br>';
        }
        echo '</div>';
    }

    public function seo_section_callback() {
        echo '<p>' . __('Enable SEO optimization features for generated content.', 'premium-auto-content') . '</p>';
    }

    public function seo_optimization_field() {
        $checked = $this->settings['seo_optimization'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[seo_optimization]" value="1" ' . $checked . ' /> ' . __('Enable automatic SEO optimization including meta tags, headings structure, keyword density, and readability improvements.', 'premium-auto-content') . '</label>';
    }

    public function internal_linking_field() {
        $checked = $this->settings['internal_linking'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[internal_linking]" value="1" ' . $checked . ' /> ' . __('Automatically add relevant internal links to existing content on your site.', 'premium-auto-content') . '</label>';
    }

    public function schema_markup_field() {
        $checked = $this->settings['schema_markup'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[schema_markup]" value="1" ' . $checked . ' /> ' . __('Generate JSON-LD schema markup for articles, FAQs, how-to guides, and other content types.', 'premium-auto-content') . '</label>';
    }

    public function publishing_section_callback() {
        echo '<p>' . __('Configure how generated content is published.', 'premium-auto-content') . '</p>';
    }

    public function auto_publish_field() {
        $checked = $this->settings['auto_publish'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[auto_publish]" value="1" ' . $checked . ' /> ' . __('Automatically publish generated content without review. Disable to save as draft for manual review.', 'premium-auto-content') . '</label>';
    }

    public function featured_image_generation_field() {
        $checked = $this->settings['featured_image_generation'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[featured_image_generation]" value="1" ' . $checked . ' /> ' . __('Generate AI-powered featured images for posts using DALL-E integration (requires additional API key).', 'premium-auto-content') . '</label>';
    }

    public function scheduler_section_callback() {
        echo '<p>' . __('Set up automated content generation on a schedule.', 'premium-auto-content') . '</p>';
    }

    public function schedule_enabled_field() {
        $checked = $this->settings['schedule_enabled'] ? 'checked="checked"' : '';
        echo '<label><input type="checkbox" name="premium_auto_content_settings[schedule_enabled]" value="1" ' . $checked . ' /> ' . __('Enable automated content generation scheduler.', 'premium-auto-content') . '</label>';
    }

    public function schedule_frequency_field() {
        $frequencies = [
            'hourly' => __('Hourly', 'premium-auto-content'),
            'twicedaily' => __('Twice Daily', 'premium-auto-content'),
            'daily' => __('Daily', 'premium-auto-content'),
            'weekly' => __('Weekly', 'premium-auto-content'),
            'monthly' => __('Monthly', 'premium-auto-content'),
        ];
        $value = $this->settings['schedule_frequency'] ?? 'daily';
        echo '<select name="premium_auto_content_settings[schedule_frequency]" class="regular-text">';
        foreach ($frequencies as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function schedule_time_field() {
        $value = $this->settings['schedule_time'] ?? '02:00';
        echo '<input type="time" name="premium_auto_content_settings[schedule_time]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    public function max_posts_per_run_field() {
        $value = $this->settings['max_posts_per_run'] ?? 5;
        echo '<input type="number" name="premium_auto_content_settings[max_posts_per_run]" value="' . esc_attr($value) . '" min="1" max="50" class="small-text" />';
        echo '<p class="description">' . __('Maximum number of posts to generate per scheduled run.', 'premium-auto-content') . '</p>';
    }

    /**
     * Get setting value
     */
    public function get_setting($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get all settings
     */
    public function get_settings() {
        return $this->settings;
    }
}

// Initialize the plugin
function premium_auto_content_generator() {
    return Premium_Auto_Content_Generator::get_instance();
}

// Kick off the plugin
premium_auto_content_generator();

// Helper functions
function premium_auto_content_get_plugin() {
    return Premium_Auto_Content_Generator::get_instance();
}

function premium_auto_content_generate($params) {
    $generator = new Premium_Content_Generator();
    return $generator->generate($params);
}

// Activation hook for external use
register_activation_hook(__FILE__, function() {
    // Plugin activation handled by class
});

// Deactivation hook for external use
register_deactivation_hook(__FILE__, function() {
    // Plugin deactivation handled by class
});