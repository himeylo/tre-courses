<?php
if (!defined('ABSPATH')) exit;

/**
 * Format date ranges like:
 * - March 3–5, 2026
 * - March 28–April 2, 2026
 * - December 30, 2026–January 2, 2027
 */
function tre_courses_format_date_range($start, $end) {
  if (!$start && !$end) return '';

  try {
    $s = $start ? new DateTime($start) : null;
    $e = $end   ? new DateTime($end)   : null;
  } catch (Exception $ex) {
    return trim((string)$start . (((string)$start && (string)$end) ? '–' : '') . (string)$end);
  }

  if ($s && !$e) return $s->format('F j, Y');
  if (!$s && $e) return $e->format('F j, Y');

  if ($s->format('Y-m') === $e->format('Y-m')) {
    // Same month + year
    return $s->format('F j') . '–' . $e->format('j, Y');
  }

  if ($s->format('Y') === $e->format('Y')) {
    // Same year
    return $s->format('F j') . '–' . $e->format('F j, Y');
  }

  // Different years
  return $s->format('F j, Y') . '–' . $e->format('F j, Y');
}

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

  $term_slug = (string) get_field('category_slug');
  $limit     = (int) (get_field('limit') ?: 12);
  $show_cost = (bool) get_field('show_cost');
  $show_venue = (bool) get_field('show_venue');
  $show_organizer = (bool) get_field('show_organizer');
  $cta_text = trim((string) get_field('cta_text'));
  $show_featured_image = (bool) get_field('show_featured_image');

  if (!$term_slug) {
    echo '<div>Please select a course category.</div>';
    return;
  }

  $today = current_time('Y-m-d');

  $q = new WP_Query([
    'post_type'      => TRE_COURSES_CPT,
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'tax_query'      => [
      [
        'taxonomy' => TRE_COURSES_TAX,
        'field'    => 'slug',
        'terms'    => $term_slug,
      ],
    ],
  ]);

  $courses = [];

  while ($q->have_posts()) {
    $q->the_post();
    $post_id = get_the_ID();

    $start = (string) get_field('start_date', $post_id);
    $end   = (string) get_field('end_date', $post_id);

    $primary_sort = tre_courses_upcoming_sort_key($start, $end, $today);
    $primary_upcoming = ($primary_sort !== null);

    $occurrences = [];
    $occurrence_sorts = [];

    if (have_rows('occurrences', $post_id)) {
      while (have_rows('occurrences', $post_id)) {
        the_row();
        $os = (string) get_sub_field('occ_start_date');
        $oe = (string) get_sub_field('occ_end_date');
        $sort_key = tre_courses_upcoming_sort_key($os, $oe, $today);
        if ($sort_key !== null) {
          $occurrences[] = [
            'start' => $os,
            'end'   => $oe,
          ];
          $occurrence_sorts[] = $sort_key;
        }
      }
    }

    if (!$primary_upcoming && empty($occurrence_sorts)) {
      continue;
    }

    $sort_candidates = [];
    if ($primary_sort !== null) $sort_candidates[] = $primary_sort;
    if (!empty($occurrence_sorts)) $sort_candidates = array_merge($sort_candidates, $occurrence_sorts);

    $external_url = (string) get_field('external_url', $post_id);
    $course_url = $external_url ?: get_permalink($post_id);

    $courses[] = [
      'sort_key' => min($sort_candidates),
      'id'       => $post_id,
      'title'    => get_the_title(),
      'url'      => $course_url,
      'start'    => $start,
      'end'      => $end,
      'city'     => (string) get_field('city', $post_id),
      'state'    => (string) get_field('state', $post_id),
      'venue'    => (string) get_field('venue', $post_id),
      'org'      => (string) get_field('organizer', $post_id),
      'cost'     => (string) get_field('cost', $post_id),
      'occurrences' => $occurrences,
    ];
  }

  wp_reset_postdata();

  if (empty($courses)) {
    echo '<div class="tre-courses-empty">No upcoming courses currently listed.</div>';
    return;
  }

  usort($courses, function ($a, $b) {
    if ($a['sort_key'] === $b['sort_key']) {
      return strcasecmp($a['title'], $b['title']);
    }
    return $a['sort_key'] <=> $b['sort_key'];
  });

  $courses = array_slice($courses, 0, max(1, $limit));
  $cta_label = $cta_text !== '' ? $cta_text : 'Details / Register';

  echo '<div class="tre-courses-list" data-category="' . esc_attr($term_slug) . '">';

  foreach ($courses as $course) {
    $location_parts = array_filter([
      $show_venue ? $course['venue'] : '',
      trim($course['city'] . (($course['city'] && $course['state']) ? ', ' : '') . $course['state']),
    ]);
    $location = implode(' — ', $location_parts);
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
      echo '<header class="tre-course__header">';
        echo '<div class="tre-course__title">';
          if ($course['url']) {
            echo '<a class="tre-course__link" href="' . esc_url($course['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($course['title']) . '</a>';
          } else {
            echo esc_html($course['title']);
          }
        echo '</div>';
        if ($thumb) {
          echo '<div class="tre-course__thumb-wrap">' . $thumb . '</div>';
        }
      echo '</header>';

      echo '<div class="tre-course__meta">';

        // $primary_range = tre_courses_format_date_range($course['start'], $course['end']);
        // if ($primary_range) {
          echo '<div class="tre-course__dates"><strong>Dates:</strong></div>';
        // }

        $items = [];
        $seen_keys = [];
        if (tre_courses_upcoming_sort_key($course['start'], $course['end'], $today) !== null) {
          $primary_item = tre_courses_format_date_range($course['start'], $course['end']);
          $primary_key = tre_courses_normalize_date($course['start']) . '|' . tre_courses_normalize_date($course['end']);
          if ($primary_item && !isset($seen_keys[$primary_key])) {
            $items[] = $primary_item;
            $seen_keys[$primary_key] = true;
          }
        }
        if (!empty($course['occurrences'])) {
          foreach ($course['occurrences'] as $occ) {
            $r = tre_courses_format_date_range($occ['start'], $occ['end']);
            $occ_key = tre_courses_normalize_date($occ['start']) . '|' . tre_courses_normalize_date($occ['end']);
            if ($r && !isset($seen_keys[$occ_key])) {
              $items[] = $r;
              $seen_keys[$occ_key] = true;
            }
          }
        }
        if (!empty($items)) {
          echo '<div class="tre-course__occurrences"><ul>';
          foreach ($items as $r) {
            echo '<li>' . esc_html($r) . '</li>';
          }
          echo '</ul></div>';
        }

        if ($location) {
          echo '<div class="tre-course__location"><strong>Location:</strong> ' . esc_html($location) . '</div>';
        }
        if ($show_organizer && $course['org']) {
          echo '<div class="tre-course__org"><strong>Host:</strong> ' . esc_html($course['org']) . '</div>';
        }
        if ($show_cost && $course['cost'] !== '') {
          echo '<div class="tre-course__cost"><strong>Cost:</strong> ' . esc_html($course['cost']) . '</div>';
        }

      echo '</div>';

      if ($course['url']) {
        echo '<div class="tre-course__cta"><a class="tre-course__button" href="' . esc_url($course['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($cta_label) . '</a></div>';
      }

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
