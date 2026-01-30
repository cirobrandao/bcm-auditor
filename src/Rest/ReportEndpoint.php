<?php
namespace BCMAuditor\Rest;

use BCMAuditor\Report\ReportBuilder;
use BCMAuditor\Util\Token;

final class ReportEndpoint {
  public function hooks(): void {
    add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    register_rest_route('bcm-auditor/v1', '/report', [
      'methods' => 'GET',
      'permission_callback' => [$this, 'permission'],
      'callback' => [$this, 'report'],
      'args' => [
        'token' => [
          'required' => true,
          'type' => 'string',
        ],
        'refresh' => [
          'required' => false,
          'type' => 'boolean',
          'default' => false,
        ],
      ],
    ]);
  }

  public function permission(\WP_REST_Request $req): bool {
    $token = (string) $req->get_param('token');
    if (!Token::verify($token)) {
      return false;
    }

    // Very simple rate limit: 60 req/hour per IP.
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'bcm_auditor_rl_' . md5((string)$ip);
    $count = (int) get_transient($key);
    $count++;
    set_transient($key, $count, HOUR_IN_SECONDS);
    if ($count > 60) {
      return false;
    }

    return true;
  }

  public function report(\WP_REST_Request $req): \WP_REST_Response {
    $refresh = (bool) $req->get_param('refresh');

    $cacheKey = 'bcm_auditor_report_cache';
    if (!$refresh) {
      $cached = get_transient($cacheKey);
      if (is_array($cached)) {
        return new \WP_REST_Response($cached, 200);
      }
    }

    $data = (new ReportBuilder())->build();
    set_transient($cacheKey, $data, 5 * MINUTE_IN_SECONDS);

    return new \WP_REST_Response($data, 200);
  }
}
