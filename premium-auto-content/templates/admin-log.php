<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('Generation Log', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="log-filters" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
        <form method="get" id="log-filter-form">
            <input type="hidden" name="page" value="premium-auto-content-log" />
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label for="filter-status"><?php _e('Status:', 'premium-auto-content'); ?></label>
                    <select name="status" id="filter-status" class="regular-text">
                        <option value=""><?php _e('All', 'premium-auto-content'); ?></option>
                        <option value="completed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'completed'); ?>><?php _e('Completed', 'premium-auto-content'); ?></option>
                        <option value="failed" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'failed'); ?>><?php _e('Failed', 'premium-auto-content'); ?></option>
                        <option value="pending" <?php selected(isset($_GET['status']) ? $_GET['status'] : '', 'pending'); ?>><?php _e('Pending', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                
                <div>
                    <label for="filter-date-from"><?php _e('From:', 'premium-auto-content'); ?></label>
                    <input type="date" name="date_from" id="filter-date-from" class="regular-text" value="<?php echo esc_attr($_GET['date_from'] ?? ''); ?>" />
                </div>
                
                <div>
                    <label for="filter-date-to"><?php _e('To:', 'premium-auto-content'); ?></label>
                    <input type="date" name="date_to" id="filter-date-to" class="regular-text" value="<?php echo esc_attr($_GET['date_to'] ?? ''); ?>" />
                </div>
                
                <div>
                    <button type="submit" class="button button-primary"><?php _e('Filter', 'premium-auto-content'); ?></button>
                    <a href="<?php echo admin_url('admin.php?page=premium-auto-content-log'); ?>" class="button"><?php _e('Reset', 'premium-auto-content'); ?></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Summary -->
    <div class="log-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <?php
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        $where = '';
        $params = [];
        if (!empty($_GET['status'])) {
            $where = 'WHERE status = %s';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['date_from'])) {
            $where .= ($where ? ' AND' : ' WHERE') . ' DATE(created_at) >= %s';
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where .= ($where ? ' AND' : ' WHERE') . ' DATE(created_at) <= %s';
            $params[] = $_GET['date_to'];
        }
        
        $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table $where", $params));
        $completed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table $where AND status = 'completed'", $params));
        $failed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table $where AND status = 'failed'", $params));
        $avg_score = $wpdb->get_var($wpdb->prepare("SELECT AVG(seo_score) FROM $table $where AND status = 'completed'", $params));
        $total_tokens = $wpdb->get_var($wpdb->prepare("SELECT SUM(api_tokens_used) FROM $table $where", $params));
        ?>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total); ?></span>
            <span class="stat-label"><?php _e('Total', 'premium-auto-content'); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #46b450;"><?php echo number_format($completed); ?></span>
            <span class="stat-label"><?php _e('Successful', 'premium-auto-content'); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-number" style="color: #dc3232;"><?php echo number_format($failed); ?></span>
            <span class="stat-label"><?php _e('Failed', 'premium-auto-content'); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo round($avg_score ?? 0); ?></span>
            <span class="stat-label"><?php _e('Avg SEO Score', 'premium-auto-content'); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total_tokens ?? 0); ?></span>
            <span class="stat-label"><?php _e('Total Tokens', 'premium-auto-content'); ?></span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="logs-table-wrap">
        <form method="post" id="logs-bulk-action">
            <?php wp_nonce_field('bulk-logs-action', 'logs_nonce'); ?>
            <input type="hidden" name="action" value="bulk_logs" />
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action" id="bulk-action-select">
                        <option value=""><?php _e('Bulk Actions', 'premium-auto-content'); ?></option>
                        <option value="delete"><?php _e('Delete', 'premium-auto-content'); ?></option>
                        <option value="retry"><?php _e('Retry Failed', 'premium-auto-content'); ?></option>
                    </select>
                    <button type="submit" class="button action" id="do-bulk-action"><?php _e('Apply', 'premium-auto-content'); ?></button>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped" id="logs-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="check-all-logs" /></th>
                        <th><?php _e('Title', 'premium-auto-content'); ?></th>
                        <th style="width: 100px;"><?php _e('Type', 'premium-auto-content'); ?></th>
                        <th style="width: 80px;"><?php _e('Status', 'premium-auto-content'); ?></th>
                        <th style="width: 80px;"><?php _e('SEO Score', 'premium-auto-content'); ?></th>
                        <th style="width: 80px;"><?php _e('Tokens', 'premium-auto-content'); ?></th>
                        <th style="width: 100px;"><?php _e('Time (s)', 'premium-auto-content'); ?></th>
                        <th style="width: 150px;"><?php _e('Date', 'premium-auto-content'); ?></th>
                        <th style="width: 200px;"><?php _e('Error / Details', 'premium-auto-content'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $page = max(1, absint($_GET['paged'] ?? 1));
                    $per_page = 50;
                    $offset = ($page - 1) * $per_page;
                    
                    $orderby = 'created_at';
                    $order = 'DESC';
                    
                    $logs = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT %d OFFSET %d",
                        array_merge($params, [$per_page, $offset])
                    ));
                    
                    foreach ($logs as $log): ?>
                        <tr>
                            <td><input type="checkbox" name="log_ids[]" value="<?php echo $log->id; ?>" /></td>
                            <td>
                                <strong><?php echo esc_html(wp_trim_words($log->title, 10)); ?></strong>
                                <?php if ($log->post_id): ?>
                                    <br><a href="<?php echo admin_url('post.php?post=' . $log->post_id . '&action=edit'); ?>" target="_blank" class="view-post-link">
                                        <span class="dashicons dashicons-external" style="font-size: 12px;"></span> <?php _e('View Post', 'premium-auto-content'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(ucfirst($log->content_type)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($log->status); ?>">
                                    <?php echo esc_html(ucfirst($log->status)); ?>
                                </span>
                            </td>
                            <td>
                                <div class="seo-score-bar" style="width: 80px;">
                                    <div class="seo-score-fill" style="width: <?php echo esc_attr($log->seo_score); ?>%"></div>
                                </div>
                                <span><?php echo esc_html($log->seo_score); ?></span>
                            </td>
                            <td><?php echo number_format($log->api_tokens_used); ?></td>
                            <td><?php echo esc_html(number_format($log->generation_time, 2)); ?></td>
                            <td><?php echo esc_html(date('M j, Y H:i', strtotime($log->created_at))); ?></td>
                            <td>
                                <?php if ($log->error_message): ?>
                                    <div class="error-message" style="color: #dc3232; font-size: 12px; max-width: 300px;">
                                        <?php echo esc_html(wp_trim_words($log->error_message, 20)); ?>
                                        <?php if (strlen($log->error_message) > 150): ?>
                                            <button type="button" class="show-full-error" data-error="<?php echo esc_attr($log->error_message); ?>" style="background: none; border: none; color: #0073aa; padding: 0; font-size: 11px; cursor: pointer;"><?php _e('Show more', 'premium-auto-content'); ?></button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #999;"><?php _e('No errors', 'premium-auto-content'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $total_pages = ceil($total / $per_page);
                    $base_url = admin_url('admin.php?page=premium-auto-content-log');
                    if (!empty($_GET['status'])) $base_url .= '&status=' . urlencode($_GET['status']);
                    if (!empty($_GET['date_from'])) $base_url .= '&date_from=' . urlencode($_GET['date_from']);
                    if (!empty($_GET['date_to'])) $base_url .= '&date_to=' . urlencode($_GET['date_to']);
                    
                    echo paginate_links([
                        'base' => $base_url . '&paged=%#%',
                        'format' => '',
                        'current' => $page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'type' => 'plain',
                    ]);
                    ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Error Detail Modal -->
    <div id="error-modal" class="premium-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><?php _e('Error Details', 'premium-auto-content'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <pre id="full-error" style="background: #f8f8f8; padding: 15px; border-radius: 4px; overflow: auto; max-height: 400px;"></pre>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Check all
        $('#check-all-logs').on('change', function() {
            $('#logs-table tbody input[type="checkbox"]').prop('checked', $(this).prop('checked'));
        });

        // Show full error
        $(document).on('click', '.show-full-error', function() {
            var error = $(this).data('error');
            $('#full-error').text(error);
            $('#error-modal').show();
        });

        // Close modal
        $('.modal-close, .modal-overlay').on('click', function() {
            $('#error-modal').hide();
        });

        // Bulk action confirmation
        $('#logs-bulk-action').on('submit', function(e) {
            var action = $('#bulk-action-select').val();
            var checked = $('#logs-table tbody input[type="checkbox"]:checked').length;
            
            if (!action) {
                e.preventDefault();
                alert('<?php _e('Please select a bulk action', 'premium-auto-content'); ?>');
                return;
            }
            
            if (checked === 0) {
                e.preventDefault();
                alert('<?php _e('Please select at least one item', 'premium-auto-content'); ?>');
                return;
            }
            
            if (action === 'delete' && !confirm('<?php _e('Are you sure you want to delete the selected log entries?', 'premium-auto-content'); ?>')) {
                e.preventDefault();
            }
        });
    });
    </script>

    <style>
    .stat-card { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; text-align: center; }
    .stat-number { font-size: 24px; font-weight: 600; display: block; }
    .stat-label { color: #666; font-size: 12px; }
    .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-failed { background: #f8d7da; color: #721c24; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .seo-score-bar { width: 80px; height: 6px; background: #eee; border-radius: 3px; display: inline-block; vertical-align: middle; margin-right: 5px; }
    .seo-score-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #dc3232, #ffc107, #46b450); }
    .view-post-link { font-size: 11px; color: #0073aa; text-decoration: none; }
    .view-post-link:hover { text-decoration: underline; }
    .error-message { display: flex; align-items: center; gap: 5px; }
    .premium-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000; display: flex; align-items: center; justify-content: center; }
    .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { position: relative; background: #fff; border-radius: 8px; padding: 30px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-header h3 { margin: 0; }
    .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; line-height: 1; }
    .modal-body { padding: 10px 0; }
    </style>