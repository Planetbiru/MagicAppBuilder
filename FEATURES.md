## MagicAppBuilder – Feature Summary

**MagicAppBuilder** is a robust, modular, and extensible application development framework designed to accelerate the creation of modern web applications. Below is a summary of its key features and improvements:

### Core Features

- **Entity & Module Management**
  - Visual Entity Editor with drag-and-drop field reordering.
  - Import entities from SQL, XLSX, and CSV files.
  - Add entities from SQL without clearing existing data.
  - Import entity structures directly from Excel and CSV with automatic schema inference.
  - Grouped table/entity lists (Custom vs. System Tables).
  - Hierarchical module management with unlimited multi-level menu support.
  - Automatic parent module creation and role inheritance.
  - Project-based entity editor data storage for better portability and version control.

- **Menu System**
  - Unlimited multi-level navigation menus with Bootstrap-compatible styling.
  - Menu structure based on hierarchical module definitions.
  - Menu translation and localization per user language.
  - Menu caching system for improved performance.
  - Efficient menu cache updates and theme filtering based on menu type.
  - Improved menu cache efficiency and support for development mode preview.

- **Validation & Security**
  - Validator Builder for generating validation classes with PHP attributes.
  - Automatic validation on insert/update operations, with exception handling.
  - Validation message localization.
  - Built-in module validation integration.
  - Password history management and secure password reset via email.
  - Secure configuration management with encryption/decryption support.
  - Role-based menu caching and special access mechanisms for admin levels.
  - User role safety checks to prevent accidental lockout.

- **Database & Export**
  - Supports MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server.
  - Asynchronous, per-table database export with real-time status and batch download.
  - Database structure auto-update after application upgrade.
  - Database time zone conversion for SQLServer and SQLite.
  - Table filtering and grouping in export and editor views.

- **User Experience & UI**
  - Integrated file manager for project files.
  - WYSIWYG HTML editor for composing messages.
  - Enhanced captions, tooltips, and error messages.
  - Synchronized translation editors.
  - Theme system with dynamic theme-color support for mobile browsers (dark/light mode).
  - Improved UI for sortable handlers and drag-and-drop operations.
  - Project exporter/importer for easy backup and migration.
  - Scroll position memory in database manager.
  - Default theme style improvements and responsive design.
  - Error handling pages (403, 404) for better user experience.

- **Localization & Internationalization**
  - Default language support and customizable language priority.
  - Menu and validation message localization.
  - Enhanced language localization for entities and modules.

- **Performance & Reliability**
  - Optimized caching, database queries, and internal workflows.
  - Auto-update menu cache and improved cache efficiency.
  - Refined error handling and stability improvements.

- **Security & Access Control**
  - IP forwarding support for proxy access.
  - Session variable updates to prevent conflicts and allow simultaneous logins.
  - Password policy enforcement and history tracking.

- **Developer Tools & Extensibility**
  - Built-in application updater for seamless upgrades.
  - Dockerfile included for containerized deployment.
  - Enhanced code documentation and maintainability.
  - Backend-only with subquery support for flexible query configurations.
  - Enhanced compatibility with older PHP versions.

### Notable Improvements & Fixes

- Numerous bug fixes and code quality improvements.
- Enhanced compatibility with older PHP versions.
- Improved error detection for entities and validators.
- UI and workflow enhancements for a smoother user experience.
- Automatic database structure updates and validation integration.
- Improved menu and theme management for both production and development environments.

---

**MagicAppBuilder** is designed to be production-ready, scalable, and user-friendly, making it suitable for building secure, modern, and feature-rich web