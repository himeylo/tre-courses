# TRE Courses Block Plugin

Goal: display upcoming courses in 4 categories using an ACF block (Gutenberg).
CPT: course
Taxonomy: course_category
Terms (slugs): instructor-certification, certified-instructor-courses, rider-licensing, licensed-rider-courses

ACF Fields on course:
- start_date (date)
- end_date (date)
- city, state, venue, organizer, cost, external_url
- occurrences (repeater):
  - occ_start_date (date)
  - occ_end_date (date)

ACF Block: TRE Courses List
Block fields:
- category_slug (select)
- limit (number)
- show_cost (true/false)
- show_venue (true/false)
- show_organizer (true/false)
- cta_text (text)

Rules:
- A course is "upcoming" if primary end_date >= today OR any occurrence end_date >= today.
- Sorting: by earliest upcoming start date across primary range and occurrences.
- Hide past occurrences in the "Other dates" list.
- Date display: "March 3–5, 2026" (full month name), cross-month/year handled.
- Do not hardcode colors; inherit theme.

## Codex implementation rules (must follow)

- ACF repeater: do NOT read raw postmeta arrays/serialized structures.
  Always use have_rows('occurrences', $post_id) + the_row() + get_sub_field().
- Query performance: do not attempt complex SQL/meta_query against repeater fields.
  Fetch up to 100 posts max per category and then filter/sort in PHP.
- No new dependencies: do not add Composer, npm, webpack, build pipelines, or new plugins unless specifically directed to do so
