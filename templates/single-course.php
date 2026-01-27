<?php
/**
 * Single Course template.
 *
 * @package tre-courses-block
 */

if (!defined('ABSPATH')) {
  exit;
}

get_header();

while (have_posts()) :
  the_post();
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

  $show_title = tre_courses_get_setting('tre_courses_single_show_title', 1);
  $show_featured_image = tre_courses_get_setting('tre_courses_single_show_featured_image', 1);
  $show_date = tre_courses_get_setting('tre_courses_single_show_date', 1);
  $date_format = tre_courses_get_setting('tre_courses_single_date_format', 'smart');
  $show_venue = tre_courses_get_setting('tre_courses_single_show_venue', 1);
  $show_city_state = tre_courses_get_setting('tre_courses_single_show_city_state', 1);
  $show_map_link = tre_courses_get_setting('tre_courses_single_show_map_link', 1);
  $show_organizer = tre_courses_get_setting('tre_courses_single_show_organizer', 1);
  $host_label = tre_courses_get_setting('tre_courses_single_host_label', 'Host');
  $show_cost = tre_courses_get_setting('tre_courses_single_show_cost', 1);
  $show_cta = tre_courses_get_setting('tre_courses_single_show_cta', 1);
  $cta_text = tre_courses_get_setting('tre_courses_single_cta_text', 'Details / Register');
  $open_new_tab = tre_courses_get_setting('tre_courses_single_open_new_tab', 1);
  $show_content = tre_courses_get_setting('tre_courses_single_show_content', 1);
  $map_url = $show_map_link ? tre_courses_build_map_link($address, $city_state) : '';
  $date_label = tre_courses_format_date_range($start, $end, $date_format);
  ?>

  <?php if (isset($_GET['tre_debug'])) : ?>
    <div style="margin:1rem 0;padding:.5rem 1rem;border:1px dashed currentColor;font-size:0.9rem;">
      Template: Single Course (single-course.php)
    </div>
  <?php endif; ?>

  <main id="primary" class="site-main tre-course-single">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <?php if ($show_title) : ?>
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>
      <?php endif; ?>

      <?php if ($show_featured_image && has_post_thumbnail()) : ?>
        <div class="tre-course-single__image">
          <?php the_post_thumbnail('large'); ?>
        </div>
      <?php endif; ?>

      <div class="tre-course-single__meta">
        <?php if ($show_date && $date_label) : ?>
          <div class="tre-course-single__date"><strong>Date:</strong> <?php echo esc_html($date_label); ?></div>
        <?php endif; ?>
        <?php if ($show_venue && $venue) : ?>
          <div class="tre-course-single__venue"><strong>Class Location:</strong> <?php echo esc_html($venue); ?></div>
        <?php endif; ?>
        <?php if ($show_city_state && $city_state) : ?>
          <div class="tre-course-single__city">
            <strong>Location:</strong> <?php echo esc_html($city_state); ?>
            <?php if ($map_url) : ?>
              <span class="tre-course__map"> (<a href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">map</a>)</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if ($show_organizer && $organizer) : ?>
          <div class="tre-course-single__host">
            <strong><?php echo esc_html($host_label); ?>:</strong>
            <?php if ($organizer_link) : ?>
              <a href="<?php echo esc_url($organizer_link); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($organizer); ?></a>
            <?php else : ?>
              <?php echo esc_html($organizer); ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if ($show_cost && $cost) : ?>
          <div class="tre-course-single__cost"><strong>Cost:</strong> <?php echo esc_html($cost); ?></div>
        <?php endif; ?>
      </div>

      <?php if ($show_cta && $external_url) : ?>
        <div class="tre-course__cta">
          <a class="tre-course__button" href="<?php echo esc_url($external_url); ?>"<?php echo $open_new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php echo esc_html($cta_text); ?>
          </a>
        </div>
      <?php endif; ?>

      <?php if ($show_content) : ?>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>
    </article>
  </main>
<?php
endwhile;

get_footer();
