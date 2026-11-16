<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('License Management', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <!-- License Status -->
    <div class="license-status-panel" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 30px; margin-bottom: 30px; text-align: center;">
        <?php
        $license = get_option('premium_auto_content_license', []);
        $is_valid = !empty($license['status']) && $license['status'] === 'valid';
        ?>
        
        <div class="license-status <?php echo $is_valid ? 'valid' : 'invalid'; ?>" style="display: inline-block; padding: 30px 60px; border-radius: 8px; background: <?php echo $is_valid ? '#d4edda' : '#f8d7da'; ?>; border: 2px solid <?php echo $is_valid ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <span class="dashicons <?php echo $is_valid ? 'dashicons-yes-alt' : 'dashicons-no-alt'; ?>" style="font-size: 48px; color: <?php echo $is_valid ? '#155724' : '#721c24'; ?>; vertical-align: middle; margin-right: 15px;"></span>
            <span style="font-size: 24px; font-weight: 600; color: <?php echo $is_valid ? '#155724' : '#721c24'; ?>; vertical-align: middle;">
                <?php echo $is_valid ? __('License Active', 'premium-auto-content') : __('License Not Activated', 'premium-auto-content'); ?>
            </span>
        </div>
        
        <?php if ($is_valid): ?>
            <div class="license-details" style="margin-top: 20px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                <table class="license-info-table" style="width: 100%;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600; width: 30%;"><?php _e('License Key:', 'premium-auto-content'); ?></td>
                        <td style="padding: 8px 0; font-family: monospace; background: #f4f4f4; padding: 5px 10px; border-radius: 3px;"><?php echo esc_html(substr($license['key'], 0, 8) . '****' . substr($license['key'], -4)); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600;"><?php _e('Status:', 'premium-auto-content'); ?></td>
                        <td style="padding: 8px 0;"><span style="color: #46b450; font-weight: 600;"><?php _e('Valid', 'premium-auto-content'); ?></span></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600;"><?php _e('Verified:', 'premium-auto-content'); ?></td>
                        <td style="padding: 8px 0;"><?php echo esc_html(date('M j, Y H:i', strtotime($license['verified_at']))); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: 600;"><?php _e('Expires:', 'premium-auto-content'); ?></td>
                        <td style="padding: 8px 0;"><?php echo esc_html(date('M j, Y', strtotime($license['expires_at']))); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="license-actions" style="margin-top: 20px;">
                <button type="button" id="renew-license" class="button button-secondary">
                    <span class="dashicons dashicons-update"></span> <?php _e('Renew License', 'premium-auto-content'); ?>
                </button>
                <button type="button" id="deactivate-license" class="button" style="margin-left: 10px;">
                    <span class="dashicons dashicons-no-alt"></span> <?php _e('Deactivate', 'premium-auto-content'); ?>
                </button>
            </div>
        <?php else: ?>
            <p style="margin-top: 20px; color: #666; max-width: 500px; margin-left: auto; margin-right: auto;">
                <?php _e('Activate your premium license to unlock all features including unlimited content generation, priority support, and advanced SEO optimization.', 'premium-auto-content'); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Activation Form -->
    <?php if (!$is_valid): ?>
    <div class="license-activation-panel" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 30px; max-width: 600px; margin: 0 auto 30px;">
        <h2 style="text-align: center; margin-bottom: 20px;"><?php _e('Activate Premium License', 'premium-auto-content'); ?></h2>
        
        <form id="license-activation-form">
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="license-key" style="display: block; font-weight: 600; margin-bottom: 8px;"><?php _e('License Key *', 'premium-auto-content'); ?></label>
                <input type="text" id="license-key" name="license_key" class="regular-text" style="width: 100%; padding: 12px; font-size: 16px;" placeholder="LUXWP-XXXX-XXXX-XXXX-XXXX" required />
                <p class="description" style="margin-top: 8px;"><?php _e('Enter your license key from LuxWP. Format: LUXWP-XXXX-XXXX-XXXX-XXXX', 'premium-auto-content'); ?></p>
            </div>
            
            <div class="form-actions" style="text-align: center;">
                <button type="submit" class="button button-primary button-large" style="width: 100%; padding: 15px 30px; font-size: 16px;">
                    <span class="dashicons dashicons-unlock" style="vertical-align: middle;"></span> <?php _e('Activate License', 'premium-auto-content'); ?>
                </button>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="margin-bottom: 10px;"><?php _e('Don\'t have a license key?', 'premium-auto-content'); ?></p>
            <a href="https://luxwp.com/premium-wordpress-themes-and-plugins" target="_blank" class="button button-secondary button-large">
                <span class="dashicons dashicons-external" style="vertical-align: middle;"></span> <?php _e('Get Premium License at LuxWP', 'premium-auto-content'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Features Comparison -->
    <div class="features-comparison" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 30px; margin-bottom: 30px;">
        <h2 style="text-align: center; margin-bottom: 30px;"><?php _e('Free vs Premium Features', 'premium-auto-content'); ?></h2>
        
        <table class="comparison-table" style="width: 100%; border-collapse: collapse; max-width: 1000px; margin: 0 auto;">
            <thead>
                <tr style="background: #f4f4f4;">
                    <th style="padding: 15px; text-align: left; border: 1px solid #ddd; width: 40%;"><?php _e('Feature', 'premium-auto-content'); ?></th>
                    <th style="padding: 15px; text-align: center; border: 1px solid #ddd; width: 30%;"><?php _e('Free Version', 'premium-auto-content'); ?></th>
                    <th style="padding: 15px; text-align: center; border: 1px solid #ddd; width: 30%;"><?php _e('Premium Version', 'premium-auto-content'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('AI Content Generation', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Monthly Generation Limit', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">10 posts</td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">Unlimited</td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Content Templates', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">3 templates</td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">Unlimited</td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('SEO Optimization', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Internal Linking', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Schema Markup Generation', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Automated Scheduler', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Bulk Generation', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Featured Image Generation', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('SEO Analytics Dashboard', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Multi-language Support', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">English only</td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;">12+ languages</td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('Priority Support', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('API Access (REST)', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 15px; border: 1px solid #ddd;"><?php _e('White-label Option', 'premium-auto-content'); ?></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span></td>
                    <td style="padding: 15px; text-align: center; border: 1px solid #ddd;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Purchase CTA -->
    <div class="purchase-cta" style="text-align: center; padding: 40px; background: linear-gradient(135deg, #0073aa 0%, #005177 100%); border-radius: 8px; color: #fff;">
        <h2 style="margin: 0 0 15px; font-size: 32px;"><?php _e('Unlock Full Potential', 'premium-auto-content'); ?></h2>
        <p style="font-size: 18px; margin-bottom: 25px; opacity: 0.9;"><?php _e('Get unlimited AI-powered content generation, advanced SEO tools, and priority support.', 'premium-auto-content'); ?></p>
        <a href="https://luxwp.com/premium-wordpress-themes-and-plugins" target="_blank" class="button button-large" style="background: #fff; color: #0073aa; font-size: 18px; padding: 15px 40px; text-decoration: none; display: inline-block; border-radius: 4px; font-weight: 600;">
            <span class="dashicons dashicons-external" style="vertical-align: middle;"></span> <?php _e('Upgrade to Premium at LuxWP', 'premium-auto-content'); ?>
        </a>
        <p style="margin-top: 15px; opacity: 0.7; font-size: 14px;"><?php _e('Visit LuxWP for premium WordPress themes and plugins', 'premium-auto-content'); ?></p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // License activation
        $('#license-activation-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var key = $('#license-key').val().trim();
            
            if (!key) {
                alert('<?php _e('Please enter a license key', 'premium-auto-content'); ?>');
                return;
            }
            
            if (!key.startsWith('LUXWP-')) {
                if (!confirm('<?php _e('License key should start with "LUXWP-". Continue anyway?', 'premium-auto-content'); ?>')) {
                    return;
                }
            }
            
            $btn.prop('disabled', true).text('<?php _e('Activating...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_verify_license',
                    nonce: premiumAutoContent.nonce,
                    license_key: key
                },
                success: function(response) {
                    if (response.success && response.data.valid) {
                        alert('<?php _e('License activated successfully!', 'premium-auto-content'); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e('Activation failed:', 'premium-auto-content'); ?> ' + (response.data.message || '<?php _e('Invalid license key', 'premium-auto-content'); ?>'));
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-unlock" style="vertical-align: middle;"></span> <?php _e('Activate License', 'premium-auto-content'); ?>');
                }
            });
        });

        // Renew license
        $('#renew-license').on('click', function() {
            $('#license-key').val('');
            $('html, body').animate({scrollTop: $('#license-activation-form').offset().top - 50}, 500);
            $('#license-key').focus();
        });

        // Deactivate license
        $('#deactivate-license').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to deactivate your license? This will disable premium features.', 'premium-auto-content'); ?>')) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php _e('Deactivating...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_deactivate_license',
                    nonce: premiumAutoContent.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('License deactivated', 'premium-auto-content'); ?>');
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-no-alt"></span> <?php _e('Deactivate', 'premium-auto-content'); ?>');
                }
            });
        });
    });
    </script>

    <style>
    .comparison-table th { font-weight: 600; }
    .comparison-table td { vertical-align: middle; }
    .comparison-table .dashicons { font-size: 20px; width: 20px; height: 20px; }
    .license-info-table td { vertical-align: middle; }
    </style>