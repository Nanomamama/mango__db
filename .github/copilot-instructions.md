# Copilot Instructions for mango__db

## Project Overview
- This is a PHP-based web application for managing mango products, user activities, bookings, and admin operations.
- The codebase is organized into `admin/` and `user/` directories, each with their own PHP scripts, CSS, and image assets.
- Data is stored and managed via MySQL, with database access handled in `admin/db.php`.
- The `data/` directory contains static JSON files for Thai provinces, amphures, and tambons, used for address selection.

## Key Components
- `admin/`: Admin dashboard, CRUD for mango/products/courses, order management, authentication, and reporting.
- `user/`: User-facing pages for browsing mangoes/products, booking, cart, checkout, order status, and profile management.
- `uploads/`, `admin/image/`, `user/image/`: File upload and image storage locations.

## Patterns & Conventions
- Each major entity (mango, product, course, booking) has dedicated CRUD scripts (e.g., `add_mango.php`, `edit_mango.php`, `delete_mango.php`).
- Authentication is handled separately for admin (`admin_login.php`, `auth.php`) and users (`member_login.php`, `login_check.php`).
- Admin and user UIs are separated; shared logic is minimal.
- Use `include`/`require` for shared components (e.g., `navbar.php`, `footer.php`, `sidebar.php`).
- File uploads are processed and stored in the respective `uploads/` directories.
- Static data (provinces, etc.) is loaded from JSON, not the database.

## Developer Workflows
- No build step; PHP files are served directly.
- To test, run a local PHP server in the project root:
  ```cmd
  php -S localhost:8000
  ```
- Database connection settings are in `admin/db.php`.
- Debugging is typically done via `echo`, `var_dump`, or browser dev tools.

## Integration Points
- No external frameworks (e.g., Laravel, Symfony) are used; this is a custom PHP app.
- Relies on MySQL for persistent storage.
- Uses static JSON for address data.
- No frontend JS frameworks; UI is classic PHP/HTML/CSS.

## Examples
- To add a new mango variety: update `admin/add_mango.php`, `admin/manage_mango.php`, and related CRUD scripts.
- To update user profile logic: see `user/member_profile.php` and `user/register_save.php`.

## Recommendations for AI Agents
- Respect the separation between `admin/` and `user/` logic.
- Follow the CRUD script pattern for new entities.
- Use existing file upload and static data loading patterns as templates.
- Reference `admin/db.php` for any database access.
- When in doubt, look for similar scripts in the relevant directory for examples.
