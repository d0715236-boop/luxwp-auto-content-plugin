<div class="pac-block-generator" data-block-id="<?php echo esc_attr(uniqid()); ?>">
    <div class="pac-block-toolbar">
        <span class="dashicons dashicons-welcome-write"></span>
        <strong><?php _e('AI Content Generator Block', 'premium-auto-content'); ?></strong>
    </div>
    
    <div class="pac-block-form">
        <div class="pac-block-field">
            <label><?php _e('Topic / Prompt', 'premium-auto-content'); ?></label>
            <textarea class="pac-block-prompt" rows="3" placeholder="<?php _e('Enter your topic...', 'premium-auto-content'); ?>"></textarea>
        </div>
        
        <div class="pac-block-options">
            <select class="pac-block-type">
                <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                <option value="product"><?php _e('Product', 'premium-auto-content'); ?></option>
            </select>
            <select class="pac-block-length">
                <option value="short"><?php _e('Short', 'premium-auto-content'); ?></option>
                <option value="medium" selected><?php _e('Medium', 'premium-auto-content'); ?></option>
                <option value="long"><?php _e('Long', 'premium-auto-content'); ?></option>
            </select>
        </div>
        
        <button type="button" class="button button-primary pac-block-generate">
            <span class="dashicons dashicons-welcome-write"></span> <?php _e('Generate', 'premium-auto-content'); ?>
        </button>
    </div>
    
    <div class="pac-block-preview" style="display: none;">
        <div class="pac-block-preview-header">
            <strong><?php _e('Preview', 'premium-auto-content'); ?></strong>
            <button type="button" class="pac-block-insert"><?php _e('Insert into Editor', 'premium-auto-content'); ?></button>
        </div>
        <div class="pac-block-preview-content"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.pac-block-generate').on('click', function() {
        var $block = $(this).closest('.pac-block-generator');
        var $btn = $(this);
        var $preview = $block.find('.pac-block-preview');
        var $previewContent = $block.find('.pac-block-preview-content');
        
        $btn.prop('disabled', true).text('<?php _e('Generating...', 'premium-auto-content'); ?>');
        
        $.ajax({
            url: premiumAutoContent.ajax_url,
            type: 'POST',
            data: {
                action: 'premium_auto_content_generate',
                nonce: premiumAutoContent.nonce,
                prompt: $block.find('.pac-block-prompt').val(),
                content_type: $block.find('.pac-block-type').val(),
                length: $block.find('.pac-block-length').val(),
            },
            success: function(response) {
                if (response.success) {
                    $previewContent.html(formatContent(response.data.content));
                    $preview.show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-welcome-write"></span> <?php _e('Generate', 'premium-auto-content'); ?>');
            }
        });
    });
    
    $('.pac-block-insert').on('click', function() {
        var $block = $(this).closest('.pac-block-generator');
        var content = $block.find('.pac-block-preview-content').html();
        // This would integrate with Gutenberg API in full implementation
        alert('<?php _e('Insert functionality requires Gutenberg integration.', 'premium-auto-content'); ?>');
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
});
</script>

<style>
.pac-block-generator { border: 1px solid #ddd; border-radius: 4px; padding: 15px; background: #fff; }
.pac-block-toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 15px; color: #666; font-size: 13px; }
.pac-block-field { margin-bottom: 15px; }
.pac-block-field label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
.pac-block-field textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-family: inherit; }
.pac-block-options { display: flex; gap: 10px; margin-bottom: 15px; }
.pac-block-options select { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
.pac-block-generate { width: 100%; }
.pac-block-preview { border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
.pac-block-preview-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
.pac-block-preview-content { max-height: 300px; overflow: auto; line-height: 1.6; }
.pac-block-preview-content h1, .pac-block-preview-content h2, .pac-block-preview-content h3 { margin-top: 1em; }
</style>