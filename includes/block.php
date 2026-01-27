<?php
if (!defined('ABSPATH')) exit;

function tre_courses_normalize_date($date) {
  if (!$date) return '';

  try {
    $dt = new DateTime($date);
    return $dt->format('Y-m-d');
  } catch (Exception $ex) {
    return '';
  }
}

function tre_courses_date_to_timestamp($date) {
  if (!$date) return null;
  try {
    $dt = new DateTime($date);
    return $dt->getTimestamp();
  } catch (Exception $ex) {
    return null;
  }
}

function tre_courses_upcoming_sort_key($start, $end, $today) {
  $start = tre_courses_normalize_date($start);
  $end   = tre_courses_normalize_date($end ?: $start);

  if (!$end || $end < $today) return null;

  $sort_date = $start ?: $end;
  if ($sort_date < $today) {
    $sort_date = $today;
  }

  return tre_courses_date_to_timestamp($sort_date);
}

function tre_courses_badge_date($start, $end) {
  $start = tre_courses_normalize_date($start);
  $end   = tre_courses_normalize_date($end ?: $start);

  return $start ?: $end;
}

// Register the ACF block.
add_action('acf/init', function () {
  if (!function_exists('acf_register_block_type')) return;

  acf_register_block_type([
    'name'            => 'tre-courses-list',
    'title'           => 'TRE Courses List',
    'description'     => 'Displays upcoming courses filtered by course category.',
    'render_callback' => 'tre_courses_render_courses_list_block',
    'category'        => 'widgets',
    'icon'            => 'calendar',
    'keywords'        => ['courses', 'events', 'training'],
    'supports'        => [
      'align' => false,
      'mode'  => true,
      'jsx'   => false,
    ],
  ]);
});

function tre_courses_render_courses_list_block($block, $content = '', $is_preview = false, $post_id = 0) {
  if (!function_exists('get_field')) {
    echo '<div><strong>TRE Courses:</strong> ACF is required for this block.</div>';
    return;
  }

  $term_id = (int) get_field('category_term');
  $limit     = (int) (get_field('limit') ?: 12);
  $show_cost = (bool) get_field('show_cost');
  $show_venue = (bool) get_field('show_venue');
  $show_organizer = (bool) get_field('show_organizer');
  $cta_text = trim((string) get_field('cta_text'));
  $show_featured_image = (bool) get_field('show_featured_image');

  if (!$term_id) {
    echo '<div>Please select a course category.</div>';
    return;
  }

  $today = current_time('Y-m-d');

  $q = new WP_Query([
    'post_type'      => TRE_COURSES_CPT,
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'meta_key'       => 'start_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
      [
        'key'     => 'end_date',
        'value'   => $today,
        'compare' => '>=',
        'type'    => 'DATE',
      ],
    ],
    'tax_query'      => [
      [
        'taxonomy' => 'category',
        'field'    => 'term_id',
        'terms'    => $term_id,
      ],
    ],
  ]);

  $courses = [];

  while ($q->have_posts()) {
    $q->the_post();
    $post_id = get_the_ID();

    $start = (string) get_field('start_date', $post_id);
    $end   = (string) get_field('end_date', $post_id);
    $sort_key = tre_courses_upcoming_sort_key($start, $end, $today);

    if ($sort_key === null) {
      continue;
    }

    $external_url = (string) get_field('external_url', $post_id);
    $course_url = $external_url ?: get_permalink($post_id);

    $course_type_names = wp_get_post_terms($post_id, TRE_COURSES_TAX, ['fields' => 'names']);
    $type_name = '';
    if (!is_wp_error($course_type_names) && !empty($course_type_names)) {
      $type_name = implode(', ', $course_type_names);
    }

    $courses[] = [
      'sort_key' => $sort_key,
      'id'       => $post_id,
      'title'    => get_the_title(),
      'type_name' => $type_name,
      'url'      => $course_url,
      'start'    => $start,
      'end'      => $end,
      'venue'    => (string) get_field('venue', $post_id),
      'city_state' => (string) get_field('city_state', $post_id),
      'address'  => (string) get_field('address', $post_id),
      'org'      => (string) get_field('organizer', $post_id),
      'org_link' => (string) get_field('organizer_link', $post_id),
      'cost'     => (string) get_field('cost', $post_id),
    ];
  }

  wp_reset_postdata();

  if (empty($courses)) {
    echo '<div class="tre-courses-empty">No upcoming courses currently listed.</div>';
    return;
  }

  usort($courses, function ($a, $b) {
    if ($a['sort_key'] === $b['sort_key']) {
      $a_label = $a['type_name'] !== '' ? $a['type_name'] : $a['title'];
      $b_label = $b['type_name'] !== '' ? $b['type_name'] : $b['title'];
      return strcasecmp($a_label, $b_label);
    }
    return $a['sort_key'] <=> $b['sort_key'];
  });

  $courses = array_slice($courses, 0, max(1, $limit));

  echo '<div class="tre-courses-list" data-category="' . esc_attr((string) $term_id) . '">';

  foreach ($courses as $course) {
    $thumb = '';
    if ($show_featured_image && has_post_thumbnail($course['id'])) {
      $thumb = get_the_post_thumbnail(
        $course['id'],
        'medium',
        [
          'class' => 'tre-course__thumb',
          'loading' => 'lazy',
        ]
      );
    }

    echo '<article class="tre-course">';
      echo '<div class="tre-course__card-wrap">';
        echo '<div class="tre-course__card">';
          echo '<div class="tre-course__content">';
            echo '<header class="tre-course__header">';
              echo '<div class="tre-course__title">';
                if ($course['url']) {
                  $label = $course['type_name'] !== '' ? $course['type_name'] : $course['title'];
                  echo '<a class="tre-course__link" href="' . esc_url($course['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
                } else {
                  $label = $course['type_name'] !== '' ? $course['type_name'] : $course['title'];
                  echo esc_html($label);
                }
              echo '</div>';
            echo '</header>';

            echo '<div class="tre-course__meta">';

        $date_range = tre_courses_format_date_range($course['start'], $course['end']);
        if ($date_range) {
          echo '<div class="tre-course__dates">' . esc_html($date_range) . '</div>';
        }

        $location_label = trim((string) $course['city_state']);
        $map_parts = array_filter([
          $show_venue ? $course['venue'] : '',
          $course['address'],
          $location_label,
        ]);
        $location_query = implode(', ', $map_parts);
        $location_url = $location_query ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($location_query) : '';
        if ($location_label) {
          echo '<div class="tre-course__location">' . esc_html($location_label);
          if ($location_url) {
            echo ' <span class="tre-course__map">(<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">map</a>)</span>';
          }
          echo '</div>';
        }
        if ($show_organizer && $course['org']) {
          $org_label = esc_html($course['org']);
          if ($course['org_link']) {
            $org_label = '<a href="' . esc_url($course['org_link']) . '" target="_blank" rel="noopener noreferrer">' . $org_label . '</a>';
          }
          echo '<div class="tre-course__org">' . $org_label . ' is the training provider</div>';
        }
        if ($show_cost && $course['cost'] !== '') {
          echo '<div class="tre-course__cost">' . esc_html($course['cost']) . '</div>';
        }
            echo '</div>';

          echo '</div>';

          if ($thumb) {
            echo '<div class="tre-course__thumb-wrap">' . $thumb . '</div>';
          }
        echo '</div>';

        echo '<span class="tre-course__chevron" aria-hidden="true"></span>';

      echo '</div>';
    echo '</article>';
  }

  echo '</div>';
}

// Admin notice if ACF not active.
add_action('admin_notices', function () {
  if (function_exists('acf_register_block_type')) return;
  if (!current_user_can('activate_plugins')) return;

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if ($screen && strpos((string)$screen->id, 'plugins') === false) {
    // Keep it quiet outside Plugins screen.
    return;
  }

  echo '<div class="notice notice-warning"><p><strong>TRE Courses:</strong> This plugin expects ACF Pro to be active for fields + block UI. CPT/taxonomy will still work.</p></div>';
});
