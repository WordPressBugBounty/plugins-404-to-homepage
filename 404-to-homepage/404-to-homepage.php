<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Redirect 404 to Homepage
Plugin URI: https://wordpress.org/plugins/404-to-homepage/
Description: Redirect 404 missing pages to the homepage.
Author: pipdig
Author URI: https://www.pipdig.co/
Version: 1.1
License: GPLv2 or later
*/

add_action('template_redirect', function() {

	if (wp_doing_ajax() || wp_doing_cron() || is_admin() || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('REST_REQUEST') && REST_REQUEST)) return;

	if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') return;

	if (empty($_SERVER['REQUEST_URI'])) return;

	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

	if (substr($path, -4) === '.php') return;
	
	if (substr($path, -4) === '.txt') return;

	if (strpos($path, 'sitemap.xml') !== false) return;

	// Exclude uploads directory (cached)
	static $uploads_path = null;
	if ($uploads_path === null) {
		$u = wp_get_upload_dir();
		$uploads_path = parse_url($u['baseurl'], PHP_URL_PATH);
	}

	if ($uploads_path && strpos($path, $uploads_path) === 0) return;

	global $wp_query;

	if (empty($wp_query) || empty($wp_query->is_404)) return;

	// Prevent redirect loop if homepage ever 404s
	$home_path = untrailingslashit(parse_url(home_url('/'), PHP_URL_PATH));
	if (untrailingslashit($path) === $home_path) return;

	wp_safe_redirect(home_url('/'), 301);
	die;

}, PHP_INT_MAX);