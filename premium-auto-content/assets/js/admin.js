/**
 * Premium Auto Content Generator - Admin JavaScript
 */

(function($) {
    'use strict';
    
    var PremiumAutoContent = {
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initColorPickers();
        },
        
        bindEvents: function() {
            // Copy to clipboard buttons
            $(document).on('click', '.copy-to-clipboard', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var text = $btn.data('clipboard-text') || $btn.closest('.copy-target').find('code, pre').text();
                
                navigator.clipboard.writeText(text).then(function() {
                    PremiumAutoContent.showToast('Copied to clipboard!', 'success');
                    $btn.addClass('copied');
                    setTimeout(function() { $btn.removeClass('copied'); }, 2000);
                }).catch(function() {
                    PremiumAutoContent.showToast('Failed to copy', 'error');
                });
            });
            
            // Confirm delete actions
            $(document).on('click', '.confirm-delete', function(e) {
                if (!confirm(premiumAutoContent.strings.confirm_delete)) {
                    e.preventDefault();
                }
            });
            
            // Toggle password visibility
            $(document).on('click', '.toggle-password', function() {
                var $input = $(this).closest('.input-group').find('input');
                var type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
                $(this).find('.dashicons').toggleClass('dashicons-visibility dashicons-hidden');
            });
            
            // Auto-save forms
            $(document).on('change', '.auto-save-form input, .auto-save-form select, .auto-save-form textarea', function() {
                var $form = $(this).closest('.auto-save-form');
                clearTimeout($form.data('save-timer'));
                $form.data('save-timer', setTimeout(function() {
                    $form.trigger('submit');
                }, 1000));
            });
            
            // Table row actions
            $(document).on('click', '.row-action', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var action = $btn.data('action');
                var id = $btn.data('id');
                
                if (action === 'delete' && !confirm(premiumAutoContent.strings.confirm_delete)) {
                    return;
                }
                
                PremiumAutoContent.ajax({
                    action: 'premium_auto_content_' + action,
                    id: id
                }).done(function(response) {
                    if (response.success) {
                        if (action === 'delete') {
                            $btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
                        }
                        PremiumAutoContent.showToast(response.data.message, 'success');
                    } else {
                        PremiumAutoContent.showToast(response.data.message, 'error');
                    }
                });
            });
        },
        
        initTooltips: function() {
            $('.has-tooltip').each(function() {
                var $el = $(this);
                var text = $el.data('tooltip');
                $el.attr('title', text);
            });
        },
        
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        $(this).trigger('change');
                    }
                });
            }
        },
        
        ajax: function(data) {
            return $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: $.extend({
                    nonce: premiumAutoContent.nonce
                }, data),
                dataType: 'json'
            });
        },
        
        rest: function(endpoint, method, data) {
            return $.ajax({
                url: premiumAutoContent.rest_url + endpoint,
                type: method || 'GET',
                data: data ? JSON.stringify(data) : null,
                contentType: 'application/json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', premiumAutoContent.nonce);
                },
                dataType: 'json'
            });
        },
        
        showToast: function(message, type) {
            type = type || 'info';
            
            var $toast = $('<div class="pac-toast pac-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            
            // Animate in
            setTimeout(function() { $toast.addClass('show'); }, 10);
            
            // Remove after delay
            setTimeout(function() {
                $toast.removeClass('show');
                setTimeout(function() { $toast.remove(); }, 300);
            }, 3000);
        },
        
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },
        
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() { inThrottle = false; }, limit);
                }
            };
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        PremiumAutoContent.init();
    });
    
    // Expose globally
    window.PremiumAutoContent = PremiumAutoContent;
    
})(jQuery);