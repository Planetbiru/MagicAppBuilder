## MagicAppBuilder – Feature Summary

**MagicAppBuilder** is a robust, modular, and extensible application development framework designed to accelerate the creation of modern web applications. Below is a summary of its key features and recent improvements:

### Core Features

* **Entity & Module Management**

  * Visual Entity Editor with drag-and-drop field reordering.
  * Import entities from SQL, XLSX, and CSV files.
  * Add entities from SQL without clearing existing data.
  * Import entity structures directly from Excel and CSV with automatic schema inference.
  * Grouped table/entity lists (Custom vs. System Tables).
  * Hierarchical module management with unlimited multi-level menu support.
  * Automatic parent module creation and role inheritance.
  * Project-based entity editor data storage for better portability and version control.
  * **Entity metadata support**:

    * Description, Created At, Updated At, Created By, Updated By.
  * **Column rename now preserves data** within the editor, avoiding data loss.
  * **Export entity structure as Markdown** for documentation and team collaboration.
  * **Entity export uses server-stored definition**, ensuring consistency.

* **Menu System**

  * Unlimited multi-level navigation menus with Bootstrap-compatible styling.
  * Menu structure based on hierarchical module definitions.
  * Menu translation and localization per user language.
  * Menu caching system for improved performance.
  * Efficient menu cache updates and theme filtering based on menu type.
  * Improved menu cache efficiency and support for development mode preview.

* **Validation & Security**

  * Validator Builder for generating validation classes with PHP attributes.
  * Automatic validation on insert/update operations, with exception handling.
  * Validation message localization.
  * Built-in module validation integration.
  * Password history management and secure password reset via email.
  * Secure configuration management with encryption/decryption support.
  * Role-based menu caching and special access mechanisms for admin levels.
  * User role safety checks to prevent accidental lockout.

* **Database & Export**

  * Supports **MySQL**, **MariaDB**, **PostgreSQL**, **SQLite**, and **SQL Server**.
  * **Database export to Excel (`.xlsx`)**:

    * One sheet per table.
    * Column headers match database schema.
    * Excel column types inferred from actual database types (string, number, date).
  * Export structure and data to Database Explorer from Entity Editor.
  * Database structure auto-update after application upgrade.
  * Database time zone conversion for SQLServer and SQLite.
  * Table filtering and grouping in export and editor views.
  * Import **SQLite databases** (`.db`, `.sqlite`) from both Database Explorer and Entity Editor with structure and data parsing.
  * Import data from SQL with `INSERT INTO` statements attached to entities.

* **Documentation Tools**

  * **Markdown documentation export**:

    * Export entity definitions and metadata as Markdown files.
    * Includes table name, column types, constraints, and optional descriptions.
    * Helps during development, handover, or maintenance.

* **User Experience & UI**

  * Integrated file manager for project files.
  * WYSIWYG HTML editor for composing messages.
  * Enhanced captions, tooltips, and error messages.
  * Synchronized translation editors.
  * Theme system with dynamic theme-color support for mobile browsers (dark/light mode).
  * Improved UI for sortable handlers and drag-and-drop operations.
  * Scroll position memory in database manager.
  * Icon size refinements in **Entity Editor diagram tab**.
  * Default theme style improvements and responsive design.
  * Error handling pages (403, 404) for better user experience.

* **Localization & Internationalization**

  * Default language support and customizable language priority.
  * Menu and validation message localization.
  * Enhanced language localization for entities and modules.

* **Performance & Reliability**

  * Optimized caching, database queries, and internal workflows.
  * Auto-update menu cache and improved cache efficiency.
  * Refined error handling and stability improvements.

* **Security & Access Control**

  * IP forwarding support for proxy access.
  * Session variable updates to prevent conflicts and allow simultaneous logins.
  * Password policy enforcement and history tracking.
  * **Superuser-only access enforcement** for critical builder features.

* **Developer Tools & Extensibility**

  * Built-in application updater for seamless upgrades.
  * Dockerfile included for containerized deployment.
  * Enhanced code documentation and maintainability.
  * Backend-only with subquery support for flexible query configurations.
  * Improved compatibility with older PHP versions.

---

**MagicAppBuilder** is designed to be production-ready, scalable, and user-friendly, making it suitable for building secure, modern, and feature-rich web