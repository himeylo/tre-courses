<?php
/**
 * Plugin Name: TRE Courses (ACF Block + CPT)
 * Description: Adds a Courses custom post type with course categories, ACF Pro fields (including occurrences repeater), and an ACF block to display upcoming course lists by category with external links.
 * Version: 1.0.0
 * Author: TRE
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) exit;

define('TRE_COURSES_PLUGIN_FILE', __FILE__);
define('TRE_COURSES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRE_COURSES_PLUGIN_URL', plugin_dir_url(__FILE__));

define('TRE_COURSES_CPT', 'course');
define('TRE_COURSES_TAX', 'course_category');

require_once TRE_COURSES_PLUGIN_DIR . 'includes/cpt-tax.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/acf-groups.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/block.php';

// Activation: register CPT/tax then flush rewrites.
register_activation_hook(__FILE__, function () {
  tre_courses_register_cpt();
  tre_courses_register_tax();
  flush_rewrite_rules();
});

// Deactivation: flush rewrites.
register_deactivation_hook(__FILE__, function () {
  flush_rewrite_rules();
});

// Frontend styles for the block output.
add_action('wp_enqueue_scripts', function () {
  wp_register_style('tre-courses', TRE_COURSES_PLUGIN_URL . 'assets/tre-courses.css', [], '1.0.0');
});

add_action('enqueue_block_assets', function () {
  // Load on both editor + frontend (cheap CSS, safe).
  wp_enqueue_style('tre-courses');
});
