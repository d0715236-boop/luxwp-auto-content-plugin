<?php
/**
 * Content Generator Class
 */

class Premium_Content_Generator {

    private $api;
    private $seo_optimizer;
    private $settings;

    public function __construct() {
        $this->settings = get_option('premium_auto_content_settings', []);
        $this->api = new Premium_Gemini_API(
            $this->settings['gemini_api_key'] ?? '',
            $this->settings['gemini_model'] ?? 'gemini-1.5-pro'
        );
        $this->seo_optimizer = new Premium_SEO_Optimizer();
    }

    public function generate($params = []) {
        $start_time = microtime(true);
        
        $defaults = [
            'prompt' => '',
            'content_type' => 'post',
            'length' => 'medium',
            'seo_keywords' => '',
            'language' => 'en_US',
            'title' => '',
            'template_id' => 0,
        ];
        
        $params = array_merge($defaults, $params);
        
        // Validate required parameters
        if (empty($params['prompt'])) {
            return ['error' => 'Prompt is required'];
        }

        if (!$this->settings['gemini_api_key']) {
            return ['error' => 'Gemini API key is not configured'];
        }

        // Build enhanced prompt
        $enhanced_prompt = $this->build_prompt($params);
        
        // Generate content using Gemini API
        $result = $this->api->generate_content($enhanced_prompt, [
            'length' => $params['length'],
            'seo_keywords' => $params['seo_keywords'],
            'language' => $params['language'],
            'temperature' => 0.7,
        ]);

        if (isset($result['error'])) {
            $this->log_generation($params, '', 0, 'failed', $result['error'], 0, microtime(true) - $start_time);
            return $result;
        }

        $content = $result['content'];
        $tokens = $result['tokens'];

        // Apply SEO optimization if enabled
        if ($this->settings['seo_optimization']) {
            $content = $this->seo_optimizer->optimize($content, $params['seo_keywords'], $params['content_type']);
        }

        // Add internal links if enabled
        if ($this->settings['internal_linking']) {
            $content = $this->add_internal_links($content);
        }

        // Generate schema markup if enabled
        $schema = '';
        if ($this->settings['schema_markup']) {
            $schema = $this->generate_schema_markup($content, $params);
        }

        // Create or update post
        $post_id = $this->create_post($params, $content, $schema);
        
        $generation_time = microtime(true) - $start_time;
        
        // Log generation
        $this->log_generation($params, $content, $post_id, 'completed', '', $tokens['total_tokens'] ?? 0, $generation_time);

        return [
            'success' => true,
            'post_id' => $post_id,
            'content' => $content,
            'schema' => $schema,
            'tokens' => $tokens,
            'generation_time' => $generation_time,
        ];
    }

    public function generate_from_template($template_id, $count = 1) {
        $template = get_post($template_id);
        if (!$template || $template->post_type !== 'premium_content_template') {
            return ['error' => 'Invalid template'];
        }

        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $result = $this->generate([
                'prompt' => get_post_meta($template_id, '_prompt_template', true),
                'content_type' => get_post_meta($template_id, '_content_type', true),
                'length' => get_post_meta($template_id, '_default_length', true),
                'seo_keywords' => get_post_meta($template_id, '_seo_keywords', true),
                'language' => $this->settings['default_language'] ?? 'en_US',
            ]);
            $results[] = $result;
        }

        return $results;
    }

    private function build_prompt($params) {
        $length_guides = [
            'short' => 'approximately 500-800 words',
            'medium' => 'approximately 1500-2000 words',
            'long' => 'approximately 3000-4000 words',
            'comprehensive' => 'approximately 5000+ words',
        ];

        $length_guide = $length_guides[$params['length']] ?? $length_guides['medium'];

        $content_type_guides = [
            'post' => 'a comprehensive blog post',
            'page' => 'a detailed landing page',
            'product' => 'a product description page',
            'portfolio' => 'a portfolio case study',
            'testimonial' => 'a customer testimonial',
            'faq' => 'an FAQ page',
        ];

        $type_guide = $content_type_guides[$params['content_type']] ?? $content_type_guides['post'];

        $prompt = "Create {$type_guide} about: {$params['prompt']}\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Length: {$length_guide}\n";
        $prompt .= "- Content type: {$params['content_type']}\n";
        
        if (!empty($params['seo_keywords'])) {
            $prompt .= "- Target SEO keywords: {$params['seo_keywords']}\n";
        }
        
        $prompt .= "- Language: " . $this->get_language_name($params['language']) . "\n\n";
        
        $prompt .= "Structure the content with:\n";
        $prompt .= "1. Compelling SEO-optimized title (H1)\n";
        $prompt .= "2. Meta description (150-160 characters)\n";
        $prompt .= "3. Introduction with hook\n";
        $prompt .= "4. Well-organized sections with H2/H3 headings\n";
        $prompt .= "5. Bullet points and numbered lists for readability\n";
        $prompt .= "6. Practical examples and actionable tips\n";
        $prompt .= "7. Conclusion with call-to-action\n";
        $prompt .= "8. FAQ section (if applicable)\n\n";
        
        $prompt .= "Format for WordPress Gutenberg editor compatibility.\n";
        $prompt .= "Include suggested featured image description.\n";
        $prompt .= "Add internal linking placeholders like [internal-link:post-slug].\n";

        return $prompt;
    }

    private function get_language_name($locale) {
        $languages = [
            'en_US' => 'English (US)',
            'en_GB' => 'English (UK)',
            'es_ES' => 'Spanish',
            'fr_FR' => 'French',
            'de_DE' => 'German',
            'it_IT' => 'Italian',
            'pt_BR' => 'Portuguese (Brazil)',
            'ru_RU' => 'Russian',
            'zh_CN' => 'Chinese (Simplified)',
            'ja_JP' => 'Japanese',
            'ko_KR' => 'Korean',
            'ar_SA' => 'Arabic',
            'hi_IN' => 'Hindi',
        ];
        return $languages[$locale] ?? 'English';
    }

    private function create_post($params, $content, $schema) {
        // Extract title from content or use provided title
        $title = $params['title'];
        if (empty($title)) {
            if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
                $title = trim($matches[1]);
            } else {
                $title = wp_trim_words($params['prompt'], 10, '');
            }
        }

        // Extract meta description
        $meta_desc = '';
        if (preg_match('/meta description[:\s]+(.+)$/im', $content, $matches)) {
            $meta_desc = trim($matches[1]);
        }

        // Prepare post data
        $post_data = [
            'post_title' => $title,
            'post_content' => $this->format_for_gutenberg($content),
            'post_status' => $this->settings['auto_publish'] ? 'publish' : 'draft',
            'post_type' => $params['content_type'],
            'post_author' => get_current_user_id(),
        ];

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return 0;
        }

        // Save meta data
        update_post_meta($post_id, '_premium_auto_generated', true);
        update_post_meta($post_id, '_premium_auto_prompt', $params['prompt']);
        update_post_meta($post_id, '_premium_auto_seo_keywords', $params['seo_keywords']);
        update_post_meta($post_id, '_premium_auto_schema', $schema);
        update_post_meta($post_id, '_premium_auto_meta_desc', $meta_desc);
        update_post_meta($post_id, '_premium_auto_tokens', $this->api->get_token_usage()['total_tokens'] ?? 0);

        // Add schema markup to post
        if ($schema) {
            update_post_meta($post_id, '_schema_markup', $schema);
        }

        // Generate featured image if enabled
        if ($this->settings['featured_image_generation']) {
            $this->generate_featured_image($post_id, $params['prompt']);
        }

        return $post_id;
    }

    private function format_for_gutenberg($content) {
        // Convert markdown-style formatting to Gutenberg blocks
        $blocks = [];
        
        // Split content by headings
        $sections = preg_split('/(^|\n)(#{2,3}\s+.+)$/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $current_block = '';
        $in_list = false;
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (empty($section)) continue;
            
            // Heading
            if (preg_match('/^#{2,3}\s+(.+)$/', $section, $matches)) {
                if ($current_block) {
                    $blocks[] = $this->create_paragraph_block($current_block);
                    $current_block = '';
                }
                $level = substr_count($section, '#');
                $blocks[] = [
                    'blockName' => 'core/heading',
                    'attrs' => ['level' => $level, 'content' => trim($matches[1])],
                    'innerHTML' => '<h' . $level . '>' . esc_html(trim($matches[1])) . '</h' . $level . '>',
                ];
            }
            // List items
            elseif (preg_match('/^[\-\*]\s+(.+)$/m', $section)) {
                $items = [];
                foreach (explode("\n", $section) as $line) {
                    if (preg_match('/^[\-\*]\s+(.+)$/', $line, $m)) {
                        $items[] = '<li>' . esc_html(trim($m[1])) . '</li>';
                    }
                }
                if ($items) {
                    if ($current_block) {
                        $blocks[] = $this->create_paragraph_block($current_block);
                        $current_block = '';
                    }
                    $blocks[] = [
                        'blockName' => 'core/list',
                        'attrs' => ['ordered' => false],
                        'innerHTML' => '<ul>' . implode('', $items) . '</ul>',
                    ];
                }
            }
            // Numbered list
            elseif (preg_match('/^\d+\.\s+(.+)$/m', $section)) {
                $items = [];
                foreach (explode("\n", $section) as $line) {
                    if (preg_match('/^\d+\.\s+(.+)$/', $line, $m)) {
                        $items[] = '<li>' . esc_html(trim($m[1])) . '</li>';
                    }
                }
                if ($items) {
                    if ($current_block) {
                        $blocks[] = $this->create_paragraph_block($current_block);
                        $current_block = '';
                    }
                    $blocks[] = [
                        'blockName' => 'core/list',
                        'attrs' => ['ordered' => true],
                        'innerHTML' => '<ol>' . implode('', $items) . '</ol>',
                    ];
                }
            }
            // Regular paragraph
            else {
                $current_block .= $section . "\n\n";
            }
        }
        
        if ($current_block) {
            $blocks[] = $this->create_paragraph_block($current_block);
        }

        // Convert blocks to HTML
        $html = '';
        foreach ($blocks as $block) {
            $html .= '<!-- wp:' . $block['blockName'] . ' ' . json_encode($block['attrs']) . ' -->' . "\n";
            $html .= $block['innerHTML'] . "\n";
            $html .= '<!-- /wp:' . $block['blockName'] . ' -->' . "\n";
        }

        return $html;
    }

    private function create_paragraph_block($content) {
        // Clean up content
        $content = trim($content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return [
            'blockName' => 'core/paragraph',
            'attrs' => [],
            'innerHTML' => '<p>' . implode('</p><p>', array_map('esc_html', explode("\n\n", $content))) . '</p>',
        ];
    }

    private function add_internal_links($content) {
        // Find relevant existing posts for internal linking
        $keywords = $this->extract_keywords($content);
        if (empty($keywords)) return $content;

        $linked_posts = $this->find_related_posts($keywords);
        if (empty($linked_posts)) return $content;

        // Add internal links (simplified - replace placeholders)
        $link_count = 0;
        $max_links = 5;
        
        foreach ($linked_posts as $post) {
            if ($link_count >= $max_links) break;
            
            $keyword = $post['keyword'];
            $url = get_permalink($post['id']);
            $title = $post['title'];
            
            // Replace first occurrence of keyword with link
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            if (preg_match($pattern, $content)) {
                $content = preg_replace(
                    $pattern,
                    '<a href="' . esc_url($url) . '" title="' . esc_attr($title) . '">' . $keyword . '</a>',
                    $content,
                    1
                );
                $link_count++;
            }
        }

        return $content;
    }

    private function extract_keywords($content) {
        // Simple keyword extraction
        $words = str_word_count(strtolower($content), 1);
        $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how'];
        
        $keywords = [];
        foreach ($words as $word) {
            if (strlen($word) > 4 && !in_array($word, $stop_words)) {
                $keywords[$word] = ($keywords[$word] ?? 0) + 1;
            }
        }
        
        arsort($keywords);
        return array_keys(array_slice($keywords, 0, 10));
    }

    private function find_related_posts($keywords) {
        global $wpdb;
        
        $placeholders = implode(',', array_fill(0, count($keywords), '%s'));
        $query = $wpdb->prepare(
            "SELECT ID, post_title, post_name FROM {$wpdb->posts} 
             WHERE post_status = 'publish' 
             AND post_type IN ('post', 'page') 
             AND (post_title LIKE %s OR post_content LIKE %s)
             ORDER BY post_date DESC LIMIT 20",
            ...array_merge(
                array_map(fn($k) => "%$k%", $keywords),
                array_map(fn($k) => "%$k%", $keywords)
            )
        );
        
        $results = $wpdb->get_results($query);
        
        $linked = [];
        foreach ($results as $post) {
            foreach ($keywords as $keyword) {
                if (stripos($post->post_title, $keyword) !== false || stripos($post->post_content, $keyword) !== false) {
                    $linked[] = [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'keyword' => $keyword,
                    ];
                    break;
                }
            }
        }
        
        return $linked;
    }

    private function generate_schema_markup($content, $params) {
        $title = $params['title'] ?? '';
        if (empty($title) && preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
        }

        $description = '';
        if (preg_match('/meta description[:\s]+(.+)$/im', $content, $matches)) {
            $description = trim($matches[1]);
        } else {
            $description = wp_trim_words(strip_tags($content), 30, '');
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'datePublished' => current_time('c'),
            'dateModified' => current_time('c'),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', get_current_user_id()),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(512),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink(), // Will be updated after post creation
            ],
        ];

        // Add FAQ schema if FAQ section exists
        if (preg_match('/##\s*FAQ|##\s*Frequently Asked Questions/i', $content)) {
            $schema['@graph'] = [['@type' => 'FAQPage', 'mainEntity' => []]];
            
            // Extract FAQ items
            $faq_section = '';
            if (preg_match('/##\s*FAQ.*?(?=##|$)/is', $content, $matches)) {
                $faq_section = $matches[0];
            }
            
            $questions = [];
            preg_match_all('/###\s*(.+?)\n(.+?)(?=###|##|$)/s', $faq_section, $matches);
            for ($i = 0; $i < count($matches[1]); $i++) {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => trim($matches[1][$i]),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => trim(strip_tags($matches[2][$i])),
                    ],
                ];
            }
            
            if (!empty($questions)) {
                $schema['@graph'][0]['mainEntity'] = $questions;
            }
        }

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function generate_featured_image($post_id, $prompt) {
        // This would integrate with DALL-E or similar service
        // For now, placeholder for future implementation
        update_post_meta($post_id, '_premium_auto_image_prompt', $prompt);
    }

    private function log_generation($params, $content, $post_id, $status, $error = '', $tokens = 0, $time = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $wpdb->insert($table, [
            'post_id' => $post_id,
            'content_type' => $params['content_type'],
            'title' => $params['title'] ?? wp_trim_words($params['prompt'], 10),
            'prompt' => $params['prompt'],
            'generated_content' => $content,
            'seo_score' => $this->seo_optimizer->calculate_score($content, $params['seo_keywords']),
            'status' => $status,
            'error_message' => $error,
            'api_tokens_used' => $tokens,
            'generation_time' => $time,
        ]);
    }

    public function get_generation_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'premium_auto_content_log';
        
        $stats = [];
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $stats['success'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'completed'");
        $stats['failed'] = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");
        $stats['total_tokens'] = $wpdb->get_var("SELECT SUM(api_tokens_used) FROM $table");
        $stats['avg_time'] = $wpdb->get_var("SELECT AVG(generation_time) FROM $table WHERE status = 'completed'");
        
        return $stats;
    }
}