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

function tre_courses_next_start_date_from_occurrences($occurrences, $today) {
  $next = '';

  foreach ($occurrences as $occ) {
    $os = tre_courses_normalize_date($occ['start']);
    $oe = tre_courses_normalize_date($occ['end'] ?: $os);
    if ($oe && $oe >= $today) {
      $candidate = $os ?: $oe;
      if ($candidate && ($next === '' || $candidate < $next)) {
        $next = $candidate;
      }
    }
  }

  return $next;
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

    $occurrences = [];
    $occurrence_sorts = [];

    if (have_rows('occurrences', $post_id)) {
      while (have_rows('occurrences', $post_id)) {
        the_row();
        $os = (string) get_sub_field('occ_start_date');
        $oe = (string) get_sub_field('occ_end_date');
        $ov = (string) get_sub_field('occ_venue');
        $ocs = (string) get_sub_field('occ_city_state');
        $oa = (string) get_sub_field('occ_address');
        $ocost = (string) get_sub_field('occ_cost');
        $sort_key = tre_courses_upcoming_sort_key($os, $oe, $today);
        if ($sort_key !== null) {
          $occurrences[] = [
            'start' => $os,
            'end'   => $oe,
            'venue' => $ov,
            'city_state' => $ocs,
            'address' => $oa,
            'cost'  => $ocost,
            'sort_key' => $sort_key,
          ];
          $occurrence_sorts[] = $sort_key;
        }
      }
    }

    if (empty($occurrence_sorts)) {
      continue;
    }

    usort($occurrences, function ($a, $b) {
      if ($a['sort_key'] === $b['sort_key']) {
        return strcasecmp((string) $a['start'], (string) $b['start']);
      }
      return $a['sort_key'] <=> $b['sort_key'];
    });

    $external_url = (string) get_field('external_url', $post_id);
    $course_url = $external_url ?: get_permalink($post_id);

    $courses[] = [
      'sort_key' => min($occurrence_sorts),
      'id'       => $post_id,
      'title'    => get_the_title(),
      'url'      => $course_url,
      'org'      => (string) get_field('organizer', $post_id),
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

    $badge_month = '';
    $badge_day = '';
    $badge_date = tre_courses_next_start_date_from_occurrences($course['occurrences'], $today);
    if ($badge_date) {
      try {
        $badge_dt = new DateTime($badge_date);
        $badge_month = strtoupper($badge_dt->format('M'));
        $badge_day = $badge_dt->format('j');
      } catch (Exception $ex) {
        $badge_month = '';
        $badge_day = '';
      }
    }

    echo '<article class="tre-course">';
      if ($badge_month && $badge_day) {
        echo '<div class="tre-course__badge" aria-hidden="true">';
          echo '<span class="tre-course__badge-month">' . esc_html($badge_month) . '</span>';
          echo '<span class="tre-course__badge-day">' . esc_html($badge_day) . '</span>';
        echo '</div>';
      }
      echo '<div class="tre-course__card">';
        echo '<div class="tre-course__content">';
          echo '<header class="tre-course__header">';
            echo '<div class="tre-course__title">';
              if ($course['url']) {
                echo '<a class="tre-course__link" href="' . esc_url($course['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($course['title']) . '</a>';
              } else {
                echo esc_html($course['title']);
              }
            echo '</div>';
          echo '</header>';

          echo '<div class="tre-course__meta">';

        if (!empty($course['occurrences'])) {
          $seen_keys = [];
          echo '<div class="tre-course__occurrences"><strong>Dates:</strong><ul>';
          foreach ($course['occurrences'] as $occ) {
            $r = tre_courses_format_date_range($occ['start'], $occ['end']);
            if (!$r) continue;

            $occ_key = implode('|', [
              tre_courses_normalize_date($occ['start']),
              tre_courses_normalize_date($occ['end']),
              (string) $occ['city_state'],
              (string) $occ['address'],
              (string) $occ['venue'],
              (string) $occ['cost'],
            ]);
            if (isset($seen_keys[$occ_key])) continue;
            $seen_keys[$occ_key] = true;

            $location_label = trim((string) $occ['city_state']);
            $map_parts = array_filter([
              $show_venue ? $occ['venue'] : '',
              $occ['address'],
              $location_label,
            ]);
            $location_query = implode(', ', $map_parts);
            $location_url = $location_query ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($location_query) : '';

            $line = esc_html($r);
            if ($location_label) {
              $line .= ' — ' . esc_html($location_label);
              if ($location_url) {
                $line .= ' <span class="tre-course__map">(<a href="' . esc_url($location_url) . '" target="_blank" rel="noopener noreferrer">map</a>)</span>';
              }
            }
            if ($show_cost && $occ['cost'] !== '') {
              $line .= ' — ' . esc_html($occ['cost']);
            }

            echo '<li>' . $line . '</li>';
          }
          echo '</ul></div>';
        }
        if ($show_organizer && $course['org']) {
          echo '<div class="tre-course__org"><strong>Host:</strong> ' . esc_html($course['org']) . '</div>';
        }
          echo '</div>';

          if ($course['url']) {
            echo '<div class="tre-course__cta"><a class="tre-course__button" href="' . esc_url($course['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($cta_label) . '</a></div>';
          }
        echo '</div>';

        if ($thumb) {
          echo '<div class="tre-course__thumb-wrap">' . $thumb . '</div>';
        }

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
