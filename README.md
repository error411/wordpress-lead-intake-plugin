# WP Lead Intake Manager

WP Lead Intake Manager is a lightweight WordPress plugin for collecting service inquiries from a public page and managing those leads inside the WordPress admin.

It provides a shortcode-based intake form, stores submissions in a custom database table, and gives site administrators a simple lead review screen with status tracking. The project is intentionally small and practical: it focuses on the kind of plugin architecture, security practices, and WordPress APIs used in real client work.

## Why I Built This

Many small business WordPress sites need more than a contact form, but less than a full CRM. This plugin demonstrates how I would build that middle layer: a maintainable custom plugin that captures structured lead data, validates submissions, and gives admins a clear workflow for follow-up.

The goal was to show practical WordPress development beyond theme edits, while keeping the codebase easy to explain, extend, and review.

## Features

- Frontend lead intake form via `[lead_intake_form]`
- Fields for name, email, phone, service needed, and notes
- Required-field validation and email validation
- Nonce-protected form submission through `admin-post.php`
- Sanitized input and escaped output
- Custom leads table created on activation
- Admin menu page for reviewing submitted leads
- Lead statuses: `new`, `contacted`, and `closed`
- Minimal public and admin CSS
- Uninstall cleanup for plugin-owned data

## WordPress Concepts Demonstrated

- Plugin bootstrap file and WordPress plugin headers
- Activation hook with `register_activation_hook()`
- Uninstall cleanup with `uninstall.php`
- Shortcode registration with `add_shortcode()`
- Frontend form handling with `admin_post_*` actions
- Nonce generation and verification
- Capability checks with `current_user_can()`
- Custom database table creation with `dbDelta()`
- Database reads and writes with `$wpdb`
- Input sanitization with WordPress sanitization helpers
- Output escaping with `esc_html()`, `esc_attr()`, and `esc_url()`
- Modular class-based plugin organization

## Installation

1. Copy this folder into your local WordPress install:

   ```text
   wp-content/plugins/wordpress-lead-intake-plugin
   ```

2. Activate **WP Lead Intake Manager** from the WordPress Plugins screen.

3. Add the shortcode to any page:

   ```text
   [lead_intake_form]
   ```

4. Submit a test lead from the frontend page.

5. Open **Lead Intake** in the WordPress admin to view and update lead statuses.

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

- Add pagination and search to the admin lead table
- Add optional email notifications for new leads
- Add CSV export for submitted leads
- Add a settings screen for service options
- Add custom capabilities for non-admin staff users
