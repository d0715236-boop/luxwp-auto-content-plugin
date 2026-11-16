<?php
/**
 * Google Gemini API Integration Class
 */

class Premium_Gemini_API {

    private $api_key;
    private $model;
    private $base_url = 'https://generativelanguage.googleapis.com/v1beta';
    private $last_error = '';
    private $token_usage = [
        'prompt_tokens' => 0,
        'completion_tokens' => 0,
        'total_tokens' => 0,
    ];

    public function __construct($api_key = '', $model = 'gemini-1.5-pro') {
        $this->api_key = $api_key;
        $this->model = $model;
    }

    public function set_api_key($api_key) {
        $this->api_key = $api_key;
    }

    public function set_model($model) {
        $this->model = $model;
    }

    public function test_connection() {
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'API key is not configured',
            ];
        }

        $url = $this->base_url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key;
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Hello, this is a test message. Please respond with "Connection successful".']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 50,
            ]
        ];

        $response = $this->make_request($url, $payload);
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'error' => $response['error']['message'] ?? 'Unknown error',
            ];
        }

        return [
            'success' => true,
            'message' => 'Connection successful',
            'model' => $this->model,
        ];
    }

    public function generate_content($prompt, $options = []) {
        if (empty($this->api_key)) {
            return [
                'error' => 'API key is not configured',
            ];
        }

        $url = $this->base_url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key;

        $system_instruction = $options['system_instruction'] ?? $this->get_default_system_instruction();
        
        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $system_instruction]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'topK' => $options['top_k'] ?? 40,
                'topP' => $options['top_p'] ?? 0.95,
                'maxOutputTokens' => $options['max_tokens'] ?? $this->get_max_tokens_for_length($options['length'] ?? 'medium'),
                'candidateCount' => 1,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
            ],
        ];

        // Add SEO keywords if provided
        if (!empty($options['seo_keywords'])) {
            $payload['contents'][0]['parts'][] = ['text' => "\n\nSEO Keywords to incorporate naturally: " . $options['seo_keywords']];
        }

        // Add language instruction
        if (!empty($options['language']) && $options['language'] !== 'en_US') {
            $language_map = [
                'es_ES' => 'Spanish',
                'fr_FR' => 'French',
                'de_DE' => 'German',
                'it_IT' => 'Italian',
                'pt_BR' => 'Portuguese',
                'ru_RU' => 'Russian',
                'zh_CN' => 'Chinese (Simplified)',
                'ja_JP' => 'Japanese',
                'ko_KR' => 'Korean',
                'ar_SA' => 'Arabic',
                'hi_IN' => 'Hindi',
            ];
            $lang = $language_map[$options['language']] ?? 'English';
            $payload['contents'][0]['parts'][] = ['text' => "\n\nWrite the content in " . $lang . "."];
        }

        $response = $this->make_request($url, $payload);

        if (isset($response['error'])) {
            $this->last_error = $response['error']['message'] ?? 'Unknown error';
            return [
                'error' => $this->last_error,
            ];
        }

        // Extract generated content
        $content = '';
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $response['candidates'][0]['content']['parts'][0]['text'];
        }

        // Update token usage
        if (isset($response['usageMetadata'])) {
            $this->token_usage = [
                'prompt_tokens' => $response['usageMetadata']['promptTokenCount'] ?? 0,
                'completion_tokens' => $response['usageMetadata']['candidatesTokenCount'] ?? 0,
                'total_tokens' => $response['usageMetadata']['totalTokenCount'] ?? 0,
            ];
        }

        return [
            'content' => $content,
            'tokens' => $this->token_usage,
            'model' => $this->model,
            'finish_reason' => $response['candidates'][0]['finishReason'] ?? 'STOP',
        ];
    }

    public function generate_structured_content($prompt, $schema, $options = []) {
        $url = $this->base_url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
                'responseMimeType' => 'application/json',
                'responseSchema' => $schema,
            ],
        ];

        $response = $this->make_request($url, $payload);

        if (isset($response['error'])) {
            return ['error' => $response['error']['message'] ?? 'Unknown error'];
        }

        $content = '';
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $response['candidates'][0]['content']['parts'][0]['text'];
        }

        $parsed = json_decode($content, true);
        
        return [
            'content' => $parsed ?: $content,
            'raw' => $content,
            'tokens' => $this->token_usage,
        ];
    }

    public function get_last_error() {
        return $this->last_error;
    }

    public function get_token_usage() {
        return $this->token_usage;
    }

    private function make_request($url, $payload) {
        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
            'timeout' => 120,
            'sslverify' => true,
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return ['error' => ['message' => $response->get_error_message()]];
        }

        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 400) {
            $error_data = json_decode($body, true);
            return ['error' => $error_data['error'] ?? ['message' => 'HTTP ' . $code]];
        }

        return json_decode($body, true);
    }

    private function get_default_system_instruction() {
        return "You are an expert content writer specializing in creating high-quality, SEO-optimized WordPress content. 
Your content should be:
- Well-structured with proper headings (H2, H3)
- Engaging and informative
- Optimized for search engines with natural keyword integration
- Formatted for readability with short paragraphs, bullet points, and numbered lists
- Include relevant examples, statistics, and actionable advice
- Written in a professional yet accessible tone
- Include proper meta descriptions and title suggestions
- Follow E-E-A-T principles (Experience, Expertise, Authoritativeness, Trustworthiness)

When generating content for WordPress, consider:
- Gutenberg block compatibility
- Featured image suggestions
- Internal linking opportunities
- Schema markup readiness
- Mobile-responsive formatting";
    }

    private function get_max_tokens_for_length($length) {
        $limits = [
            'short' => 2000,
            'medium' => 4000,
            'long' => 8000,
            'comprehensive' => 12000,
        ];
        return $limits[$length] ?? 4000;
    }

    public function list_models() {
        if (empty($this->api_key)) {
            return ['error' => 'API key is not configured'];
        }

        $url = $this->base_url . '/models?key=' . $this->api_key;
        $response = wp_remote_get($url, ['timeout' => 30]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}