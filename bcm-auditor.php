<?php
/**
 * Plugin Name:       BCM Auditor
 * Plugin URI:        https://github.com/cirobrandao/bcm-auditor
 * Description:       Generates a secure, token-protected JSON report to help size a VPS for your WordPress site.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            BCM Network
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bcm-auditor
 */

if (!defined('ABSPATH')) {
  exit;
}

define('BCM_AUDITOR_VERSION', '0.1.0');
define('BCM_AUDITOR_FILE', __FILE__);
define('BCM_AUDITOR_DIR', plugin_dir_path(__FILE__));

autoload_bcm_auditor();

function autoload_bcm_auditor(): void {
  require_once BCM_AUDITOR_DIR . 'src/Plugin.php';
  require_once BCM_AUDITOR_DIR . 'src/Admin/AdminPage.php';
  require_once BCM_AUDITOR_DIR . 'src/Rest/ReportEndpoint.php';
  require_once BCM_AUDITOR_DIR . 'src/Report/ReportBuilder.php';
  require_once BCM_AUDITOR_DIR . 'src/Util/Token.php';
}

add_action('plugins_loaded', function () {
  \BCMAuditor\Plugin::instance()->boot();
});
