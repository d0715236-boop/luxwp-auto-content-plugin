<?php
/**
 * Settings Class for Premium Auto Content Generator
 */

class Premium_Auto_Content_Settings {

    private $settings = [];

    public function __construct() {
        $this->load();
    }

    public function load() {
        $this->settings = get_option('premium_auto_content_settings', $this->get_defaults());
    }

    public function get_defaults() {
        return [
            'gemini_api_key' => '',
            'gemini_model' => 'gemini-1.5-pro',
            'default_language' => 'en_US',
            'content_length' => 'medium',
            'seo_optimization' => true,
            'auto_publish' => false,
            'content_types' => ['post'],
            'custom_prompts' => [],
            'schedule_enabled' => false,
            'schedule_frequency' => 'daily',
            'schedule_time' => '02:00',
            'max_posts_per_run' => 5,
            'featured_image_generation' => false,
            'internal_linking' => true,
            'schema_markup' => true,
        ];
    }

    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    public function get_all() {
        return $this->settings;
    }

    public function set($key, $value) {
        $this->settings[$key] = $value;
        return $this->save();
    }

    public function update($data) {
        $this->settings = array_merge($this->settings, $data);
        return $this->save();
    }

    public function save() {
        return update_option('premium_auto_content_settings', $this->settings);
    }

    public function reset() {
        $this->settings = $this->get_defaults();
        return $this->save();
    }

    public function has_api_key() {
        return !empty($this->settings['gemini_api_key']);
    }

    public function get_model() {
        return $this->settings['gemini_model'] ?? 'gemini-1.5-pro';
    }

    public function get_api_key() {
        return $this->settings['gemini_api_key'] ?? '';
    }
}