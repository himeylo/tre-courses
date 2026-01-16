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

  if (!$term_slug) {
    echo '<div>Please select a course category.</div>';
    return;
  }

  $today = current_time('Y-m-d');

  $q = new WP_Query([
    'post_type'      => TRE_COURSES_CPT,
    'post_status'    => 'publish',
    'posts_per_page' => max(1, $limit),
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
        'taxonomy' => TRE_COURSES_TAX,
        'field'    => 'slug',
        'terms'    => $term_slug,
      ],
    ],
  ]);

  if (!$q->have_posts()) {
    echo '<div class="tre-courses-empty">No upcoming courses currently listed.</div>';
    return;
  }

  echo '<div class="tre-courses-list" data-category="' . esc_attr($term_slug) . '">';

  while ($q->have_posts()) {
    $q->the_post();

    $title = get_the_title();
    $url   = (string) get_field('external_url');

    $start = (string) get_field('start_date');
    $end   = (string) get_field('end_date');

    $city  = (string) get_field('city');
    $state = (string) get_field('state');
    $venue = (string) get_field('venue');
    $org   = (string) get_field('organizer');
    $cost  = (string) get_field('cost');

    $location_parts = array_filter([
      $venue,
      trim($city . (($city && $state) ? ', ' : '') . $state),
    ]);
    $location = implode(' — ', $location_parts);

    echo '<article class="tre-course">';
      echo '<header class="tre-course__title">';
        if ($url) {
          echo '<a class="tre-course__link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($title) . '</a>';
        } else {
          echo esc_html($title);
        }
      echo '</header>';

      echo '<div class="tre-course__meta">';

        $primary_range = tre_courses_format_date_range($start, $end);
        if ($primary_range) {
          echo '<div class="tre-course__dates"><strong>Dates:</strong> ' . esc_html($primary_range) . '</div>';
        }

        if (have_rows('occurrences')) {
          $items = [];
          while (have_rows('occurrences')) {
            the_row();
            $os = (string) get_sub_field('occ_start_date');
            $oe = (string) get_sub_field('occ_end_date');
            $r  = tre_courses_format_date_range($os, $oe);
            if ($r) $items[] = $r;
          }
          if (!empty($items)) {
            echo '<div class="tre-course__occurrences"><strong>Other dates:</strong><ul>';
            foreach ($items as $r) {
              echo '<li>' . esc_html($r) . '</li>';
            }
            echo '</ul></div>';
          }
        }

        if ($location) {
          echo '<div class="tre-course__location"><strong>Location:</strong> ' . esc_html($location) . '</div>';
        }
        if ($org) {
          echo '<div class="tre-course__org"><strong>Host:</strong> ' . esc_html($org) . '</div>';
        }
        if ($show_cost && $cost !== '') {
          echo '<div class="tre-course__cost"><strong>Cost:</strong> ' . esc_html($cost) . '</div>';
        }

      echo '</div>';

      if ($url) {
        echo '<div class="tre-course__cta"><a class="tre-course__button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">Details / Register</a></div>';
      }

    echo '</article>';
  }

  wp_reset_postdata();
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
