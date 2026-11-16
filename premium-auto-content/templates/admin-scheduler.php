<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('Content Scheduler', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <!-- Scheduler Settings -->
    <div class="scheduler-settings" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <h2><?php _e('Automated Generation Settings', 'premium-auto-content'); ?></h2>
        <form method="post" action="options.php" id="scheduler-settings-form">
            <?php
            settings_fields('premium_auto_content_settings');
            do_settings_fields('premium-auto-content-settings', 'premium_auto_content_scheduler_section');
            submit_button(__('Save Scheduler Settings', 'premium-auto-content'), 'primary', 'submit', false);
            ?>
        </form>
    </div>

    <!-- Schedule New Generation -->
    <div class="schedule-form-panel" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <h2><?php _e('Schedule New Content Generation', 'premium-auto-content'); ?></h2>
        <form id="schedule-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="sched-template"><?php _e('Template *', 'premium-auto-content'); ?></label>
                    <select id="sched-template" name="template_id" class="regular-text" required>
                        <option value=""><?php _e('Select a template...', 'premium-auto-content'); ?></option>
                        <?php foreach ($templates as $template): 
                            if (get_post_meta($template->ID, '_is_active', true)): ?>
                                <option value="<?php echo $template->ID; ?>"><?php echo esc_html($template->post_title); ?></option>
                            <?php endif; endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sched-content-type"><?php _e('Content Type', 'premium-auto-content'); ?></label>
                    <select id="sched-content-type" name="content_type" class="regular-text">
                        <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                        <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                        <option value="product"><?php _e('Product Description', 'premium-auto-content'); ?></option>
                        <option value="portfolio"><?php _e('Portfolio Case Study', 'premium-auto-content'); ?></option>
                        <option value="faq"><?php _e('FAQ Page', 'premium-auto-content'); ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sched-scheduled-at"><?php _e('Scheduled Date & Time *', 'premium-auto-content'); ?></label>
                    <input type="datetime-local" id="sched-scheduled-at" name="scheduled_at" class="regular-text" required />
                </div>

                <div class="form-group">
                    <label for="sched-priority"><?php _e('Priority', 'premium-auto-content'); ?></label>
                    <select id="sched-priority" name="priority" class="regular-text">
                        <option value="0"><?php _e('Normal', 'premium-auto-content'); ?></option>
                        <option value="5"><?php _e('High', 'premium-auto-content'); ?></option>
                        <option value="10"><?php _e('Urgent', 'premium-auto-content'); ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="sched-title"><?php _e('Custom Title (optional)', 'premium-auto-content'); ?></label>
                <input type="text" id="sched-title" name="title" class="large-text" placeholder="<?php _e('Leave empty to use template default', 'premium-auto-content'); ?>" />
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="sched-prompt"><?php _e('Custom Prompt (optional)', 'premium-auto-content'); ?></label>
                <textarea id="sched-prompt" name="prompt" rows="4" class="large-text" placeholder="<?php _e('Leave empty to use template prompt', 'premium-auto-content'); ?>"></textarea>
            </div>

            <div class="form-group">
                <label for="sched-seo-keywords"><?php _e('SEO Keywords (optional)', 'premium-auto-content'); ?></label>
                <input type="text" id="sched-seo-keywords" name="seo_keywords" class="large-text" placeholder="<?php _e('Leave empty to use template keywords', 'premium-auto-content'); ?>" />
            </div>

            <button type="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-calendar"></span> <?php _e('Schedule Generation', 'premium-auto-content'); ?>
            </button>
        </form>
    </div>

    <!-- Recurring Schedule Builder -->
    <div class="recurring-schedule-panel" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <h2><?php _e('Create Recurring Schedule', 'premium-auto-content'); ?></h2>
        <p><?php _e('Set up automatic recurring content generation based on a template.', 'premium-auto-content'); ?></p>
        <form id="recurring-schedule-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="rec-template"><?php _e('Template *', 'premium-auto-content'); ?></label>
                    <select id="rec-template" name="template_id" class="regular-text" required>
                        <option value=""><?php _e('Select a template...', 'premium-auto-content'); ?></option>
                        <?php foreach ($templates as $template): 
                            if (get_post_meta($template->ID, '_is_active', true)): ?>
                                <option value="<?php echo $template->ID; ?>"><?php echo esc_html($template->post_title); ?></option>
                            <?php endif; endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rec-frequency"><?php _e('Frequency *', 'premium-auto-content'); ?></label>
                    <select id="rec-frequency" name="frequency" class="regular-text" required>
                        <option value="daily"><?php _e('Daily', 'premium-auto-content'); ?></option>
                        <option value="weekly"><?php _e('Weekly', 'premium-auto-content'); ?></option>
                        <option value="monthly"><?php _e('Monthly', 'premium-auto-content'); ?></option>
                        <option value="custom"><?php _e('Custom Cron', 'premium-auto-content'); ?></option>
                    </select>
                </div>

                <div class="form-group" id="rec-time-wrapper">
                    <label for="rec-time"><?php _e('Time', 'premium-auto-content'); ?></label>
                    <input type="time" id="rec-time" name="time" class="regular-text" value="02:00" />
                </div>

                <div class="form-group" id="rec-day-wrapper" style="display: none;">
                    <label for="rec-day"><?php _e('Day of Week', 'premium-auto-content'); ?></label>
                    <select id="rec-day" name="day" class="regular-text">
                        <option value="1"><?php _e('Monday', 'premium-auto-content'); ?></option>
                        <option value="2"><?php _e('Tuesday', 'premium-auto-content'); ?></option>
                        <option value="3"><?php _e('Wednesday', 'premium-auto-content'); ?></option>
                        <option value="4"><?php _e('Thursday', 'premium-auto-content'); ?></option>
                        <option value="5"><?php _e('Friday', 'premium-auto-content'); ?></option>
                        <option value="6"><?php _e('Saturday', 'premium-auto-content'); ?></option>
                        <option value="0"><?php _e('Sunday', 'premium-auto-content'); ?></option>
                    </select>
                </div>

                <div class="form-group" id="rec-cron-wrapper" style="display: none;">
                    <label for="rec-cron"><?php _e('Custom Cron Expression', 'premium-auto-content'); ?></label>
                    <input type="text" id="rec-cron" name="cron" class="regular-text" placeholder="0 2 * * *" />
                    <p class="description"><?php _e('Standard cron format: minute hour day month weekday', 'premium-auto-content'); ?></p>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="rec-count"><?php _e('Posts per Run', 'premium-auto-content'); ?></label>
                <input type="number" id="rec-count" name="count" class="small-text" value="1" min="1" max="10" />
            </div>

            <button type="submit" class="button button-primary">
                <span class="dashicons dashicons-clock"></span> <?php _e('Create Recurring Schedule', 'premium-auto-content'); ?>
            </button>
        </form>
    </div>

    <!-- Queue Status -->
    <div class="queue-status">
        <h2><?php _e('Generation Queue', 'premium-auto-content'); ?></h2>
        <div class="queue-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <?php
            global $wpdb;
            $queue_table = $wpdb->prefix . 'premium_auto_content_queue';
            $pending = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table WHERE status = 'pending'");
            $processing = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table WHERE status = 'processing'");
            $completed = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table WHERE status = 'completed'");
            $failed = $wpdb->get_var("SELECT COUNT(*) FROM $queue_table WHERE status = 'failed'");
            ?>
            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <span class="stat-number" style="color: #ffc107;"><?php echo $pending; ?></span>
                <span class="stat-label"><?php _e('Pending', 'premium-auto-content'); ?></span>
            </div>
            <div class="stat-card" style="border-left: 4px solid #0073aa;">
                <span class="stat-number" style="color: #0073aa;"><?php echo $processing; ?></span>
                <span class="stat-label"><?php _e('Processing', 'premium-auto-content'); ?></span>
            </div>
            <div class="stat-card" style="border-left: 4px solid #46b450;">
                <span class="stat-number" style="color: #46b450;"><?php echo $completed; ?></span>
                <span class="stat-label"><?php _e('Completed', 'premium-auto-content'); ?></span>
            </div>
            <div class="stat-card" style="border-left: 4px solid #dc3232;">
                <span class="stat-number" style="color: #dc3232;"><?php echo $failed; ?></span>
                <span class="stat-label"><?php _e('Failed', 'premium-auto-content'); ?></span>
            </div>
        </div>

        <div class="queue-actions" style="margin-bottom: 15px;">
            <button type="button" id="process-queue-now" class="button button-primary">
                <span class="dashicons dashicons-update"></span> <?php _e('Process Queue Now', 'premium-auto-content'); ?>
            </button>
            <button type="button" id="clear-completed" class="button">
                <span class="dashicons dashicons-trash"></span> <?php _e('Clear Completed (30+ days)', 'premium-auto-content'); ?>
            </button>
            <button type="button" id="retry-failed" class="button">
                <span class="dashicons dashicons-rotate"></span> <?php _e('Retry Failed', 'premium-auto-content'); ?>
            </button>
        </div>

        <table class="wp-list-table widefat fixed striped" id="queue-table">
            <thead>
                <tr>
                    <th style="width: 5%;"><?php _e('ID', 'premium-auto-content'); ?></th>
                    <th style="width: 20%;"><?php _e('Title / Template', 'premium-auto-content'); ?></th>
                    <th style="width: 10%;"><?php _e('Type', 'premium-auto-content'); ?></th>
                    <th style="width: 10%;"><?php _e('Status', 'premium-auto-content'); ?></th>
                    <th style="width: 10%;"><?php _e('Priority', 'premium-auto-content'); ?></th>
                    <th style="width: 15%;"><?php _e('Scheduled', 'premium-auto-content'); ?></th>
                    <th style="width: 10%;"><?php _e('Retries', 'premium-auto-content'); ?></th>
                    <th style="width: 15%;"><?php _e('Actions', 'premium-auto-content'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $queue_items = $wpdb->get_results("SELECT * FROM $queue_table ORDER BY priority DESC, scheduled_at ASC LIMIT 100");
                foreach ($queue_items as $item):
                    $template_name = '';
                    if ($item->template_id) {
                        $template = get_post($item->template_id);
                        $template_name = $template ? $template->post_title : 'Template #' . $item->template_id;
                    }
                ?>
                    <tr data-id="<?php echo $item->id; ?>">
                        <td><?php echo $item->id; ?></td>
                        <td>
                            <strong><?php echo esc_html($item->title ?: ($template_name ? $template_name : 'Custom')); ?></strong>
                            <?php if ($template_name): ?>
                                <br><small><?php echo esc_html($template_name); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(ucfirst($item->content_type)); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($item->status); ?>">
                                <?php echo esc_html(ucfirst($item->status)); ?>
                            </span>
                        </td>
                        <td><?php echo $item->priority; ?></td>
                        <td><?php echo esc_html(date('M j, Y H:i', strtotime($item->scheduled_at))); ?></td>
                        <td><?php echo $item->retry_count; ?></td>
                        <td>
                            <div class="row-actions">
                                <?php if ($item->status === 'pending'): ?>
                                    <button type="button" class="button button-small run-now" data-id="<?php echo $item->id; ?>">
                                        <span class="dashicons dashicons-play"></span> <?php _e('Run Now', 'premium-auto-content'); ?>
                                    </button>
                                <?php endif; ?>
                                <?php if ($item->status === 'failed' && $item->retry_count < 3): ?>
                                    <button type="button" class="button button-small retry-item" data-id="<?php echo $item->id; ?>">
                                        <span class="dashicons dashicons-rotate"></span> <?php _e('Retry', 'premium-auto-content'); ?>
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="button button-small cancel-item" data-id="<?php echo $item->id; ?>">
                                    <span class="dashicons dashicons-trash"></span> <?php _e('Cancel', 'premium-auto-content'); ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($queue_items)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #666;">
                            <?php _e('Queue is empty. Schedule some content generation!', 'premium-auto-content'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Recurring schedule frequency change
        $('#rec-frequency').on('change', function() {
            var freq = $(this).val();
            $('#rec-time-wrapper').toggle(freq !== 'custom');
            $('#rec-day-wrapper').toggle(freq === 'weekly');
            $('#rec-cron-wrapper').toggle(freq === 'custom');
        });

        // Schedule form submit
        $('#schedule-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.prop('disabled', true).text('<?php _e('Scheduling...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_schedule_generation',
                    nonce: premiumAutoContent.nonce,
                    template_id: $('#sched-template').val(),
                    content_type: $('#sched-content-type').val(),
                    scheduled_at: $('#sched-scheduled-at').val(),
                    priority: $('#sched-priority').val(),
                    title: $('#sched-title').val(),
                    prompt: $('#sched-prompt').val(),
                    seo_keywords: $('#sched-seo-keywords').val(),
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Generation scheduled successfully!', 'premium-auto-content'); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + response.data.message);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-calendar"></span> <?php _e('Schedule Generation', 'premium-auto-content'); ?>');
                }
            });
        });

        // Recurring schedule form
        $('#recurring-schedule-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.prop('disabled', true).text('<?php _e('Creating...', 'premium-auto-content'); ?>');
            
            // Calculate next run time based on frequency
            var freq = $('#rec-frequency').val();
            var time = $('#rec-time').val();
            var day = $('#rec-day').val();
            var cron = $('#rec-cron').val();
            var scheduled_at = calculateNextRun(freq, time, day, cron);
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_create_recurring',
                    nonce: premiumAutoContent.nonce,
                    template_id: $('#rec-template').val(),
                    frequency: freq,
                    time: time,
                    day: day,
                    cron: cron,
                    count: $('#rec-count').val(),
                    scheduled_at: scheduled_at,
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Recurring schedule created!', 'premium-auto-content'); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + response.data.message);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-clock"></span> <?php _e('Create Recurring Schedule', 'premium-auto-content'); ?>');
                }
            });
        });

        function calculateNextRun(freq, time, day, cron) {
            var now = new Date();
            var [hours, minutes] = time.split(':').map(Number);
            
            if (freq === 'custom' && cron) {
                // Would need cron parser - simplified
                return new Date(now.getTime() + 3600000).toISOString().slice(0, 19).replace('T', ' ');
            }
            
            var next = new Date(now);
            next.setHours(hours, minutes, 0, 0);
            
            if (freq === 'daily') {
                if (next <= now) next.setDate(next.getDate() + 1);
            } else if (freq === 'weekly') {
                var targetDay = parseInt(day);
                var diff = (targetDay - next.getDay() + 7) % 7;
                if (diff === 0 && next <= now) diff = 7;
                next.setDate(next.getDate() + diff);
            } else if (freq === 'monthly') {
                if (next <= now) next.setMonth(next.getMonth() + 1);
            }
            
            return next.toISOString().slice(0, 19).replace('T', ' ');
        }

        // Process queue now
        $('#process-queue-now').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php _e('Processing...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_process_queue',
                    nonce: premiumAutoContent.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Queue processed!', 'premium-auto-content'); ?>');
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php _e('Process Queue Now', 'premium-auto-content'); ?>');
                }
            });
        });

        // Clear completed
        $('#clear-completed').on('click', function() {
            if (!confirm('<?php _e('Clear all completed items older than 30 days?', 'premium-auto-content'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php _e('Clearing...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_clear_completed',
                    nonce: premiumAutoContent.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Cleared!', 'premium-auto-content'); ?>');
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e('Clear Completed (30+ days)', 'premium-auto-content'); ?>');
                }
            });
        });

        // Retry failed
        $('#retry-failed').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php _e('Retrying...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_retry_failed',
                    nonce: premiumAutoContent.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Failed items rescheduled!', 'premium-auto-content'); ?>');
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-rotate"></span> <?php _e('Retry Failed', 'premium-auto-content'); ?>');
                }
            });
        });

        // Run queue item now
        $(document).on('click', '.run-now', function() {
            var $btn = $(this);
            var id = $btn.data('id');
            $btn.prop('disabled', true).text('<?php _e('Running...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_run_queue_item',
                    nonce: premiumAutoContent.nonce,
                    queue_id: id,
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-play"></span> <?php _e('Run Now', 'premium-auto-content'); ?>');
                }
            });
        });

        // Retry queue item
        $(document).on('click', '.retry-item', function() {
            var $btn = $(this);
            var id = $btn.data('id');
            $btn.prop('disabled', true).text('<?php _e('Retrying...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_retry_queue_item',
                    nonce: premiumAutoContent.nonce,
                    queue_id: id,
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-rotate"></span> <?php _e('Retry', 'premium-auto-content'); ?>');
                }
            });
        });

        // Cancel queue item
        $(document).on('click', '.cancel-item', function() {
            if (!confirm('<?php _e('Cancel this scheduled generation?', 'premium-auto-content'); ?>')) return;
            
            var $btn = $(this);
            var id = $btn.data('id');
            $btn.prop('disabled', true).text('<?php _e('Cancelling...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_cancel_queue_item',
                    nonce: premiumAutoContent.nonce,
                    queue_id: id,
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e('Cancel', 'premium-auto-content'); ?>');
                }
            });
        });
    });
    </script>

    <style>
    .scheduler-settings h2 { margin-top: 0; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
    .form-group .description { margin: 5px 0 0; font-size: 12px; color: #666; }
    .stat-card { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; text-align: center; }
    .stat-number { font-size: 24px; font-weight: 600; display: block; }
    .stat-label { color: #666; font-size: 12px; }
    .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-failed { background: #f8d7da; color: #721c24; }
    .row-actions { display: flex; gap: 5px; flex-wrap: wrap; }
    .row-actions .button { padding: 3px 8px; font-size: 12px; }
    .queue-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    </style>