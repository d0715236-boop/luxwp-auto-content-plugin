<div class="premium-auto-content-form" id="premium-auto-content-form-<?php echo esc_attr(uniqid()); ?>">
    <?php if (!is_user_logged_in()): ?>
        <div class="pac-notice pac-error">
            <span class="dashicons dashicons-lock"></span>
            <p><?php _e('Please log in to use the AI Content Generator.', 'premium-auto-content'); ?></p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="button button-primary"><?php _e('Log In', 'premium-auto-content'); ?></a>
        </div>
    <?php elseif (!current_user_can('edit_posts')): ?>
        <div class="pac-notice pac-error">
            <span class="dashicons dashicons-warning"></span>
            <p><?php _e('You do not have permission to generate content.', 'premium-auto-content'); ?></p>
        </div>
    <?php else: ?>
        <form class="pac-generator-form" data-template="<?php echo esc_attr($atts['template_id']); ?>">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3 class="pac-form-title">
                    <span class="dashicons dashicons-welcome-write"></span>
                    <?php _e('AI Content Generator', 'premium-auto-content'); ?>
                </h3>
            <?php endif; ?>
            
            <div class="pac-form-group">
                <label for="pac-prompt"><?php _e('What would you like to write about?', 'premium-auto-content'); ?></label>
                <textarea id="pac-prompt" name="prompt" rows="4" placeholder="<?php _e('Enter your topic, keywords, or detailed instructions...', 'premium-auto-content'); ?>" required></textarea>
                <p class="pac-hint"><?php _e('Be specific for better results. Example: "Write a beginner\'s guide to WordPress SEO covering keywords, meta tags, and technical setup."', 'premium-auto-content'); ?></p>
            </div>
            
            <div class="pac-form-row">
                <div class="pac-form-group">
                    <label for="pac-content-type"><?php _e('Content Type', 'premium-auto-content'); ?></label>
                    <select id="pac-content-type" name="content_type">
                        <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                        <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                        <option value="product"><?php _e('Product Description', 'premium-auto-content'); ?></option>
                        <option value="portfolio"><?php _e('Portfolio Case Study', 'premium-auto-content'); ?></option>
                        <option value="faq"><?php _e('FAQ Page', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                
                <div class="pac-form-group">
                    <label for="pac-length"><?php _e('Length', 'premium-auto-content'); ?></label>
                    <select id="pac-length" name="length">
                        <option value="short"><?php _e('Short (~500 words)', 'premium-auto-content'); ?></option>
                        <option value="medium" selected><?php _e('Medium (~1500 words)', 'premium-auto-content'); ?></option>
                        <option value="long"><?php _e('Long (~3000 words)', 'premium-auto-content'); ?></option>
                        <option value="comprehensive"><?php _e('Comprehensive (~5000+ words)', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                
                <div class="pac-form-group">
                    <label for="pac-language"><?php _e('Language', 'premium-auto-content'); ?></label>
                    <select id="pac-language" name="language">
                        <option value="en_US"><?php _e('English (US)', 'premium-auto-content'); ?></option>
                        <option value="es_ES"><?php _e('Spanish', 'premium-auto-content'); ?></option>
                        <option value="fr_FR"><?php _e('French', 'premium-auto-content'); ?></option>
                        <option value="de_DE"><?php _e('German', 'premium-auto-content'); ?></option>
                        <option value="pt_BR"><?php _e('Portuguese', 'premium-auto-content'); ?></option>
                        <option value="ru_RU"><?php _e('Russian', 'premium-auto-content'); ?></option>
                        <option value="zh_CN"><?php _e('Chinese', 'premium-auto-content'); ?></option>
                        <option value="ja_JP"><?php _e('Japanese', 'premium-auto-content'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="pac-form-group">
                <label for="pac-seo-keywords"><?php _e('SEO Keywords (optional)', 'premium-auto-content'); ?></label>
                <input type="text" id="pac-seo-keywords" name="seo_keywords" placeholder="keyword1, keyword2, keyword3" />
            </div>
            
            <div class="pac-form-actions">
                <button type="submit" class="button button-primary button-large pac-generate-btn">
                    <span class="dashicons dashicons-welcome-write"></span>
                    <span class="btn-text"><?php echo esc_attr($atts['button_text']); ?></span>
                    <span class="spinner"></span>
                </button>
            </div>
        </form>
        
        <!-- Result Display -->
        <div class="pac-result" style="display: none;">
            <div class="pac-result-header">
                <h4><?php _e('Generated Content', 'premium-auto-content'); ?></h4>
                <div class="pac-result-actions">
                    <button type="button" class="button pac-copy-btn">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'premium-auto-content'); ?>
                    </button>
                    <button type="button" class="button button-primary pac-save-btn">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Save as Draft', 'premium-auto-content'); ?>
                    </button>
                    <button type="button" class="button pac-regenerate-btn">
                        <span class="dashicons dashicons-update"></span> <?php _e('Regenerate', 'premium-auto-content'); ?>
                    </button>
                </div>
            </div>
            <div class="pac-result-content"></div>
            <div class="pac-result-meta">
                <span class="pac-meta-item"><strong><?php _e('SEO Score:', 'premium-auto-content'); ?></strong> <span class="pac-seo-score">0</span></span>
                <span class="pac-meta-item"><strong><?php _e('Tokens:', 'premium-auto-content'); ?></strong> <span class="pac-tokens">0</span></span>
                <span class="pac-meta-item"><strong><?php _e('Time:', 'premium-auto-content'); ?></strong> <span class="pac-time">0s</span></span>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.pac-generator-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('.pac-generate-btn');
        var $result = $form.siblings('.pac-result');
        var $content = $result.find('.pac-result-content');
        
        $btn.prop('disabled', true).addClass('loading');
        $btn.find('.spinner').css('display', 'inline-block');
        $btn.find('.btn-text').text('<?php _e('Generating...', 'premium-auto-content'); ?>');
        $result.hide();
        
        var data = {
            action: 'premium_auto_content_generate',
            nonce: premiumAutoContent.nonce,
            prompt: $form.find('[name="prompt"]').val(),
            content_type: $form.find('[name="content_type"]').val(),
            length: $form.find('[name="length"]').val(),
            seo_keywords: $form.find('[name="seo_keywords"]').val(),
            language: $form.find('[name="language"]').val(),
        };
        
        $.ajax({
            url: premiumAutoContent.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $content.html(formatContent(response.data.content));
                    $result.find('.pac-seo-score').text(response.data.seo_score || 'N/A');
                    $result.find('.pac-tokens').text(number_format(response.data.tokens?.total_tokens || 0));
                    $result.find('.pac-time').text(response.data.generation_time?.toFixed(2) + 's');
                    $result.show();
                    $('html, body').animate({scrollTop: $result.offset().top - 50}, 500);
                    
                    // Store result for save/copy
                    $result.data('result', response.data);
                } else {
                    alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + (response.data.message || '<?php _e('Generation failed', 'premium-auto-content'); ?>'));
                }
            },
            error: function() {
                alert('<?php _e('An error occurred. Please try again.', 'premium-auto-content'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('loading');
                $btn.find('.spinner').hide();
                $btn.find('.btn-text').text('<?php echo esc_js($atts['button_text']); ?>');
            }
        });
    });
    
    // Copy content
    $(document).on('click', '.pac-copy-btn', function() {
        var $result = $(this).closest('.pac-result');
        var data = $result.data('result');
        if (data && data.content) {
            navigator.clipboard.writeText(data.content).then(function() {
                var $btn = $(this);
                var orig = $btn.html();
                $btn.html('<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'premium-auto-content'); ?>');
                setTimeout(function() { $btn.html(orig); }, 2000);
            }.bind(this));
        }
    });
    
    // Regenerate
    $(document).on('click', '.pac-regenerate-btn', function() {
        $(this).closest('.pac-result').hide();
        $(this).closest('.pac-form').find('.pac-generate-btn').click();
    });
    
    // Save as draft (would need REST API endpoint)
    $(document).on('click', '.pac-save-btn', function() {
        alert('<?php _e('Save as draft functionality requires backend implementation.', 'premium-auto-content'); ?>');
    });
    
    function formatContent(content) {
        return content
            .replace(/^### (.*$)/gm, '<h3>$1</h3>')
            .replace(/^## (.*$)/gm, '<h2>$1</h2>')
            .replace(/^# (.*$)/gm, '<h1>$1</h1>')
            .replace(/^\- (.*$)/gm, '<li>$1</li>')
            .replace(/^\* (.*$)/gm, '<li>$1</li>')
            .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/^(.+)$/gm, '<p>$1</p>')
            .replace(/<p><\/p>/g, '')
            .replace(/<p>(<h[1-3]>)/g, '$1')
            .replace(/(<\/h[1-3]>)<\/p>/g, '$1')
            .replace(/<p>(<ul>)/g, '$1')
            .replace(/(<\/ul>)<\/p>/g, '$1');
    }
    
    function number_format(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
});
</script>

<style>
.premium-auto-content-form { max-width: 800px; margin: 0 auto; }
.pac-notice { padding: 20px; border-radius: 4px; text-align: center; background: #f8f8f8; border: 1px solid #ddd; }
.pac-notice.pac-error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
.pac-notice .dashicons { font-size: 32px; margin-bottom: 10px; display: block; }
.pac-generator-form { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.pac-form-title { margin-top: 0; margin-bottom: 25px; color: #1d2327; display: flex; align-items: center; gap: 10px; }
.pac-form-title .dashicons { color: #0073aa; }
.pac-form-group { margin-bottom: 20px; }
.pac-form-group label { display: block; font-weight: 600; margin-bottom: 8px; }
.pac-form-group input, .pac-form-group select, .pac-form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
.pac-form-group textarea { min-height: 100px; resize: vertical; }
.pac-hint { margin: 8px 0 0; font-size: 12px; color: #666; font-style: italic; }
.pac-form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.pac-form-actions { margin-top: 30px; text-align: center; }
.pac-generate-btn { min-width: 200px; }
.pac-generate-btn.loading .spinner { display: inline-block !important; }
.pac-generate-btn .spinner { display: none; float: none; margin: 0 0 0 10px; vertical-align: middle; }
.pac-result { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-top: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.pac-result-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; flex-wrap: wrap; gap: 10px; }
.pac-result-header h4 { margin: 0; }
.pac-result-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.pac-result-content { line-height: 1.7; max-height: 500px; overflow-y: auto; }
.pac-result-content h1, .pac-result-content h2, .pac-result-content h3 { margin-top: 1.5em; color: #1d2327; }
.pac-result-content ul, .pac-result-content ol { margin: 1em 0; padding-left: 2em; }
.pac-result-content p { margin: 1em 0; }
.pac-result-meta { display: flex; gap: 20px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; flex-wrap: wrap; font-size: 13px; color: #666; }
@media (max-width: 600px) {
    .pac-form-row { grid-template-columns: 1fr; }
    .pac-result-header { flex-direction: column; align-items: flex-start; }
}
</style>