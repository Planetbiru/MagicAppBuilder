### MagicAppBuilder – Feature Summary

**MagicAppBuilder** is a robust, modular, and extensible application development framework designed to accelerate the creation of modern web applications. It offers a comprehensive set of tools for building secure, scalable, and user-friendly applications with minimal effort.

---

### Core Features & Improvements

#### **Entity & Module Management**
- **Visual Entity Editor** with drag-and-drop field reordering.
- **Import entities** from SQL, XLSX, and CSV files, including the ability to add entities from SQL without clearing existing data.
- **Import entity structures** directly from Excel and CSV with automatic schema inference.
- **Grouped table/entity lists** (Custom vs. System Tables).
- **Hierarchical module management** with unlimited multi-level menu support.
- **Automatic parent module creation** and role inheritance.
- **Project-based entity editor data storage** for better portability and version control.
- **Entity metadata support** for descriptions, creation/update timestamps, and user tracking.
- **Column rename** now preserves data within the editor to prevent data loss.
- **Export entity structure as Markdown** for easy documentation and team collaboration.
- **Entity export** uses the server-stored definition, ensuring consistency.

#### **Menu System**
- **Unlimited multi-level navigation menus** with Bootstrap-compatible styling.
- Menu structure is based on hierarchical module definitions.
- **Menu translation and localization** per user language.
- **Optimized menu caching system** for improved performance.
- Efficient menu cache updates and theme filtering based on menu type.
- Support for a **development mode preview** to check menu changes before deployment.

#### **Validation & Security**
- **Validator Builder** for generating validation classes with PHP attributes.
- **Automatic validation** on insert/update operations with built-in exception handling and localized messages.
- **Secure password management** including history tracking and secure password reset via email.
- **Secure configuration management** with encryption/decryption support.
- **Role-based menu caching** and special access mechanisms for admin levels.
- **User role safety checks** to prevent accidental lockout.
- **Superuser-only access enforcement** for critical builder features.
- IP forwarding support for proxy access.
- Session variable updates to prevent conflicts and allow simultaneous logins.

#### **Database & Export**
- **Supports MySQL, MariaDB, PostgreSQL, SQLite, and SQL Server.**
- **Asynchronous, per-table database export** with real-time status and batch download.
- **Database export to Excel (.xlsx)**, with one sheet per table, schema-matched column headers, and inferred Excel column types.
- **Import SQLite databases** from both the Database Explorer and Entity Editor, parsing both structure and data.
- **Import data from SQL** with `INSERT INTO` statements attached to entities.
- **Database structure auto-update** after application upgrades.
- Database time zone conversion for SQLServer and SQLite.
- Table filtering and grouping in export and editor views.

#### **User Experience & UI**
- **Integrated file manager** for project files.
- **WYSIWYG HTML editor** for composing messages.
- Enhanced captions, tooltips, and error messages.
- Synchronized translation editors.
- **Theme system** with dynamic color support for mobile browsers (dark/light mode).
- Improved UI for sortable handlers and drag-and-drop operations.
- **Project exporter/importer** for easy backup and migration.
- Scroll position memory in the database manager.
- **Improved responsiveness** and theme style refinements.
- Better error handling pages (403, 404) for a smoother user experience.

#### **Localization & Internationalization**
- Default language support with customizable language priority.
- Menu and validation message localization.
- Enhanced language localization for entities and modules.

#### **Performance & Reliability**
- Optimized caching, database queries, and internal workflows.
- Refined error handling and stability improvements.
- Numerous bug fixes and code quality improvements.

#### **Developer Tools & Extensibility**
- **Built-in application updater** for seamless upgrades.
- Dockerfile included for containerized deployment.
- Enhanced code documentation and maintainability.
- Backend-only support with subquery functionality.
- **Improved compatibility with older PHP versions.**

---
**MagicAppBuilder** is production-ready, scalable, and designed to help you build modern, feature-rich web applications with minimal effort.