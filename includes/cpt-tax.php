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
    'taxonomies'         => ['category'],
    'has_archive'        => true,
    'rewrite'            => ['slug' => 'courses'],
    'show_in_nav_menus'  => true,
    'hierarchical'       => false,
  ];

  register_post_type(TRE_COURSES_CPT, $args);
}

add_action('init', 'tre_courses_register_cpt');

/**
 * Register Course Type taxonomy.
 */
function tre_courses_register_tax() {
  $labels = [
    'name'          => 'Course Types',
    'singular_name' => 'Course Type',
    'search_items'  => 'Search Course Types',
    'all_items'     => 'All Course Types',
    'edit_item'     => 'Edit Course Type',
    'update_item'   => 'Update Course Type',
    'add_new_item'  => 'Add New Course Type',
    'menu_name'     => 'Course Types',
  ];

  $args = [
    'labels'            => $labels,
    'public'            => true,
    'show_in_rest'      => true,
    'hierarchical'      => true,
    'rewrite'           => ['slug' => 'course-type'],
    'show_admin_column' => true,
  ];

  register_taxonomy(TRE_COURSES_TAX, [TRE_COURSES_CPT], $args);
}

add_action('init', 'tre_courses_register_tax');
