<?php
namespace BCMAuditor\Admin;

use BCMAuditor\Util\Token;

final class AdminPage {
  public function hooks(): void {
    add_action('admin_menu', [$this, 'admin_menu']);
    add_action('admin_post_bcm_auditor_generate', [$this, 'handle_generate']);
  }

  public function admin_menu(): void {
    add_management_page(
      __('BCM Auditor', 'bcm-auditor'),
      __('BCM Auditor', 'bcm-auditor'),
      'manage_options',
      'bcm-auditor',
      [$this, 'render']
    );
  }

  public function handle_generate(): void {
    if (!current_user_can('manage_options')) {
      wp_die('Forbidden');
    }
    check_admin_referer('bcm_auditor_generate');

    $raw = Token::generate_raw();
    Token::store_hash($raw);

    // Store raw token once in a transient so we can show it after redirect (one-time display).
    set_transient('bcm_auditor_last_token', $raw, 60);

    wp_safe_redirect(admin_url('tools.php?page=bcm-auditor&generated=1'));
    exit;
  }

  public function render(): void {
    if (!current_user_can('manage_options')) {
      return;
    }

    $justGenerated = !empty($_GET['generated']);
    $raw = $justGenerated ? get_transient('bcm_auditor_last_token') : null;
    if ($justGenerated) {
      delete_transient('bcm_auditor_last_token');
    }

    $base = rest_url('bcm-auditor/v1/report');

    echo '<div class="wrap">';
    echo '<h1>BCM Auditor</h1>';

    echo '<p>This plugin generates a token-protected JSON report for VPS sizing. Share ONLY the link with BCM.</p>';

    echo '<h2>Generate report link</h2>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="bcm_auditor_generate">';
    wp_nonce_field('bcm_auditor_generate');
    submit_button(Token::has_token() ? 'Regenerate token/link' : 'Generate token/link', 'primary', '');
    echo '</form>';

    if ($raw) {
      $url = add_query_arg(['token' => $raw], $base);
      echo '<div class="notice notice-success"><p><strong>Link generated.</strong></p></div>';
      echo '<p><strong>Report URL (JSON):</strong></p>';
      echo '<p><code style="display:block; padding:10px;">' . esc_html($url) . '</code></p>';
      echo '<p>You can also force refresh: <code>' . esc_html($url . '&refresh=1') . '</code></p>';
    } elseif (Token::has_token()) {
      echo '<p>A token already exists. For security, the token is not shown again. Click “Regenerate” to create a new link.</p>';
    } else {
      echo '<p>No token yet. Generate one to create a report URL.</p>';
    }

    echo '</div>';
  }
}
