/**
 * Premium Auto Content Generator - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    var PremiumAutoContentFrontend = {
        init: function() {
            this.bindGeneratorForm();
            this.bindResultActions();
            this.initShortcodes();
        },
        
        bindGeneratorForm: function() {
            $('.pac-generator-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('.pac-generate-btn');
                var $result = $form.siblings('.pac-result');
                var $content = $result.find('.pac-result-content');
                
                if ($btn.hasClass('loading')) return;
                
                $btn.addClass('loading').prop('disabled', true);
                $btn.find('.spinner').css('display', 'inline-block');
                $btn.find('.btn-text').text(premiumAutoContent.strings.generating);
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
                            $content.html(PremiumAutoContentFrontend.formatContent(response.data.content));
                            $result.find('.pac-seo-score').text(response.data.seo_score || 'N/A');
                            $result.find('.pac-tokens').text(PremiumAutoContentFrontend.formatNumber(response.data.tokens?.total_tokens || 0));
                            $result.find('.pac-time').text(response.data.generation_time?.toFixed(2) + 's');
                            $result.show();
                            $('html, body').animate({scrollTop: $result.offset().top - 50}, 500);
                            $result.data('result', response.data);
                        } else {
                            PremiumAutoContentFrontend.showNotice($form, response.data.message || premiumAutoContent.strings.error, 'error');
                        }
                    },
                    error: function() {
                        PremiumAutoContentFrontend.showNotice($form, premiumAutoContent.strings.error, 'error');
                    },
                    complete: function() {
                        $btn.removeClass('loading').prop('disabled', false);
                        $btn.find('.spinner').hide();
                        $btn.find('.btn-text').text($form.data('button-text') || 'Generate Content');
                    }
                });
            });
        },
        
        bindResultActions: function() {
            // Copy content
            $(document).on('click', '.pac-copy-btn', function() {
                var $result = $(this).closest('.pac-result');
                var data = $result.data('result');
                if (data && data.content) {
                    navigator.clipboard.writeText(data.content).then(function() {
                        var $btn = $(this);
                        var orig = $btn.html();
                        $btn.html('<span class="dashicons dashicons-yes"></span> Copied!');
                        setTimeout(function() { $btn.html(orig); }, 2000);
                    }.bind(this)).catch(function() {
                        PremiumAutoContentFrontend.showToast('Failed to copy', 'error');
                    });
                }
            });
            
            // Regenerate
            $(document).on('click', '.pac-regenerate-btn', function() {
                var $form = $(this).closest('.pac-result').siblings('.pac-generator-form');
                $form.find('.pac-generate-btn').click();
            });
            
            // Save as draft
            $(document).on('click', '.pac-save-btn', function() {
                var $result = $(this).closest('.pac-result');
                var data = $result.data('result');
                
                if (!data) return;
                
                var $btn = $(this);
                $btn.prop('disabled', true).text('Saving...');
                
                $.ajax({
                    url: premiumAutoContent.rest_url + 'generate',
                    type: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', premiumAutoContent.nonce);
                    },
                    data: JSON.stringify({
                        prompt: data.prompt,
                        content_type: data.content_type,
                        length: data.length,
                        seo_keywords: data.seo_keywords,
                        language: data.language,
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.post_id) {
                            var editUrl = premiumAutoContent.admin_url + 'post.php?post=' + response.post_id + '&action=edit';
                            if (confirm('Post created successfully! Open in editor?')) {
                                window.open(editUrl, '_blank');
                            }
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> Save as Draft');
                    }
                });
            });
        },
        
        initShortcodes: function() {
            // Auto-initialize any generator forms on page load
            $('.pac-generator-form[data-auto-init="true"]').each(function() {
                var $form = $(this);
                if ($form.find('[name="prompt"]').val()) {
                    $form.trigger('submit');
                }
            });
        },
        
        formatContent: function(content) {
            if (!content) return '';
            
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
                .replace(/(<\/ul>)<\/p>/g, '$1')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                .replace(/\*([^*]+)\*/g, '<em>$1</em>');
        },
        
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        showNotice: function($container, message, type) {
            type = type || 'info';
            var $notice = $('<div class="pac-notice pac-' + type + '">' + message + '</div>');
            $container.before($notice);
            setTimeout(function() { $notice.fadeOut(300, function() { $(this).remove(); }); }, 5000);
        },
        
        showToast: function(message, type) {
            type = type || 'info';
            
            var $toast = $('<div class="pac-toast pac-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            
            setTimeout(function() { $toast.addClass('show'); }, 10);
            
            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() { $toast.remove(); }, 300);
            }, 3000);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        PremiumAutoContentFrontend.init();
    });
    
    // Expose globally
    window.PremiumAutoContentFrontend = PremiumAutoContentFrontend;
    
})(jQuery);

// Toast styles (injected via JS for frontend)
(function() {
    var style = document.createElement('style');
    style.textContent = `
        .pac-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 25px;
            border-radius: 4px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            z-index: 99999;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .pac-toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        .pac-toast-success { background: #46b450; }
        .pac-toast-error { background: #dc3232; }
        .pac-toast-info { background: #0073aa; }
        .pac-toast-warning { background: #ffc107; color: #333; }
    `;
    document.head.appendChild(style);
})();