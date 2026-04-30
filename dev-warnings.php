<?php
/**
 * Dev-only admin warnings. Removed at deploy time (see deploy.php).
 */

namespace Dropp;

if (!defined('ABSPATH')) {
	exit;
}

add_action('admin_notices', function () {
	$expected = [
		'assets/js/dropp.js',
		'assets/js/dropp-admin.js',
		'assets/js/dropp-location-button.js',
		'assets/css/dropp.css',
		'assets/css/dropp-admin.css',
	];
	$missing = array_filter($expected, fn($rel) => !file_exists(__DIR__ . '/' . $rel));
	if (!$missing) {
		return;
	}
	printf(
		'<div class="notice notice-error"><p><strong>%s</strong></p><p>%s</p><pre>%s</pre><p><code>npm install &amp;&amp; npm run production</code></p></div>',
		esc_html__('Dropp for WooCommerce: built assets are missing.', 'dropp-for-woocommerce'),
		esc_html__('Run the asset build to generate them. Missing files:', 'dropp-for-woocommerce'),
		esc_html(implode("\n", $missing))
	);
});
