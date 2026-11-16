<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('Settings', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <form method="post" action="options.php" id="premium-auto-content-settings-form">
        <?php
        settings_fields('premium_auto_content_settings');
        do_settings_sections('premium-auto-content-settings');
        submit_button(__('Save Settings', 'premium-auto-content'), 'primary', 'submit', false);
        ?>
    </form>

    <div class="premium-auto-content-tabs" style="margin-top: 30px;">
        <h2><?php _e('API Connection Test', 'premium-auto-content'); ?></h2>
        <div class="api-test-panel">
            <p><?php _e('Test your Gemini API connection before saving settings.', 'premium-auto-content'); ?></p>
            <div class="test-form">
                <input type="password" id="test-api-key" class="regular-text" placeholder="<?php _e('Enter API Key to test', 'premium-auto-content'); ?>" />
                <select id="test-api-model" class="regular-text" style="margin-left: 10px;">
                    <option value="gemini-1.5-pro"><?php _e('Gemini 1.5 Pro', 'premium-auto-content'); ?></option>
                    <option value="gemini-1.5-flash"><?php _e('Gemini 1.5 Flash', 'premium-auto-content'); ?></option>
                    <option value="gemini-1.0-pro"><?php _e('Gemini 1.0 Pro', 'premium-auto-content'); ?></option>
                </select>
                <button type="button" id="test-api-connection" class="button button-primary" style="margin-left: 10px;">
                    <span class="dashicons dashicons-update" style="display: none;"></span>
                    <span class="button-text"><?php _e('Test Connection', 'premium-auto-content'); ?></span>
                </button>
            </div>
            <div id="api-test-result" class="test-result" style="display: none; margin-top: 15px;"></div>
        </div>
    </div>

    <div class="premium-auto-content-tabs" style="margin-top: 30px;">
        <h2><?php _e('Usage Statistics', 'premium-auto-content'); ?></h2>
        <div class="usage-stats">
            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'premium_auto_content_log';
            $total_tokens = $wpdb->get_var("SELECT SUM(api_tokens_used) FROM $table WHERE status = 'completed'");
            $total_requests = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'completed'");
            $avg_tokens = $total_requests > 0 ? round($total_tokens / $total_requests) : 0;
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Metric', 'premium-auto-content'); ?></th>
                        <th><?php _e('Value', 'premium-auto-content'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Total API Requests', 'premium-auto-content'); ?></td>
                        <td><?php echo number_format($total_requests); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Total Tokens Used', 'premium-auto-content'); ?></td>
                        <td><?php echo number_format($total_tokens ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Average Tokens/Request', 'premium-auto-content'); ?></td>
                        <td><?php echo number_format($avg_tokens); ?></td>
                    </tr>
                    <tr>
                        <td><?php _e('Estimated Cost (USD)', 'premium-auto-content'); ?></td>
                        <td>$<?php echo number_format(($total_tokens ?? 0) * 0.00001, 4); ?> <?php _e("(estimate for Gemini 1.5 Pro)", 'premium-auto-content'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="premium-auto-content-tabs" style="margin-top: 30px;">
        <h2><?php _e('Export / Import Settings', 'premium-auto-content'); ?></h2>
        <div class="export-import-panel">
            <div class="export-section">
                <h3><?php _e('Export Settings', 'premium-auto-content'); ?></h3>
                <p><?php _e('Download your current settings as a JSON file for backup or migration.', 'premium-auto-content'); ?></p>
                <button type="button" id="export-settings" class="button"><?php _e('Export Settings', 'premium-auto-content'); ?></button>
            </div>
            <div class="import-section">
                <h3><?php _e('Import Settings', 'premium-auto-content'); ?></h3>
                <p><?php _e('Upload a previously exported settings file.', 'premium-auto-content'); ?></p>
                <form id="import-form" enctype="multipart/form-data">
                    <input type="file" name="settings_file" accept=".json" />
                    <button type="submit" class="button button-primary"><?php _e('Import Settings', 'premium-auto-content'); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // API Connection Test
        $('#test-api-connection').on('click', function() {
            var $btn = $(this);
            var $icon = $btn.find('.dashicons');
            var $text = $btn.find('.button-text');
            var apiKey = $('#test-api-key').val();
            var model = $('#test-api-model').val();
            var $result = $('#api-test-result');
            
            if (!apiKey) {
                $result.removeClass('success error').addClass('error').html('<p><?php _e('Please enter an API key', 'premium-auto-content'); ?></p>').show();
                return;
            }
            
            $btn.prop('disabled', true);
            $icon.show().addClass('spin');
            $text.text('<?php _e('Testing...', 'premium-auto-content'); ?>');
            $result.hide();
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_test_api',
                    nonce: premiumAutoContent.nonce,
                    api_key: apiKey,
                    model: model
                },
                success: function(response) {
                    if (response.success) {
                        $result.removeClass('error').addClass('success').html('<p><strong><?php _e('Success!', 'premium-auto-content'); ?></strong> ' + response.data.message + '</p>');
                    } else {
                        $result.removeClass('success').addClass('error').html('<p><strong><?php _e('Error:', 'premium-auto-content'); ?></strong> ' + response.data.message + '</p>');
                    }
                    $result.show();
                },
                error: function() {
                    $result.removeClass('success').addClass('error').html('<p><?php _e('Connection test failed. Please check your network.', 'premium-auto-content'); ?></p>').show();
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $icon.hide().removeClass('spin');
                    $text.text('<?php _e('Test Connection', 'premium-auto-content'); ?>');
                }
            });
        });

        // Export Settings
        $('#export-settings').on('click', function() {
            var settings = <?php echo json_encode(get_option('premium_auto_content_settings', [])); ?>;
            // Remove API key for security
            delete settings.gemini_api_key;
            
            var blob = new Blob([JSON.stringify(settings, null, 2)], {type: 'application/json'});
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'premium-auto-content-settings-' + new Date().toISOString().split('T')[0] + '.json';
            a.click();
            URL.revokeObjectURL(url);
        });

        // Import Settings
        $('#import-form').on('submit', function(e) {
            e.preventDefault();
            var file = $(this).find('input[type="file"]')[0].files[0];
            if (!file) return;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    $.ajax({
                        url: premiumAutoContent.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'premium_auto_content_save_settings',
                            nonce: premiumAutoContent.nonce,
                            settings: settings
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php _e('Settings imported successfully!', 'premium-auto-content'); ?>');
                                location.reload();
                            } else {
                                alert('<?php _e('Import failed:', 'premium-auto-content'); ?> ' + response.data.message);
                            }
                        }
                    });
                } catch (err) {
                    alert('<?php _e('Invalid JSON file', 'premium-auto-content'); ?>');
                }
            };
            reader.readAsText(file);
        });
    });
    </script>

    <style>
    .premium-auto-content-tabs { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; }
    .premium-auto-content-tabs h2 { margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #eee; }
    .api-test-panel .test-form { margin: 15px 0; }
    .test-result { padding: 15px; border-radius: 4px; }
    .test-result.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .test-result.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .export-import-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .export-section, .import-section { padding: 20px; background: #fafafa; border-radius: 4px; }
    .export-section h3, .import-section h3 { margin-top: 0; }
    .dashicons.spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>