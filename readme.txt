=== BCM Auditor ===
Contributors: bcmnetwork
Tags: audit, vps, sizing, report
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate a token-protected JSON report for VPS sizing.

== Description ==
BCM Auditor helps the site owner grant read-only access to a technical report (JSON) containing disk usage, file counts, database size, and runtime limits.

== Usage ==
1. Install and activate the plugin.
2. Go to WordPress admin → Tools → BCM Auditor.
3. Click "Generate token/link".
4. Share the generated URL with BCM.

== REST API ==
GET /wp-json/bcm-auditor/v1/report?token=YOUR_TOKEN
Optional: &refresh=1

== Privacy ==
This plugin does NOT export user emails, post content, or wp-config secrets.
