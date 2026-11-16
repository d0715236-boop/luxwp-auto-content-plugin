<?php
/**
 * SEO Optimizer Class
 */

class Premium_SEO_Optimizer {

    private $settings;

    public function __construct() {
        $this->settings = get_option('premium_auto_content_settings', []);
    }

    public function optimize($content, $keywords = '', $content_type = 'post') {
        if (empty($content)) return $content;

        // Optimize keyword density
        $content = $this->optimize_keywords($content, $keywords);
        
        // Improve heading structure
        $content = $this->improve_headings($content);
        
        // Add meta description if missing
        $content = $this->ensure_meta_description($content, $keywords);
        
        // Improve readability
        $content = $this->improve_readability($content);
        
        // Add alt text suggestions for images
        $content = $this->add_image_alt_suggestions($content);
        
        // Ensure proper content length
        $content = $this->adjust_content_length($content, $content_type);

        return $content;
    }

    private function optimize_keywords($content, $keywords) {
        if (empty($keywords)) return $content;

        $keyword_list = array_map('trim', explode(',', $keywords));
        $keyword_list = array_filter($keyword_list);
        
        if (empty($keyword_list)) return $content;

        $content_lower = strtolower($content);
        $word_count = str_word_count($content);
        $target_density = 1.5; // 1.5% keyword density

        foreach ($keyword_list as $keyword) {
            $keyword_lower = strtolower($keyword);
            $current_count = substr_count($content_lower, $keyword_lower);
            $target_count = max(1, floor($word_count * $target_density / 100));
            
            if ($current_count < $target_count) {
                // Add keyword naturally in appropriate places
                $content = $this->inject_keyword($content, $keyword, $target_count - $current_count);
            } elseif ($current_count > $target_count * 2) {
                // Keyword stuffing detected - could warn but don't auto-remove
            }
        }

        return $content;
    }

    private function inject_keyword($content, $keyword, $count) {
        // Find good places to inject keywords (after headings, in first paragraph, etc.)
        $sentences = preg_split('/(?<=[.!?])\s+/', $content);
        $injected = 0;
        
        for ($i = 0; $i < count($sentences) && $injected < $count; $i++) {
            $sentence = $sentences[$i];
            $sentence_lower = strtolower($sentence);
            
            // Skip if already contains keyword
            if (strpos($sentence_lower, strtolower($keyword)) !== false) continue;
            
            // Good positions: early in content, after headings, in conclusions
            $is_good_position = ($i < 3) || 
                               (isset($sentences[$i-1]) && preg_match('/^#{2,3}/', $sentences[$i-1])) ||
                               ($i > count($sentences) - 5);
            
            if ($is_good_position && strlen($sentence) > 50) {
                // Inject keyword naturally
                $words = explode(' ', $sentence);
                $insert_pos = min(5, count($words) - 1);
                array_splice($words, $insert_pos, 0, $keyword);
                $sentences[$i] = implode(' ', $words);
                $injected++;
            }
        }
        
        return implode(' ', $sentences);
    }

    private function improve_headings($content) {
        // Ensure H1 exists
        if (!preg_match('/^#\s+/m', $content)) {
            // Try to extract title from first line
            $lines = explode("\n", $content);
            $first_line = trim($lines[0]);
            if (!empty($first_line) && !preg_match('/^#{1,3}/', $first_line)) {
                $content = "# " . $first_line . "\n\n" . implode("\n", array_slice($lines, 1));
            }
        }

        // Ensure heading hierarchy (H2 after H1, H3 after H2)
        $lines = explode("\n", $content);
        $last_heading_level = 1;
        
        foreach ($lines as $i => $line) {
            if (preg_match('/^(#{2,3})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                if ($level > $last_heading_level + 1) {
                    // Fix heading level
                    $lines[$i] = str_repeat('#', $last_heading_level + 1) . ' ' . $matches[2];
                    $level = $last_heading_level + 1;
                }
                $last_heading_level = $level;
            }
        }

        return implode("\n", $lines);
    }

    private function ensure_meta_description($content, $keywords) {
        if (preg_match('/meta description[:\s]+/i', $content)) return $content;

        // Generate meta description from first paragraph
        $first_paragraph = '';
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^#{1,3}/', $line) && strlen($line) > 50) {
                $first_paragraph = $line;
                break;
            }
        }

        if ($first_paragraph) {
            $meta_desc = wp_trim_words(strip_tags($first_paragraph), 25, '...');
            // Add at the beginning or after title
            if (preg_match('/^#\s+.+$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $pos = $matches[0][1] + strlen($matches[0][0]);
                $content = substr($content, 0, $pos) . "\n\n**Meta Description:** " . $meta_desc . "\n\n" . substr($content, $pos);
            } else {
                $content = "**Meta Description:** " . $meta_desc . "\n\n" . $content;
            }
        }

        return $content;
    }

    private function improve_readability($content) {
        // Break long paragraphs
        $lines = explode("\n", $content);
        $result = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || preg_match('/^#{1,3}/', $line) || preg_match('/^[\-\*]\s+/', $line) || preg_match('/^\d+\.\s+/', $line)) {
                $result[] = $line;
                continue;
            }
            
            // If paragraph is very long (>200 words), split it
            if (str_word_count($line) > 200) {
                $sentences = preg_split('/(?<=[.!?])\s+/', $line);
                $chunk = [];
                $word_count = 0;
                
                foreach ($sentences as $sentence) {
                    $chunk[] = $sentence;
                    $word_count += str_word_count($sentence);
                    
                    if ($word_count >= 150) {
                        $result[] = implode(' ', $chunk);
                        $chunk = [];
                        $word_count = 0;
                    }
                }
                
                if ($chunk) {
                    $result[] = implode(' ', $chunk);
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    private function add_image_alt_suggestions($content) {
        // Add comments for suggested image placements with alt text
        $lines = explode("\n", $content);
        $result = [];
        $heading_count = 0;
        
        foreach ($lines as $line) {
            $result[] = $line;
            
            if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $heading_count++;
                // Suggest image after every 2-3 H2 headings
                if ($heading_count % 3 === 0) {
                    $suggested_alt = 'Illustration for: ' . $matches[1];
                    $result[] = '<!-- wp:image {"alt":"' . esc_attr($suggested_alt) . '","caption":"' . esc_attr($suggested_alt) . '"} -->';
                    $result[] = '<!-- /wp:image -->';
                    $result[] = '';
                }
            }
        }

        return implode("\n", $result);
    }

    private function adjust_content_length($content, $content_type) {
        $word_count = str_word_count(strip_tags($content));
        
        $targets = [
            'post' => ['min' => 1000, 'max' => 3000],
            'page' => ['min' => 800, 'max' => 2500],
            'product' => ['min' => 300, 'max' => 1000],
            'portfolio' => ['min' => 500, 'max' => 1500],
        ];
        
        $target = $targets[$content_type] ?? $targets['post'];
        
        if ($word_count < $target['min']) {
            // Content too short - add note for expansion
            $content .= "\n\n<!-- Note: Content is shorter than recommended minimum of {$target['min']} words. Consider expanding sections. -->";
        } elseif ($word_count > $target['max'] * 1.5) {
            // Content very long - add note
            $content .= "\n\n<!-- Note: Content exceeds recommended maximum. Consider splitting into multiple posts. -->";
        }

        return $content;
    }

    public function calculate_score($content, $keywords = '') {
        $score = 0;
        $max_score = 100;
        
        // Word count (20 points)
        $word_count = str_word_count(strip_tags($content));
        if ($word_count >= 1500) $score += 20;
        elseif ($word_count >= 1000) $score += 15;
        elseif ($word_count >= 500) $score += 10;
        elseif ($word_count >= 300) $score += 5;
        
        // Heading structure (15 points)
        $h1_count = substr_count($content, '# ');
        $h2_count = substr_count($content, '## ');
        $h3_count = substr_count($content, '### ');
        if ($h1_count === 1 && $h2_count >= 3) $score += 15;
        elseif ($h1_count === 1 && $h2_count >= 2) $score += 10;
        elseif ($h1_count >= 1) $score += 5;
        
        // Keyword optimization (20 points)
        if (!empty($keywords)) {
            $keyword_list = array_map('trim', explode(',', $keywords));
            $keyword_list = array_filter($keyword_list);
            $found = 0;
            $content_lower = strtolower($content);
            foreach ($keyword_list as $keyword) {
                if (strpos($content_lower, strtolower($keyword)) !== false) $found++;
            }
            if ($found === count($keyword_list)) $score += 20;
            elseif ($found >= count($keyword_list) * 0.7) $score += 15;
            elseif ($found >= count($keyword_list) * 0.5) $score += 10;
            elseif ($found > 0) $score += 5;
        } else {
            $score += 10; // Neutral if no keywords specified
        }
        
        // Meta description (10 points)
        if (preg_match('/meta description[:\s]+/i', $content)) $score += 10;
        
        // Readability - paragraph length (10 points)
        $paragraphs = preg_split('/\n\s*\n/', $content);
        $avg_length = 0;
        $count = 0;
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (!empty($p) && !preg_match('/^#{1,3}/', $p) && !preg_match('/^[\-\*]/', $p)) {
                $avg_length += str_word_count($p);
                $count++;
            }
        }
        if ($count > 0) {
            $avg_length = $avg_length / $count;
            if ($avg_length <= 150) $score += 10;
            elseif ($avg_length <= 200) $score += 7;
            elseif ($avg_length <= 300) $score += 5;
        }
        
        // Lists and formatting (10 points)
        $list_count = substr_count($content, '- ') + substr_count($content, '* ') + 
                      preg_match_all('/^\d+\.\s/m', $content);
        if ($list_count >= 5) $score += 10;
        elseif ($list_count >= 3) $score += 7;
        elseif ($list_count >= 1) $score += 5;
        
        // Images/media suggestions (10 points)
        if (preg_match('/wp:image/', $content)) $score += 10;
        elseif (preg_match('/!\[.*\]\(.*\)/', $content)) $score += 5;
        
        // FAQ section (5 points)
        if (preg_match('/##\s*FAQ|##\s*Frequently Asked Questions/i', $content)) $score += 5;
        
        // Schema markup readiness (5 points)
        // This is handled separately
        
        return min($score, $max_score);
    }

    public function get_recommendations($content, $keywords = '') {
        $recommendations = [];
        
        $word_count = str_word_count(strip_tags($content));
        if ($word_count < 1000) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Content is under 1000 words. Consider expanding for better SEO performance.',
            ];
        }
        
        $h1_count = substr_count($content, '# ');
        if ($h1_count !== 1) {
            $recommendations[] = [
                'type' => 'error',
                'message' => 'Content should have exactly one H1 heading.',
            ];
        }
        
        $h2_count = substr_count($content, '## ');
        if ($h2_count < 3) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Content should have at least 3 H2 headings for good structure.',
            ];
        }
        
        if (!preg_match('/meta description[:\s]+/i', $content)) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Meta description is missing. Add a compelling meta description (150-160 characters).',
            ];
        }
        
        if (!empty($keywords)) {
            $keyword_list = array_map('trim', explode(',', $keywords));
            $keyword_list = array_filter($keyword_list);
            $content_lower = strtolower($content);
            $missing = [];
            foreach ($keyword_list as $keyword) {
                if (strpos($content_lower, strtolower($keyword)) === false) {
                    $missing[] = $keyword;
                }
            }
            if (!empty($missing)) {
                $recommendations[] = [
                    'type' => 'warning',
                    'message' => 'Missing keywords: ' . implode(', ', $missing),
                ];
            }
        }
        
        if (!preg_match('/wp:image|!\[.*\]\(.*\)/', $content)) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Consider adding images with descriptive alt text for better engagement.',
            ];
        }
        
        return $recommendations;
    }
}