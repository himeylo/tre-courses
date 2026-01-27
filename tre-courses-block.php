<?php
/**
 * Plugin Name: TRE Courses (ACF Block + CPT)
 * Description: Adds a Courses custom post type with a course type taxonomy, ACF Pro fields, and an ACF block to display upcoming course lists by category with external links.
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
define('TRE_COURSES_TAX', 'course_type');

require_once TRE_COURSES_PLUGIN_DIR . 'includes/cpt-tax.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/acf-groups.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/template-helpers.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/block.php';
require_once TRE_COURSES_PLUGIN_DIR . 'includes/customizer.php';

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

add_filter('template_include', function ($template) {
  if (is_tax(TRE_COURSES_TAX)) {
    $plugin_template = TRE_COURSES_PLUGIN_DIR . 'templates/taxonomy-course_type.php';
    if (file_exists($plugin_template)) {
      return $plugin_template;
    }
  }

  return $template;
}, 999);

add_filter('taxonomy_template', function ($template) {
  if (is_tax(TRE_COURSES_TAX)) {
    $plugin_template = TRE_COURSES_PLUGIN_DIR . 'templates/taxonomy-course_type.php';
    if (file_exists($plugin_template)) {
      return $plugin_template;
    }
  }

  return $template;
}, 999);

add_filter('single_template', function ($template) {
  if (is_singular(TRE_COURSES_CPT)) {
    $plugin_template = TRE_COURSES_PLUGIN_DIR . 'templates/single-course.php';
    if (file_exists($plugin_template)) {
      return $plugin_template;
    }
  }

  return $template;
}, 999);
