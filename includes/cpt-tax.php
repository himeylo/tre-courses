<?php
if (!defined('ABSPATH')) exit;

/**
 * Register Courses CPT.
 */
function tre_courses_register_cpt() {
  $labels = [
    'name'               => 'Courses',
    'singular_name'      => 'Course',
    'menu_name'          => 'Courses',
    'name_admin_bar'     => 'Course',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Course',
    'new_item'           => 'New Course',
    'edit_item'          => 'Edit Course',
    'view_item'          => 'View Course',
    'all_items'          => 'All Courses',
    'search_items'       => 'Search Courses',
    'not_found'          => 'No courses found.',
    'not_found_in_trash' => 'No courses found in Trash.',
  ];

  $args = [
    'labels'             => $labels,
    'public'             => true,
    'show_in_rest'       => true,
    'menu_icon'          => 'dashicons-welcome-learn-more',
    'supports'           => ['title', 'editor', 'excerpt', 'revisions', 'thumbnail'],
    'has_archive'        => true,
    'rewrite'            => ['slug' => 'courses'],
    'show_in_nav_menus'  => true,
    'hierarchical'       => false,
  ];

  register_post_type(TRE_COURSES_CPT, $args);
}

add_action('init', 'tre_courses_register_cpt');

/**
 * Register Course Categories taxonomy.
 */
function tre_courses_register_tax() {
  $labels = [
    'name'          => 'Course Categories',
    'singular_name' => 'Course Category',
    'search_items'  => 'Search Course Categories',
    'all_items'     => 'All Course Categories',
    'edit_item'     => 'Edit Course Category',
    'update_item'   => 'Update Course Category',
    'add_new_item'  => 'Add New Course Category',
    'menu_name'     => 'Course Categories',
  ];

  $args = [
    'labels'            => $labels,
    'public'            => true,
    'show_in_rest'      => true,
    'hierarchical'      => true,
    'rewrite'           => ['slug' => 'course-category'],
    'show_admin_column' => true,
  ];

  register_taxonomy(TRE_COURSES_TAX, [TRE_COURSES_CPT], $args);
}

add_action('init', 'tre_courses_register_tax');

/**
 * Ensure the four requested default terms exist.
 */
function tre_courses_ensure_default_terms() {
  $terms = [
    'instructor-certification'      => 'Instructor Certification',
    'certified-instructor-courses'  => 'Certified Instructor Courses',
    'rider-licensing'               => 'Rider Licensing',
    'licensed-rider-courses'        => 'Licensed Rider Courses',
  ];

  foreach ($terms as $slug => $name) {
    if (!term_exists($slug, TRE_COURSES_TAX)) {
      wp_insert_term($name, TRE_COURSES_TAX, ['slug' => $slug]);
    }
  }
}
add_action('init', 'tre_courses_ensure_default_terms', 20);
