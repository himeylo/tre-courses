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
    'publicly_queryable' => false,
    'show_in_rest'       => true,
    'menu_icon'          => 'dashicons-welcome-learn-more',
    'supports'           => ['title', 'editor', 'excerpt', 'revisions', 'thumbnail'],
    'taxonomies'         => ['category'],
    'has_archive'        => false,
    'rewrite'            => false,
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

  $course_type_base = sanitize_title((string) get_theme_mod('tre_courses_course_type_base', 'course'));
  if (!$course_type_base) {
    $course_type_base = 'course';
  }

  $args = [
    'labels'            => $labels,
    'public'            => true,
    'show_in_rest'      => true,
    'hierarchical'      => true,
    'rewrite'           => ['slug' => $course_type_base],
    'show_admin_column' => true,
  ];

  register_taxonomy(TRE_COURSES_TAX, [TRE_COURSES_CPT], $args);
}

add_action('init', 'tre_courses_register_tax');

function tre_courses_register_course_rewrites() {
  add_rewrite_tag('%course_type%', '([^/]+)', 'course_type=');
  add_rewrite_tag('%tre_course_month%', '([0-9]{4}-[a-z]{3})', 'tre_course_month=');
  add_permastruct(TRE_COURSES_CPT, 'courses/%course_type%/%tre_course_month%', [
    'with_front' => false,
  ]);
}

add_action('init', 'tre_courses_register_course_rewrites', 20);

add_filter('query_vars', function ($vars) {
  $vars[] = 'tre_course_month';
  return $vars;
});

add_filter('post_type_link', function ($permalink, $post) {
  if ($post->post_type !== TRE_COURSES_CPT) {
    return $permalink;
  }

  $start_date = function_exists('get_field') ? (string) get_field('start_date', $post->ID) : (string) get_post_meta($post->ID, 'start_date', true);
  if (!$start_date) {
    return $permalink;
  }

  try {
    $dt = new DateTime($start_date);
  } catch (Exception $ex) {
    return $permalink;
  }

  $month = strtolower($dt->format('M'));
  $year = $dt->format('Y');
  $term_slug = 'course';
  $terms = get_the_terms($post->ID, TRE_COURSES_TAX);
  if (!is_wp_error($terms) && !empty($terms)) {
    $term = array_shift($terms);
    if (!empty($term->slug)) {
      $term_slug = $term->slug;
    }
  }

  $path = sprintf('courses/%s/%s-%s', $term_slug, $year, $month);
  return home_url(user_trailingslashit($path));
}, 10, 2);

add_action('pre_get_posts', function ($query) {
  if (!is_admin() && $query->is_main_query()) {
    $course_type = $query->get('course_type');
    $month = $query->get('tre_course_month');

    if ($course_type && $month && $query->get('post_type') === TRE_COURSES_CPT) {
      $parts = explode('-', $month);
      if (count($parts) === 2) {
        $year = $parts[0];
        $mon = $parts[1];
        $month_map = [
          'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
          'may' => '05', 'jun' => '06', 'jul' => '07', 'aug' => '08',
          'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12',
        ];
        $month_num = $month_map[$mon] ?? '';
        if ($month_num) {
          $start = $year . '-' . $month_num . '-01';
          $end = $year . '-' . $month_num . '-31';
          $meta_query = [
            [
              'key' => 'start_date',
              'value' => [$start, $end],
              'compare' => 'BETWEEN',
              'type' => 'DATE',
            ],
          ];
          $tax_query = [
            [
              'taxonomy' => TRE_COURSES_TAX,
              'field' => 'slug',
              'terms' => $course_type,
            ],
          ];

          $match = get_posts([
            'post_type' => TRE_COURSES_CPT,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => $meta_query,
            'tax_query' => $tax_query,
          ]);

          if (!empty($match)) {
            $query->set('p', $match[0]);
            $query->set('post_type', TRE_COURSES_CPT);
          } else {
            $query->set('p', 0);
            $query->set('post_type', TRE_COURSES_CPT);
            $query->set('posts_per_page', 0);
          }
        }
      }
    }
  }
});
