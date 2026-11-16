<?php
$post_id = $post_id ?? get_the_ID();
$is_auto = get_post_meta($post_id, '_premium_auto_generated', true);
$seo_score = get_post_meta($post_id, '_premium_auto_seo_score', true);
$tokens = get_post_meta($post_id, '_premium_auto_tokens', 0);
$meta_desc = get_post_meta($post_id, '_premium_auto_meta_desc', true);
$schema = get_post_meta($post_id, '_premium_auto_schema', true);
$prompt = get_post_meta($post_id, '_premium_auto_prompt', true);
?>

<article class="premium-auto-content-article" itemscope itemtype="https://schema.org/Article">
    <?php if ($atts['show_seo'] === 'true' && ($seo_score || $tokens || $meta_desc)): ?>
        <div class="pac-article-meta" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <h4 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-chart-bar" style="color: #0073aa;"></span>
                <?php _e('AI Generation Metrics', 'premium-auto-content'); ?>
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <?php if ($seo_score): ?>
                    <div class="pac-metric">
                        <div class="pac-metric-value" style="font-size: 24px; font-weight: 700; color: <?php echo $seo_score >= 80 ? '#46b450' : ($seo_score >= 60 ? '#ffc107' : '#dc3232'); ?>;"><?php echo esc_html($seo_score); ?></div>
                        <div class="pac-metric-label" style="font-size: 13px; color: #666;"><?php _e('SEO Score', 'premium-auto-content'); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($tokens): ?>
                    <div class="pac-metric">
                        <div class="pac-metric-value" style="font-size: 24px; font-weight: 700; color: #0073aa;"><?php echo number_format($tokens); ?></div>
                        <div class="pac-metric-label" style="font-size: 13px; color: #666;"><?php _e('Tokens Used', 'premium-auto-content'); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($meta_desc): ?>
                    <div class="pac-metric" style="grid-column: 1 / -1;">
                        <div class="pac-metric-label" style="font-size: 13px; color: #666; margin-bottom: 5px;"><?php _e('Meta Description:', 'premium-auto-content'); ?></div>
                        <div style="font-style: italic; color: #333;"><?php echo esc_html($meta_desc); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="pac-article-content" itemprop="articleBody">
        <?php the_content(); ?>
    </div>

    <?php if ($atts['show_schema'] === 'true' && $schema): ?>
        <details class="pac-schema-details" style="margin-top: 40px; padding: 20px; background: #f4f4f4; border-radius: 8px;">
            <summary style="cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-code"></span>
                <?php _e('Schema Markup (JSON-LD)', 'premium-auto-content'); ?>
            </summary>
            <pre style="margin-top: 15px; overflow: auto; background: #fff; padding: 15px; border-radius: 4px; font-size: 12px; line-height: 1.5;"><?php echo esc_html($schema); ?></pre>
        </details>
    <?php endif; ?>

    <?php if ($prompt && $atts['show_seo'] === 'true'): ?>
        <details class="pac-prompt-details" style="margin-top: 20px; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
            <summary style="cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-lightbulb"></span>
                <?php _e('Original Generation Prompt', 'premium-auto-content'); ?>
            </summary>
            <div style="margin-top: 15px; font-style: italic; color: #666;"><?php echo esc_html($prompt); ?></div>
        </details>
    <?php endif; ?>
</article>

<style>
.pac-article-meta { border-left: 4px solid #0073aa; }
.pac-metric { text-align: center; }
.pac-schema-details summary, .pac-prompt-details summary { user-select: none; }
.pac-schema-details summary:hover, .pac-prompt-details summary:hover { color: #0073aa; }
</style>