<?php
/**
 * Scheduler Class for Premium Auto Content Generator
 */

class Premium_Scheduler {

    public function run_scheduled_generation($generator, $settings) {
        if (!$settings['schedule_enabled']) {
            return ['message' => 'Scheduler is disabled'];
        }

        $max_posts = $settings['max_posts_per_run'] ?? 5;
        $templates = $this->get_active_templates();
        
        if (empty($templates)) {
            return ['message' => 'No active templates found'];
        }

        $results = [];
        $generated = 0;

        foreach ($templates as $template) {
            if ($generated >= $max_posts) break;

            $template_id = $template->ID;
            $count = min(1, $max_posts - $generated); // Generate 1 per template per run

            $template_results = $generator->generate_from_template($template_id, $count);
            
            foreach ($template_results as $result) {
                $results[] = $result;
                if (isset($result['success']) && $result['success']) {
                    $generated++;
                }
            }
        }

        // Also process queue
        $queue_results = $this->process_queue($generator, $max_posts - $generated);
        $results = array_merge($results, $queue_results);

        return [
            'generated' => $generated,
            'results' => $results,
            'timestamp' => current_time('mysql'),
        ];
    }

    public function process_queue($generator, $max_posts = 5) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
             WHERE status = 'pending' 
             AND scheduled_at <= %s 
             ORDER BY priority DESC, scheduled_at ASC 
             LIMIT %d",
            current_time('mysql'),
            $max_posts
        ));

        $results = [];

        foreach ($items as $item) {
            // Update status to processing
            $wpdb->update($table, ['status' => 'processing'], ['id' => $item->id]);

            try {
                $result = $generator->generate([
                    'prompt' => $item->prompt,
                    'content_type' => $item->content_type,
                    'length' => 'medium',
                    'seo_keywords' => $item->seo_keywords,
                    'language' => get_option('premium_auto_content_settings')['default_language'] ?? 'en_US',
                    'title' => $item->title,
                ]);

                if (isset($result['error'])) {
                    // Increment retry count
                    $retry_count = $item->retry_count + 1;
                    if ($retry_count >= 3) {
                        $wpdb->update($table, [
                            'status' => 'failed',
                            'retry_count' => $retry_count,
                        ], ['id' => $item->id]);
                    } else {
                        $wpdb->update($table, [
                            'status' => 'pending',
                            'retry_count' => $retry_count,
                            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
                        ], ['id' => $item->id]);
                    }
                    $results[] = ['error' => $result['error'], 'queue_id' => $item->id];
                } else {
                    $wpdb->update($table, [
                        'status' => 'completed',
                    ], ['id' => $item->id]);
                    $results[] = ['success' => true, 'post_id' => $result['post_id'], 'queue_id' => $item->id];
                }
            } catch (Exception $e) {
                $wpdb->update($table, [
                    'status' => 'failed',
                    'retry_count' => $item->retry_count + 1,
                ], ['id' => $item->id]);
                $results[] = ['error' => $e->getMessage(), 'queue_id' => $item->id];
            }
        }

        return $results;
    }

    private function get_active_templates() {
        return get_posts([
            'post_type' => 'premium_content_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => '_is_active',
            'meta_value' => 1,
        ]);
    }

    public function add_to_queue($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $defaults = [
            'template_id' => 0,
            'content_type' => 'post',
            'title' => '',
            'prompt' => '',
            'seo_keywords' => '',
            'scheduled_at' => current_time('mysql'),
            'priority' => 0,
        ];
        
        $data = array_merge($defaults, $data);
        
        $result = $wpdb->insert($table, [
            'template_id' => $data['template_id'],
            'content_type' => $data['content_type'],
            'title' => $data['title'],
            'prompt' => $data['prompt'],
            'seo_keywords' => $data['seo_keywords'],
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'pending',
            'priority' => $data['priority'],
        ]);
        
        if ($result === false) {
            return ['error' => 'Failed to add to queue'];
        }
        
        return ['success' => true, 'id' => $wpdb->insert_id];
    }

    public function get_queue_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $stats = [];
        $stats['pending'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
        $stats['processing'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'processing'");
        $stats['completed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'completed'");
        $stats['failed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        // Next scheduled
        $next = $wpdb->get_row(
            "SELECT * FROM $table 
             WHERE status = 'pending' 
             ORDER BY scheduled_at ASC 
             LIMIT 1"
        );
        $stats['next_scheduled'] = $next ? $next->scheduled_at : null;
        
        return $stats;
    }

    public function clear_completed($older_than_days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table 
             WHERE status = 'completed' 
             AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $older_than_days
        ));
        
        return $result !== false ? ['success' => true, 'deleted' => $result] : ['error' => 'Failed to clear completed items'];
    }

    public function reschedule_failed() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_queue';
        
        $items = $wpdb->get_results(
            "SELECT * FROM $table WHERE status = 'failed' AND retry_count < 3"
        );
        
        $count = 0;
        foreach ($items as $item) {
            $wpdb->update($table, [
                'status' => 'pending',
                'scheduled_at' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            ], ['id' => $item->id]);
            $count++;
        }
        
        return ['success' => true, 'rescheduled' => $count];
    }
}