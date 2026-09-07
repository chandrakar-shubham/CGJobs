<?php
/**
 * Plugin Name: CGJobs API Bridge
 * Description: Connects a WordPress public website to the CGJobs Laravel API. Laravel remains the source of truth for jobs.
 * Version: 1.0.0
 * Author: CGJobs
 * Requires at least: 6.4
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

final class CGJobs_API_Bridge {
    private const OPTION = 'cgjobs_api_base_url';
    private const CACHE_TTL = 120;

    public static function init(): void {
        add_action('admin_menu', [self::class, 'admin_menu']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_shortcode('cgjobs_jobs', [self::class, 'jobs_shortcode']);
        add_shortcode('cgjobs_job', [self::class, 'job_shortcode']);
    }

    public static function admin_menu(): void {
        add_options_page('CGJobs API', 'CGJobs API', 'manage_options', 'cgjobs-api', [self::class, 'settings_page']);
    }

    public static function register_settings(): void {
        register_setting('cgjobs_api', self::OPTION, [
            'type' => 'string',
            'sanitize_callback' => function ($value) {
                return esc_url_raw(rtrim((string) $value, '/'));
            },
            'default' => '',
        ]);
    }

    private static function base_url(): string {
        return rtrim((string) get_option(self::OPTION, ''), '/');
    }

    private static function request(string $path, array $query = []): array {
        $base = self::base_url();
        if ($base === '') {
            return ['success' => false, 'message' => 'CGJobs API URL is not configured.'];
        }
        $url = $base . '/' . ltrim($path, '/');
        if ($query) {
            $url = add_query_arg($query, $url);
        }
        $cache_key = 'cgjobs_' . md5($url);
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }
        $response = wp_remote_get($url, [
            'timeout' => 8,
            'headers' => ['Accept' => 'application/json'],
        ]);
        if (is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }
        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($code < 200 || $code >= 300 || !is_array($data)) {
            return ['success' => false, 'message' => 'CGJobs API request failed.'];
        }
        set_transient($cache_key, $data, self::CACHE_TTL);
        return $data;
    }

    public static function jobs_shortcode(array $atts = []): string {
        $atts = shortcode_atts(['limit' => 20, 'category' => '', 'department' => ''], $atts, 'cgjobs_jobs');
        $data = self::request('/jobs', array_filter([
            'limit' => min(max((int) $atts['limit'], 1), 50),
            'category' => $atts['category'],
            'department' => $atts['department'],
            'lang' => 'hi',
        ], static fn($v) => $v !== ''));
        if (empty($data['success'])) {
            return '<div class="cgjobs-api-error">Unable to load jobs right now.</div>';
        }
        $jobs = $data['jobs'] ?? [];
        if (!$jobs) {
            return '<div class="cgjobs-empty">No jobs found.</div>';
        }
        ob_start();
        echo '<div class="cgjobs-list">';
        foreach ($jobs as $job) {
            $id = isset($job['custom_id']) ? $job['custom_id'] : ($job['id'] ?? '');
            $title = esc_html($job['title'] ?? $job['title_en'] ?? 'Job');
            $summary = esc_html(wp_trim_words(wp_strip_all_tags((string) ($job['summary'] ?? '')), 24));
            $url = esc_url(add_query_arg('job_id', rawurlencode((string) $id), get_permalink()));
            echo '<article class="cgjobs-card">';
            echo '<h3><a href="' . $url . '">' . $title . '</a></h3>';
            if ($summary !== '') echo '<p>' . $summary . '</p>';
            echo '</article>';
        }
        echo '</div>';
        return (string) ob_get_clean();
    }

    public static function job_shortcode(array $atts = []): string {
        $atts = shortcode_atts(['id' => ''], $atts, 'cgjobs_job');
        $id = $atts['id'] !== '' ? $atts['id'] : sanitize_text_field(wp_unslash($_GET['job_id'] ?? ''));
        if ($id === '') return '<div class="cgjobs-empty">Select a job.</div>';
        $data = self::request('/news/' . rawurlencode($id), ['lang' => 'hi']);
        if (empty($data['success']) || empty($data['item'])) return '<div class="cgjobs-api-error">Job not found.</div>';
        $job = $data['item'];
        ob_start();
        echo '<article class="cgjobs-detail">';
        echo '<h1>' . esc_html($job['title'] ?? 'Job') . '</h1>';
        if (!empty($job['summary'])) echo '<p>' . esc_html($job['summary']) . '</p>';
        if (!empty($job['content'])) echo wp_kses_post(wpautop((string) $job['content']));
        if (!empty($job['apply_url'])) echo '<p><a class="cgjobs-apply" href="' . esc_url($job['apply_url']) . '" rel="nofollow noopener">Apply Now</a></p>';
        echo '</article>';
        return (string) ob_get_clean();
    }

    public static function settings_page(): void {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap"><h1>CGJobs API</h1><form method="post" action="options.php">';
        settings_fields('cgjobs_api');
        echo '<table class="form-table"><tr><th><label for="cgjobs_api_base_url">Laravel API base URL</label></th><td>';
        printf('<input id="cgjobs_api_base_url" name="%s" type="url" class="regular-text" value="%s" placeholder="https://api.example.com/api/v1" />', esc_attr(self::OPTION), esc_attr(self::base_url()));
        echo '<p class="description">Use the Laravel API v1 base URL. WordPress never connects directly to the Laravel database.</p></td></tr></table>';
        submit_button();
        echo '</form></div>';
    }
}

CGJobs_API_Bridge::init();
