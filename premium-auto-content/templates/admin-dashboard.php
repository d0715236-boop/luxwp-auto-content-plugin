<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-editor-expand" style="vertical-align: middle;"></span>
        <?php _e('Premium Auto Content Generator', 'premium-auto-content'); ?>
    </h1>
    
    <div class="premium-auto-content-header-actions">
        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-generator'); ?>" class="button button-primary">
            <span class="dashicons dashicons-plus"></span> <?php _e('Generate Content', 'premium-auto-content'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-templates'); ?>" class="button">
            <span class="dashicons dashicons-admin-page"></span> <?php _e('Manage Templates', 'premium-auto-content'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-settings'); ?>" class="button">
            <span class="dashicons dashicons-admin-generic"></span> <?php _e('Settings', 'premium-auto-content'); ?>
        </a>
    </div>

    <hr class="wp-header-end">

    <!-- Stats Cards -->
    <div class="premium-auto-content-stats">
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-editor-expand"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($stats['total_generated']); ?></span>
                <span class="stat-label"><?php _e('Total Generated', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning"><span class="dashicons dashicons-warning"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($stats['total_failed']); ?></span>
                <span class="stat-label"><?php _e('Failed', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-performance"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($stats['total_tokens'] ?? 0); ?></span>
                <span class="stat-label"><?php _e('Tokens Used', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-chart-bar"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo round($stats['avg_seo_score'] ?? 0); ?></span>
                <span class="stat-label"><?php _e('Avg SEO Score', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($stats['this_week']); ?></span>
                <span class="stat-label"><?php _e('This Week', 'premium-auto-content'); ?></span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-calendar"></span></div>
            <div class="stat-content">
                <span class="stat-number"><?php echo number_format($stats['this_month']); ?></span>
                <span class="stat-label"><?php _e('This Month', 'premium-auto-content'); ?></span>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="premium-auto-content-dashboard-grid">
        <div class="dashboard-column">
            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-lightbulb"></span> <?php _e('Quick Actions', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <div class="quick-actions">
                        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-generator'); ?>" class="button button-large" style="width: 100%; margin-bottom: 10px;">
                            <span class="dashicons dashicons-plus-alt"></span> <?php _e('Generate New Content', 'premium-auto-content'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-templates'); ?>" class="button button-large" style="width: 100%; margin-bottom: 10px;">
                            <span class="dashicons dashicons-admin-page"></span> <?php _e('Create Template', 'premium-auto-content'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-scheduler'); ?>" class="button button-large" style="width: 100%; margin-bottom: 10px;">
                            <span class="dashicons dashicons-clock"></span> <?php _e('Schedule Generation', 'premium-auto-content'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-settings'); ?>" class="button button-large" style="width: 100%;">
                            <span class="dashicons dashicons-admin-generic"></span> <?php _e('Configure Settings', 'premium-auto-content'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-info"></span> <?php _e('Plugin Info', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <dl class="plugin-info">
                        <dt><?php _e('Version', 'premium-auto-content'); ?></dt>
                        <dd><?php echo PREMIUM_AUTO_CONTENT_VERSION; ?></dd>
                        
                        <dt><?php _e('License', 'premium-auto-content'); ?></dt>
                        <dd>
                            <?php 
                            $license = get_option('premium_auto_content_license', []);
                            if (!empty($license['status']) && $license['status'] === 'valid') {
                                echo '<span class="license-valid">' . __('Active', 'premium-auto-content') . '</span>';
                            } else {
                                echo '<span class="license-invalid">' . __('Not Activated', 'premium-auto-content') . '</span>';
                            }
                            ?>
                        </dd>
                        
                        <dt><?php _e('API Status', 'premium-auto-content'); ?></dt>
                        <dd>
                            <?php 
                            $settings = get_option('premium_auto_content_settings', []);
                            if (!empty($settings['gemini_api_key'])) {
                                echo '<span class="api-connected">' . __('Connected', 'premium-auto-content') . '</span>';
                            } else {
                                echo '<span class="api-disconnected">' . __('Not Configured', 'premium-auto-content') . '</span>';
                            }
                            ?>
                        </dd>
                        
                        <dt><?php _e('Scheduler', 'premium-auto-content'); ?></dt>
                        <dd>
                            <?php 
                            if (!empty($settings['schedule_enabled'])) {
                                echo '<span class="scheduler-active">' . __('Active', 'premium-auto-content') . ' (' . $settings['schedule_frequency'] . ')';
                            } else {
                                echo '<span class="scheduler-inactive">' . __('Inactive', 'premium-auto-content') . '</span>';
                            }
                            ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="dashboard-column">
            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-history"></span> <?php _e('Recent Generation Activity', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <?php if (!empty($stats['recent'])): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Title', 'premium-auto-content'); ?></th>
                                    <th><?php _e('Type', 'premium-auto-content'); ?></th>
                                    <th><?php _e('Status', 'premium-auto-content'); ?></th>
                                    <th><?php _e('SEO Score', 'premium-auto-content'); ?></th>
                                    <th><?php _e('Date', 'premium-auto-content'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent'] as $log): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html(wp_trim_words($log->title, 8)); ?></strong></td>
                                        <td><?php echo esc_html(ucfirst($log->content_type)); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                                                <?php echo esc_html(ucfirst($log->status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="seo-score-bar">
                                                <div class="seo-score-fill" style="width: <?php echo esc_attr($log->seo_score); ?>%"></div>
                                            </div>
                                            <span><?php echo esc_html($log->seo_score); ?></span>
                                        </td>
                                        <td><?php echo esc_html(date('M j, Y H:i', strtotime($log->created_at))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="view-all">
                            <a href="<?php echo admin_url('admin.php?page=premium-auto-content-log'); ?>">
                                <?php _e('View All Logs', 'premium-auto-content'); ?> &rarr;
                            </a>
                        </p>
                    <?php else: ?>
                        <p class="no-activity"><?php _e('No generation activity yet. Start by creating your first content!', 'premium-auto-content'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=premium-auto-content-generator'); ?>" class="button button-primary">
                            <?php _e('Generate First Content', 'premium-auto-content'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-star-filled"></span> <?php _e('Premium Themes & Plugins', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <p><?php _e('Enhance your WordPress site with premium themes and plugins from our partner:', 'premium-auto-content'); ?></p>
                    <div class="partner-promo">
                        <a href="https://luxwp.com/premium-wordpress-themes-and-plugins" target="_blank" class="button button-primary button-large" style="width: 100%;">
                            <span class="dashicons dashicons-external"></span> 
                            <?php _e('Visit LuxWP - Premium WordPress Themes & Plugins', 'premium-auto-content'); ?>
                        </a>
                        <p class="promo-note"><?php _e('Discover 100+ premium themes and plugins for WordPress at LuxWP', 'premium-auto-content'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.premium-auto-content-wrap { max-width: 1400px; }
.premium-auto-content-header-actions { margin: 15px 0; }
.premium-auto-content-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
.stat-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: #0073aa; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; }
.stat-icon.warning { background: #dc3232; }
.stat-number { font-size: 28px; font-weight: 600; display: block; }
.stat-label { color: #666; font-size: 13px; }
.premium-auto-content-dashboard-grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
.dashboard-column { display: flex; flex-direction: column; gap: 20px; }
.quick-actions .button { text-align: left; }
.plugin-info { margin: 0; }
.plugin-info dt { font-weight: 600; margin-top: 10px; }
.plugin-info dd { margin: 5px 0 0 0; color: #666; }
.license-valid, .api-connected, .scheduler-active { color: #46b450; font-weight: 500; }
.license-invalid, .api-disconnected, .scheduler-inactive { color: #dc3232; font-weight: 500; }
.status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.status-completed { background: #d4edda; color: #155724; }
.status-failed { background: #f8d7da; color: #721c24; }
.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cce5ff; color: #004085; }
.seo-score-bar { width: 80px; height: 6px; background: #eee; border-radius: 3px; display: inline-block; vertical-align: middle; margin-right: 5px; }
.seo-score-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #dc3232, #ffc107, #46b450); }
.partner-promo { margin-top: 15px; }
.promo-note { margin-top: 10px; font-size: 13px; color: #666; }
.no-activity { text-align: center; padding: 30px; color: #666; }
</style>