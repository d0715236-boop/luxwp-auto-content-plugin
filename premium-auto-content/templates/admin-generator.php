<div class="wrap premium-auto-content-wrap">
    <h1 class="wp-heading-inline"><?php _e('Content Generator', 'premium-auto-content'); ?></h1>
    <hr class="wp-header-end">

    <div class="premium-auto-content-generator">
        <!-- Generator Form -->
        <div class="generator-panel">
            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-welcome-write"></span> <?php _e('Generate New Content', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <form id="content-generator-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gen-prompt"><?php _e('Content Prompt / Topic *', 'premium-auto-content'); ?></label>
                                <textarea id="gen-prompt" name="prompt" rows="4" class="large-text" placeholder="<?php _e('Enter your topic, keywords, or detailed instructions for the AI...', 'premium-auto-content'); ?>" required></textarea>
                                <p class="description"><?php _e('Describe what you want to write about. Be specific for better results. Example: "Write a comprehensive guide on WordPress SEO optimization for beginners, covering keywords, meta tags, and technical SEO."', 'premium-auto-content'); ?></p>
                            </div>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label for="gen-content-type"><?php _e('Content Type', 'premium-auto-content'); ?></label>
                                <select id="gen-content-type" name="content_type" class="regular-text">
                                    <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                                    <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                                    <option value="product"><?php _e('Product Description', 'premium-auto-content'); ?></option>
                                    <option value="portfolio"><?php _e('Portfolio Case Study', 'premium-auto-content'); ?></option>
                                    <option value="faq"><?php _e('FAQ Page', 'premium-auto-content'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="gen-length"><?php _e('Content Length', 'premium-auto-content'); ?></label>
                                <select id="gen-length" name="length" class="regular-text">
                                    <option value="short"><?php _e('Short (~500 words)', 'premium-auto-content'); ?></option>
                                    <option value="medium" selected><?php _e('Medium (~1500 words)', 'premium-auto-content'); ?></option>
                                    <option value="long"><?php _e('Long (~3000 words)', 'premium-auto-content'); ?></option>
                                    <option value="comprehensive"><?php _e('Comprehensive (~5000+ words)', 'premium-auto-content'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="gen-language"><?php _e('Language', 'premium-auto-content'); ?></label>
                                <select id="gen-language" name="language" class="regular-text">
                                    <option value="en_US"><?php _e('English (US)', 'premium-auto-content'); ?></option>
                                    <option value="en_GB"><?php _e('English (UK)', 'premium-auto-content'); ?></option>
                                    <option value="es_ES"><?php _e('Spanish', 'premium-auto-content'); ?></option>
                                    <option value="fr_FR"><?php _e('French', 'premium-auto-content'); ?></option>
                                    <option value="de_DE"><?php _e('German', 'premium-auto-content'); ?></option>
                                    <option value="it_IT"><?php _e('Italian', 'premium-auto-content'); ?></option>
                                    <option value="pt_BR"><?php _e('Portuguese (Brazil)', 'premium-auto-content'); ?></option>
                                    <option value="ru_RU"><?php _e('Russian', 'premium-auto-content'); ?></option>
                                    <option value="zh_CN"><?php _e('Chinese (Simplified)', 'premium-auto-content'); ?></option>
                                    <option value="ja_JP"><?php _e('Japanese', 'premium-auto-content'); ?></option>
                                    <option value="ko_KR"><?php _e('Korean', 'premium-auto-content'); ?></option>
                                    <option value="ar_SA"><?php _e('Arabic', 'premium-auto-content'); ?></option>
                                    <option value="hi_IN"><?php _e('Hindi', 'premium-auto-content'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="gen-seo-keywords"><?php _e('SEO Keywords (comma-separated)', 'premium-auto-content'); ?></label>
                                <input type="text" id="gen-seo-keywords" name="seo_keywords" class="large-text" placeholder="wordpress seo, seo optimization, search engine ranking" />
                                <p class="description"><?php _e('Enter target keywords to naturally incorporate into the content.', 'premium-auto-content'); ?></p>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="gen-title"><?php _e('Custom Title (optional)', 'premium-auto-content'); ?></label>
                                <input type="text" id="gen-title" name="title" class="large-text" placeholder="<?php _e('Leave empty for AI-generated title', 'premium-auto-content'); ?>" />
                            </div>
                        </div>

                        <div class="form-row template-selector">
                            <label><?php _e('Or use a template:', 'premium-auto-content'); ?></label>
                            <select id="gen-template" class="regular-text" style="max-width: 400px;">
                                <option value=""><?php _e('Select a template...', 'premium-auto-content'); ?></option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?php echo $template->ID; ?>" 
                                            data-prompt="<?php echo esc_attr(get_post_meta($template->ID, '_prompt_template', true)); ?>"
                                            data-type="<?php echo esc_attr(get_post_meta($template->ID, '_content_type', true)); ?>"
                                            data-length="<?php echo esc_attr(get_post_meta($template->ID, '_default_length', true)); ?>"
                                            data-keywords="<?php echo esc_attr(get_post_meta($template->ID, '_seo_keywords', true)); ?>">
                                        <?php echo esc_html($template->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select a template to auto-fill the form fields.', 'premium-auto-content'); ?></p>
                        </div>

                        <div class="form-actions">
                            <button type="submit" id="generate-btn" class="button button-primary button-large">
                                <span class="dashicons dashicons-welcome-write"></span>
                                <span class="btn-text"><?php _e('Generate Content', 'premium-auto-content'); ?></span>
                                <span class="spinner" style="float: none; margin: 0 0 0 10px; display: none;"></span>
                            </button>
                            <button type="button" id="generate-save-template" class="button button-secondary">
                                <span class="dashicons dashicons-admin-page"></span> <?php _e('Save as Template', 'premium-auto-content'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Panel -->
        <div class="generator-panel" id="results-panel" style="display: none;">
            <div class="postbox">
                <h2 class="hndle"><span class="dashicons dashicons-visibility"></span> <?php _e('Generated Content', 'premium-auto-content'); ?></h2>
                <div class="inside">
                    <div class="result-actions">
                        <button type="button" id="copy-content" class="button">
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy Content', 'premium-auto-content'); ?>
                        </button>
                        <button type="button" id="create-post" class="button button-primary">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Create Post', 'premium-auto-content'); ?>
                        </button>
                        <button type="button" id="regenerate" class="button">
                            <span class="dashicons dashicons-update"></span> <?php _e('Regenerate', 'premium-auto-content'); ?>
                        </button>
                    </div>

                    <div class="result-tabs">
                        <button type="button" class="tab-btn active" data-tab="content"><?php _e('Content', 'premium-auto-content'); ?></button>
                        <button type="button" class="tab-btn" data-tab="seo"><?php _e('SEO Analysis', 'premium-auto-content'); ?></button>
                        <button type="button" class="tab-btn" data-tab="schema"><?php _e('Schema Markup', 'premium-auto-content'); ?></button>
                        <button type="button" class="tab-btn" data-tab="raw"><?php _e('Raw Output', 'premium-auto-content'); ?></button>
                    </div>

                    <div class="tab-content">
                        <div class="tab-panel active" id="tab-content">
                            <div id="generated-content" class="generated-content-preview"></div>
                        </div>
                        <div class="tab-panel" id="tab-seo">
                            <div id="seo-analysis"></div>
                        </div>
                        <div class="tab-panel" id="tab-schema">
                            <pre id="schema-markup" class="schema-code"></pre>
                        </div>
                        <div class="tab-panel" id="tab-raw">
                            <pre id="raw-output" class="raw-code"></pre>
                        </div>
                    </div>

                    <div class="result-meta">
                        <div class="meta-item">
                            <strong><?php _e('Tokens Used:', 'premium-auto-content'); ?></strong>
                            <span id="tokens-used">0</span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Generation Time:', 'premium-auto-content'); ?></strong>
                            <span id="generation-time">0s</span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('SEO Score:', 'premium-auto-content'); ?></strong>
                            <span id="seo-score">0</span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Model:', 'premium-auto-content'); ?></strong>
                            <span id="model-used">gemini-1.5-pro</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div id="save-template-modal" class="premium-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Save as Template', 'premium-auto-content'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <form id="save-template-form">
                <div class="form-group">
                    <label><?php _e('Template Name *', 'premium-auto-content'); ?></label>
                    <input type="text" name="name" class="regular-text" required />
                </div>
                <div class="form-group">
                    <label><?php _e('Description', 'premium-auto-content'); ?></label>
                    <textarea name="description" rows="3" class="large-text"></textarea>
                </div>
                <div class="form-group">
                    <label><?php _e('Content Type *', 'premium-auto-content'); ?></label>
                    <select name="content_type" class="regular-text" required>
                        <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                        <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                        <option value="product"><?php _e('Product Description', 'premium-auto-content'); ?></option>
                        <option value="portfolio"><?php _e('Portfolio Case Study', 'premium-auto-content'); ?></option>
                        <option value="faq"><?php _e('FAQ Page', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('Default Length', 'premium-auto-content'); ?></label>
                    <select name="default_length" class="regular-text">
                        <option value="short"><?php _e('Short (~500 words)', 'premium-auto-content'); ?></option>
                        <option value="medium" selected><?php _e('Medium (~1500 words)', 'premium-auto-content'); ?></option>
                        <option value="long"><?php _e('Long (~3000 words)', 'premium-auto-content'); ?></option>
                        <option value="comprehensive"><?php _e('Comprehensive (~5000+ words)', 'premium-auto-content'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('SEO Keywords', 'premium-auto-content'); ?></label>
                    <input type="text" name="seo_keywords" class="large-text" />
                </div>
                <div class="modal-actions">
                    <button type="button" class="button modal-close"><?php _e('Cancel', 'premium-auto-content'); ?></button>
                    <button type="submit" class="button button-primary"><?php _e('Save Template', 'premium-auto-content'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var currentResult = null;
        
        // Template selector change
        $('#gen-template').on('change', function() {
            var option = $(this).find('option:selected');
            if (option.val()) {
                $('#gen-prompt').val(option.data('prompt'));
                $('#gen-content-type').val(option.data('type'));
                $('#gen-length').val(option.data('length'));
                $('#gen-seo-keywords').val(option.data('keywords'));
            }
        });

        // Generate content
        $('#content-generator-form').on('submit', function(e) {
            e.preventDefault();
            
            var $btn = $('#generate-btn');
            var $spinner = $btn.find('.spinner');
            var $btnText = $btn.find('.btn-text');
            
            $btn.prop('disabled', true);
            $spinner.show();
            $btnText.text('<?php _e('Generating...', 'premium-auto-content'); ?>');
            
            var data = {
                action: 'premium_auto_content_generate',
                nonce: premiumAutoContent.nonce,
                prompt: $('#gen-prompt').val(),
                content_type: $('#gen-content-type').val(),
                length: $('#gen-length').val(),
                seo_keywords: $('#gen-seo-keywords').val(),
                language: $('#gen-language').val(),
            };
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        currentResult = response.data;
                        displayResult(response.data);
                        $('#results-panel').show();
                        $('html, body').animate({scrollTop: $('#results-panel').offset().top - 50}, 500);
                    } else {
                        alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + (response.data.message || '<?php _e('Generation failed', 'premium-auto-content'); ?>'));
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred. Please try again.', 'premium-auto-content'); ?>');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    $spinner.hide();
                    $btnText.text('<?php _e('Generate Content', 'premium-auto-content'); ?>');
                }
            });
        });

        // Display result
        function displayResult(data) {
            $('#generated-content').html(formatContent(data.content));
            $('#raw-output').text(data.content);
            $('#schema-markup').text(data.schema || '<?php _e('No schema markup generated', 'premium-auto-content'); ?>');
            $('#tokens-used').text(number_format(data.tokens?.total_tokens || 0));
            $('#generation-time').text(data.generation_time?.toFixed(2) + 's');
            $('#seo-score').text(data.seo_score || '<?php _e('N/A', 'premium-auto-content'); ?>');
            $('#model-used').text(data.model || 'gemini-1.5-pro');
            
            // SEO Analysis
            if (data.seo_analysis) {
                renderSeoAnalysis(data.seo_analysis);
            }
        }

        function formatContent(content) {
            // Convert markdown-like to HTML for preview
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

        function renderSeoAnalysis(analysis) {
            var html = '<div class="seo-analysis">';
            if (analysis.score) {
                html += '<div class="seo-score-large">' + analysis.score + '/100</div>';
            }
            if (analysis.recommendations && analysis.recommendations.length) {
                html += '<h4><?php _e('Recommendations:', 'premium-auto-content'); ?></h4><ul>';
                analysis.recommendations.forEach(function(rec) {
                    var cls = rec.type === 'error' ? 'error' : (rec.type === 'warning' ? 'warning' : 'info');
                    html += '<li class="' + cls + '">' + rec.message + '</li>';
                });
                html += '</ul>';
            }
            html += '</div>';
            $('#seo-analysis').html(html);
        }

        // Tab switching
        $('.tab-btn').on('click', function() {
            var tab = $(this).data('tab');
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.tab-panel').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });

        // Copy content
        $('#copy-content').on('click', function() {
            var content = currentResult?.content || '';
            navigator.clipboard.writeText(content).then(function() {
                var $btn = $(this);
                var origText = $btn.html();
                $btn.html('<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'premium-auto-content'); ?>');
                setTimeout(function() { $btn.html(origText); }, 2000);
            }.bind(this));
        });

        // Create post
        $('#create-post').on('click', function() {
            if (!currentResult) return;
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('<?php _e('Creating...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.rest_url + 'generate',
                type: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', premiumAutoContent.nonce);
                },
                data: JSON.stringify({
                    prompt: $('#gen-prompt').val(),
                    content_type: $('#gen-content-type').val(),
                    length: $('#gen-length').val(),
                    seo_keywords: $('#gen-seo-keywords').val(),
                    language: $('#gen-language').val(),
                }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.post_id) {
                        var editUrl = '<?php echo admin_url('post.php?post='); ?>' + response.post_id + '&action=edit';
                        if (confirm('<?php _e('Post created successfully! Open in editor?', 'premium-auto-content'); ?>')) {
                            window.open(editUrl, '_blank');
                        }
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php _e('Create Post', 'premium-auto-content'); ?>');
                }
            });
        });

        // Regenerate
        $('#regenerate').on('click', function() {
            $('#content-generator-form').submit();
        });

        // Save template modal
        $('#generate-save-template').on('click', function() {
            if (!currentResult) {
                alert('<?php _e('Generate content first', 'premium-auto-content'); ?>');
                return;
            }
            $('#save-template-modal').show();
            $('#save-template-form')[0].reset();
            $('#save-template-form input[name="name"]').focus();
        });

        $('.modal-close, .modal-overlay').on('click', function() {
            $('#save-template-modal').hide();
        });

        $('#save-template-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.prop('disabled', true).text('<?php _e('Saving...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_save_template',
                    nonce: premiumAutoContent.nonce,
                    name: $form.find('input[name="name"]').val(),
                    description: $form.find('textarea[name="description"]').val(),
                    content_type: $form.find('select[name="content_type"]').val(),
                    default_length: $form.find('select[name="default_length"]').val(),
                    seo_keywords: $form.find('input[name="seo_keywords"]').val(),
                    prompt_template: currentResult.prompt || $('#gen-prompt').val(),
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e('Template saved successfully!', 'premium-auto-content'); ?>');
                        $('#save-template-modal').hide();
                        location.reload();
                    } else {
                        alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + response.data.message);
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('<?php _e('Save Template', 'premium-auto-content'); ?>');
                }
            });
        });

        // Number format helper
        function number_format(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    });
    </script>

    <style>
    .premium-auto-content-generator { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .generator-panel { display: flex; flex-direction: column; }
    .generator-panel .postbox { flex: 1; }
    .form-row { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
    .form-group .description { margin: 5px 0 0; font-size: 12px; color: #666; }
    .form-actions { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; }
    .result-actions { margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
    .result-tabs { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 15px; }
    .tab-btn { background: none; border: none; padding: 10px 20px; cursor: pointer; font-size: 13px; color: #666; border-bottom: 3px solid transparent; margin-bottom: -1px; }
    .tab-btn:hover { color: #0073aa; }
    .tab-btn.active { color: #0073aa; border-bottom-color: #0073aa; font-weight: 600; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .generated-content-preview { max-height: 500px; overflow-y: auto; padding: 20px; background: #fafafa; border: 1px solid #ddd; border-radius: 4px; line-height: 1.6; }
    .generated-content-preview h1, .generated-content-preview h2, .generated-content-preview h3 { margin-top: 1.5em; }
    .generated-content-preview ul, .generated-content-preview ol { margin: 1em 0; padding-left: 2em; }
    .schema-code, .raw-code { max-height: 500px; overflow: auto; background: #f4f4f4; padding: 15px; border-radius: 4px; font-size: 12px; line-height: 1.5; }
    .result-meta { display: flex; gap: 20px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; flex-wrap: wrap; }
    .meta-item { display: flex; flex-direction: column; gap: 3px; }
    .seo-analysis { padding: 15px; }
    .seo-score-large { font-size: 48px; font-weight: 700; text-align: center; color: #0073aa; margin-bottom: 20px; }
    .seo-analysis ul { margin: 0; padding-left: 20px; }
    .seo-analysis li { margin: 10px 0; padding: 10px; border-radius: 4px; }
    .seo-analysis li.error { background: #f8d7da; color: #721c24; }
    .seo-analysis li.warning { background: #fff3cd; color: #856404; }
    .seo-analysis li.info { background: #d1ecf1; color: #0c5460; }
    .premium-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000; display: flex; align-items: center; justify-content: center; }
    .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { position: relative; background: #fff; border-radius: 8px; padding: 30px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-header h3 { margin: 0; }
    .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; line-height: 1; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
    @media (max-width: 1024px) {
        .premium-auto-content-generator { grid-template-columns: 1fr; }
    }
    </style>