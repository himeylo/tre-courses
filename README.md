# TRE Courses Block Plugin

Goal: display upcoming courses using an ACF block (Gutenberg).
CPT: course
Taxonomy: course_type (course name)
Grouping: built-in post categories

ACF Fields on course:
- start_date (date)
- end_date (date)
- venue (class location)
- city_state
- address
- cost
- organizer, external_url

ACF Block: TRE Courses List
Block fields:
- category_term (taxonomy select)
- limit (number)
- show_cost (true/false)
- show_venue (true/false)
- show_organizer (true/false)
- cta_text (text)

Rules:
- A course is "upcoming" if end_date >= today.
- Sorting: by earliest upcoming start date.
- Date display: "March 3–5, 2026" (full month name), cross-month/year handled.
- Locations display City, State and include a "(map)" link to the full address.
- Do not hardcode colors; inherit theme.

## Codex implementation rules (must follow)

- Query performance: fetch up to 100 posts max per category and then filter/sort in PHP.
- No new dependencies: do not add Composer, npm, webpack, build pipelines, or new plugins unless specifically directed to do so
