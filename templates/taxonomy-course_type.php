<?php
/**
 * Course Type taxonomy archive.
 *
 * @package tre-courses-block
 */

if (!defined('ABSPATH')) {
  exit;
}

get_header();

$term = get_queried_object();
$term_id = isset($term->term_id) ? (int) $term->term_id : 0;
$term_name = $term_id ? single_term_title('', false) : '';
$term_description = $term_id ? term_description($term_id, 'course_type') : '';
$term_image_id = $term_id ? (int) get_term_meta($term_id, 'thumbnail_id', true) : 0;
$term_image = $term_image_id ? wp_get_attachment_image($term_image_id, 'large', false, ['class' => 'tre-course-type__image']) : '';

$show_term_image = tre_courses_get_setting('tre_courses_archive_show_term_image', 1);
$show_term_title = tre_courses_get_setting('tre_courses_archive_show_term_title', 1);
$show_term_description = tre_courses_get_setting('tre_courses_archive_show_term_description', 1);
$group_by_month = tre_courses_get_setting('tre_courses_archive_group_by_month', 1);
$show_date = tre_courses_get_setting('tre_courses_archive_show_date', 1);
$show_city_state = tre_courses_get_setting('tre_courses_archive_show_city_state', 1);
$show_venue = tre_courses_get_setting('tre_courses_archive_show_venue', 0);
$show_map_link = tre_courses_get_setting('tre_courses_archive_show_map_link', 0);
$show_organizer = tre_courses_get_setting('tre_courses_archive_show_organizer', 1);
$date_format = tre_courses_get_setting('tre_courses_archive_date_format', 'smart');
$link_date = tre_courses_get_setting('tre_courses_archive_link_date', 1);
$open_new_tab = tre_courses_get_setting('tre_courses_archive_open_new_tab', 1);
$host_label = tre_courses_get_setting('tre_courses_archive_host_label', 'Host');

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
    $venue = function_exists('get_field') ? (string) get_field('venue', $post_id) : (string) get_post_meta($post_id, 'venue', true);
    $city_state = function_exists('get_field') ? (string) get_field('city_state', $post_id) : (string) get_post_meta($post_id, 'city_state', true);
    $address = function_exists('get_field') ? (string) get_field('address', $post_id) : (string) get_post_meta($post_id, 'address', true);
    $cost = function_exists('get_field') ? (string) get_field('cost', $post_id) : (string) get_post_meta($post_id, 'cost', true);
    $organizer = function_exists('get_field') ? (string) get_field('organizer', $post_id) : (string) get_post_meta($post_id, 'organizer', true);
    $organizer_link = function_exists('get_field') ? (string) get_field('organizer_link', $post_id) : (string) get_post_meta($post_id, 'organizer_link', true);
    $external_url = function_exists('get_field') ? (string) get_field('external_url', $post_id) : (string) get_post_meta($post_id, 'external_url', true);

    if (!$start) {
      continue;
    }

    $month_key = (new DateTime($start))->format('Y-m');
    $courses[$month_key][] = [
      'start' => $start,
      'end' => $end,
      'venue' => $venue,
      'city_state' => $city_state,
      'address' => $address,
      'cost' => $cost,
      'organizer' => $organizer,
      'organizer_link' => $organizer_link,
      'url' => $external_url ?: get_permalink($post_id),
    ];
  }
  wp_reset_postdata();
}
?>

<?php if (isset($_GET['tre_debug'])) : ?>
  <div style="margin:1rem 0;padding:.5rem 1rem;border:1px dashed currentColor;font-size:0.9rem;">
    Template: Course Type Archive (taxonomy-course_type.php)
  </div>
<?php endif; ?>

<main id="primary" class="site-main tre-course-type">
  <div class="tre-course-type__header">
    <?php if ($show_term_image && $term_image) : ?>
      <div class="tre-course-type__image-wrap">
        <?php echo $term_image; ?>
      </div>
    <?php endif; ?>
    <div class="tre-course-type__intro">
      <?php if ($show_term_title && $term_name) : ?>
        <h1 class="tre-course-type__title"><?php echo esc_html($term_name); ?></h1>
      <?php endif; ?>
      <?php if ($show_term_description && $term_description) : ?>
        <div class="tre-course-type__description"><?php echo wp_kses_post($term_description); ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (empty($courses)) : ?>
    <p class="tre-course-type__empty">No upcoming courses are available.</p>
  <?php else : ?>
    <?php if ($group_by_month) : ?>
      <?php foreach ($courses as $month_key => $items) : ?>
        <?php $month_label = (new DateTime($month_key . '-01'))->format('F Y'); ?>
        <section class="tre-course-type__group">
          <h2 class="tre-course-type__month"><?php echo esc_html($month_label); ?></h2>
          <ul class="tre-course-type__list">
            <?php foreach ($items as $item) : ?>
              <?php $date_label = tre_courses_format_date_range($item['start'], $item['end'], $date_format); ?>
              <?php $map_url = $show_map_link ? tre_courses_build_map_link($item['address'], $item['city_state']) : ''; ?>
              <li class="tre-course-type__item">
                <?php if ($show_date && $date_label) : ?>
                  <?php if ($link_date) : ?>
                    <a class="tre-course-type__date" href="<?php echo esc_url($item['url']); ?>"<?php echo $open_new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                      <?php echo esc_html($date_label); ?>
                    </a>
                  <?php else : ?>
                    <span class="tre-course-type__date"><?php echo esc_html($date_label); ?></span>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($show_venue && $item['venue']) : ?>
                  <span class="tre-course-type__venue"><?php echo esc_html($item['venue']); ?></span>
                <?php endif; ?>
                <?php if ($show_city_state && $item['city_state']) : ?>
                  <span class="tre-course-type__city">
                    <?php echo esc_html($item['city_state']); ?>
                    <?php if ($map_url) : ?>
                      <span class="tre-course__map"> (<a href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">map</a>)</span>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
                <?php if ($item['cost'] !== '') : ?>
                  <span class="tre-course-type__cost"><?php echo esc_html($item['cost']); ?></span>
                <?php endif; ?>
                <?php if ($show_organizer && $item['organizer']) : ?>
                  <span class="tre-course-type__host">
                    <?php echo esc_html($host_label); ?>:
                    <?php if ($item['organizer_link']) : ?>
                      <a href="<?php echo esc_url($item['organizer_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($item['organizer']); ?></a>
                    <?php else : ?>
                      <?php echo esc_html($item['organizer']); ?>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endforeach; ?>
    <?php else : ?>
      <section class="tre-course-type__group">
        <ul class="tre-course-type__list">
          <?php foreach ($courses as $items) : ?>
            <?php foreach ($items as $item) : ?>
              <?php $date_label = tre_courses_format_date_range($item['start'], $item['end'], $date_format); ?>
              <?php $map_url = $show_map_link ? tre_courses_build_map_link($item['address'], $item['city_state']) : ''; ?>
              <li class="tre-course-type__item">
                <?php if ($show_date && $date_label) : ?>
                  <?php if ($link_date) : ?>
                    <a class="tre-course-type__date" href="<?php echo esc_url($item['url']); ?>"<?php echo $open_new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                      <?php echo esc_html($date_label); ?>
                    </a>
                  <?php else : ?>
                    <span class="tre-course-type__date"><?php echo esc_html($date_label); ?></span>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($show_venue && $item['venue']) : ?>
                  <span class="tre-course-type__venue"><?php echo esc_html($item['venue']); ?></span>
                <?php endif; ?>
                <?php if ($show_city_state && $item['city_state']) : ?>
                  <span class="tre-course-type__city">
                    <?php echo esc_html($item['city_state']); ?>
                    <?php if ($map_url) : ?>
                      <span class="tre-course__map"> (<a href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">map</a>)</span>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
                <?php if ($item['cost'] !== '') : ?>
                  <span class="tre-course-type__cost"><?php echo esc_html($item['cost']); ?></span>
                <?php endif; ?>
                <?php if ($show_organizer && $item['organizer']) : ?>
                  <span class="tre-course-type__host">
                    <?php echo esc_html($host_label); ?>:
                    <?php if ($item['organizer_link']) : ?>
                      <a href="<?php echo esc_url($item['organizer_link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($item['organizer']); ?></a>
                    <?php else : ?>
                      <?php echo esc_html($item['organizer']); ?>
                    <?php endif; ?>
                  </span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php
get_footer();
