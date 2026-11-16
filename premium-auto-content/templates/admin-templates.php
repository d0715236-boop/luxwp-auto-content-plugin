<div class="wrap premium-auto-content-wrap">
    <div class="templates-header">
        <h1 class="wp-heading-inline"><?php _e('Content Templates', 'premium-auto-content'); ?></h1>
        <a href="#" id="add-template-btn" class="button button-primary page-title-action">
            <span class="dashicons dashicons-plus"></span> <?php _e('Add New Template', 'premium-auto-content'); ?>
        </a>
    </div>
    <hr class="wp-header-end">

    <!-- Add/Edit Template Modal -->
    <div id="template-modal" class="premium-modal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 id="modal-title"><?php _e('Add New Template', 'premium-auto-content'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <form id="template-form">
                <input type="hidden" name="template_id" id="template-id" value="0" />
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="template-name"><?php _e('Template Name *', 'premium-auto-content'); ?></label>
                        <input type="text" id="template-name" name="name" class="regular-text" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="template-description"><?php _e('Description', 'premium-auto-content'); ?></label>
                        <textarea id="template-description" name="description" rows="3" class="large-text"></textarea>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label for="template-content-type"><?php _e('Content Type *', 'premium-auto-content'); ?></label>
                        <select id="template-content-type" name="content_type" class="regular-text" required>
                            <option value="post"><?php _e('Blog Post', 'premium-auto-content'); ?></option>
                            <option value="page"><?php _e('Page', 'premium-auto-content'); ?></option>
                            <option value="product"><?php _e('Product Description', 'premium-auto-content'); ?></option>
                            <option value="portfolio"><?php _e('Portfolio Case Study', 'premium-auto-content'); ?></option>
                            <option value="faq"><?php _e('FAQ Page', 'premium-auto-content'); ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="template-default-length"><?php _e('Default Length', 'premium-auto-content'); ?></label>
                        <select id="template-default-length" name="default_length" class="regular-text">
                            <option value="short"><?php _e('Short (~500 words)', 'premium-auto-content'); ?></option>
                            <option value="medium" selected><?php _e('Medium (~1500 words)', 'premium-auto-content'); ?></option>
                            <option value="long"><?php _e('Long (~3000 words)', 'premium-auto-content'); ?></option>
                            <option value="comprehensive"><?php _e('Comprehensive (~5000+ words)', 'premium-auto-content'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="template-seo-keywords"><?php _e('SEO Keywords', 'premium-auto-content'); ?></label>
                        <input type="text" id="template-seo-keywords" name="seo_keywords" class="large-text" placeholder="keyword1, keyword2, keyword3" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="template-prompt"><?php _e('Prompt Template *', 'premium-auto-content'); ?></label>
                        <textarea id="template-prompt" name="prompt_template" rows="8" class="large-text" required placeholder="<?php _e('Enter the prompt template. Use {topic} as placeholder for the main topic...', 'premium-auto-content'); ?>"></textarea>
                        <p class="description"><?php _e('Use {topic} as a placeholder that will be replaced with the actual topic when generating. Example: "Write a comprehensive guide about {topic} covering best practices, tips, and examples."', 'premium-auto-content'); ?></p>
                    </div>
                </div>

                <div class="form-row">
                    <label>
                        <input type="checkbox" name="is_active" id="template-active" value="1" checked />
                        <?php _e('Active (available for generation)', 'premium-auto-content'); ?>
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="button modal-close"><?php _e('Cancel', 'premium-auto-content'); ?></button>
                    <button type="submit" class="button button-primary"><?php _e('Save Template', 'premium-auto-content'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates List Table -->
    <div class="templates-list">
        <?php if (!empty($templates)): ?>
            <table class="wp-list-table widefat fixed striped" id="templates-table">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php _e('ID', 'premium-auto-content'); ?></th>
                        <th style="width: 30%;"><?php _e('Name', 'premium-auto-content'); ?></th>
                        <th style="width: 15%;"><?php _e('Type', 'premium-auto-content'); ?></th>
                        <th style="width: 15%;"><?php _e('Length', 'premium-auto-content'); ?></th>
                        <th style="width: 10%;"><?php _e('Status', 'premium-auto-content'); ?></th>
                        <th style="width: 10%;"><?php _e('Keywords', 'premium-auto-content'); ?></th>
                        <th style="width: 15%;"><?php _e('Actions', 'premium-auto-content'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $template): 
                        $content_type = get_post_meta($template->ID, '_content_type', true);
                        $default_length = get_post_meta($template->ID, '_default_length', true);
                        $seo_keywords = get_post_meta($template->ID, '_seo_keywords', true);
                        $is_active = get_post_meta($template->ID, '_is_active', true);
                        $prompt = get_post_meta($template->ID, '_prompt_template', true);
                    ?>
                        <tr data-id="<?php echo $template->ID; ?>" 
                            data-name="<?php echo esc_attr($template->post_title); ?>"
                            data-description="<?php echo esc_attr($template->post_content); ?>"
                            data-content-type="<?php echo esc_attr($content_type); ?>"
                            data-default-length="<?php echo esc_attr($default_length); ?>"
                            data-seo-keywords="<?php echo esc_attr($seo_keywords); ?>"
                            data-prompt="<?php echo esc_attr($prompt); ?>"
                            data-active="<?php echo $is_active ? '1' : '0'; ?>">
                            <td><?php echo $template->ID; ?></td>
                            <td><strong><?php echo esc_html($template->post_title); ?></strong></td>
                            <td><?php echo esc_html(ucfirst($content_type)); ?></td>
                            <td><?php echo esc_html(ucfirst($default_length)); ?></td>
                            <td>
                                <span class="status-badge <?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $is_active ? __('Active', 'premium-auto-content') : __('Inactive', 'premium-auto-content'); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html(wp_trim_words($seo_keywords, 5)); ?></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="button button-small edit-template" data-id="<?php echo $template->ID; ?>">
                                        <span class="dashicons dashicons-edit"></span> <?php _e('Edit', 'premium-auto-content'); ?>
                                    </button>
                                    <button type="button" class="button button-small use-template" data-id="<?php echo $template->ID; ?>">
                                        <span class="dashicons dashicons-welcome-write"></span> <?php _e('Use', 'premium-auto-content'); ?>
                                    </button>
                                    <button type="button" class="button button-small delete-template" data-id="<?php echo $template->ID; ?>">
                                        <span class="dashicons dashicons-trash"></span> <?php _e('Delete', 'premium-auto-content'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-templates">
                <div class="dashicons dashicons-admin-page" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></div>
                <h3><?php _e('No Templates Yet', 'premium-auto-content'); ?></h3>
                <p><?php _e('Create your first content template to speed up content generation.', 'premium-auto-content'); ?></p>
                <button type="button" id="add-first-template" class="button button-primary button-large">
                    <span class="dashicons dashicons-plus"></span> <?php _e('Create Template', 'premium-auto-content'); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Open add template modal
        $('#add-template-btn, #add-first-template').on('click', function(e) {
            e.preventDefault();
            openTemplateModal();
        });

        // Edit template
        $(document).on('click', '.edit-template', function() {
            var $row = $(this).closest('tr');
            openTemplateModal($row.data());
        });

        // Use template (redirect to generator)
        $(document).on('click', '.use-template', function() {
            var templateId = $(this).data('id');
            window.location.href = '<?php echo admin_url('admin.php?page=premium-auto-content-generator&template='); ?>' + templateId;
        });

        // Delete template
        $(document).on('click', '.delete-template', function() {
            if (!confirm('<?php _e('Are you sure you want to delete this template?', 'premium-auto-content'); ?>')) return;
            
            var $btn = $(this);
            var templateId = $btn.data('id');
            
            $btn.prop('disabled', true).text('<?php _e('Deleting...', 'premium-auto-content'); ?>');
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: {
                    action: 'premium_auto_content_delete_template',
                    nonce: premiumAutoContent.nonce,
                    template_id: templateId
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            if ($('#templates-table tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        alert('<?php _e('Error:', 'premium-auto-content'); ?> ' + response.data.message);
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e('Delete', 'premium-auto-content'); ?>');
                    }
                }
            });
        });

        function openTemplateModal(data = null) {
            var $modal = $('#template-modal');
            var $form = $('#template-form');
            
            $form[0].reset();
            $('#template-id').val('0');
            $('#modal-title').text('<?php _e('Add New Template', 'premium-auto-content'); ?>');
            $('#template-active').prop('checked', true);
            
            if (data) {
                $('#template-id').val(data.id);
                $('#modal-title').text('<?php _e('Edit Template', 'premium-auto-content'); ?>');
                $('#template-name').val(data.name);
                $('#template-description').val(data.description);
                $('#template-content-type').val(data.content_type);
                $('#template-default-length').val(data.default_length);
                $('#template-seo-keywords').val(data.seo_keywords);
                $('#template-prompt').val(data.prompt);
                $('#template-active').prop('checked', data.active === '1');
            }
            
            $modal.show();
            $('#template-name').focus();
        }

        // Close modal
        $('.modal-close, .modal-overlay').on('click', function() {
            $('#template-modal').hide();
        });

        // Save template
        $('#template-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var templateId = $('#template-id').val();
            
            $btn.prop('disabled', true).text(templateId ? '<?php _e('Updating...', 'premium-auto-content'); ?>' : '<?php _e('Saving...', 'premium-auto-content'); ?>');
            
            var formData = {
                action: templateId ? 'premium_auto_content_update_template' : 'premium_auto_content_save_template',
                nonce: premiumAutoContent.nonce,
                template_id: templateId,
                name: $('#template-name').val(),
                description: $('#template-description').val(),
                content_type: $('#template-content-type').val(),
                default_length: $('#template-default-length').val(),
                seo_keywords: $('#template-seo-keywords').val(),
                prompt_template: $('#template-prompt').val(),
                is_active: $('#template-active').is(':checked') ? 1 : 0,
            };
            
            $.ajax({
                url: premiumAutoContent.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert(templateId ? '<?php _e('Template updated successfully!', 'premium-auto-content'); ?>' : '<?php _e('Template saved successfully!', 'premium-auto-content'); ?>');
                        $('#template-modal').hide();
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
    });
    </script>

    <style>
    .templates-header { display: flex; justify-content: space-between; align-items: center; margin: 15px 0; }
    .templates-header h1 { margin: 0; }
    .no-templates { text-align: center; padding: 60px 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
    .no-templates h3 { margin-top: 15px; }
    .no-templates p { color: #666; margin-bottom: 20px; }
    .row-actions { display: flex; gap: 5px; flex-wrap: wrap; }
    .row-actions .button { padding: 3px 8px; font-size: 12px; }
    .row-actions .dashicons { vertical-align: middle; margin-right: 3px; }
    .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .status-active { background: #d4edda; color: #155724; }
    .status-inactive { background: #f8d7da; color: #721c24; }
    .premium-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 100000; display: flex; align-items: center; justify-content: center; }
    .modal-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { position: relative; background: #fff; border-radius: 8px; padding: 30px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .modal-header h3 { margin: 0; }
    .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; line-height: 1; }
    .form-row { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
    .form-group .description { margin: 5px 0 0; font-size: 12px; color: #666; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
    </style>