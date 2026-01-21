<?php
/**
 * Course Type taxonomy archive.
 *
 * @package tre-courses-block
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('tre_courses_format_date_range')) {
  /**
   * Format date ranges like:
   * - March 3–5, 2026
   * - March 28–April 2, 2026
   * - December 30, 2026–January 2, 2027
   */
  function tre_courses_format_date_range($start, $end) {
    if (!$start && !$end) {
      return '';
    }

    try {
      $s = $start ? new DateTime($start) : null;
      $e = $end ? new DateTime($end) : null;
    } catch (Exception $ex) {
      return trim((string) $start . (((string) $start && (string) $end) ? '–' : '') . (string) $end);
    }

    if ($s && !$e) {
      return $s->format('F j, Y');
    }
    if (!$s && $e) {
      return $e->format('F j, Y');
    }

    if ($s->format('Y-m') === $e->format('Y-m')) {
      return $s->format('F j') . '–' . $e->format('j, Y');
    }

    if ($s->format('Y') === $e->format('Y')) {
      return $s->format('F j') . '–' . $e->format('F j, Y');
    }

    return $s->format('F j, Y') . '–' . $e->format('F j, Y');
  }
}

get_header();

$term = get_queried_object();
$term_id = isset($term->term_id) ? (int) $term->term_id : 0;
$term_name = $term_id ? single_term_title('', false) : '';
$term_description = $term_id ? term_description($term_id, 'course_type') : '';
$term_image_id = $term_id ? (int) get_term_meta($term_id, 'thumbnail_id', true) : 0;
$term_image = $term_image_id ? wp_get_attachment_image($term_image_id, 'large', false, ['class' => 'tre-course-type__image']) : '';

$today = current_time('Y-m-d');
$courses = [];

if ($term_id) {
  $q = new WP_Query([
    'post_type'      => 'course',
    'post_status'    => 'publish',
    'posts_per_page' => 200,
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
        'taxonomy' => 'course_type',
        'field'    => 'term_id',
        'terms'    => $term_id,
      ],
    ],
  ]);

  while ($q->have_posts()) {
    $q->the_post();
    $post_id = get_the_ID();
    $start = function_exists('get_field') ? (string) get_field('start_date', $post_id) : (string) get_post_meta($post_id, 'start_date', true);
    $end = function_exists('get_field') ? (string) get_field('end_date', $post_id) : (string) get_post_meta($post_id, 'end_date', true);
    $city_state = function_exists('get_field') ? (string) get_field('city_state', $post_id) : (string) get_post_meta($post_id, 'city_state', true);
    $organizer = function_exists('get_field') ? (string) get_field('organizer', $post_id) : (string) get_post_meta($post_id, 'organizer', true);
    $external_url = function_exists('get_field') ? (string) get_field('external_url', $post_id) : (string) get_post_meta($post_id, 'external_url', true);

    if (!$start) {
      continue;
    }

    $month_key = (new DateTime($start))->format('Y-m');
    $courses[$month_key][] = [
      'start' => $start,
      'end' => $end,
      'city_state' => $city_state,
      'organizer' => $organizer,
      'url' => $external_url ?: get_permalink($post_id),
    ];
  }
  wp_reset_postdata();
}
?>

<main id="primary" class="site-main tre-course-type">
  <div class="tre-course-type__header">
    <?php if ($term_image) : ?>
      <div class="tre-course-type__image-wrap">
        <?php echo $term_image; ?>
      </div>
    <?php endif; ?>
    <div class="tre-course-type__intro">
      <?php if ($term_name) : ?>
        <h1 class="tre-course-type__title"><?php echo esc_html($term_name); ?></h1>
      <?php endif; ?>
      <?php if ($term_description) : ?>
        <div class="tre-course-type__description"><?php echo wp_kses_post($term_description); ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($courses)) : ?>
    <p class="tre-course-type__empty">No upcoming courses are available.</p>
  <?php else : ?>
    <?php foreach ($courses as $month_key => $items) : ?>
      <?php $month_label = (new DateTime($month_key . '-01'))->format('F Y'); ?>
      <section class="tre-course-type__group">
        <h2 class="tre-course-type__month"><?php echo esc_html($month_label); ?></h2>
        <ul class="tre-course-type__list">
          <?php foreach ($items as $item) : ?>
            <?php $date_label = tre_courses_format_date_range($item['start'], $item['end']); ?>
            <li class="tre-course-type__item">
              <?php if ($date_label) : ?>
                <a class="tre-course-type__date" href="<?php echo esc_url($item['url']); ?>" target="_blank" rel="noopener noreferrer">
                  <?php echo esc_html($date_label); ?>
                </a>
              <?php endif; ?>
              <?php if ($item['city_state']) : ?>
                <span class="tre-course-type__city"><?php echo esc_html($item['city_state']); ?></span>
              <?php endif; ?>
              <?php if ($item['organizer']) : ?>
                <span class="tre-course-type__host">Host: <?php echo esc_html($item['organizer']); ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php
get_footer();
