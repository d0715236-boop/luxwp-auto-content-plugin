/**
 * Premium Auto Content Generator - Gutenberg Blocks
 */

(function(wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var __ = wp.i18n.__;
    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var Button = wp.components.Button;
    var Textarea = wp.components.Textarea;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var PanelBody = wp.components.PanelBody;
    var InspectorControls = wp.editor.InspectorControls;
    var ServerSideRender = wp.serverSideRender;
    var Fragment = wp.element.Fragment;

    // AI Content Generator Block
    registerBlockType('premium-auto-content/generator', {
        title: __('AI Content Generator', 'premium-auto-content'),
        icon: 'welcome-write',
        category: 'widgets',
        keywords: ['ai', 'content', 'generator', 'gpt', 'gemini'],
        attributes: {
            prompt: { type: 'string', default: '' },
            contentType: { type: 'string', default: 'post' },
            length: { type: 'string', default: 'medium' },
            language: { type: 'string', default: 'en_US' },
            seoKeywords: { type: 'string', default: '' },
            generatedContent: { type: 'string', default: '' },
            isGenerating: { type: 'boolean', default: false },
            seoScore: { type: 'number', default: 0 },
            tokensUsed: { type: 'number', default: 0 },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            
            var handleGenerate = function() {
                if (!attributes.prompt.trim()) return;
                
                setAttributes({ isGenerating: true });
                
                wp.apiFetch({
                    path: '/premium-auto-content/v1/generate',
                    method: 'post',
                    data: {
                        prompt: attributes.prompt,
                        content_type: attributes.contentType,
                        length: attributes.length,
                        seo_keywords: attributes.seoKeywords,
                        language: attributes.language,
                    }
                }).then(function(response) {
                    if (response.success) {
                        setAttributes({
                            generatedContent: response.data.content,
                            seoScore: response.data.seo_score || 0,
                            tokensUsed: response.data.tokens?.total_tokens || 0,
                            isGenerating: false,
                        });
                    } else {
                        alert('Error: ' + (response.data.message || 'Generation failed'));
                        setAttributes({ isGenerating: false });
                    }
                }).catch(function() {
                    alert('An error occurred. Please try again.');
                    setAttributes({ isGenerating: false });
                });
            };
            
            var handleInsert = function() {
                if (!attributes.generatedContent) return;
                
                var blocks = wp.blocks.parse(attributes.generatedContent);
                if (blocks.length) {
                    wp.data.dispatch('core/block-editor').insertBlocks(blocks);
                    setAttributes({ generatedContent: '' });
                }
            };
            
            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, { title: __('Settings', 'premium-auto-content'), initialOpen: true },
                        el(Textarea, {
                            label: __('Topic / Prompt', 'premium-auto-content'),
                            value: attributes.prompt,
                            onChange: function(value) { setAttributes({ prompt: value }); },
                            rows: 4,
                            placeholder: __('Enter your topic...', 'premium-auto-content'),
                        }),
                        el(SelectControl, {
                            label: __('Content Type', 'premium-auto-content'),
                            value: attributes.contentType,
                            options: [
                                { value: 'post', label: __('Blog Post', 'premium-auto-content') },
                                { value: 'page', label: __('Page', 'premium-auto-content') },
                                { value: 'product', label: __('Product Description', 'premium-auto-content') },
                                { value: 'portfolio', label: __('Portfolio Case Study', 'premium-auto-content') },
                                { value: 'faq', label: __('FAQ Page', 'premium-auto-content') },
                            ],
                            onChange: function(value) { setAttributes({ contentType: value }); },
                        }),
                        el(SelectControl, {
                            label: __('Content Length', 'premium-auto-content'),
                            value: attributes.length,
                            options: [
                                { value: 'short', label: __('Short (~500 words)', 'premium-auto-content') },
                                { value: 'medium', label: __('Medium (~1500 words)', 'premium-auto-content') },
                                { value: 'long', label: __('Long (~3000 words)', 'premium-auto-content') },
                                { value: 'comprehensive', label: __('Comprehensive (~5000+ words)', 'premium-auto-content') },
                            ],
                            onChange: function(value) { setAttributes({ length: value }); },
                        }),
                        el(SelectControl, {
                            label: __('Language', 'premium-auto-content'),
                            value: attributes.language,
                            options: [
                                { value: 'en_US', label: __('English (US)', 'premium-auto-content') },
                                { value: 'es_ES', label: __('Spanish', 'premium-auto-content') },
                                { value: 'fr_FR', label: __('French', 'premium-auto-content') },
                                { value: 'de_DE', label: __('German', 'premium-auto-content') },
                                { value: 'pt_BR', label: __('Portuguese (Brazil)', 'premium-auto-content') },
                                { value: 'ru_RU', label: __('Russian', 'premium-auto-content') },
                                { value: 'zh_CN', label: __('Chinese (Simplified)', 'premium-auto-content') },
                                { value: 'ja_JP', label: __('Japanese', 'premium-auto-content') },
                            ],
                            onChange: function(value) { setAttributes({ language: value }); },
                        }),
                        el(Textarea, {
                            label: __('SEO Keywords', 'premium-auto-content'),
                            value: attributes.seoKeywords,
                            onChange: function(value) { setAttributes({ seoKeywords: value }); },
                            rows: 2,
                            help: __('Comma-separated keywords to optimize for', 'premium-auto-content'),
                        })
                    )
                ),
                
                el('div', { className: 'pac-block-generator-editor' },
                    el('div', { className: 'pac-block-toolbar' },
                        el('span', { className: 'dashicons dashicons-welcome-write' }),
                        el('strong', null, __('AI Content Generator', 'premium-auto-content'))
                    ),
                    
                    attributes.generatedContent ? (
                        el('div', { className: 'pac-block-preview' },
                            el('div', { className: 'pac-block-preview-header' },
                                el('strong', null, __('Preview', 'premium-auto-content')),
                                el('div', { className: 'pac-block-meta' },
                                    attributes.seoScore && el('span', { className: 'pac-meta-item' },
                                        el('span', { className: 'pac-meta-label' }, __('SEO Score: ', 'premium-auto-content')),
                                        el('span', { className: 'pac-meta-value', style: { color: attributes.seoScore >= 80 ? '#46b450' : attributes.seoScore >= 60 ? '#ffc107' : '#dc3232' } }, attributes.seoScore)
                                    ),
                                    attributes.tokensUsed && el('span', { className: 'pac-meta-item' },
                                        el('span', { className: 'pac-meta-label' }, __('Tokens: ', 'premium-auto-content')),
                                        el('span', { className: 'pac-meta-value' }, attributes.tokensUsed.toLocaleString())
                                    )
                                )
                            ),
                            el('div', { className: 'pac-block-preview-content', dangerouslySetInnerHTML: { __html: formatForEditor(attributes.generatedContent) } }),
                            el('div', { className: 'pac-block-actions' },
                                el(Button, {
                                    isPrimary: true,
                                    onClick: handleInsert,
                                    icon: 'plus'
                                }, __('Insert into Editor', 'premium-auto-content')),
                                el(Button, {
                                    onClick: handleGenerate,
                                    isBusy: attributes.isGenerating,
                                    variant: 'secondary'
                                }, __('Regenerate', 'premium-auto-content'))
                            )
                        )
                    ) : (
                        el('div', { className: 'pac-block-empty' },
                            el('p', null, __('Configure settings in the sidebar and click Generate to create AI-powered content.', 'premium-auto-content')),
                            el(Button, {
                                isPrimary: true,
                                onClick: handleGenerate,
                                isBusy: attributes.isGenerating,
                                disabled: !attributes.prompt.trim()
                            }, attributes.isGenerating ? __('Generating...', 'premium-auto-content') : __('Generate Content', 'premium-auto-content'))
                        )
                    )
                )
            );
        },
        
        save: function() {
            // Rendered server-side
            return null;
        }
    });
    
    // AI Content Display Block
    registerBlockType('premium-auto-content/display', {
        title: __('AI Content Display', 'premium-auto-content'),
        icon: 'visibility',
        category: 'widgets',
        keywords: ['ai', 'content', 'display', 'generated'],
        attributes: {
            postId: { type: 'number', default: 0 },
            showSeo: { type: 'boolean', default: true },
            showSchema: { type: 'boolean', default: false },
        },
        
        edit: function(props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            
            return el(Fragment, null,
                el(InspectorControls, null,
                    el(PanelBody, { title: __('Settings', 'premium-auto-content'), initialOpen: true },
                        el('label', { style: { display: 'block', marginBottom: '10px' } },
                            el('strong', null, __('Post ID (0 = current post)', 'premium-auto-content')),
                            el('br'),
                            el('input', {
                                type: 'number',
                                min: 0,
                                value: attributes.postId,
                                onChange: function(e) { setAttributes({ postId: parseInt(e.target.value) || 0 }); },
                                style: { width: '100%', padding: '8px', marginTop: '5px' },
                                placeholder: '0'
                            })
                        ),
                        el(ToggleControl, {
                            label: __('Show SEO Metrics', 'premium-auto-content'),
                            checked: attributes.showSeo,
                            onChange: function(value) { setAttributes({ showSeo: value }); },
                        }),
                        el(ToggleControl, {
                            label: __('Show Schema Markup', 'premium-auto-content'),
                            checked: attributes.showSchema,
                            onChange: function(value) { setAttributes({ showSchema: value }); },
                        })
                    )
                ),
                
                el('div', { className: 'pac-block-display-editor' },
                    el('div', { className: 'pac-block-toolbar' },
                        el('span', { className: 'dashicons dashicons-visibility' }),
                        el('strong', null, __('AI Content Display', 'premium-auto-content'))
                    ),
                    el('div', { className: 'pac-block-display-preview' },
                        attributes.postId ? 
                            __('Displaying content from post ID: ', 'premium-auto-content') + attributes.postId :
                            __('Displaying content from current post', 'premium-auto-content'),
                        el('br'),
                        el('small', { style: { color: '#666' } },
                            __('Content will be rendered on the frontend.', 'premium-auto-content')
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
    
    // Helper function to format content for editor preview
    function formatForEditor(content) {
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
    }
    
})(window.wp);