<?php
if (!defined('ABSPATH')) exit;

function tre_courses_get_setting($key, $default = '') {
  $value = get_theme_mod($key, null);
  if ($value === null || $value === '') {
    return $default;
  }
  return $value;
}

function tre_courses_format_date_range($start, $end, $format = 'smart') {
  if (!$start && !$end) {
    return '';
  }

  try {
    $s = $start ? new DateTime($start) : null;
    $e = $end ? new DateTime($end) : null;
  } catch (Exception $ex) {
    return trim((string) $start . (((string) $start && (string) $end) ? '–' : '') . (string) $end);
  }

  if (!$s && $e) {
    return $e->format('F j, Y');
  }
  if ($s && !$e) {
    return $s->format('F j, Y');
  }

  if ($format === 'start') {
    return $s->format('F j, Y');
  }

  if ($format === 'full') {
    return $s->format('F j, Y') . '–' . $e->format('F j, Y');
  }

  if ($s->format('Y-m') === $e->format('Y-m')) {
    return $s->format('F j') . '–' . $e->format('j, Y');
  }

  if ($s->format('Y') === $e->format('Y')) {
    return $s->format('F j') . '–' . $e->format('F j, Y');
  }

  return $s->format('F j, Y') . '–' . $e->format('F j, Y');
}

function tre_courses_build_map_link($address, $fallback = '') {
  $query = $address ? $address : $fallback;
  if (!$query) {
    return '';
  }

  return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
}
