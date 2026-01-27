<?php
if (!defined('ABSPATH')) exit;

function tre_courses_sanitize_checkbox($value) {
  return $value ? 1 : 0;
}

function tre_courses_sanitize_date_format($value) {
  $allowed = ['smart', 'full', 'start'];
  return in_array($value, $allowed, true) ? $value : 'smart';
}

add_action('customize_register', function ($wp_customize) {
  $wp_customize->add_panel('tre_courses_options', [
    'title' => __('Course Options', 'tre-courses-block'),
    'priority' => 35,
  ]);

  $wp_customize->add_section('tre_courses_archive_layout', [
    'title' => __('Course Type Archive Layout', 'tre-courses-block'),
    'priority' => 35,
    'panel' => 'tre_courses_options',
  ]);

  $wp_customize->add_section('tre_courses_single_layout', [
    'title' => __('Single Course Layout', 'tre-courses-block'),
    'priority' => 36,
    'panel' => 'tre_courses_options',
  ]);

  $wp_customize->add_setting('tre_courses_course_type_base', [
    'default' => 'course',
    'sanitize_callback' => 'sanitize_title',
  ]);
  $wp_customize->add_control('tre_courses_course_type_base', [
    'label' => __('Course type URL base', 'tre-courses-block'),
    'description' => __('Slug used for course type archive URLs (example: "course" gives /course/term-name/).', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'text',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_term_image', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_term_image', [
    'label' => __('Show term image', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_term_title', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_term_title', [
    'label' => __('Show term title', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_term_description', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_term_description', [
    'label' => __('Show term description', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_group_by_month', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_group_by_month', [
    'label' => __('Group by month', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_date_format', [
    'default' => 'smart',
    'sanitize_callback' => 'tre_courses_sanitize_date_format',
  ]);
  $wp_customize->add_control('tre_courses_archive_date_format', [
    'label' => __('Date format', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'select',
    'choices' => [
      'smart' => __('Smart range', 'tre-courses-block'),
      'full' => __('Full start/end', 'tre-courses-block'),
      'start' => __('Start date only', 'tre-courses-block'),
    ],
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_date', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_date', [
    'label' => __('Show course date', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_link_date', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_link_date', [
    'label' => __('Link date to registration URL', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_open_new_tab', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_open_new_tab', [
    'label' => __('Open date links in new tab', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_venue', [
    'default' => 0,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_venue', [
    'label' => __('Show class location', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_city_state', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_city_state', [
    'label' => __('Show city, state', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_map_link', [
    'default' => 0,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_map_link', [
    'label' => __('Show map link', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_organizer', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_organizer', [
    'label' => __('Show organizer', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_archive_host_label', [
    'default' => __('Host', 'tre-courses-block'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tre_courses_archive_host_label', [
    'label' => __('Organizer label', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'text',
  ]);

  $wp_customize->add_setting('tre_courses_archive_show_cost', [
    'default' => 0,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_archive_show_cost', [
    'label' => __('Show cost', 'tre-courses-block'),
    'section' => 'tre_courses_archive_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_title', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_title', [
    'label' => __('Show course title', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_featured_image', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_featured_image', [
    'label' => __('Show featured image', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_date_format', [
    'default' => 'smart',
    'sanitize_callback' => 'tre_courses_sanitize_date_format',
  ]);
  $wp_customize->add_control('tre_courses_single_date_format', [
    'label' => __('Date format', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'select',
    'choices' => [
      'smart' => __('Smart range', 'tre-courses-block'),
      'full' => __('Full start/end', 'tre-courses-block'),
      'start' => __('Start date only', 'tre-courses-block'),
    ],
  ]);

  $wp_customize->add_setting('tre_courses_single_show_date', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_date', [
    'label' => __('Show course date', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_venue', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_venue', [
    'label' => __('Show class location', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_city_state', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_city_state', [
    'label' => __('Show city, state', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_map_link', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_map_link', [
    'label' => __('Show map link', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_organizer', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_organizer', [
    'label' => __('Show organizer', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_host_label', [
    'default' => __('Host', 'tre-courses-block'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tre_courses_single_host_label', [
    'label' => __('Organizer label', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'text',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_cost', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_cost', [
    'label' => __('Show cost', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_cta', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_cta', [
    'label' => __('Show CTA button', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_cta_text', [
    'default' => __('Details / Register', 'tre-courses-block'),
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('tre_courses_single_cta_text', [
    'label' => __('CTA button text', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'text',
  ]);

  $wp_customize->add_setting('tre_courses_single_open_new_tab', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_open_new_tab', [
    'label' => __('Open CTA in new tab', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);

  $wp_customize->add_setting('tre_courses_single_show_content', [
    'default' => 1,
    'sanitize_callback' => 'tre_courses_sanitize_checkbox',
  ]);
  $wp_customize->add_control('tre_courses_single_show_content', [
    'label' => __('Show content editor', 'tre-courses-block'),
    'section' => 'tre_courses_single_layout',
    'type' => 'checkbox',
  ]);
});

add_action('customize_save_after', function () {
  flush_rewrite_rules();
});
