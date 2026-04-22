# WP Lead Intake Manager

WP Lead Intake Manager is a small, interview-friendly WordPress plugin that adds a frontend lead intake form and a simple admin screen for reviewing submissions.

The goal is to demonstrate practical plugin development: shortcode rendering, form handling, nonce checks, sanitization, escaping, a custom database table, activation and uninstall hooks, and a modular file structure.

## Features

- Frontend shortcode: `[lead_intake_form]`
- Lead fields:
  - Name
  - Email
  - Phone
  - Service needed
  - Notes
- Submission handling through `admin-post.php`
- Nonce verification before processing form data
- Input sanitization and validation
- Custom database table created with `dbDelta()`
- Admin menu page for viewing submitted leads
- Basic lead status workflow:
  - New
  - Contacted
  - Closed
- Minimal public and admin CSS
- Cleanup on uninstall

## WordPress Concepts Used

- Plugin headers and bootstrap constants
- Activation hook with `register_activation_hook()`
- Uninstall cleanup via `uninstall.php`
- Shortcodes with `add_shortcode()`
- Form submission actions with `admin_post_*`
- Nonces with `wp_nonce_field()` and `wp_verify_nonce()`
- Capability checks with `current_user_can()`
- Database operations with `$wpdb`
- Table creation with `dbDelta()`
- Escaping output with functions like `esc_html()`, `esc_attr()`, and `esc_url()`
- Sanitizing input with functions like `sanitize_text_field()`, `sanitize_email()`, and `sanitize_textarea_field()`

## Installation

1. Copy this folder into your local WordPress install under:

   ```text
   wp-content/plugins/wordpress-lead-intake-plugin
   ```

2. In the WordPress admin, go to **Plugins** and activate **WP Lead Intake Manager**.

3. Create or edit a page and add:

   ```text
   [lead_intake_form]
   ```

4. Visit that page and submit a test lead.

5. In the WordPress admin menu, open **Lead Intake** to view and update submitted leads.

## Project Structure

```text
lead-intake-manager.php
uninstall.php
includes/
  class-lim-activator.php
  class-lim-admin.php
  class-lim-db.php
  class-lim-form-handler.php
  class-lim-shortcode.php
assets/
  css/admin.css
  css/public.css
README.md
```

## Roadmap

- Add pagination and search to the admin table
- Add CSV export for leads
- Add configurable notification emails
- Add optional service choices in plugin settings
- Add more granular capabilities for non-admin staff users

## Notes for Interview Discussion

This MVP intentionally avoids extra dependencies and heavy abstractions. The code is organized by responsibility so each part is easy to explain:

- `LIM_Shortcode` renders the public form
- `LIM_Form_Handler` validates and saves submissions
- `LIM_DB` owns table creation and database queries
- `LIM_Admin` renders the admin list and status updates
- `LIM_Activator` handles activation-time setup
