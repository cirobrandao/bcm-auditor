<?php
namespace BCMAuditor;

use BCMAuditor\Admin\AdminPage;
use BCMAuditor\Rest\ReportEndpoint;

final class Plugin {
  private static ?Plugin $instance = null;

  public static function instance(): Plugin {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function boot(): void {
    if (is_admin()) {
      (new AdminPage())->hooks();
    }

    (new ReportEndpoint())->hooks();
  }
}
