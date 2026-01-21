<?php
if (!defined('ABSPATH')) exit;

/**
 * Register ACF local field groups (requires ACF Pro).
 */
add_action('acf/init', function () {
  if (!function_exists('acf_add_local_field_group')) return;

  // Course Fields
  acf_add_local_field_group([
    'key' => 'group_tre_course_fields',
    'title' => 'Course Fields',
    'fields' => [
      [
        'key' => 'field_tre_start_date',
        'label' => 'Start Date',
        'name' => 'start_date',
        'type' => 'date_picker',
        'return_format' => 'Y-m-d',
        'display_format' => 'm/d/Y',
        'first_day' => 0,
        'wrapper' => [
          'width' => '50',
        ],
      ],
      [
        'key' => 'field_tre_end_date',
        'label' => 'End Date',
        'name' => 'end_date',
        'type' => 'date_picker',
        'return_format' => 'Y-m-d',
        'display_format' => 'm/d/Y',
        'first_day' => 0,
        'wrapper' => [
          'width' => '50',
        ],
      ],
      [
        'key' => 'field_tre_venue',
        'label' => 'Class Location',
        'name' => 'venue',
        'type' => 'text',
        'instructions' => 'Optional (ex: “ABC Training Center”).',
      ],
      [
        'key' => 'field_tre_city_state',
        'label' => 'City, State',
        'name' => 'city_state',
        'type' => 'text',
        'instructions' => 'Example: Austin, TX',
      ],
      [
        'key' => 'field_tre_address',
        'label' => 'Address',
        'name' => 'address',
        'type' => 'text',
        'instructions' => 'Street address used for map link.',
      ],
      [
        'key' => 'field_tre_cost',
        'label' => 'Cost',
        'name' => 'cost',
        'type' => 'text',
        'instructions' => 'Optional (ex: “$295” or “Free”).',
      ],
      [
        'key' => 'field_tre_organizer',
        'label' => 'Host / Organizer',
        'name' => 'organizer',
        'type' => 'text',
      ],
      [
        'key' => 'field_tre_external_url',
        'label' => 'External URL',
        'name' => 'external_url',
        'type' => 'url',
        'instructions' => 'Where users go for full details / registration.',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'post_type',
          'operator' => '==',
          'value' => TRE_COURSES_CPT,
        ],
      ],
    ],
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);

  // Block Settings
  acf_add_local_field_group([
    'key' => 'group_tre_courses_block_settings',
    'title' => 'TRE Courses List Block Settings',
    'fields' => [
      [
        'key' => 'field_tre_block_category_term',
        'label' => 'Course Category',
        'name' => 'category_term',
        'type' => 'taxonomy',
        'taxonomy' => 'category',
        'field_type' => 'select',
        'allow_null' => 0,
        'add_term' => 0,
        'save_terms' => 0,
        'load_terms' => 0,
        'return_format' => 'id',
        'multiple' => 0,
      ],
      [
        'key' => 'field_tre_block_limit',
        'label' => 'Max items to show',
        'name' => 'limit',
        'type' => 'number',
        'default_value' => 12,
        'min' => 1,
        'step' => 1,
      ],
      [
        'key' => 'field_tre_block_show_cost',
        'label' => 'Show cost',
        'name' => 'show_cost',
        'type' => 'true_false',
        'ui' => 1,
        'default_value' => 1,
      ],
      [
        'key' => 'field_tre_block_show_venue',
        'label' => 'Show venue',
        'name' => 'show_venue',
        'type' => 'true_false',
        'ui' => 1,
        'default_value' => 1,
      ],
      [
        'key' => 'field_tre_block_show_organizer',
        'label' => 'Show organizer',
        'name' => 'show_organizer',
        'type' => 'true_false',
        'ui' => 1,
        'default_value' => 1,
      ],
      [
        'key' => 'field_tre_block_cta_text',
        'label' => 'CTA button text',
        'name' => 'cta_text',
        'type' => 'text',
        'instructions' => 'Optional. Defaults to "Details / Register".',
      ],
      [
        'key' => 'field_tre_block_show_featured_image',
        'label' => 'Show featured image',
        'name' => 'show_featured_image',
        'type' => 'true_false',
        'ui' => 1,
        'default_value' => 0,
      ],
    ],
    'location' => [
      [
        [
          'param' => 'block',
          'operator' => '==',
          'value' => 'acf/tre-courses-list',
        ],
      ],
    ],
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
});
