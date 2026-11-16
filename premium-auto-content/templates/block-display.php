<div class="pac-block-display" data-block-id="<?php echo esc_attr(uniqid()); ?>">
    <div class="pac-block-toolbar">
        <span class="dashicons dashicons-visibility"></span>
        <strong><?php _e('AI Content Display Block', 'premium-auto-content'); ?></strong>
    </div>
    
    <div class="pac-block-form">
        <div class="pac-block-field">
            <label><?php _e('Post ID (leave empty for current post)', 'premium-auto-content'); ?></label>
            <input type="number" class="pac-block-post-id" min="1" placeholder="<?php _e('Current post', 'premium-auto-content'); ?>" />
        </div>
        
        <div class="pac-block-options">
            <label class="pac-checkbox">
                <input type="checkbox" class="pac-block-show-seo" checked />
                <span><?php _e('Show SEO Metrics', 'premium-auto-content'); ?></span>
            </label>
            <label class="pac-checkbox">
                <input type="checkbox" class="pac-block-show-schema" />
                <span><?php _e('Show Schema Markup', 'premium-auto-content'); ?></span>
            </label>
        </div>
    </div>
    
    <div class="pac-block-preview" style="display: none;">
        <div class="pac-block-preview-header">
            <strong><?php _e('Preview', 'premium-auto-content'); ?></strong>
        </div>
        <div class="pac-block-preview-content"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.pac-block-post-id, .pac-block-show-seo, .pac-block-show-schema').on('change', function() {
        var $block = $(this).closest('.pac-block-display');
        var postId = $block.find('.pac-block-post-id').val() || 'current';
        var showSeo = $block.find('.pac-block-show-seo').is(':checked');
        var showSchema = $block.find('.pac-block-show-schema').is(':checked');
        
        $block.find('.pac-block-preview').hide();
        
        // In full implementation, this would fetch via REST API
        $block.find('.pac-block-preview-content').html('<p><?php _e('Preview requires post ID and backend integration.', 'premium-auto-content'); ?></p>');
        $block.find('.pac-block-preview').show();
    });
});
</script>

<style>
.pac-block-display { border: 1px solid #ddd; border-radius: 4px; padding: 15px; background: #fff; }
.pac-block-toolbar { display: flex; align-items: center; gap: 8px; margin-bottom: 15px; color: #666; font-size: 13px; }
.pac-block-field { margin-bottom: 15px; }
.pac-block-field label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
.pac-block-field input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
.pac-block-options { display: flex; gap: 20px; }
.pac-checkbox { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.pac-block-preview { border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; }
.pac-block-preview-content { line-height: 1.6; }
</style>