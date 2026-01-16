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
        'label' => 'Primary Start Date',
        'name' => 'start_date',
        'type' => 'date_picker',
        'return_format' => 'Y-m-d',
        'display_format' => 'F j, Y',
        'first_day' => 0,
      ],
      [
        'key' => 'field_tre_end_date',
        'label' => 'Primary End Date',
        'name' => 'end_date',
        'type' => 'date_picker',
        'return_format' => 'Y-m-d',
        'display_format' => 'F j, Y',
        'first_day' => 0,
      ],
      [
        'key' => 'field_tre_city',
        'label' => 'City',
        'name' => 'city',
        'type' => 'text',
      ],
      [
        'key' => 'field_tre_state',
        'label' => 'State',
        'name' => 'state',
        'type' => 'text',
        'instructions' => 'Example: TX',
      ],
      [
        'key' => 'field_tre_venue',
        'label' => 'Venue / Business Name',
        'name' => 'venue',
        'type' => 'text',
        'instructions' => 'Optional (ex: “ABC Training Center”).',
      ],
      [
        'key' => 'field_tre_organizer',
        'label' => 'Host / Organizer',
        'name' => 'organizer',
        'type' => 'text',
      ],
      [
        'key' => 'field_tre_cost',
        'label' => 'Cost',
        'name' => 'cost',
        'type' => 'text',
        'instructions' => 'Optional (ex: “$295” or “Free”).',
      ],
      [
        'key' => 'field_tre_external_url',
        'label' => 'External URL',
        'name' => 'external_url',
        'type' => 'url',
        'instructions' => 'Where users go for full details / registration.',
      ],
      [
        'key' => 'field_tre_occurrences',
        'label' => 'Other Date Options (Occurrences)',
        'name' => 'occurrences',
        'type' => 'repeater',
        'instructions' => 'Add additional scheduled offerings for the same course. Keep the next upcoming offering in the Primary Start/End fields (used for sorting).',
        'min' => 0,
        'layout' => 'row',
        'button_label' => 'Add Occurrence',
        'sub_fields' => [
          [
            'key' => 'field_tre_occ_start',
            'label' => 'Start Date',
            'name' => 'occ_start_date',
            'type' => 'date_picker',
            'return_format' => 'Y-m-d',
            'display_format' => 'F j, Y',
            'first_day' => 0,
          ],
          [
            'key' => 'field_tre_occ_end',
            'label' => 'End Date',
            'name' => 'occ_end_date',
            'type' => 'date_picker',
            'return_format' => 'Y-m-d',
            'display_format' => 'F j, Y',
            'first_day' => 0,
          ],
        ],
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
        'key' => 'field_tre_block_category_slug',
        'label' => 'Course Category',
        'name' => 'category_slug',
        'type' => 'select',
        'choices' => [
          'instructor-certification'     => 'Instructor Certification',
          'certified-instructor-courses' => 'Certified Instructor Courses',
          'rider-licensing'              => 'Rider Licensing',
          'licensed-rider-courses'       => 'Licensed Rider Courses',
        ],
        'ui' => 1,
        'return_format' => 'value',
        'allow_null' => 0,
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
