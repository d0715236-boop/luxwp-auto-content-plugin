<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('SEO Analytics', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <!-- Date Range Filter -->
    <div class="seo-filters" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
        <form method="get" id="seo-filter-form">
            <input type="hidden" name="page" value="premium-auto-content-seo" />
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label><?php _e('Period:', 'premium-auto-content'); ?></label>
                    <select name="period" class="regular-text">
                        <option value="7d" <?php selected($_GET['period'] ?? '', '7d'); ?>><?php _e('Last 7 Days', 'premium-auto-content'); ?></option>
                        <option value="30d" <?php selected($_GET['period'] ?? '', '30d'); ?>><?php _e('Last 30 Days', 'premium-auto-content'); ?></option>
                        <option value="90d" <?php selected($_GET['period'] ?? '', '90d'); ?>><?php _e('Last 90 Days', 'premium-auto-content'); ?></option>
                        <option value="1y" <?php selected($_GET['period'] ?? '', '1y'); ?>><?php _e('Last Year', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="button button-primary"><?php _e('Apply', 'premium-auto-content'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="seo-stats-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        $period = $_GET['period'] ?? '30d';
        $interval_map = ['7d' => '7 DAY', '30d' => '30 DAY', '90d' => '90 DAY', '1y' => '1 YEAR'];
        $interval = $interval_map[$period] ?? '30 DAY';
        
        $total_posts = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)"));
        $avg_score = $wpdb->get_var($wpdb->prepare("SELECT AVG(seo_score) FROM $table WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)"));
        $total_tokens = $wpdb->get_var($wpdb->prepare("SELECT SUM(api_tokens_used) FROM $table WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)"));
        $avg_tokens = $total_posts > 0 ? round($total_tokens / $total_posts) : 0;
        
        $top_keywords = $wpdb->get_results($wpdb->prepare(
            "SELECT seo_keywords, COUNT(*) as count FROM $table 
             WHERE status = 'completed' AND seo_keywords != '' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
             GROUP BY seo_keywords ORDER BY count DESC LIMIT 10"
        ));
        ?>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-editor-expand"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($total_posts); ?></span>
                <span class="stat-label"><?php _e('Posts Generated', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-chart-bar"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo round($avg_score ?? 0); ?></span>
                <span class="stat-label"><?php _e('Average SEO Score', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-performance"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($total_tokens ?? 0); ?></span>
                <span class="stat-label"><?php _e('Total Tokens', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-chart-line"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($avg_tokens); ?></span>
                <span class="stat-label"><?php _e('Avg Tokens/Post', 'premium-auto-content'); ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- SEO Score Distribution -->
        <div class="postbox">
            <h2 class="hndle"><span class="dashicons dashicons-chart-bar"></span> <?php _e('SEO Score Distribution', 'premium-auto-content'); ?></h2>
            <div class="inside">
                <canvas id="score-distribution-chart" height="300"></canvas>
                <div class="chart-legend" id="score-legend"></div>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="postbox">
            <h2 class="hndle"><span class="dashicons dashicons-chart-line"></span> <?php _e('Generation Trends', 'premium-auto-content'); ?></h2>
            <div class="inside">
                <canvas id="trends-chart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Content Type Performance & Top Performing Content -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div class="postbox">
            <h2 class="hndle"><span class="dashicons dashicons-admin-page"></span> <?php _e('Content Type Performance', 'premium-auto-content'); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Type', 'premium-auto-content'); ?></th>
                            <th><?php _e('Count', 'premium-auto-content'); ?></th>
                            <th><?php _e('Avg SEO Score', 'premium-auto-content'); ?></th>
                            <th><?php _e('Avg Tokens', 'premium-auto-content'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $type_stats = $wpdb->get_results($wpdb->prepare(
                            "SELECT content_type, COUNT(*) as count, AVG(seo_score) as avg_score, AVG(api_tokens_used) as avg_tokens
                             FROM $table 
                             WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                             GROUP BY content_type"
                        ));
                        foreach ($type_stats as $stat): ?>
                            <tr>
                                <td><?php echo esc_html(ucfirst($stat->content_type)); ?></td>
                                <td><?php echo number_format($stat->count); ?></td>
                                <td><?php echo round($stat->avg_score); ?></td>
                                <td><?php echo number_format(round($stat->avg_tokens)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="postbox">
            <h2 class="hndle"><span class="dashicons dashicons-star-filled"></span> <?php _e('Top Performing Content', 'premium-auto-content'); ?></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'premium-auto-content'); ?></th>
                            <th><?php _e('SEO Score', 'premium-auto-content'); ?></th>
                            <th><?php _e('Tokens', 'premium-auto-content'); ?></th>
                            <th><?php _e('Date', 'premium-auto-content'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $top_content = $wpdb->get_results($wpdb->prepare(
                            "SELECT title, seo_score, api_tokens_used, created_at, post_id
                             FROM $table 
                             WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                             ORDER BY seo_score DESC, created_at DESC 
                             LIMIT 10"
                        ));
                        foreach ($top_content as $content): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html(wp_trim_words($content->title, 8)); ?></strong>
                                    <?php if ($content->post_id): ?>
                                        <br><a href="<?php echo admin_url('post.php?post=' . $content->post_id . '&action=edit'); ?>" target="_blank" style="font-size: 11px;">
                                            <span class="dashicons dashicons-external" style="font-size: 10px;"></span> <?php _e('Edit', 'premium-auto-content'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="seo-score-bar" style="width: 80px;">
                                        <div class="seo-score-fill" style="width: <?php echo esc_attr($content->seo_score); ?>%"></div>
                                    </div>
                                    <?php echo esc_html($content->seo_score); ?>
                                </td>
                                <td><?php echo number_format($content->api_tokens_used); ?></td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($content->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SEO Recommendations Summary -->
    <div class="postbox">
        <h2 class="hndle"><span class="dashicons dashicons-lightbulb"></span> <?php _e('Common SEO Issues', 'premium-auto-content'); ?></h2>
        <div class="inside">
            <?php
            // Analyze recent content for common issues
            $recent_content = $wpdb->get_results($wpdb->prepare(
                "SELECT generated_content FROM $table 
                 WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                 LIMIT 50"
            ));
            
            $issues = [
                'short_content' => 0,
                'missing_h1' => 0,
                'few_headings' => 0,
                'missing_meta' => 0,
                'no_lists' => 0,
                'no_images' => 0,
            ];
            
            foreach ($recent_content as $item) {
                $content = $item->generated_content;
                $word_count = str_word_count(strip_tags($content));
                $h1_count = substr_count($content, '# ');
                $h2_count = substr_count($content, '## ');
                $has_meta = preg_match('/meta description[:\s]+/i', $content);
                $has_lists = (substr_count($content, '- ') + substr_count($content, '* ') + preg_match_all('/^\d+\.\s/m', $content)) > 0;
                $has_images = (preg_match('/wp:image/', $content) || preg_match('/!\[.*\]\(.*\)/', $content));
                
                if ($word_count < 1000) $issues['short_content']++;
                if ($h1_count !== 1) $issues['missing_h1']++;
                if ($h2_count < 3) $issues['few_headings']++;
                if (!$has_meta) $issues['missing_meta']++;
                if (!$has_lists) $issues['no_lists']++;
                if (!$has_images) $issues['no_images']++;
            }
            
            $total_analyzed = count($recent_content);
            ?>
            <table class="wp-list-table widefat fixed striped" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th><?php _e('Issue', 'premium-auto-content'); ?></th>
                        <th><?php _e('Affected Posts', 'premium-auto-content'); ?></th>
                        <th><?php _e('Percentage', 'premium-auto-content'); ?></th>
                        <th><?php _e('Recommendation', 'premium-auto-content'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $issue_details = [
                        'short_content' => [
                            'label' => __('Content too short (< 1000 words)', 'premium-auto-content'),
                            'rec' => __('Use "Long" or "Comprehensive" length setting for more in-depth content.', 'premium-auto-content'),
                        ],
                        'missing_h1' => [
                            'label' => __('Missing or multiple H1 headings', 'premium-auto-content'),
                            'rec' => __('Ensure each post has exactly one H1 heading (title).', 'premium-auto-content'),
                        ],
                        'few_headings' => [
                            'label' => __('Too few H2 headings (< 3)', 'premium-auto-content'),
                            'rec' => __('Structure content with more H2 sections for better readability and SEO.', 'premium-auto-content'),
                        ],
                        'missing_meta' => [
                            'label' => __('Missing meta description', 'premium-auto-content'),
                            'rec' => __('Enable SEO optimization in settings to auto-generate meta descriptions.', 'premium-auto-content'),
                        ],
                        'no_lists' => [
                            'label' => __('No bullet/numbered lists', 'premium-auto-content'),
                            'rec' => __('Add lists to improve readability and featured snippet chances.', 'premium-auto-content'),
                        ],
                        'no_images' => [
                            'label' => __('No image suggestions', 'premium-auto-content'),
                            'rec' => __('Enable featured image generation or add image placeholders manually.', 'premium-auto-content'),
                        ],
                    ];
                    
                    foreach ($issues as $key => $count):
                        if ($count > 0):
                            $pct = $total_analyzed > 0 ? round(($count / $total_analyzed) * 100) : 0;
                    ?>
                        <tr>
                            <td><?php echo $issue_details[$key]['label']; ?></td>
                            <td><?php echo $count; ?> / <?php echo $total_analyzed; ?></td>
                            <td>
                                <div class="seo-score-bar" style="width: 100px;">
                                    <div class="seo-score-fill" style="width: <?php echo $pct; ?>%; background: #dc3232;"></div>
                                </div>
                                <?php echo $pct; ?>%
                            </td>
                            <td><?php echo $issue_details[$key]['rec']; ?></td>
                        </tr>
                    <?php
                        endif;
                    endforeach;
                    if (array_sum($issues) === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #46b450;">
                                <?php _e('No common SEO issues detected! Great job.', 'premium-auto-content'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Chart.js would be needed for charts - simplified version with CSS
    jQuery(document).ready(function($) {
        // Simple CSS-based chart rendering for score distribution
        renderScoreDistribution();
        renderTrends();
    });

    function renderScoreDistribution() {
        // This would use Chart.js in production
        // For now, we'll show a simple summary
        var $container = $('#score-legend');
        $container.html('<p style="text-align: center; color: #666; padding: 20px;"><?php _e('Chart visualization requires Chart.js library. Install via npm or CDN for full charts.', 'premium-auto-content'); ?></p>');
    }

    function renderTrends() {
        var $container = $('#trends-chart');
        // Would render with Chart.js
    }
    </script>

    <style>
    .stat-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .stat-icon { width: 50px; height: 50px; border-radius: 10px; background: #0073aa; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; }
    .stat-number { font-size: 28px; font-weight: 600; display: block; }
    .stat-label { color: #666; font-size: 13px; }
    .seo-score-bar { width: 80px; height: 6px; background: #eee; border-radius: 3px; display: inline-block; vertical-align: middle; margin-right: 5px; }
    .seo-score-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #dc3232, #ffc107, #46b450); }
    .chart-legend { display: flex; justify-content: center; gap: 15px; margin-top: 15px; flex-wrap: wrap; font-size: 12px; }
    .legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-color { width: 12px; height: 12px; border-radius: 2px; }
    </style>