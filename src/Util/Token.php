<?php
namespace BCMAuditor\Util;

final class Token {
  private const OPT_HASH = 'bcm_auditor_token_hash';
  private const OPT_CREATED = 'bcm_auditor_token_created';

  public static function generate_raw(): string {
    // 48 hex chars.
    return bin2hex(random_bytes(24));
  }

  public static function store_hash(string $raw): void {
    update_option(self::OPT_HASH, self::hash($raw), false);
    update_option(self::OPT_CREATED, time(), false);
  }

  public static function get_created_at(): int {
    return (int) get_option(self::OPT_CREATED, 0);
  }

  public static function has_token(): bool {
    $h = get_option(self::OPT_HASH, '');
    return is_string($h) && $h !== '';
  }

  public static function verify(?string $raw): bool {
    if (!$raw || !is_string($raw)) {
      return false;
    }
    $expected = get_option(self::OPT_HASH, '');
    if (!is_string($expected) || $expected === '') {
      return false;
    }
    $actual = self::hash($raw);
    return hash_equals($expected, $actual);
  }

  private static function hash(string $raw): string {
    // HMAC with WP salts so hash is not reusable outside this site.
    $key = wp_salt('auth');
    return hash_hmac('sha256', $raw, $key);
  }
}
