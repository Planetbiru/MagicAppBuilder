# MagicAppBuilder Version 0.34

## What's New

When users create reference entities and filter entities, MagicAppBuilder now includes validation for the following data:

-    Entity name
-    Table name
-    Primary key name
-    Value column name
-    Reference object name
-    Reference column name

This validation is designed to make it easier for users when creating reference and filter entities. Now, users can be sure whether the reference entity information they entered is correct or not. Before this validation was added, users had to carefully examine the table structure and manually input the information to match the columns in the table.

If a user enters an incorrect table name, MagicAppBuilder will display the available table names from the database so that the user can select the correct one. Once a table is selected, MagicAppBuilder will automatically choose the first primary key from that table, then continue verifying the value column name, reference object name, and reference column name.

Once all values are correct, MagicAppBuilder will mark the reference or filter entity as correctly created and it won't be checked again if the user returns to the reference or filter entity editor within the same session. However, MagicAppBuilder will revalidate if the user switches to another module, as there is a potential for changes in the database structure.

# MagicAppBuilder Version 0.35

## What's New

New Features:

-    Integrated File Manager: A new file manager has been added to MagicAppBuilder, providing a seamless interface for managing your application’s files.

Improvements:

-    File Editing Capabilities: Users can now view, edit, and save any files located within the application's directory. This feature makes it easier to work with and manage the content of your project directly within MagicAppBuilder.



# MagicAppBuilder Version 0.36

## What's New

### 1. **Updated Application Preparation Process**

-   The application preparation process has been enhanced with the following updates:
    
    -   **Directory Structure**: New directories have been added for better organization and scalability:
        
        -   `inc.app`: Centralized directory for application-related configurations and assets.
            
        -   `inc.themes`: Dedicated folder to manage application themes.
            
        -   `inc.lib/classes/{{AppNamespace}}`: Namespace-specific class directory to facilitate better class management and easy updates.
            
    -   These directories ensure better modularity and separation of concerns for future app extensions and theme management.
        

### 2. **Improved Composer Setup**

-   The Composer setup has been updated for a smoother and more efficient development workflow:
    
    -   **Dependency Management**: Improved handling of project dependencies, ensuring that third-party libraries and components are properly managed.
        
    -   **Autoloading**: Updated autoloading mechanism for better performance and compatibility with the new directory structure.
        
    -   **Composer Scripts**: Enhanced Composer scripts for easier installation and setup of the application and its dependencies.
        
    -   These changes will help streamline the setup process and ensure that dependencies are correctly configured every time the project is initialized or updated.
        

### 3. **Integration with Themes**

-   **Theme Integration**: MagicAppBuilder version 0.36 has built-in support for managing themes directly within the framework:
    
    -   **Flexible Theme System**: Easily switch between different themes by simply updating the configuration.
        
    -   **Custom Theme Support**: You can create and integrate custom themes to provide tailored designs for different parts of the application.
        
    -   **Theme Management**: The new `inc.themes` directory allows for easy management, adding, and switching between themes across different sections of your app.
        
    -   **Enhanced UI Customization**: The integration with themes gives developers the ability to easily customize the visual appearance of the app without modifying the core code.
        

### 4. **New Input Types Support**

-   **File Input Enhancements**: MagicAppBuilder version 0.36 introduces support for new input types:
    
    -   **File**, **Image**, **Audio**, and **Video** inputs.
        
    -   Supports both **single** and **multiple** file uploads.
        
-   **Direct Upload**: Files are now uploaded directly to a specified directory, improving file management and reducing complexity.
    
-   **Custom Upload Path**: Users can easily configure and change the target directory for uploaded files through the application settings or input configuration.
    

### 5. **Menu Caching System**

-   **Menu Cache Added**: MagicAppBuilder version 0.36 introduces a menu caching system to enhance performance and efficiency.
    
-   **Role-Based Menu Caching**: Menus generated based on the admin level (via admin roles) are now cached in the database as JSON. This eliminates the need to repeatedly check admin roles based on the admin level ID during each request.
    
-   **Faster Menu Access**: Cached menus are ready to use immediately, reducing database queries and improving application performance—especially in complex systems with multiple user roles.

These new input types provide greater flexibility in form handling and media integration across your application.


### 6. **Special Access for Admin Levels**

-   **Special Access Mechanism**: MagicAppBuilder version 0.36 introduces a **special access mechanism** tied to admin levels.
    
-   **Bypass Role Restrictions**: Admins whose admin level has been granted special access can bypass standard role-based permissions for specific modules.
    
-   **Failsafe Access to Critical Modules**: This ensures that critical modules, such as user access management, remain accessible—**even if no individual admin roles explicitly include them**. It prevents lockouts and maintains application operability.
    
-   **Safer Permission Management**: This enhancement provides a safety net, ensuring that key administrative modules remain accessible by designated admin levels regardless of potentially misconfigured role settings.

### 7. **Other Improvements**

-   **Bug Fixes**: Several bug fixes and minor improvements have been applied to ensure smoother operation.
    
-   **Performance Optimization**: Optimized certain functions and processes to improve the overall performance of the app.
    
-   **Documentation Updates**: The documentation has been updated to reflect the changes in the application preparation process and new directory structure.


**MagicAppBuilder version 0.36.0 marks the first public release.**

Everyone can now start using MagicAppBuilder to build fully functional monolithic applications with support for MySQL, MariaDB, PostgreSQL, and SQLite databases. While this version is not yet considered 100% stable, it is safe and reliable enough for development and testing purposes. It lays a solid foundation for future updates and encourages developers to explore and contribute as the project continues to grow.

With its modular structure, theme integration, and improved development workflow, MagicAppBuilder is designed to simplify the application-building process for developers of all levels. Whether you're creating internal tools, admin panels, or full-scale business applications, MagicAppBuilder provides a flexible and extensible foundation that can grow with your project needs.


# MagicAppBuilder Version 0.37

## What's New

### Removed Absolute Paths in Configuration for Production Environment

Absolute paths for the application's root directory, language directory, and others are no longer used by the application itself. These absolute paths are now only utilized by MagicAppBuilder during the build process. This change reduces the potential for configuration errors in production environments and simplifies the deployment process.

### Add Application URL

Added support for storing and displaying the **application URL**, making integration and documentation easier.

### Add HTML Element on Micro Services Application Architecture

Users can now add **custom HTML elements** within microservices-based applications, providing more flexibility for UI customization.

### Add WYSIWYG HTML Editor

WYSIWYG HTML Editor usefull to compose message to another user.

### Unused Files Removal for Faster Builds

Unnecessary or temporary files are now automatically excluded during the build process, resulting in faster application generation times and a cleaner output structure.

## Bug Fixes

1.  **Admin role**  
    Fixed an issue where **admin role permissions** were not applied correctly, leading to improper access control.
    
2.  **File renderer**  
    Resolved a bug where **files could not be rendered or previewed properly** in the UI.
    
3.  **Loading indication on first language addition**  
    Fixed missing or incorrect **loading indicators** when adding the first language to a project.
    
4.  **Multiple input**  
    Fixed an issue where **multiple select dropdowns and tag editors** did not render correctly for multiple input fields.
    
5.  **Add HTML element**  
    Fixed a bug that caused an error when trying to add an HTML element through the visual builder.
    
## Code Smell Fixes

Improved code quality by cleaning up **redundant or inefficient code**, leading to better performance and maintainability.


# MagicObject Version 0.38

## What's New

### Added

-   Introduced password history management for admin users.
    
    -   Added function `passwordExists($database, $adminId, $hashPassword)` to check if a password has been previously used.
        
    -   Added function `createPasswordHistory($database, $adminId, $hashPassword)` to save newly created passwords into history records.
        
-   Improved security by preventing reuse of old passwords during password change operations.
    
-   Added password reset via email functionality.
    
    -   Admin users can request a password reset link to be sent to their registered email address.
        
    -   Added support for generating secure reset tokens and emailing HTML-based reset instructions using PHPMailer.        
        
    -   Token expiration and validation should be implemented to secure the reset process.
        
-   Added application cookie configuration support.
    
    -   Added support for configuring cookie attributes such as `path`, `domain`, `secure`, `httponly`, and `samesite`.
        
    -   These settings can now be managed through the session configuration interface.
        
    -   Enhances control over session security and cross-domain compatibility.
        
-   Added functionality to create entities from a given table name.
    
    -   This feature allows developers to quickly generate entity classes based on the structure of a database table.
        
    -   Automatically maps table columns to entity properties.
        
    -   Supports primary key detection and inclusion in the generated entity.
        
    -   Simplifies the process of creating entities, reducing manual effort and potential errors.
        
-   Added default language application support.
    
    -   Developers can now set a default language for the application.
        
    -   This feature ensures that the application uses a predefined language when no specific language is selected by the user and if the user is not logged in yet.
        
    -   Enhances the user experience by providing a consistent language fallback mechanism.
        
-   Introduced chart template functionality.
    
    -   Added pre-defined chart templates for common use cases such as bar charts, line charts, pie charts, and more.
        
    -   Templates are customizable, allowing developers to modify chart properties such as colors, labels, and data sources.
        
    -   Integrated with the existing data visualization module for seamless chart generation.
        
    -   Simplifies the process of creating and embedding charts in applications, reducing development time and effort.


### Changed

-   Updated session variable names to prevent conflicts.
    
    -   This change allows MagicAppBuilder and applications built with MagicObject to log in simultaneously in the same browser without session collision.
        

### Fixed

-   Minor bug fixes and optimizations.
    

### Notes

-   The new password history features help enforce stronger password policies and enhance system security.
    
-   Make sure to update your database schema to include the `user_password_history` (or equivalent) table if not already present.
    
-   Due to the session variable name changes, ensure any custom session handling code is reviewed and updated if necessary.
    
-   The cookie configuration feature allows developers to tailor session behavior to suit various deployment environments and security requirements.


# MagicAppBuilder Version 1.0

## Overview

MagicAppBuilder 1.0 marks the first official **stable release**, building on everything introduced in version 0.38 and adding numerous enhancements. It is ready for production use and provides a powerful, scalable, and reliable framework for developing modern web applications.

## What's New

### Stable & Production-Ready

Version 1.0 delivers the stability, features, and performance enhancements required for production deployment, combining improvements from earlier iterations with new capabilities.

## Key Features

-   **Password History Management**  
    Prevents admin users from reusing old passwords, improving account security.
    
-   **Password Reset via Email**  
    Enables secure password reset for admins through email-based reset links.
    
-   **Entity Generator**  
    Auto-generates entity classes from database tables to reduce manual work.
    
-   **Default Language Support**  
    Ensures consistent language fallback for users who are not logged in.
    
-   **Chart Templates**  
    Provides customizable and pre-defined templates for data visualization.
    
-   **Application Cookie Configuration**  
    Enhanced cookie management, including cross-domain and secure options.
    
-   **Dockerfile Included**  
    A Dockerfile is now included to simplify container-based deployment.
    
-   **Scroll Position Memory in Database Manager**  
    Automatically saves and restores the table list’s scroll position, helping users continue seamlessly.
    
-   **Auto-Update Menu Cache**  
    Menu cache is now automatically updated when modules change, ensuring the latest structure is always shown.
    

## Improvements

-   **Upgraded MagicApp & MagicObject**  
    Updated to the latest versions for improved stability and compatibility.
    
-   **Enhanced Code Documentation**  
    Improved docblocks for better developer experience and maintainability.
    
-   **Improved Multilingual Support**  
    Updated module translations for smoother internationalization.
    
-   **Refined Error Message Design**  
    Error messages now offer better visual clarity and consistency.
    
-   **Session Management Enhancements**  
    Prevents conflicts during simultaneous logins between MagicAppBuilder and MagicObject-based apps in the same browser.
    
-   **Performance Enhancements**  
    Optimizations to caching, database queries, and internal workflows for faster performance.
    
-   **Cookie Attribute Fixes**  
    Fixed cookie handling to behave consistently across environments.
    
-   **Improved Server Routing**  
    Requests to `lib.themes` are now properly redirected to enhance asset handling and access control.
    
-   **Default Theme Improvements**  
    Visual and responsive improvements to the default theme for a better cross-device experience.
    
-   **New Error Pages**  
    Added `403.php` and `404.php` pages for clearer handling of unauthorized and not-found requests.
    
-   **Backend-Only with Subquery Support**  
    You can now use the "Subquery" option even when "Backend Only" mode is enabled.
    
-   **Expanded Browser Support**  
    Officially supports:
    
    -   Mozilla Firefox
        
    -   Microsoft Edge
        
    -   Google Chrome
        
    -   Opera
        
    -   Brave
        
    -   Vivaldi
        
    
    _Use of unsupported browsers may lead to compatibility issues with JavaScript, CSS, and HTML._
    
-   **Form Module Fixes**  
    Improved reliability and functionality in the form module feature.
    
-   **Save & Load Module Configuration**  
    Allows saving and reusing module feature configurations across different modules.

## Bug Fixes

-   Numerous fixes from version 0.x to improve system stability and user experience.

## Notes

-   **Partial Backward Compatibility**  
    While compatible with version 0.38 in most areas, version 1.0 does not fully support downgrading to earlier versions.
    
-   **Upgrade Recommended**  
    Developers are encouraged to migrate their projects to version 1.0 to benefit from the latest features, improvements, and stability.

**MagicAppBuilder 1.0** is the result of extensive development, testing, and community feedback — offering a dependable and feature-rich platform for building secure, modern, and scalable applications.

# MagicAppBuilder Version 1.1

## What's New

### Database Time Zone Conversion

A new configuration option, `database.timeZoneSystem`, has been added for SQLServer and SQLite databases. This feature allows the system to automatically handle time zone conversions when users operate in different time zones, ensuring accurate date and time management throughout the application.

**Example:**

If the `$currentUser` object has a `timeZone` property, you can use the following code:

```php
if($currentUser->issetTimeZone() && $currentUser->getTimeZone() != $database->getDatabaseCredentials()->getTimeZone())
{
    date_default_timezone_set($currentUser->getTimeZone());
    $database->getDatabaseCredentials()->setTimeZone($currentUser->getTimeZone());
    $database->setTimeZone($currentUser->getTimeZone());
}
```

This code sets the application's time zone based on the user's time zone, while allowing the database to continue operating in a separate time zone, such as `UTC+0`. For time zone conversion, the database uses both the `database.timeZoneSystem` and `database.timeZone` configurations.

### IP Forwarding Support for Proxy Access

Implemented IP forwarding logic to correctly capture the client’s real IP address when the application is accessed through a proxy.

**Example:**

-  Enable forwarding via Cloudflare proxy:

```yaml
ipForwarding:
    enabled: true
    headers: 
        - CF-Connecting-IP
        - X-Forwarded-For
        - True-Client-IP
```

-  Disable forwarding:

```yaml
ipForwarding:
    enabled: false
    headers: [ ]
```

### Secure Configuration with `EncryptOut` and `DecryptIn`

Added support for secure configuration management using `EncryptOut` and `DecryptIn` annotations. Application configuration values such as database and session settings can now be encrypted and decrypted automatically, enhancing security for sensitive information.

### ERD Relation Selection via Context Menu

When creating an ERD, users can now choose which related tables to display by right-clicking on an entity. MagicAppBuilder will show a context menu listing all tables that can be referenced from the selected entity. Users can check the desired tables, and MagicAppBuilder will update the diagram to add the checked entities automatically.

## Improvements

-   **Menu Cache Efficiency**  
    The menu caching mechanism has been improved to be more efficient. Updates to the menu cache now consume fewer resources and respond faster, ensuring that menu changes are reflected promptly without unnecessary overhead.

-   **Theme Color for Mobile Browsers**  
    Added support for dynamic `theme-color` meta tags that automatically adjust to dark mode and light mode on mobile browsers, providing a more integrated and visually consistent user experience.

-   **IP Forwarding Support for Proxy Access**  
    Implemented IP forwarding logic to correctly capture the client’s real IP address when the application is accessed through a proxy.

    **Example:**
    
    -  Enable forwarding via Cloudflare proxy:

    ```yaml
    ipForwarding:
        enabled: true
        headers: 
            - CF-Connecting-IP
            - X-Forwarded-For
            - True-Client-IP
    ```

    -  Disable forwarding:

    ```yaml
    ipForwarding:
        enabled: false
        headers: [ ]
    ```

## Bug Fixes

-   Various bug fixes to improve stability and reliability.


# MagicAppBuilder Version 1.2

## What's New

### Advanced Asynchronous Per-Table Database Export

A powerful new feature has been introduced to allow **asynchronous, per-table database export**, providing users with more control and flexibility when exporting database contents.

Users can now **select individually per table** whether to export:

-   Structure only
-   Data only
-   Or both structure and data

This is especially useful for large databases where selective export is essential for performance and data management.

#### Key Features:

-   Asynchronous processing — tables are exported one at a time without freezing the UI.
-   Real-time status indicators (e.g., `in-progress`, `finish`) for each table export.
-   Exported content is automatically compiled into a single `.sql` file.
-   Automatic file download once all selected tables have been processed.

## Improvements

-   **Enhanced Export UI:**  
    A new intuitive UI allows users to check/uncheck structure and data options for each table using a simple interface.
    
-   **Export Status Tracking:**  
    Visual export status (`...`, `✓`) appears inline in the table list to inform users of the progress and completion of each export operation.
    
-   **Reliable Batch Download:**  
    Exported tables are incrementally appended into one file, ensuring all selected tables are included in the final downloadable `.sql` archive.

## Bug Fixes

-   Fixed several edge cases in database table name detection for SQLite and PostgreSQL.
-   Improved error handling in AJAX export logic for better feedback when an export fails.


# MagicAppBuilder Version 1.3

## What's New

### Add Entities from SQL Without Clearing Existing Data

You can now import entities from an SQL file without removing the existing ones. This feature is useful when you want to add one or more new entities from an SQL file to your current project without overwriting or clearing the existing entities.

### Input Validation

User input validation now leverages the input validation features introduced in MagicObject version 3.14, providing more robust and consistent validation across the application.

### Validator Builder

A new **Validator Builder** is introduced, allowing developers to generate validation classes based on field definitions and annotations. These classes use PHP attributes such as `@Required`, `@Email`, `@Min`, etc., and are generated automatically per module and action (insert/update).

### Validation Integration with MagicObject

MagicObject has been updated to support automatic validation via annotations. When validation rules are defined in the generated validator class, MagicObject can now automatically enforce them on property assignment or during data binding.

### Validate on Insert and Update

All **insert** and **update** operations now include automatic validation. The corresponding validator class is applied based on the current operation type, ensuring only valid data is processed or persisted.

### Exception Handling for Invalid Data

If validation fails during insert or update operations, a specific **validation exception** is thrown immediately to stop the operation. This prevents invalid data from reaching the database.

### Form Restoration and Error Highlighting

When a validation error occurs, the form is **automatically restored** to its previous state, and the field with the error is **highlighted** on the client side. This improves user experience and guides users to correct their input.

### Menu Translation

A new **Menu Translation** feature is now available, allowing you to translate menu labels based on the user's selected language. Each user level will see the menu in their preferred language. Translated menus are cached, ensuring optimal performance and responsiveness across the system.

### `application_id` Column in Error Cache Entity

A new `application_id` column has been added to the Error Cache entity. This enhancement makes it easier to search and filter error logs based on the originating application, especially in multi-application environments.

## Bug Fixes

-   Bug fixes in the entity editor when designing database structures.
    
-   Resolved issues related to entity field synchronization and missing metadata after import.



# MagicAppBuilder Version 1.4.0

## What's New

-   **Validator Class Editor:** A new editor is available for managing validator classes, equipped with tools for testing validation classes.

-   **Error Detection for Entities and Validators:** Find errors more easily within your entities and validators with this new feature.

-   **Save and Load Format Strings:** You can now save and load string, date, and number formats for data output in list and detail views.

-   **Menu Localization:** Translate menus to match the user's preferred language.

-   **Validation Message Localization:** Translate input validation messages to match the user's preferred language.

-   **Database Export Query Conversion:** Convert `CREATE TABLE` and `INSERT` statements to match the target DBMS during database export.

-   **New Database Conversion Support:** You can now convert `CREATE TABLE` and `INSERT` statements to SQL Server from:

    -  MySQL → SQL Server

    -  MariaDB → SQL Server

    -  PostgreSQL → SQL Server

    -  SQLite → SQL Server
    
    Supported target databases now include:

    -  MySQL

    -  MariaDB

    -  PostgreSQL

    -  SQLite

    -  SQL Server
    

## Improvements

-   **Enhanced Captions and Tooltips:** Captions and tooltips on the main module and entity generation forms have been improved for clarity.

-   **Enhanced Language Localization:** Entity development has been improved for more effective language localization.

-   **Enhanced File Manager:** The file manager now offers a better user experience.

-   **Reorganized Module Tabs:** Module tabs have been reorganized for a more intuitive workflow.


## Fixes

-   Display improvements.
-   General bug fixes.


# MagicAppBuilder Version 1.4.1

## Improvements

-   **Database Explorer Enhancements:** We've made the database explorer more stable and reliable. This includes adding the `autocomplete="off"` attribute to the SQL editor element, which helps prevent annoying browser autocomplete suggestions.
-   **Validator Generator Updates:** The validator generator has received improvements, specifically in its output. The **docblock format for generated validator classes is now more readable and clearer**, making it easier to understand and use.
-   **Optimized Dependency Management:** We've cleaned up our Composer dependencies. This means we've removed unnecessary duplicate packages, particularly ensuring `MagicObject` isn't redundantly included in the `vendor` directory when `MagicAppBuilder` already handles it. This makes your project lighter and dependency resolution smoother.
-   **Synchronized Translation Editors:** Our translation editors now feature synchronized scrolling. When you scroll one editor, the other automatically matches its position, creating a much more fluid and intuitive translation experience.

## Fixes

-   **Application Creation Directory Bug Fix:** We've squashed a bug that prevented the correct directory creation when you were setting up a new application. This ensures a smoother and more reliable setup process.


# MagicAppBuilder Version 1.5.0

## What's New

-   **Project Exporter:** You can now export individual applications as portable project files.  
    A new **Export** button has been added directly to each application card, allowing you to back up or transfer applications one at a time.
    
-   **Project Importer:** A new **Import** button is available on the **Apps** tab.  
    Use this to load a previously exported project file and restore the corresponding application instantly.
    
-   **Multiple Approval and Rejection:** We've significantly enhanced workflow management by adding support for **bulk approval and rejection** processes. 
    This means you can now configure more complex approval flows for your applications, allowing users to approve or reject **multiple rows at once**.
    
-   **Quick Application Access with Base URL:** We've introduced a new **Base Application URL** feature. 
    This allows you to define a direct URL for each application, providing quick and convenient access to your apps.


## What's Changed

-   **Icon Button Relocation:** We've moved the icon button that was previously on each application card to the **application options dialog**.  
    This change makes space for the new **Export** button and generally improves how the interface is organized, giving you a cleaner look.
    
-   **Feature Preferences Now Stored in Database:** Module feature preferences are no longer saved within project files.  
    All your user-specific feature settings are now stored directly in the **database**. This means your preferences will stick around, remaining consistent across different exports, imports, and deployments.
    
-   **Refactored Application Create and Update Forms:** We've updated the **field naming conventions** in both the application creation and update forms.  
    This change ensures greater consistency and clarity across the application, making it easier to understand and manage your forms.
    
-   **Function Refactoring to Reduce Redundancy:** Several internal functions, especially those related to module processing, application metadata handling, and form generation, have been **refactored** to eliminate redundant logic and duplicate code.  
    This not only improves maintainability but also reduces the risk of inconsistencies and simplifies future enhancements.


## Improvements

-   **Smoother Database Export Experience:** We've refined the database export process.  
    Previously, after the SQL was generated, the screen would momentarily flash as the browser opened and then immediately closed a new window to trigger the file download.  
    Now, the download process happens **seamlessly in the background**, ensuring an uninterrupted and more professional user experience.


## Fixes

-   **UI Typo Corrections:** We've addressed and fixed various typographical errors throughout the user interface, improving overall readability and professionalism.

-   **Form Data Restoration Bug:** Fixed an issue where form fields were not properly restored after validation errors in some scenarios. 
    This resolves inconsistent behaviors in pre-filling user input after failed submissions and improves error field highlighting reliability.



# MagicAppBuilder Version 1.5.1

## What's Changed

### Validator Generator Enhancement: `tableName` Support

MagicAppBuilder 1.5.1 adds integration with the `tableName` parameter introduced in **MagicObject 3.14.2** when generating validator classes.

-   When generating a validator class, the corresponding table name (if defined in the entity metadata) is now included via `@Table(name="...")` in the class-level PHPDoc.
    
-   The `@Validator` annotation is also automatically added to mark the class as a validator.
    

#### Benefits:

-   Enhances interoperability with annotation-aware tools such as ORMs, scaffolding engines, or validation frameworks.
    
-   Strengthens the link between the generated validator and its underlying database table for clearer structure and better documentation.
    

### Validation Class Editor GUI

A new interactive **Validation Class Editor** has been added to simplify the creation and management of validation annotations through a **form-based GUI**.

-   Displays fields dynamically based on the selected table structure.
    
-   Allows users to add, update, or remove multiple validation rules per field.
    
-   Automatically serializes the validation definition into a compatible JSON format.
    
-   Supports drag-and-drop sorting (including for dynamically added fields).
    

#### Benefits:

-   Eliminates manual editing of raw annotations.
    
-   Increases productivity and reduces syntax errors when defining validation rules.
    
-   Ensures consistency with backend validator class generation.
    

### Entity Field Reordering via Drag-and-Drop

The Entity Editor now supports **drag-and-drop row sorting** to reorder fields visually, replacing the previous method that used only "move up" and "move down" buttons.

-   Each row now includes a drag handle for intuitive movement.
    
-   Users can rearrange fields freely without multiple clicks.
    
-   Works seamlessly with both static and dynamically added fields.
    

#### Benefits:

-   Significantly improves usability when managing large entities.
    
-   Allows for rapid restructuring of field order without repetitive actions.
    
-   Reduces misclicks and speeds up the editing workflow.
    

### Built-in Module Validation Integration

MagicAppBuilder now integrates validator execution directly into **Bootstrap-based auto-generated modules** (e.g., Admin, Module, Group Module, etc.).

-   Modules now automatically validate input length constraints.
    
-   Validation messages are displayed in the UI and mapped to the corresponding input fields.
    
-   If a validator class does not exist, the operation continues without interruption.
    

#### Benefits:

-   Eliminates the need to manually wire validators in custom code.
    
-   Ensures consistent validation across all data entry points.
    
-   Reduces potential bugs from missing or inconsistent validation logic.
    


### User Role Safety Fix

A safeguard has been implemented to **prevent users from deleting their own user level**.

-   When attempting to delete a role or level that is currently in use by the active user, the system now blocks the operation.
    
-   This prevents accidental loss of access, which could render the system unusable.
    

#### Benefits:

-   Protects against administrative lockout.
    
-   Ensures system access is always retained by at least one active role.
    
-   Encourages safer role and permission management workflows.
    


### UI Fixes

-   **Entity Editor Context Menu:** Fixed an issue where the context menu to select related entities was not showing properly in the entity editor.
    
-   **Modal Backdrop Cleanup:** Addressed a Bootstrap 4 issue where modal backdrops were not removed when clicking outside the modal. A general fix was applied to ensure all `.modal-backdrop` elements are cleaned up once all modals are closed, restoring `body` styles as expected.
    
-   **Entity Download Button:** Fixed an issue where downloading entity definitions from the Entity Editor failed under certain configurations.
    
-   **Create New Application Dialog:** Previously, users could fill out the form before the base data from the server had fully loaded, which caused their input to be overwritten and inconsistent. Now, the form is temporarily disabled while waiting for the server response, preventing user input from being accidentally overridden by incoming default values.


### General Bug Fixes

-   Various bugs from previous versions have been resolved to improve overall stability and consistency.
    
-   Minor issues affecting UI behavior, validation processing, and dynamic content rendering have been addressed.
    
-   Codebase has been cleaned up to reduce edge case failures and improve maintainability.


# MagicAppBuilder Version 1.5.2

## What's Changed

### Project-Based Entity Editor Data Storage

MagicAppBuilder 1.5.2 introduces a major architectural change by relocating **Entity Editor configuration data** into the project directory. This includes:

-   **Entity Designs**
    
-   **Diagram Layouts and Relationships**
    
-   **Column Templates**
    
-   **Default Column Type Settings**
    

These configurations are now stored alongside other project resources, making them part of the project’s file structure.

#### Benefits:

-   **Export/Import Friendly** – All design data is now bundled with the project. When a project is exported, these configurations are preserved and can be restored during import.
    
-   **Better Version Control** – Entity design changes can now be tracked via Git or other VCS tools as part of the project directory.
    
-   **Improved Portability** – Teams working across different environments can now safely move projects without losing editor context or metadata.
    
-   **Project Isolation** – Prevents accidental sharing of configuration between unrelated projects.
    

This change ensures that the visual and structural designs built using the Entity Editor are **portable, persistent, and tightly coupled** with the project they belong to.



### Validator UI Fix

-   Fixed a minor issue where the submit button in the **Validator Editor** was incorrectly labeled as **"Update Form"**. It now correctly displays **"Update"**, providing better clarity and consistency.
    



### Bug Fixes: Application Info Update

-   Fixed an issue where the **application info update** process incorrectly applied updates to outdated records.
    
-   Added validation to ensure that only applications created **within the last hour** are eligible for updates.
    
-   Improved query efficiency by eliminating unnecessary `SELECT` calls before `UPDATE`.
    
-   Ensured proper ownership checks to avoid cross-admin updates.


# MagicAppBuilder Version 1.6

## What's New

###  Built-in Application Updater

MagicAppBuilder 1.6 introduces a **powerful new feature**: the **automatic application updater**.

With this update, users can now seamlessly upgrade MagicAppBuilder to the latest version without needing to manually download and extract ZIP files.

#### Key Features:

-    **One-click update** from the web interface
    
-    **Automatic download** and safe extraction of updates
    
-    **Version skipping supported** – users can jump directly from older versions (e.g., 1.6) to the latest without applying intermediate updates
    
-    **Safe overwrite** – updater avoids replacing sensitive files like `.env` or custom scripts (e.g., `update.php`)
    
-    Integrated with the **Magic Admin** interface under the **"About"** menu
    

#### Requirements:

-   This feature is available starting from **MagicAppBuilder 1.6**
    
-   PHP `ZipArchive` extension must be enabled
    
-   Internet connection is required to fetch updates from GitHub
    

## Other Improvements

-   Stability and performance enhancements for background update operations
    
-   Improved modular structure to support future extensibility of the updater
    

Stay up-to-date effortlessly and enjoy the newest features and improvements with minimal effort!



# MagicAppBuilder Version 1.7.0

## What's New

### Unlimited Multi-Level Menu Support

MagicAppBuilder 1.7 introduces **full support for deeply nested navigation menus** with unlimited levels.

* You can now define menu hierarchies with unlimited depth.
* Each level is styled with Bootstrap-compatible classes.
* **Active** and **open** states are automatically applied based on the current URL.
* Collapsible behavior is handled via Bootstrap’s `collapse` class and `aria-expanded` attributes.
* Parent menus automatically expand if any of their children (or grandchildren) are active.

Menu structure is now built based on each module’s **`parentModuleId`**, replacing the old two-level `moduleGroup` limitation.

> A new default theme is included with full multi-level menu support. Older themes remain compatible but only support up to two levels.


### New Hierarchical Module Management

This release also adds support for **defining modules hierarchically**:

* You can create **empty parent modules** that serve as containers for navigation only (no implementation needed).
* The module structure now reflects the intended menu hierarchy.
* Supports hierarchical access control:

  > **A user must have access to a parent module in order to see or access any of its child modules.**

This offers better flexibility and control when building role-based menu systems.


### Application Setting Update: Multi-Level Menu Checkbox

A new checkbox setting **“Multi-Level Menu”** has been added in **Application Settings**.

-   When checked, the application will treat the module structure as hierarchical.
    
-   This determines how modules should be organized and how the menu hierarchy will be rendered.
    
-   If left unchecked, the system falls back to flat or two-level menu mode (for backward compatibility).
    

> This toggle helps ensure clarity between legacy setups and the new menu system, making module management more intuitive.


### Theme Filtering Based on Menu Type

When the **Multi-Level Menu** setting is enabled in the application:

-   Only **themes that support multi-level menus** will be available for selection.
    
-   When the setting is **disabled**, only **themes that support traditional two-level menus** will be shown.
    
-   The system automatically **filters theme options** based on each theme’s declared `multi_level_menu` capability.
    
-   The selected theme is also **automatically updated** to the first valid option to prevent UI mismatches.
    

> This ensures consistency between the selected menu structure and the theme’s capabilities, preventing any rendering issues or incompatibility.


## Improvements


-   Cleaner and more consistent DOM logic for rendering menu structures.
    
-   Improved handling of active and selected states in nested menus.
    
-   Internal refactoring for better readability and maintainability.
    
-   **Unused asset files** that were not required by the default theme have been **removed** to reduce the application’s overall bootstrap size.



## Compatibility

* Fully **backward compatible** with previous menu structures.
* Older themes still work and will continue to support two-level menus.


## Manual Migration for Older Versions

If you're upgrading from MagicAppBuilder **version 1.6 or earlier**, you **must manually add the `multi_level` column** to the `menu_cache` table in your database — **regardless of whether you intend to use the multi-level menu feature or not**.

**Migration steps:**

1.  Open the **Query** tab in MagicAppBuilder.
    
2.  Run the following SQL command:
    

```sql
ALTER TABLE menu_cache ADD COLUMN multi_level BOOLEAN DEFAULT FALSE;

```

> This modification is **required** for compatibility with MagicAppBuilder 1.7, even if you don't plan to use multi-level menus.  
> The system will automatically read and write this column during menu cache operations.


Let me know if you'd like to generate a version checker or migration assistant for smoother upgrades.

# MagicAppBuilder Version 1.7.1

## What's New

-   **Improved UI During New Application Creation** The user interface now **disables scrolling** while the "waiting screen" is active during new application creation. This enhancement provides a smoother, more perfect visual experience by preventing unwanted content movement.



# MagicAppBuilder Version 1.8.0

## What's New

-   **Automatic Parent Module Creation**  
    The system now automatically creates a parent module if one doesn't already exist.
    
    -   Parent modules are generated based on either the `parentModuleId` or the `moduleGroupId`.
        
    -   This streamlines hierarchical menu setup and enhances compatibility with multi-level navigation.
        
-   **Role Inheritance from Child to Parent**  
    You can now easily copy user role permissions from a child module to its parent module.
    
    -   This ensures consistent permission structures across nested modules.
        
    -   Only permissions explicitly set to `true` in the child role will be applied to the parent.
        
-   **Release Time Information in Release List**  
    When retrieving the list of releases, each entry now includes its release timestamp.
    
    -   This provides more detailed and informative version history.
        
-   **Diagram Tab Drag-and-Drop Sorting**  
    You can now drag and drop to reorder diagram tabs within the **Entity Editor**.
    
    -   Tabs are sorted visually via mouse drag gesture.
        
    -   `All Entities` and `+` tabs are fixed in position and cannot be moved.
        
    -   This improves flexibility and control over diagram organization.
        
-   **Customizable Language Priority**
    
    Users can now set the order of languages according to their preference, allowing for a personalized and prioritized language display within the application.
    
-   **Automatic New Diagram Naming**
    
    When creating new diagrams in the Entity Editor, the system now intelligently suggests unique names.
    
    -   If a diagram name like "New Diagram" already exists, the system will automatically append a number (e.g., "New Diagram 1", "New Diagram 2") to ensure uniqueness.
        
-   **Grouped Table List in Module Creation**  
    When creating a new module, the table list is now grouped into **System Tables** and **Custom Tables** for better clarity and organization.
    
    -   Built-in tables such as `admin`, `module`, and `notification` are listed under _System Tables_.
        
    -   User-created tables are displayed under _Custom Tables_ and appear at the top of the dropdown.
        
    -   This enhancement makes it easier to distinguish between core and user-defined database structures.
        
-   **Grouped Table List in Database Explorer Sidebar**  
    The **Database Explorer** sidebar now also groups tables by type.
    
    -   Tables are split into _Custom Tables_ and _System Tables_ sections for quicker navigation.
        
    -   This improves readability when working with large databases.

## UI Improvements

-   **Column Sorting UI Simplification**  
    The column reordering feature in the main form interface has been visually improved.
    
    -   Instead of using a black background, a minimalist ⠿ icon (three-dot vertical drag handle) is now used.
        
    -   This results in a cleaner and more modern appearance.
        
-   **Table Group Labels**  
    Visual separation between table groups is handled using labeled headings such as **Custom Tables** and **System Tables** in the UI.
    
## What's Changed

-   **New Column: `moduleGroupId` in `AppModuleMultiLevelImpl`**
    
    -   The `AppModuleMultiLevelImpl` entity now includes a `moduleGroupId` property.
        
    -   This addition enables fallback logic for parent module generation when `parentModuleId` is not defined.
        
-   **"Table List" Renamed to "Entity List" in Entity Editor**  
    The label previously shown as **"Table List"** within the Entity Editor has been renamed to **"Entity List"**.
    
    -   This change helps clarify that the list refers to entity representations in the diagram editor, not raw database tables.
        

## Bug Fixes

-   **Two-Level Menu Rendering**  
    Fixed an issue where nested menus (level 2) were not rendered properly in some navigation scenarios.
    
-   **Multi-Level Menu Theme Styling**  
    Resolved a display issue affecting the visual consistency of menus under multi-level navigation themes.  
    This ensures submenus are correctly aligned, indented, and styled across different levels.
    
-   **Entity Editor**
    Fixed a bug where text input fields became read-only as a side effect of the draggable column sorting feature introduced in the previous version.
    This ensures input fields remain fully editable and the cursor behaves as expected.



# MagicAppBuilder Version 1.9.0

## What's New

-   **Automatic Database Structure Update After File Extraction**
    
    The system now automatically updates the database structure after extracting update files if there are any changes in entity definitions. This ensures your database schema is always in sync with the latest application version without manual intervention.
    
-   **Restricted Database Explorer Features for MagicAppBuilder Database**
    
    The Import Structure and Entity Editor buttons are now hidden in the Database Explorer when you're viewing the MagicAppBuilder's internal database. This prevents unintended errors and maintains the integrity of the core system.
    
-   **"Sort Entity by Type" Button Added to Entity Editor**
    
    A new button, "Sort Entity by Type," has been added to the Entity Editor. This allows users to reorder entities by placing custom entities at the top, followed by specific system entities (e.g., admin, module, notification). Both groups are then sorted alphabetically, providing a more organized and intuitive view of your database entities.
    
-   **Duplicate Entity Prevention in Entity Editor**
    
    To enhance data integrity, MagicAppBuilder now prevents the creation or saving of duplicate entities. When attempting to create a new entity or save an existing one with a name that already exists, the system will prompt you to resolve the conflict, ensuring all entity names remain unique.
    

## Bug Fixes

-   **Entity Indexing Issues During Reordering Operation**
    
    Fixed several bugs related to incorrect entity indexing after reordering operations. The system now correctly maintains entity indices during:
    
    1.  Alphabetical sorting.
        
    2.  Grouped alphabetical sorting (Custom entities first, then System entities).
        
    3.  Manual reordering via "move up" and "move down" icons.
        
-   **Improved Entity and SQL Export for SQLite Databases** 
    
    Addressed issues with exporting entities and SQL in the Entity Editor, specifically for **SQLite databases** that do not have explicit database names and schemas. The export functionality now handles these cases correctly, ensuring successful exports regardless of the SQLite database configuration.


# MagicAppBuilder Version 1.9.1

## What's Changed

### Library Update: MagicObject 3.14.4

MagicAppBuilder has been updated to use **MagicObject version 3.14.4**.

#### Notable Improvements from MagicObject 3.14.4:

-   **Bug Fix: `numberFormat*` Methods Now Accept Single Parameter**
    
    Previously, calling methods like `numberFormatPercent(2)` would trigger warnings due to missing parameters. This issue has been fixed in MagicObject 3.14.4, enabling safe and clean formatting with a single argument:
    
    ```php
    $data = new MagicObject();
    $data->setPercent(2.123456);
    echo $data->numberFormatPercent(2); // Output: 2.12
    ```

    This improvement ensures better compatibility and fewer runtime warnings when formatting numbers dynamically within MagicAppBuilder.

This version contains no UI or functional changes beyond the library update but improves backend reliability through enhanced MagicObject behavior.


# MagicAppBuilder Version 1.9.2

## What's Changed

### Enhancement: Filter Control for `getTableList()` Method

The `AppDatabase::getTableList()` method now supports two additional parameters: `withApv` and `withTrash`.

#### New Parameters:

- `withApv` *(bool, default: false)* – If set to `true`, tables ending with `_apv` will be included.
- `withTrash` *(bool, default: false)* – If set to `true`, tables ending with `_trash` will be included.

#### Why This Matters:

Previously, the method always excluded tables that ended with `_apv` or `_trash`. Now developers have more control over filtering behavior when retrieving the list of tables and primary keys from the database.

#### Example Usage:

```php
// Fetch all tables, including `_apv` and `_trash` tables
$tables = AppDatabase::getTableList($database, $databaseName, $schemaName, true, true);

// Fetch tables excluding `_trash`, but including `_apv`
$tables = AppDatabase::getTableList($database, $databaseName, $schemaName, true, false);
```


### Feature: Table Grouping in **Database Explorer Export View**

Tables are now **visually grouped** into two categories when exporting structure and data in the **Database Explorer**:

-   **Custom Tables** – Tables specific to your application domain.
    
-   **System Tables** – Tables used internally by the platform (e.g., `admin`, `module`, `notification`, etc.).
    

Each group includes its own **checkbox controls** to bulk-select structure and/or data:

```txt
[✓] Structure   [✓] Data   Custom Tables
[ ] Structure   [ ] Data   System Tables
```

#### Benefits:

-   Simplifies table selection, especially in large databases.
    
-   Prevents accidental export of system tables.
    
-   Works seamlessly with dynamic AJAX loading (uses event delegation).


### UI Enhancements

#### `Edit Entity` Tab

-   Tables are now grouped into **Custom** and **System** categories when creating or editing entities.
    
-   Tables ending in `_apv` and `_trash` are now shown (if applicable), giving users full visibility of approval and trash tables.
    
-   Improves clarity and reduces clutter in the dropdown list of tables.

#### `Edit Validator` Tab

-   Similar grouping is applied when creating or modifying validator classes.
    
-   Tables in the dropdown are now categorized into **Custom** and **System** using visual `<optgroup>` labels.
    
-   This helps users quickly locate relevant tables when working with validator generation.
    
### Library Update: MagicObject 3.14.5

MagicAppBuilder now uses **MagicObject version 3.14.5**.

#### Key Improvements in MagicObject 3.14.5:

- **New Feature:** Support for parsing database credentials from a URL using `importFromUrl()`
  Example:

  ```php
  $url = 'mysql://user:pass@localhost:3306/mydb?schema=public&charset=utf8&timezone=Asia/Jakarta';
  $credentials = new PicoDatabaseCredentials();
  $credentials->importFromUrl($url);
  ```

- **Bug Fix:** Compatibility with **PHP 5**
  Fixed fatal error caused by default parameters with class type hint in the `validate()` method:

  Before (incompatible with PHP 5):

  ```php
  public function validate($a, $b, MagicObject $c = null, bool $d = true)
  ```

  After (PHP 5 compatible):

  ```php
  public function validate($a, $b, $c = null, $d = true)
  ```

  Ensures compatibility with older environments while retaining behavior in newer PHP versions.

### Impact Summary

-   Improves flexibility in table listing and entity generation.
    
-   Supports advanced workflows such as approval (`_apv`) and soft-deletion (`_trash`) logic.
    
-   Enhances user experience through clearer grouping and visibility in dropdowns.
    
-   Fixes compatibility issue in `MagicObject` that could affect older PHP installations.
    
-   Fully backward-compatible with previous versions.



# MagicAppBuilder Version 1.9.3

## What's Changed

### Enhancement: Improved Sortable Handler UI

The sortable handler in data tables has been updated for a cleaner and more consistent user experience.

#### Changes:

- Previously, the sortable feature used a dark-colored column background to indicate draggable areas.
- This approach has been replaced with a minimalist **⠿ (three-dot vertical)** character to clearly mark sortable rows without affecting the column layout or color scheme.

#### Benefits:

- Visually cleaner and more intuitive interface.
- Consistent across different themes or background colors.
- Easier for users to identify and interact with the sortable elements.

### Bug Fix: Multi-Level Menu Display in Development Mode

- Fixed an issue where multi-level menus were not displayed when `developmentMode=true`.
- Previously, the application would fail to show menus in development mode because the menu data did not exist in the database and was only available in `application.yml`.

#### Benefits:

- Developers can now preview and navigate full menu structures during development without needing to import menu data into the database.


# MagicAppBuilder Version 1.10.0

## What's New

### New Feature: Import Entities from Excel and CSV

* Added support for importing entity structures directly from `.xlsx`, `.xls`, and `.csv` files in the Entity Editor.
* The import feature reads column headers and sample data to automatically infer the table schema, including data types.
* When importing Excel files (`.xlsx` or `.xls`) containing multiple sheets, users will be prompted to select the desired sheet before generating the entity structure.

#### Benefits:

* Speeds up entity creation by allowing developers to generate database tables directly from spreadsheet data.
* Simplifies the conversion of structured spreadsheet content into database-ready formats using file input.


### New Feature: Export Entity Structure and Data to Database Explorer

* Entity Editor now supports exporting both structure **and data** to Database Explorer.
* Previously, only structure (table schema) could be exported.
* With this update, any data imported from Excel or CSV can also be saved into the target database as initial content.

#### Benefits:

* Enables seamless transfer from spreadsheet to fully usable database tables with content.
* Useful for developers defining default or technical data directly in the entity editor.
* Ideal for prototyping or preparing seeded database states.


## What's Changed

### Enhancement: Improved Sortable Handler UI

* Updated the drag handle in data tables to use a minimalist **⠿ (three-dot vertical)** icon.
* Replaces the old dark-colored header background style.

#### Benefits:

* Cleaner, lighter, and more consistent interface.
* Clearer identification of draggable areas without altering column appearance.


### Enhancement: Truncated Entity Names in SVG View

* Long entity names in the Entity Editor diagram are now truncated using `text-overflow: ellipsis`.
* Displayed via SVG `<foreignObject>` to prevent visual overflow or overlap.

#### Benefits:

* Maintains clarity and alignment of action icons (edit, delete, move).
* Looks more polished, especially for autogenerated or verbose entity names.


### Bug Fix: Multi-Level Menu Display in Development Mode

* Fixed issue where multi-level menus were not displayed when `developmentMode=true`.
* Previously, menu data only defined in `application.yml` was ignored.

#### Benefits:

* Developers can now preview full menu hierarchies during development without importing data manually into the database.


## Library Update: MagicObject 3.14.5

### Enhancement: Flexible Nested Retrieval in `retrieve()` Method

* The `retrieve(...$keys)` method now accepts:

  * Dot notation: `$obj->retrieve('user.profile.name')`
  * Arrow notation: `$obj->retrieve('user->profile->name')`
  * Multiple arguments: `$obj->retrieve('user', 'profile', 'name')`
* Automatically camelizes each key for consistent access.

#### Benefit:

* Simplifies deep property access with versatile syntax.


### Improvement: `@TimeRange` Validator Now Accepts `HH:MM` and `HH:MM:SS`

* Supports time formats in both `HH:MM` and `HH:MM:SS`.
* Input is normalized to `HH:MM:SS` for comparison.

#### Benefit:

* Adds flexibility while maintaining second-level precision.



# MagicAppBuilder Version 1.10.1

## What's Fixed

### Bug Fix: Database Download Issue

* Fixed an issue where downloading the database from MagicAppBuilder would fail or return incomplete data.

#### Benefits:

* Ensures reliable and consistent export of database contents for backup, migration, or inspection purposes.

### Bug Fix: Permission Enforcement for Superuser

* Improved permission handling to ensure that **only administrators with `superuser` level** can access MagicAppBuilder and all of its features.

#### Benefits:

* Prevents unauthorized access to core application builder tools.
* Strengthens security in multi-role environments where admin access is scoped.


# MagicAppBuilder Version 1.11.0

## What's New

### New Feature: Import SQLite Database in **Database Explorer**

* You can now import SQLite `.db` or `.sqlite` files directly in the **Database Explorer** during structure import.
* The schema is automatically extracted and transformed into internal table definitions.

### New Feature: Import SQLite Database in **Entity Editor**

* The **Entity Editor** now supports importing table structures directly from SQLite database files.
* Table definitions are read and converted into entities compatible with MagicAppBuilder.

### Enhancement: Permission Enforcement for Superuser

* Only administrators with the `superuser` level are allowed to access all features in MagicAppBuilder.
* This enhances security in multi-role admin environments and protects core application builder features.

### Enhancement: Preserve Data When Renaming Columns in Entity Editor

* When renaming a column in the **Entity Editor**, the data stored in that column will now be preserved.
* Previously, renaming a column could result in loss of existing data tied to the old column name.

#### Benefits:

* Simplifies migration from SQLite-based prototypes or legacy systems.
* Speeds up development by eliminating the need for manual schema conversion.
* Prevents unauthorized access to sensitive builder features.
* Enforces stricter access control in admin environments.
* Safer structure editing in the **Entity Editor**.
* Reduces accidental data loss when making adjustments to entity definitions.
* Improves reliability when working with test data or sample imports.

## What's Fixed

### Bug Fix: Database Download Issue

* Fixed an issue where downloading the database from MagicAppBuilder would fail or return incomplete data.

### Bug Fix: Data Formatting Based on Nullable Columns

* Resolved an issue where data was not formatted correctly based on the `nullable` property of columns.
* Now ensures:

  * `0` for numeric types,
  * `'0'` or `'false'` for boolean types,
  * `''` (empty string) for text types
    when a column is marked as `NOT NULL`.

### General Bug Fixes

* Minor internal improvements and bug fixes related to file parsing and structure rendering.

## Structure & Data Import Support (as of Version 1.11.0)

Starting from this version, **MagicAppBuilder supports importing data alongside table structures**.
When importing SQL files that contain `INSERT INTO` statements, the data will be parsed and attached to the corresponding entities—ready for insertion into the target database.

**Supported import sources for structure and data:**

1. **SQL Files**

   * Supported dialects: MySQL, MariaDB, PostgreSQL, SQLite, SQL Server
   * Includes `CREATE TABLE` and `INSERT INTO` statements

2. **SQLite Database Files (`.db`, `.sqlite`)**

   * Supports full schema and data import from binary SQLite database files.

3. **CSV Files**

   * Column headers are auto-mapped, and data types are inferred from sample values.

4. **Excel Files (`.xlsx`, `.xls`)**

   * Each sheet is treated as a table (entity), with automatic column type detection.

Berikut adalah versi final dari changelog **MagicAppBuilder Version 1.11.1** yang sudah ditambahkan fitur **Export Database to Excel**:



# MagicAppBuilder Version 1.11.1

## What's New

### Entity Metadata Support

* Each entity now stores additional metadata to improve traceability and auditing:

  * **Description** – A short description of the entity's purpose.
  * **Created At** – Timestamp of when the entity was created.
  * **Updated At** – Timestamp of the most recent modification.
  * **Created By** – Administrator who initially created the entity.
  * **Updated By** – Administrator who last modified the entity.

This metadata is visible in both the **Entity Editor** and exported files, providing better context and history tracking.

### Markdown Documentation Export

* You can now **export entity structure and metadata as a Markdown document**.
* The exported file contains:

  * Entity names
  * List of columns with types and constraints (e.g., nullable, primary key)
  * Descriptions (if available)

This feature is especially useful during development, team collaboration, and long-term project maintenance.

### Excel Export for Database Content

* A new feature has been added to **export the actual database content to an Excel (`.xlsx`) file**.
* Each table is stored as a separate sheet in the exported file.
* Column types are inferred and formatted to match Excel types (e.g., text, number, date).
* Supports multiple databases: **MySQL**, **PostgreSQL**, **SQLite**, and **SQL Server**.

This enables easier reporting, data sharing, and external data review workflows.

## What's Changed

### Export Entity Uses Server-Stored Definition

* Exporting entities now **downloads the JSON directly from the server**, instead of building the data from the in-memory editor state.
* This ensures that exported files always reflect the **latest saved version**, guaranteeing consistency and preventing accidental mismatches between edited and saved data.

## Improvements

### UI Enhancement in Entity Editor

* The icon size in the **Diagram** tab has been slightly adjusted for better alignment and visual consistency with the rest of the interface.

### Improved Developer Experience in Validator Classes

* Added **class-level docblocks** to validator classes to clearly list:

  * Which input properties are validated.
  * What validation rules are applied to each property (e.g., `@Required`, `@Email`, `@Min`, etc.).
* These annotations can now be seen **directly from IDE tooltips or documentation panels**, helping developers understand validation logic without opening the full file.
* Enables **faster development and easier debugging**, especially when integrating validation into controllers or services.

## Bug Fixes

### PHP 5 Compatibility for ValidatorUtil

* Fixed arrow function usage in `ValidatorUtil` that caused fatal errors on PHP 5.
* Rewritten using traditional anonymous functions to ensure compatibility with older PHP environments.
* Ensures smoother operation for legacy systems still running PHP 5.x.


# MagicAppBuilder Version 1.12.0

MagicAppBuilder has just rolled out version 1.12.0, packed with features and improvements designed to give you more control, better performance, and enhanced data safety.


## What's New

### Data Restoration Feature

Say goodbye to accidental data loss! MagicAppBuilder now includes a **robust data restoration feature** that lets you recover deleted information from your trash tables. This significantly boosts your data recovery capabilities, traceability, and overall operational safety.

This feature comes in two key parts:

#### 1. Application Options – Data Restoration Setup

You'll find a new "Data Restoration" section in each application's **Application Options** panel. MagicAppBuilder intelligently detects which of your tables support trash functionality. From here, you can:

* See a list of all tables that have trash enabled.
* **Select which specific tables** should support data restoration.
* Automatically generate the necessary **Primary Entities** and **Trash Entities** for seamless restoration.

#### 2. Data Restoration Module in Generated Applications

Every application you generate with MagicAppBuilder will now have a built-in **Data Restoration module**. This intuitive interface empowers users to:

* View a comprehensive list of entities eligible for restoration.
* Easily browse and search through soft-deleted (trashed) records.
* **Restore selected entries** back to their original tables with ease.

This complete solution provides an intuitive way to manage soft-deleted records, drastically improving **data safety, recovery, and auditability**.


### Entity Data Import/Export in Entity Editor

The Entity Editor now offers powerful new tools for managing your entity data, significantly enhancing the entity definition phase. Users can now easily **export and import entity data** to streamline workflows and improve reusability.

* **Export Entity Data**: You can now export the data currently in your Entity Editor's table to a **JSON file**. This includes data imported from SQL, SQLite database files, Excel, CSV, or even data you've entered manually. This feature is invaluable for saving your progress, creating templates, or sharing data with other entities that have similar column structures.
* **Import Entity Data**: Seamlessly import data back into your Entity Editor from a **JSON file**. This allows you to quickly load previously exported data, or data prepared externally, directly into your entity's table for further editing or definition.

These features are particularly beneficial during the entity definition phase, allowing users to export data for later re-import or for use with other entities that share common columns.


## Improvements

### Optimized AJAX Request Handling

We've made significant under-the-hood improvements to make your UI feel even snappier. AJAX requests for **filter operations** and **pagination** are now highly optimized. This means:

* Data filtering is **faster** and uses fewer resources.
* Navigating through lists loads **more efficiently**, even with large datasets.

These enhancements work to reduce your server load and provide a smoother user experience.


## UX Enhancements

### Confirmation Dialogs for All Form Actions

To prevent accidental changes, all sensitive actions initiated from forms now trigger **clear confirmation dialogs**. This includes actions like:

* **Activate**
* **Deactivate**
* **Delete**
* **Sort**
* **Approve**
* **Reject**

You'll always be warned before performing critical operations, making interactions clearer and reducing errors.


## Access Control

### New Permission: `allowedRestore`

We've introduced a new permission, `allowed_restore`, to give you granular control over data restoration.

* A dedicated `allowed_restore` column has been added to the `admin_role` table.
* Users **must have this permission explicitly enabled** to perform any data restoration actions.
* This ensures a clear separation of privileges: users with access to the Data Restoration module can *view* deleted/restored entries, but they **cannot restore data** unless `allowed_restore` is enabled for their role.

This flexibility allows administrators to grant view-only access for auditing purposes, while tightly restricting actual data recovery to authorized personnel.


### Bug Fixes

We've also squashed a bug related to importing entities from spreadsheets. Specifically, an issue where multiple consecutive underscores (`__`) were not correctly converted to single underscores has now been **resolved**, ensuring cleaner entity and column names.

Additionally, a bug that prevented columns from being correctly renamed *before* saving an entity during the spreadsheet import process has been fixed. This ensures your imported data's structure is accurately preserved.

Furthermore, we've fixed an issue with **column type inference** during data import from **Excel and CSV files**. The system now more accurately determines the appropriate column type based on the actual data content, leading to more reliable schema generation and data handling.

Lastly, we've resolved a bug causing **NaN (Not a Number) width values** in the entity renderer, ensuring proper display and layout of entities.


# MagicAppBuilder Version 1.13.0

Version 1.13.0 brings an exciting productivity enhancement that makes defining new entities faster and more intuitive — especially when working with existing table data from other sources.

## What's New

### Create Entities from Clipboard

Defining entities has never been easier! MagicAppBuilder now supports **pasting tabular data directly from your clipboard** into the Entity Editor — whether it comes from **Excel, Word, or tables on the web**.

When you paste this data:

* MagicAppBuilder will **automatically parse the content** into rows and columns.
* A new **entity will be created on the fly**, with column names derived from the first row.
* All table data will be instantly available for editing or saving — no manual setup required.

This feature drastically improves speed during prototyping and data modeling. It’s perfect for:

* Quickly testing ideas based on real-world data.
* Copying small datasets from spreadsheets or reports into your application.
* Reusing structured data from external tools and websites.

Combined with the **Entity Data Export/Import** feature introduced in version 1.12.0, this offers a complete, frictionless workflow for managing external tabular data.

### Permanently Delete Trash Data

You can now **permanently delete** entries from trash tables. This feature allows full cleanup of previously "soft-deleted" records that are no longer needed — ensuring better database hygiene and reducing unnecessary data retention.

Just select the data and choose the **Delete Permanently** option. A confirmation dialog will prevent accidental removal.

## Improvements

### Improved Snake Case Conversion During Data Import

We've improved how column names are converted to `snake_case` when importing data from **Excel**, **CSV**, **SQLite Binary Files**, or **SQL**:

* Before applying the snake case transformation, MagicAppBuilder now performs an **uppercase-word normalization step**.
* This prevents unwanted results when working with abbreviations or acronyms.

For example:

* `"GPIO"` is now correctly converted to `gpio` instead of `g_p_i_o`.
* `"DeviceID"` becomes `device_id` as expected.

This enhancement ensures cleaner, more predictable column names during schema generation — especially when dealing with technical or acronym-heavy datasets.

### Button Caption Update: "Import Sheet" → "Import Spreadsheet"

To improve clarity and consistency, the button previously labeled **"Import Sheet"** has been renamed to **"Import Spreadsheet"**.

This small but meaningful change helps better communicate the supported file types (Excel, CSV, etc.) and aligns with common terminology used by spreadsheet users.

### Button Caption Update: "Append Entity from SQL" → "Append Entity"

To reduce visual clutter and accommodate layout constraints, the button previously labeled **"Append Entity from SQL"** is now simply called **"Append Entity"**.

This change maintains clarity while keeping the UI clean and space-efficient.

### Localization Language Refinements

Improved the **phrasing and word choice** across multiple confirmation dialogs and messages throughout the application to ensure greater clarity and consistency. These changes enhance the overall user experience, especially in multilingual environments.

## Dependency Update

### MagicObject Library

MagicAppBuilder now includes MagicObject **version 3.16.0**, which brings several improvements and bug fixes:

* **New:** `deleteRecordByPrimaryKey($primaryKeyValue)` method for deleting records using primary or composite keys.
* **Fix:** Session handler compatibility with **Redis**, ensuring reliable session persistence when using `session.save_handler = redis`.


# MagicAppBuilder Version 1.14.0

Version 1.14.0 introduces powerful enhancements to your data modeling workflow — with a focus on interoperability and documentation. This update builds upon the clipboard and import/export improvements from the previous release.

## What's New

### Export Entities as Interactive HTML Document

You can now **export your entity definitions and ER diagrams** into a single, self-contained HTML file.

This export includes:

* A **visual diagram** showing the relationships between entities (ERD).
* A **table-based overview** of each entity's fields, types, and attributes.
* **Descriptions** for both entities and columns — clearly displayed for documentation purposes.
* Clean, printable formatting — perfect for **documentation**, **reviews**, or **project handovers**.

This feature makes it easier than ever to share and archive your data models with teams, stakeholders, or clients.

### Entity Description Field

You can now add a **description** to each entity.

* This field serves as a brief explanation of the entity's purpose or usage.
* The description is included in the **HTML export**, displayed alongside the entity name.

Use this field to improve the clarity and maintainability of your data model — especially when collaborating with others.

## Changes

### CSV Format for Entity Data Export

Entity data is now exported in **CSV format** instead of JSON.

This change improves **compatibility with spreadsheets**, databases, and other development tools. It also simplifies manual edits during early development or prototyping.

CSV format ensures that your exported data can be:

* Quickly reviewed or edited in **Excel**, **Google Sheets**, or other tools.
* Easily imported into relational databases and code generators.

### CSV Format for Entity Data Import

In alignment with the new export format, **entity data import now also uses CSV**.

This means:

* You can **round-trip** data between export and import without conversion.
* JSON format is no longer supported for import.

Make sure your CSV files follow the same structure as exported files, with headers matching column names.

### Import Behavior for SQL and Spreadsheet Data

The import behavior for SQL and spreadsheet (Excel/CSV) data has been updated.

* The `clearBeforeImport` flag for `importSQL()` and `importSheet()` methods is now set to `false`.
* This means that when importing SQL or spreadsheet data, **existing entities will no longer be automatically cleared** from the editor. New data will be added alongside existing content, allowing for incremental imports or merging of data.

### Improved Foreign Key Detection in ERD

The logic in the `createRelationships()` method has been updated to improve foreign key detection.

Previously, columns ending with `_id` were only considered foreign keys **if they were not primary keys**. Now, **all `_id` columns are treated as potential foreign keys**, even if they are also primary keys.

This change ensures more accurate and complete relationship diagrams, especially in schemas where foreign keys double as primary keys.

### UI Style Improvements

* **Improved styling for multiple input fields** (e.g., arrays or repeatable values):

  * Uses consistent grid layout for better alignment between labels and inputs.
  * Input groups now include clean add/remove buttons with intuitive spacing.
  * Compatible with dynamic form generation and editing.
  * Improves readability and usability across all generated apps.

### Internal Dependency Update

* **Upgraded MagicObject to version 3.16.2**, which includes:

  * Fix for `session_start()` warnings during session handling.
  * Improved compatibility with SQLite in `countAll()` and `countBy()` methods (from version 3.16.1).

## Bug Fixes

* **Fixed incorrect string formatting in user-defined data format templates**:

  * The `fixFormat()` method now correctly distinguishes between literal dollar signs (e.g. `$ %s`) and variable references (e.g. `$appLanguage->getCurrencyFormat()`).
  * Formats starting with invalid variable characters after `$` are no longer treated as variables.
  * Ensures consistent behavior when formatting string and numeric outputs.

* **Resolved minor inconsistencies in CSV column typing during export/import**, especially for boolean and date types.

* **Fixed incorrect SameSite attribute behavior in generated apps:**

  * The session configuration key `cookieSamesite` has been corrected to `cookieSameSite`, matching the proper casing.
  * The `SameSite` cookie attribute is now correctly set, improving security and cross-browser compatibility.

## New Configuration Options

### Custom Session and Cookie Settings

MagicAppBuilder now support **customizable session and cookie settings** through a structured `sessions` configuration.

You can define:

* `name`: Custom session name.
* `maxLifeTime`: Session maximum lifetime (in seconds).
* `saveHandler`: Session storage method (e.g., `files`, `redis`).
* `savePath`: Path or location for storing session data.
* `cookiePath`: The path scope for the session cookie.
* `cookieDomain`: The domain for which the cookie is valid.
* `cookieSecure`: Whether the cookie should only be sent over HTTPS.
* `cookieHttpOnly`: Whether the cookie is inaccessible to JavaScript.
* `cookieSameSite`: Cross-origin policy for the cookie (`Strict`, `Lax`, or `None`).

This addition gives developers full control over session behavior in MagicAppBuilder — improving security, compatibility, and flexibility across deployment environments.

To modify these settings, open the `core.yml` file located in the `inc.cfg` directory under the document root of your MagicAppBuilder installation.


## Summary

MagicAppBuilder 1.14.0 completes the shift to a more open, editable, and shareable data format. Combined with HTML documentation export and clipboard import from version 1.13.0, this release brings even more flexibility to your entity design and data integration workflows.


# MagicAppBuilder Version 1.15.0

Version 1.15.0 introduces additional flexibility in how application icons are managed, making it easier to work with scalable vector formats. This update also brings a significant enhancement to the clipboard import functionality, improved data validation in the Entity Editor, and a new feature for managing your projects. Overall, this release focuses on **enhancing visual customization, compatibility**, and **data consistency** in your generated applications.


## What's New

### Support for SVG Icons

You can now upload **SVG files** as application icons in addition to PNG files.

* SVGs offer **resolution-independent quality**, making them ideal for responsive UIs and high-DPI displays.
* The icon uploader now accepts both `.svg` and `.png` file formats.
* Internally, SVG files are sanitized and rendered consistently across all supported browsers.

This change gives developers and designers more freedom in customizing the appearance of apps built with MagicAppBuilder.


### Enhanced Clipboard Import

The **Entity Editor** now includes a smarter import feature. When a user pastes content that is not a standard HTML table (e.g., data copied directly from a spreadsheet or document), the editor will attempt to convert the clipboard content to HTML and automatically parse the first table it finds. This enhancement makes the import process more resilient and user-friendly, especially when importing from applications like **Microsoft Word**, **Google Docs**, or tables on **web pages**.


### Application and Workspace Favorites

You can now mark your most-used applications and workspaces as **favorites**. This is a powerful feature for developers who manage a large number of projects, as it provides quick access to your most important items.

* **Starring** an application or workspace will place it at the very top of the list.
* Favorited items are prioritized regardless of their active status, ensuring your key projects are always easy to find.


### Duplicate Column Name Detection

To ensure **data model integrity**, the **Entity Editor** now includes built-in validation for **duplicate column names**:

* During entity creation or editing, the system automatically checks for repeated column names.
* If duplicates are detected, a warning will be shown, highlighting the problematic field.
* This helps prevent accidental overwrites and errors during code generation or database export.


## Compatibility Note

* The SVG-to-PNG fallback mechanism is retained for legacy environments or where rasterized previews are required.
* Previously uploaded PNG icons remain compatible and functional.


## Bug Fixes

* Fixed an issue where uploading invalid or corrupt image files could cause silent failures in the icon selection interface.
* Improved error handling and feedback when uploading unsupported formats.
* Fixed rendering glitch in the entity preview panel on certain browsers.

# MagicAppBuilder Version 1.15.1

MagicAppBuilder 1.15.1 introduces a critical improvement to data type handling and enhanced account security configuration support. These updates ensure more reliable cross-database compatibility and provide developers with fine-grained control over password hashing behavior.


## Type Mapping Change: `DECIMAL` → `DOUBLE` (MySQL & SQLite)

To prevent **unintended rounding** issues during cross-DBMS conversions (e.g., values like `0.99` turning into `1` when `DECIMAL(p, s)` was misinterpreted as `DECIMAL(p, 0)`), MagicAppBuilder now **maps `DECIMAL` to `DOUBLE` or `REAL`** when generating SQL for **MySQL** and **SQLite**.

### 🔧 What Changed?

**Before (≤ 1.15.0):**

```js
decimal: 'DECIMAL'
```

**Now (≥ 1.15.1):**

```js
decimal: 'DOUBLE' // for MySQL
decimal: 'REAL'   // for SQLite
```

#### MySQL Mapping Example:

```js
const DIALECT_TYPE_MAP = {
  mysql: {
    ...
    decimal: 'DOUBLE', // ← updated
    ...
  }
};
```

#### SQLite Mapping Example:

```js
const DIALECT_TYPE_MAP = {
  sqlite: {
    ...
    decimal: 'REAL', // ← updated
    ...
  }
};
```

> PostgreSQL and SQL Server mappings remain unchanged.

### Why?

In real-world migrations, missing precision/scale often led to `DECIMAL` being interpreted as an integer (`DECIMAL(p, 0)`), which silently **rounded decimal values** — a serious issue in financial or measurement contexts.

Mapping `DECIMAL` to floating-point types avoids this silent rounding behavior.

### Trade-offs

| Type              | Pros                                          | Cons                                                      |
| ----------------- | --------------------------------------------- | --------------------------------------------------------- |
| `DOUBLE` / `REAL` | No silent rounding, better for unknown scales | Can introduce binary precision issues (`0.1 + 0.2 ≠ 0.3`) |
| `DECIMAL(p, s)`   | Exact representation, ideal for money         | Must define `p` and `s` explicitly or risk rounding       |

### How to Revert to `DECIMAL`

If you need exact decimal behavior (e.g., for accounting):

* **Override the mapping manually**:

```js
const CUSTOM_DIALECT_TYPE_MAP = {
  ...DIALECT_TYPE_MAP,
  mysql: {
    ...DIALECT_TYPE_MAP.mysql,
    decimal: 'DECIMAL', // ← revert
  }
};
```

* **Define per-column types**:

```js
column.type = 'DECIMAL(10,2)';
```

### Migration Notes

* Existing schemas **are not modified** automatically.
* The new behavior only applies to **newly generated DDLs**.
* Use `ALTER TABLE` if you want to manually change existing `DECIMAL` columns to `DOUBLE`.


## New Application Config: Account Security

MagicAppBuilder 1.15.1 introduces new **account security configuration** for password hashing.

### Default Configuration

Generated applications will now include:

```yaml
accountSecurity:
    algorithm: sha1
    salt: ''
```

### Description

* **`algorithm`**: The hash algorithm used when hashing passwords (e.g., `sha1`, `sha256`, `md5`).
* **`salt`**: An optional string appended to the password before hashing to increase entropy.

### Benefits

* Developers now have **explicit control** over how passwords are hashed.
* Supporting `salt` improves resistance against rainbow table attacks.
* You can change the algorithm or salt **without modifying application logic**, just by editing config.

### Password Column Size Increased

To accommodate longer hash outputs (e.g., SHA-512, SHAKE256), the default password column length has been increased:

```diff
- password VARCHAR(100)
+ password VARCHAR(512)
```

This ensures compatibility with a wide range of algorithms and encoding formats (e.g., hexadecimal, base64).


## Summary

| Feature                             | Description                                            |
| ----------------------------------- | ------------------------------------------------------ |
| `DECIMAL` → `DOUBLE` (MySQL/SQLite) | Prevents unintended rounding during migrations         |
| `accountSecurity` config            | Improves flexibility and security of password handling |
| Password column length              | Supports long hashes by increasing size to 512 chars   |


# MagicAppBuilder Version 1.15.2

MagicAppBuilder 1.15.2 introduces two developer-focused features that improve both **user account management** and **data import flexibility** during the application development phase. These updates give developers better control over test data and streamline the development process.


## New Feature: Delete User Accounts from *Application Option*

You can now delete user accounts directly from the **Application Option** menu within the developer interface.

### What Can You Do?

* Manually delete user accounts during the development process.
* Remove dummy or test accounts without accessing the database directly.
* Avoid confusion caused by outdated or unused user entries.

### Why This Matters

During development, user accounts are often created for:

* Testing login/logout flows
* Verifying role-based access
* Simulating real-world scenarios

However, these accounts often become obsolete and clutter the environment. This feature helps developers clean up such accounts efficiently before production or between development iterations.

### Security Notes

This feature is **available only in development mode** to prevent misuse in production. Access is:

* **Hidden in production mode**
* Restricted to **developer-admin users** only


### How to Use

1. Run your application in development mode.
2. Open the **Application Option** menu.
3. Go to the **Application User** tab.
4. Select the user you want to delete.
5. Click the **Delete** button.


## New Feature: Excel File Support for Entity Editor Import

The **Entity Editor** now supports **importing data from Excel files (`.xlsx`)**, in addition to the previously supported CSV format.

### What Changed?

**Before (≤ 1.15.1):**

* Only CSV files could be imported into the Entity Editor.

**Now (≥ 1.15.2):**

* You can import both CSV and Excel (`.xlsx`) files.

### Benefits

* Simplifies data migration from spreadsheets.
* Reduces the need to convert Excel files to CSV manually.
* Supports better formatting and column alignment from Excel data.


### How to Use

1. Open the **Entity Editor** for a specific entity.
2. Click the **Edit Entity** icon.
3. Click the **Data** button.
4. Choose an `.xlsx` or `.csv` file to import.
5. If the file contains multiple sheets, the Entity Editor will prompt you to select the sheet to import.
6. Preview the data and confirm the import.
7. Click the **Save** button to save the data.


## Summary

| Feature                     | Description                                                              |
| --------------------------- | ------------------------------------------------------------------------ |
| Delete user accounts        | Allows manual removal of test/dummy users during development             |
| Development mode only       | Feature is disabled and hidden in production mode                        |
| Excel file import           | Entity Editor now supports importing `.xlsx` files in addition to `.csv` |
| Improves data hygiene       | Keeps development environment clean and reduces clutter                  |
| Enhances import flexibility | Makes data import more convenient and compatible with Excel workflows    |



# MagicAppBuilder Version 1.15.3

MagicAppBuilder 1.15.3 introduces a small but helpful enhancement to improve visibility into application updates for developers, along with an important update to module access control.


## New Feature: Preview Release Notes from the Admin Panel

You can now **preview release notes directly from the Application Option** menu. This helps developers and testers quickly verify what changes are included in each release version, without leaving the development interface.

### What Can You Do?

* View formatted release notes in a modal popup.
* See changelogs associated with specific version tags.
* Use it as a quick reference for recent updates or regression checks.

### Where It Appears

* Go to the **Application Option** menu.
* Select the **Release Note** tab.
* Click on a version from the list to preview its release content.

### Benefits

* Makes release tracking easier during development and testing.
* Helps teams stay aligned on changes in each version.
* Reduces the need to open external changelog files.


## Access Control Update: Data Restoration Module

In this release, the **Data Restoration module is no longer treated as a "special access" module**.

### Why This Matters

* Previously, the module required special access privileges, limiting its availability even to users who had been explicitly granted permission to restore data from the recycle bin.
* Starting in **version 1.15.3**, it behaves like a standard module: access is controlled solely through role-based permissions.

### Clarification

* **Entity Deletion in Entity Editor**
  Fixed an issue where the Entity Editor would leave one undeletable entity even after multiple deletions.
  Users can now successfully delete **all entities** from the Entity Editor without any leftover entries.

* **Checkbox Visibility in Data Restoration Module**
  Fixed a bug where the **checkbox for selecting records** in the Data Restoration module did not appear
  when the user had permission to permanently delete data but **did not have permission to restore**.
  The checkbox now appears correctly as long as the user has **any applicable permission** for the selected operation.



# MagicAppBuilder Version 1.15.4

MagicAppBuilder 1.15.4 delivers several important improvements to the **Entity Editor**, making entity management more intelligent and schema generation more reliable. This version introduces enhanced handling of default values, full support for composite primary keys, a new system for determining entity creation order based on dependencies, and user-friendly autocomplete features in the module generator.


## Enhancement: Use Default Values in Insert Statements

Starting from this version, the **Entity Editor now automatically skips columns with default values** when generating SQL `INSERT` statements, if those fields are left empty.

This behavior helps avoid explicitly inserting values like `null` or `''` for columns that already have a `DEFAULT` clause defined in the database schema.

### How It Works

* When generating an `INSERT` statement:

  * Columns with empty values and a defined `DEFAULT` in the database will be **excluded** from the statement.
  * This allows the database engine to apply the default value automatically.

### Benefits

* Makes insert statements cleaner and more maintainable.
* Prevents accidental overrides of default values with empty data.
* Ensures behavior is consistent with the actual database schema.
* Reduces manual editing when generating sample data or migrating entities.


## Bug Fix: Support for Composite Primary Keys

Previously, the **Entity Editor** did not support entities with **composite primary keys**—tables with two or more columns as the primary key. This prevented users from editing or inserting data into such tables.

Starting in version 1.15.4, this limitation has been addressed.

### What’s Fixed?

* The Entity Editor now recognizes and supports **multiple-column primary keys**.
* You can edit, insert, and delete records from tables that use composite keys.
* Generated `WHERE` clauses for updates and deletions now include **all key columns**.

### Benefits

* Unlocks full support for more complex table structures.
* Ensures better compatibility with legacy databases and normalized schemas.
* Reduces manual intervention when working with composite keys.


## Bug Fixes: Default Value Generation in Entity and Schema

This version also fixes several issues related to default value handling during entity definition and SQL generation.

### What’s Fixed?

* Default values defined in the **Entity Editor** are now correctly reflected in:

  * Generated `CREATE TABLE` statements.
  * Generated `ALTER TABLE` statements.
* Default values are no longer omitted or malformed in generated SQL.
* Support for various dialects (MySQL, PostgreSQL, SQLite, SQL Server) has been refined to properly handle dialect-specific default expressions.

### Benefits

* Ensures consistency between entity definitions and generated SQL schemas.
* Reduces the need for manual fixes after exporting or syncing schema.
* Improves the reliability of database migrations and updates.


## New Feature: Automatic Entity Ordering Based on Dependency Depth

To simplify the process of creating application modules, the Entity Editor now **automatically assigns a dependency depth to each entity**, allowing developers to **generate or scaffold modules in the correct order**.

### How It Works

* Each entity is assigned a **depth value** based on its dependencies (e.g., foreign key relationships).
* Entities with **no dependencies** receive the **lowest depth** and should be created first.
* Entities that **depend on others** receive **higher depth values**, ensuring proper creation order.

### Benefits

* Prevents foreign key violations during schema creation.
* Ensures a **topologically sorted** order for module generation.
* Simplifies automation, especially in large schemas with complex relationships.

### Example

| Entity Name | Dependency Depth |
| ----------- | ---------------- |
| Artist      | 1                |
| Album       | 2                |
| Track       | 3                |

> In this example, the system will generate `Artist` before `Album`, and `Album` before `Track`.


## Enhancement: Autocomplete for Filter and Order Setup

To improve usability and reduce input errors, the **main module creation form** now includes **autocomplete support** when users define filters and sort orders.

### How It Works

* When configuring filter and order fields:

  * The input boxes for column names now provide **autocomplete suggestions** based on the table’s columns.
  * Suggestions are automatically populated, helping users avoid typos and invalid column names.

### Benefits

* Speeds up the configuration of data list views.
* Minimizes errors from manual typing of column names.
* Improves the overall experience when building and customizing modules.

# MagicAppBuilder Version 1.16.0

## Upgrade: MagicObject Updated to Version 3.16.4

This release includes an upgrade to **MagicObject 3.16.4**, bringing with it support for the new `textequals` filter type—allowing developers to define **exact string match** conditions instead of the usual `LIKE`-based search.

### What’s New in MagicObject 3.16.4?

* Introduced support for `textequals` in `PicoSpecification`.
* Exact match filters generate SQL using `=` instead of `LIKE`.
* Indexes can now be leveraged more effectively for string filters.

### Example Usage

```php
$specMap = array(
    "artistId" => PicoSpecification::filter("artistId", "number"),
    "genreId" => PicoSpecification::filter("genreId", "textequals")
);
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
```

Generates this SQL:

```sql
WHERE genre_id = 'Jazz'
```

Instead of:

```sql
WHERE LOWER(genre_id) LIKE '%jazz%'
```

### Benefits

* **More precise** filtering for string fields.
* **Better performance** due to index usage on exact matches.
* **Greater control** over search behavior in generated modules.


## Additional Notes

* Backward compatible with all existing entities and modules.
* Existing modules will continue using `text` filters unless explicitly changed to `textequals`.
* When creating a module, developers can **check the `EX` (Exact Match)** checkbox in the **Filter Element** section.

  * If checked, the generated filter will perform an **exact match** in the database using `=`.
  * Input must **exactly match** the stored value (including case sensitivity).
  * Partial matches (e.g., using only a part of a name or word) will no longer work.
  * This is ideal for filtering by codes, IDs, or values where precision is critical.
  * **Index optimization** leads to better performance on large datasets.


# MagicAppBuilder Version 1.16.1

## Cleanup: Removal of Unused Files

This version focuses on internal housekeeping to improve maintainability and reduce project bloat.

### What's Changed

* Removed legacy and unused files from the codebase.
* Cleaned up redundant assets and outdated components.
* Streamlined the project structure for improved efficiency.

> There are no functional changes in this release. All existing modules and features remain unaffected.


## Enhancement: Autocomplete for Date Format Input

To help developers define consistent and correct date/time formats when creating a module, the **Date Format** input field now supports **autocomplete** via a `<datalist>`. This feature:

* Provides a list of commonly used date and datetime formats.
* Displays preview values (e.g., `d/m/Y H:i (30/07/2025 15:30)`) while storing only the raw format (e.g., `d/m/Y H:i`).
* Reduces errors and speeds up module creation by offering ready-to-use formats.
* Supports ISO, Indonesian, and international styles.

This enhancement is available in the **Generate Module** under the **Date Format** input.


## Enhancement: Sidebar Menu Search

Generated applications now include a **search tool for the sidebar menu**, making navigation faster and more efficient, especially in apps with many modules.

### Key Highlights

* Users can **type any part of a menu label** to filter and find matching links.
* Only menu items with valid links (i.e., not starting with `#`) are included in the search results.
* Matching items are displayed in a separate panel without modifying the existing sidebar.
* The original sidebar menu is **automatically hidden** while searching and shown again when the input is cleared.
* Results are displayed in a **flat list** for quick access.

This search tool is especially helpful for administrators or developers managing large applications with many configuration or data modules.


## Bug Fix: Export Database to Excel

Fixed an issue where **columns with empty values or mixed data types** could result in incorrect cell formats or misaligned data in the exported Excel file. The export engine now:

* Properly detects and formats columns even when initial rows contain null or blank values.
* Applies consistent column typing (e.g., string, number, date) based on full data inspection.
* Improves compatibility with spreadsheet applications such as Microsoft Excel and LibreOffice Calc.

This fix ensures more reliable data exports across all supported database dialects (MySQL, PostgreSQL, SQLite, SQL Server).



# MagicAppBuilder Version 1.17.0

## Enhancement: Context Menu for Entity Editor

The **Entity Editor** now includes a powerful and interactive **context menu**, allowing users to quickly access essential actions with a right-click on any entity (table) in the diagram.

### New Context Menu Features

* **Export diagram to SVG** — Save the current diagram as an SVG image.
* **Export diagram to PNG** — Download a raster image version of the diagram.
* **Export diagram to Markdown** — Download a Markdown document representing the entity diagram and structure.
* **Copy table structure to clipboard** — Copies the SQL structure of the selected table.
* **Copy table data to clipboard** — Copies the data (rows) of the selected table.
* **Copy table structure and data to clipboard** — Copies both structure and contents in SQL format.
* **Create Entity Relation** — A submenu appears when hovering over the relation menu item, allowing users to define entity relationships directly from the context menu.

### Key Highlights

* The menu only appears on right-click over entities in the diagram.
* The relation submenu dynamically positions itself to the left or right, based on cursor position.
* If no available relations exist, the **“Create Entity Relation”** item is automatically hidden.
* Improves UX for large diagrams by reducing the need to scroll or open side panels.

This feature significantly improves workflow for database designers working within the visual editor.


## UI Enhancement: Visual Effects on Export and Clipboard Actions

To improve user experience, MagicAppBuilder now includes subtle **visual feedback** when performing context menu actions in the Entity Editor.

### Visual Effects Added

* Flash highlight + toast message when:

  * Exporting diagram to **SVG**
  * Exporting diagram to **PNG**
  * Exporting diagram to **Markdown**
* Animated tooltip with checkmark when:

  * Copying **table structure** to clipboard
  * Copying **table data** to clipboard
  * Copying **structure and data** to clipboard

These enhancements make it easier for users to confirm actions, especially when working with large diagrams or complex entity structures.

## Enhancement: Split SQL INSERT into Multiple Statements

The `toSQLInsert(dialect, maxRow)` function now supports an additional `maxRow` parameter that controls the **maximum number of rows per generated INSERT statement**.

### Key Details

* If the input data contains many rows, the function automatically splits it into multiple `INSERT` statements, each containing up to `maxRow` rows.
* This is useful for:
  * Improving compatibility with database engines that limit the number of rows per statement.
  * Enhancing readability or debugging of generated SQL scripts.
* Default value for `maxRow` is 100.

This enhancement improves flexibility and control when exporting or generating bulk SQL inserts.

## Enhancement: Fix Date/Time Format in Entity Data Editor

A new utility function has been added to the **Entity Data Editor** that helps normalize various date/time formats imported from different sources such as Excel, Access, Word, and web forms.

### Key Details

* Allows you to **convert inconsistent or non-standard date/time strings** into proper MySQL-compatible formats.
* Supports formatting as:

  * `YYYY-MM-DD` (date only)
  * `HH:mm:ss` (time only)
  * `YYYY-MM-DD HH:mm:ss` (full datetime)
* Useful for cleaning data before exporting or saving to the database.
* Works directly on the **data preview table**, allowing instant inline updates.

This enhancement is especially helpful when working with imported datasets from varied sources that use different date or time formats.

## Enhancement: Import Spreadsheet from DBF and ODS Files

The **Entity Editor** now supports importing entity definitions and data directly from **`.dbf`** (DBase) and **`.ods`** (OpenDocument Spreadsheet) files in addition to existing spreadsheet formats.

### Supported File Formats for Import

* `.xls` — Microsoft Excel 97-2003
* `.xlsx` — Microsoft Excel (modern format)
* `.csv` — Comma-separated values
* `.dbf` — DBase database files (e.g., dBase III/IV, FoxPro, Visual FoxPro)
* `.ods` — OpenDocument Spreadsheet (used by LibreOffice, OpenOffice)

### Key Features

* Automatic parsing of `.dbf` and `.ods` files using built-in parsers.
* Field headers and records are extracted and transformed into editable entity definitions.
* No third-party dependencies are required for `.dbf`, while `.ods` support uses a lightweight built-in parser or conversion via `SheetJS` fallback (if available).
* Seamless integration into the existing `importSheetFile` mechanism in the Entity Editor.

### How It Works

When a `.dbf` or `.ods` file is uploaded via the import menu:

1. The system reads the file as an `ArrayBuffer` or text content.
2. Based on the file extension:

   * `.dbf` → Uses `DBFParser` class to extract headers and records.
   * `.ods` → Uses an internal parser that reads XML content (`content.xml`) inside the ODS zip archive, or falls back to `SheetJS` if enabled.
3. The parsed table structure and data are displayed for preview and editing.

### Benefits

* Allows importing data from **LibreOffice / OpenOffice** `.ods` files.
* Enables smooth migration from **open standard** formats and **legacy desktop systems** into MagicAppBuilder.
* Expands compatibility with a wide range of spreadsheet tools.

## Bug Fix: Auto Increment Checkbox on Integer Primary Keys

Previously, when editing an existing entity, the **auto increment** checkbox was incorrectly disabled for primary key columns of type `INTEGER`.

### Problem

* When loading an existing entity definition:

  * If a column was marked as **primary key** and had data type `INTEGER`, the **Auto Increment** checkbox was disabled.
  * This prevented users from enabling or reviewing auto-increment behavior on numeric IDs.

### Resolution

This issue has been fixed in **version 1.17.0**.

* The **Auto Increment** checkbox is now properly **enabled** for columns that:

  * Are marked as **primary key**, and
  * Have the data type **`INTEGER`**, **`INT`**, or equivalent numeric types (e.g., `BIGINT` in supported dialects).
* The checkbox state is correctly preserved when loading entity metadata.

### Impact

* Users can now:

  * Enable or disable auto increment behavior on numeric primary key columns.
  * Update legacy entity definitions without restriction.
* Ensures consistent behavior with common database schema design patterns.


## Bug Fix: Cookie Lifetime Configuration

Previously, the **cookie lifetime** setting for user sessions did not behave as expected, causing cookies to **expire too early** or **fail to persist**.

### Problem

* The `cookie_lifetime` configuration defined in the global settings was **not consistently applied** to session cookies.
* In some cases:

  * Cookies were treated as session-only (deleted when the browser closed), even when `cookie_lifetime` was explicitly set.
  * Changes to the cookie lifetime had **no effect** on session persistence.

### Resolution

* The session management system now correctly applies the `cookie_lifetime` configuration when:

  * Initializing sessions (`session_set_cookie_params`)
  * Setting or refreshing session cookies (`setcookie`)
* Support has been improved for both:

  * Duration-based lifetimes (in seconds), and
  * Absolute expiration times (timestamps)

### Impact

* Session cookies now **persist correctly** according to the configured `cookie_lifetime` value.
* This is especially beneficial for:

  * "Remember Me" login features
  * Multi-tab or multi-device access
* Improves reliability of session handling for end users.


## Enhancement: Auto-Detect Length for `CHAR` and `VARCHAR` Fields

When importing data from spreadsheets or tables, the Entity Editor now **automatically estimates and sets the length** for `CHAR` and `VARCHAR` fields.

### Key Highlights

* Reduces manual setup when defining field sizes.
* Makes schema generation more accurate and efficient.

## Bug Fix: Auto Increment on SQLite Integer Primary Keys

### Issue

* In previous versions, SQLite incorrectly generated auto increment for non-`INTEGER` types or ignored `AUTOINCREMENT` due to type mismatch.

### Resolution

* The system now ensures that `AUTOINCREMENT` is only applied to `INTEGER PRIMARY KEY` columns in SQLite, as required by the SQLite spec.

### Result

* Prevents invalid syntax errors and improves compatibility with SQLite.



## Bug Fix: Auto Increment Column Generation for SQLite

### Problem

* When generating a `CREATE TABLE` query for **SQLite**, the system used MySQL-style syntax (e.g., `INTEGER(20) NOT NULL PRIMARY KEY AUTOINCREMENT`), which caused errors.
* SQLite **only allows** `AUTOINCREMENT` on columns declared as `INTEGER PRIMARY KEY` **without length or modifiers**.

### Resolution

* When exporting or generating SQL for SQLite, the system now **automatically removes the length modifier** and ensures the correct form:
  `INTEGER PRIMARY KEY AUTOINCREMENT`
* This applies only to numeric primary keys with auto increment enabled, ensuring SQLite compatibility while retaining MySQL behavior in other dialects.

### Example

**Before (Invalid in SQLite):**

```sql
payment_id INTEGER(20) NOT NULL PRIMARY KEY AUTOINCREMENT
```

**After (Valid in SQLite):**

```sql
payment_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT
```



# MagicAppBuilder Version 1.18.0

## Enhancement: Confirmation Dialog for Viewing Large Entity Data

Before opening the Entity Data Viewer, the system will now display a confirmation dialog if the number of data rows exceeds 1,000.

**Purpose**

* To prevent lag or crashes caused by rendering large tables.
* To give users full control to cancel or continue the process.

**Details**

* **Default limit**: 1,000 rows (can be configured).
* If the number of rows is ≤ the limit, data is displayed directly without confirmation.
* If the number of rows > the limit, a dialog appears showing the number of rows and offering "Continue" or "Cancel" options.



## Enhancement: Export Entity to GraphQL

The Entity Editor now supports exporting entities to a GraphQL format to simplify integration with GraphQL APIs.

**Key Features**

* Exports schema and type definitions based on the entity structure.
* Supports scalar types, relationships, and nullable/non-nullable fields.
* The output is presented as a ready-to-use `.graphqls` file.

**Benefits**

* Accelerates the development of GraphQL-based APIs.
* Reduces manual work in creating type definitions.



## UI Enhancement: Button Layout Optimization in Entity Editor

The button layout in the Entity Editor has been updated to improve readability and reduce text duplication.

**Changes**

* Eliminated the repetition of "Import" and "Export" in button labels.
* Grouped buttons by function:
  * **Import Group** — all file/spreadsheet import buttons.
  * **Export Group** — all export options (SQL, GraphQL, images, etc.).
* Button icons have been clarified to remain easily recognizable without repeated text.

**Impact**

* The interface is cleaner and more concise.
* Users can still differentiate between export and import buttons through visual grouping.



## New Feature: GraphQL Schema Parser

The Entity Editor now includes a **GraphQL Schema Parser**, allowing users to create entities directly from an existing GraphQL schema. This feature streamlines the process of generating entities from a single `.graphqls` file.

**Details**

* **Functionality**: Users can import a GraphQL schema file, and the system will automatically parse the `type` and `input` definitions, except those named `Query`.
  Between `type` and `input`, both may point to the same table in the database. Users should verify the validity of each and choose one to keep while discarding the unnecessary one.
* **Entity Generation**: Each `type` definition in the schema is converted into a new entity within the editor. The fields within each type become the columns of the new entity.
* **Relationship Handling**: The parser intelligently identifies relationships and data types, ensuring that the generated entities are accurate representations of the original schema.
* **Workflow**: This enables a seamless workflow where developers can define their schema first and then use the Entity Editor to automatically scaffold the corresponding database entities.

**When to Use**

Importing from a GraphQL Schema is recommended only when the developer does not have access to the database structure, or when the database is damaged, lost, or otherwise unavailable. This is considered a last-resort option. Entities imported from a GraphQL Schema cannot be used directly without adjustments, because primary key columns are almost always assumed to be `VARCHAR(255)`, whereas the actual database might use different data types. Users will need to update the data type of each column to ensure the system functions as intended. This limitation exists because a GraphQL Schema describes the shape of the data for client access purposes, not the precise underlying database types.


## Bug Fix: Database Export in Magic Database Page

Fixed an issue where the **Magic Database** page failed to display the list of tables available for export due to a configuration error. This issue prevented users from selecting which tables to export. The configuration has been corrected, and the page now reliably shows all exportable tables.


## Upgrade: MagicObject to Version 3.16.8

Upgraded the MagicObject library to **version 3.16.8**, which includes a bug fix for SQL Server exports:

* `BIT` values are now correctly exported as `1` (for TRUE) and `0` (for FALSE) instead of literal strings `TRUE` and `FALSE`.
  This ensures better compatibility and prevents type mismatch issues when importing into SQL Server.


# MagicAppBuilder Version 1.18.1

## Bug Fix: Drag-and-Drop Column Ordering in Entity Editor

Fixed a JavaScript error that occurred when reordering table columns or template columns in the **Entity Editor**:

```
Uncaught DOMException: Node.insertBefore: Child to insert before is not a child of this node
```

**Context**
This issue appeared when users tried to reorder columns in the data table or in the column template list within the Entity Editor. The problem happened because the target row’s parent `<tbody>` did not match the cached reference, especially when the DOM structure changed during the drag-and-drop process.

**Resolution**
The fix ensures the correct `<tbody>` is always determined at the time of the drop action, preventing mismatched parent nodes and eliminating the DOMException error.

**Impact**
Users can now smoothly reorder both table columns and template columns in the Entity Editor without encountering errors.


## Bug Fix: Column Size on Entity Import from SQLite

Fixed an issue where column size information was not properly imported from a **SQLite database file**.
Now, when entities are imported, the **column length/size attributes** are correctly detected and applied.

**Impact**
Developers no longer need to manually adjust column sizes after import, ensuring imported entities more accurately match the original SQLite schema.


## Enhancement: Configurable Database Connection Timeout

Introduced a new configuration option `connection_timeout` in **core.yml**, allowing users to control the database connection timeout (in seconds).

**Example (`core.yml`):**

```yaml
database:
    driver: sqlite
    host: ""
    port: 3306
    username: ""
    password: ""
    database_name: ""
    database_schema: public
    time_zone: Asia/Jakarta
    database_file_path: D:/MagicServer/www/MagicAppBuilder/inc.database/database2.sqlite
    connection_timeout: 10
```

**Scope**

* This timeout setting is applied to **both**:

  * The internal database connection used by **MagicAppBuilder** itself.
  * The application database connection in the **generated application**.

**Impact**
Developers can now fine-tune the maximum wait time for establishing database connections across both the builder and generated applications, improving flexibility for environments with varying network or server response times.


## Enhancement: Auto Increment on Entity Import from SQLite

When importing entities from a SQLite database file, the system will now automatically assign **Auto Increment** to the primary key column if the following conditions are met:

1. The primary key column is of type **`INTEGER`**.
2. The entity does **not** have a composite primary key.

**Impact**
This ensures that imported entities more closely reflect the original SQLite schema behavior, simplifying entity management and preventing the need for manual adjustments after import.


## Change: Dependency Depth in Entity Editor

The **dependency depth** calculation in the **Entity Editor** has been adjusted:

* Previously, the base dependency depth started at **1**.
* Now, it starts at **0**.

**Impact**
Entities without dependencies on other entities will have a dependency depth of **0** instead of **1**, providing a clearer and more accurate representation of entity relationships.


# MagicAppBuilder Version 1.18.2

## Enhancement: Management of `sqlite_internal` Tables

Added explicit handling for **`sqlite_internal` tables** during entity import from SQLite databases.
These internal system tables are now correctly separated from user-defined entities, preventing accidental imports and ensuring that only relevant application entities are managed within MagicAppBuilder.

**Impact**
Developers will no longer see unwanted internal SQLite structures in the entity list, resulting in a cleaner and more accurate entity import process.


## Bug Fix: Auto Increment Parsing in MySQL and SQLite

Fixed an issue where **Auto Increment** columns were not always correctly detected during entity import from **MySQL** and **SQLite** databases.
The parsing logic has been improved to reliably identify auto-incremented primary keys across both dialects.

**Impact**
Imported entities now correctly preserve auto-increment behavior, reducing the need for manual schema adjustments.


## Bug Fix: Composite Primary Key Parsing

Improved parsing for **composite primary keys** across multiple database engines, including **MySQL, MariaDB, PostgreSQL, and SQL Server**.
The system now accurately recognizes and maps multi-column primary keys, ensuring imported entities reflect the original schema definitions.

**Impact**
Applications with complex database schemas can now be imported without losing critical primary key definitions, improving data integrity and query reliability.


## Bug Fix: Data Type Mapping for PostgreSQL Entity Import

Corrected an issue where certain PostgreSQL data types were not properly mapped during entity import.
The improved type mapping ensures that PostgreSQL-specific column types (such as `uuid`, `jsonb`, `timestamptz`, etc.) are now correctly translated into the MagicAppBuilder entity model.

**Impact**
Developers working with PostgreSQL can now expect more faithful imports with fewer manual type corrections required.


## Change: Table Name Quoting in SQLite and PostgreSQL Export

MagicAppBuilder no longer applies quotes (`"table_name"`) around table names when exporting entities to **SQLite** and **PostgreSQL**.
This adjustment improves compatibility with standard database conventions and reduces unnecessary quoting in generated SQL.

**Impact**
Exported SQL is now cleaner and more consistent, minimizing potential conflicts with external tools or database clients.


## Upgrade: MagicObject 3.17.1

Upgraded **MagicObject** dependency to **version 3.17.1**, bringing the latest stability improvements, performance optimizations, and expanded compatibility for entity operations.

**Impact**
Generated applications benefit from the latest improvements in MagicObject, ensuring smoother runtime behavior and broader database support.

# MagicAppBuilder Version 1.19.0

## New Feature: Paste SQL Query

Version 1.19.0 introduces a significant usability improvement by allowing users to **paste SQL queries directly into the editor**. This new feature bypasses the need to save a query to a `.sql` file and then import it, greatly simplifying the workflow for developers who need to quickly import new entities.

This change reduces friction for the user, making the process of prototyping and managing database entities much more efficient. It is a key enhancement that aligns with the overall goal of making MagicAppBuilder a more streamlined and developer-friendly tool.

## New Feature: Context Menu Enhancements

This version also adds **enhanced context menu options** on the **All Entities** tab and for individual diagrams, making entity management faster and more convenient:

### Context Menu on **All Entities** Tab

* Export options:

  * **Export SVG**
  * **Export PNG**
  * **Export MD** (Markdown)
* Copy options:

  * **Copy Structure**
  * **Copy Data**
  * **Copy All** (Structure + Data)
  * **Import from Clipboard**
* Edit options:

  * **Edit Entity**
  * **Edit Data**
* **Duplicate Entity** – Quickly clone an existing entity along with its data as a draft for editing.

### Context Menu on Individual Diagrams

* Added **Duplicate Entity** entry to allow cloning an entity directly from its diagram context menu.
* Added **Import from Clipboard** entry to allow importing an entity from the clipboard.
* The duplicated entity and its data appear in the editor as a draft and will only be persisted when the user clicks **Save Entity**.

## New Feature: Export Workspace

This release introduces the ability to **export the entire workspace** in a single step. Instead of exporting applications one by one, users can now export **all active applications** within a workspace into a single ZIP file.

Inside this ZIP, each application’s own export is packaged as an individual ZIP file. This makes it much easier to back up or transfer large workspaces that contain multiple applications, ensuring a more efficient workflow when working on complex projects.

## New Feature: Magic Admin – Environment Variables & Configuration Encryption

Magic Admin now supports **creating environment variables and encrypting application configurations** directly for production environments.
This feature provides a more secure and streamlined way to manage sensitive application settings, ensuring that production deployments follow best practices for configuration management and data protection.

## New Feature: Append Entity Data from SQL `INSERT`

Version 1.19.0 expands its SQL import capabilities with support for **appending entity data** from `INSERT INTO` queries. Previously, MagicAppBuilder focused solely on creating table structures (`CREATE TABLE`). Now, when you paste or import a file containing an `INSERT INTO` query, the data within that query will be automatically added to the corresponding existing entity in the editor.

This feature is incredibly useful for:

* **Seeding initial data** into newly created entities.
* **Updating or adding to existing entities** with new data from SQL scripts without manual entry.
* **Integrating prototype data** from external SQL scripts directly into your project.

This is a significant boost to efficiency, especially for workflows involving data transfer between environments or populating datasets for testing and development.

## Bug Fixes

* **viewData() index handling** – Fixed an issue where calling `viewData()` with `index = -1` did not correctly use the current entity index. Now, if no index is provided, the method falls back to the currently selected entity and displays the appropriate data or alert if the entity has not been saved yet.
* **Database Exporter (MySQL & PostgreSQL)** – Fixed an issue where incorrect parameter passing caused the batch size not to be set properly. This resulted in row-by-row exports even for small datasets, leading to unnecessarily large dump files and excessive queries during database restore.
* **Update Primary Key values** – Fixed a bug in the update form where changing a primary key value caused the field to become empty. After the fix, primary key updates work correctly and persist the intended value.
* **Diagram persistence after JSON import** – Fixed an issue where diagrams were not saved after importing entities from a JSON file. Previously, only the entities were stored on the server while the diagrams were lost. Now, both entities and their diagrams are saved properly, ensuring that complex relationship diagrams remain intact after import.
* **Save Entity Data from Entity Editor** – Fixed an issue where saving data after adding or updating records via the **Entity Editor** did not persist correctly. Now, both new entries and modifications are reliably stored without data loss.


# MagicAppBuilder Version 1.20.0

## New Feature: Starter Package

Version 1.20.0 introduces the **Starter Package** feature, a new way to quickly jumpstart your application development. This package provides a pre-built foundation, so you don't have to start from scratch.

A **Starter Package** includes a variety of ready-to-use assets:

* **Table Designs:** Pre-defined table schemas customized for specific application types, such as libraries, hotels, restaurants, or schools.
* **Source Code and Binaries:** Relevant source code and binary files for the application, including front-end pages, reservation pages, and sales pages.
* **Themed Designs:** Thoughtfully crafted visual themes with carefully selected color schemes and element layouts to ensure a user-friendly interface.

This feature significantly streamlines your workflow and provides a robust starting point for many different types of projects.

## New Feature: Display SQLite File Content in File Manager

Previously, the File Manager displayed SQLite database files (`.sqlite` and `.db`) as binary files, which posed a risk of corruption if the user accidentally edited and saved them.
Now, MagicAppBuilder can **safely display the structure and contents** of SQLite files directly within the File Manager.

With this feature, you can:

* View table structures without opening an external tool.
* Inspect table contents in a human-readable format.
* Avoid accidental file corruption by preventing raw binary editing.

## New Feature: SQLite Download and Export in File Manager

Building on the SQLite viewer, MagicAppBuilder now provides options to manage SQLite files **directly from the File Manager**.
Developers can:

* **Download SQLite files** safely without leaving the File Manager.
* **Export individual tables** as SQL statements for easy migration.
* **Export the entire database** into a complete SQL dump.

This addition makes the File Manager not just a viewer, but also a convenient tool for database export and backup.

## New Feature: Document Viewer in File Manager

MagicAppBuilder now includes a **Document Viewer** in the File Manager. Users can **preview the contents of documents directly in the application** without downloading them.

Supported file types:

* PDF (`.pdf`)
* Word (`.docx`)
* Excel (`.xls`, `.xlsx`, `.ods`)
* Comma Separated Value (`.csv`)

This feature allows users to:

* Quickly inspect document contents in the context of their application directory.
* Switch between sheets in Excel/ODS files using tabs.
* Avoid unnecessary downloads when checking file contents.

## New Feature: Automatic Snake Case Naming on SQL Import

When importing a database from an SQL file in the **Entity Editor**, MagicAppBuilder now automatically converts the database structure and its contents to a **snake case** naming convention. This feature ensures consistency and simplifies the process of integrating external databases by standardizing column and table names.

## New Feature: Smart SQLite Database File Selection

In the **Application Settings**, the "Database File Path" field now provides a convenient way to manage your SQLite files. By clearing this field and double-clicking on it, you can now view and select all other SQLite database files (`.sqlite` and `.db`) that exist in the same directory as your current database. This allows you to quickly switch between or reuse previously created databases without having to browse for them manually.

## New Feature: Database Migration Tool

MagicAppBuilder introduces the **Database Migration Tool**, designed to help developers migrate data from an old (legacy) database into a new database structure tailored for MagicAppBuilder.

When revamping an application with MagicAppBuilder, database structures often need adjustments to fully support its features. Manually transferring data between old and new schemas can be time-consuming and error-prone.

With this tool:

* Developers can configure automatic data migration using **MagicObject**.
* MagicAppBuilder generates the required migration configuration, eliminating the need to write it manually.
* This significantly reduces effort and ensures that data from legacy databases can be reused seamlessly in the new system.

## New Feature: Bulk Delete Entities and Diagrams in Entity Editor

A new **context menu option** has been added to the **Entity Editor** that allows users to **delete all entities and diagrams at once**.
Previously, users had to remove them one by one, which was time-consuming.

With this new feature:

* You can clear all entities and diagrams in a single action.
* A confirmation dialog ensures you are aware that this action is **irreversible**.
* Both entity and diagram data are updated consistently after deletion.

This provides a faster way to reset your project when starting over or cleaning up the Entity Editor.

## New Feature: Font Viewer in File Manager

MagicAppBuilder now includes a **Font Viewer** in the File Manager. Users can **preview how text looks using a font file directly in the application**, without needing to install or open it in an external tool.

Supported font types:

* TrueType Font (`.ttf`)
* OpenType Font (`.otf`)
* Web Open Font Format (`.woff`, `.woff2`)

With this feature, you can:

* View sample text rendered with the selected font.
* Easily compare multiple fonts to distinguish their visual differences.
* Avoid the hassle of manually installing fonts just to preview them.

## Bug Fixes

* Fixed update mechanism for the `sqlite_sequence` table in SQLite databases.
  The `sqlite_sequence` table does not have a primary key, so updates were previously not possible.
  With this fix, updates now use the `name` column as the key in update forms and update actions.


# MagicAppBuilder Version 1.21.0

## Enhancement: Improved Local Storage Keys

This update fixes a bug where changes made to the color mode or sidebar status in MagicAdmin would also affect the generated app, and vice versa. Now, MagicAdmin uses separate local storage keys, so your settings in one won't interfere with the other.

## Enhancement: Improved Data List Appearance

The **Data List** view has been enhanced with additional styling classes for data columns, making the display more visually appealing.
Previously, column data appeared too tightly packed, making it harder to read. Now, spacing and styling improvements make the data clearer and easier to distinguish between columns.

## New Feature: Module Tracking and History

The update adds two new columns, **`name`** and **`module_code`**, to the **`Module`** entity. This allows the system to track and record every time a user creates or modifies a module. These changes are then stored in a new entity called **`ModuleHistory`**, which provides a record of module creation activities.

## New Feature: Dashboard Charts

A new **Dashboard Chart** feature has been added to provide an overview of user activity. This chart displays monthly statistics for the following metrics:

* `application_created`
* `module_created`
* `workspace_created`
* `admin_created`

## New Feature: Show/Hide Application

When a workspace contains too many applications, users may find it less convenient to navigate. In this release, MagicAppBuilder introduces a **Show/Hide Application** feature.

* Users can click the **eye-slash icon** to hide an application from their workspace view.
* Hidden applications are only hidden for the current user (user-specific visibility).
* To restore visibility, users can click the **Show Hidden** button to reveal hidden apps, then click the **eye icon** to unhide them.

This feature gives users more control and flexibility to manage their workspace and keep it clutter-free.

## Bug Fix: Primary Key Update with Approval

Previously, when updating a record with an approval process, the **primary key** did not change even after approval.
This issue has been fixed — now the primary key is correctly updated once the approval process is completed.

## Bug Fix: Handle Missing ZipArchive Module in PHP

In some environments, the **ZipArchive** extension may not be available in PHP, which previously caused errors during module packaging or export.
This update adds **exception handling** to gracefully detect when `ZipArchive` is unavailable and display a clear error message instead of failing silently.

## Bug Fix: Login Form with AJAX Content Loading

Previously, when a session expired, the application rendered the full login page directly into the section where AJAX content was supposed to be loaded.  
Starting from this version, the server now responds with a **401 status code** on session expiration. The response body contains a **specialized login form HTML** designed exclusively for AJAX requests, instead of the full login page.  

This allows the application to properly display the login form as a **modal dialog**, rather than misinterpreting it as part of the requested content.

## Bug Fix: Subquery Handling in Database Access

Fixed an issue with **subquery execution** when retrieving data from the database.
This problem was resolved by upgrading the **MagicObject** library to improve query handling and reliability.

## Bug Fix: Column.toBoolean() in Entity Editor

Fixed an error in the **`Column.toBoolean()`** function when generating **`CREATE TABLE`** statements in the **Entity Editor**.
Now, boolean columns are correctly converted and included in the generated SQL.


# MagicAppBuilder Version 1.22.0

## Enhancement: Scrollable Menu in MagicAdmin

The **MagicAdmin** menu interface has been improved to support scrolling when the content overflows. This ensures that all menu items remain accessible, even on smaller screens or when many items are present.

## Enhancement: Visual Indicators for Collapsible Menus

All **collapsible menus** in MagicAdmin now include a **visual marker (icon)** to distinguish between menus that contain submenus and those that do not. This provides clearer navigation cues and helps users quickly identify expandable sections.

## New Feature: Open Application and Project Directories in VS Code

From the application menu, users can now quickly open both the **application directory** and the **project directory** directly in **Visual Studio Code**. This addition streamlines the workflow for developers by reducing the steps needed to navigate to the relevant folders.

## New Feature: JSON Prettify Option for Module Configuration

When saving **module configurations**, **data references**, and **filter references**, users now have the option to store JSON in a **prettified (formatted)** style.

* This option is controlled through the `core.yml` setting:

  ```yaml
  data:
    prettify_module_data: true | false
  ```

* Default: **`false`**

  * Produces smaller files, optimizing storage and processing speed.

* When set to **`true`**, JSON files become more human-readable, making it easier for users to manually inspect or analyze configuration data.

## New Feature: Application Config Generator – YAML to XML Conversion

The **Application Config Generator** now supports converting **YAML configuration files into XML** format.

YAML is highly sensitive to indentation, and a single indentation error can cause configurations to be misread or even break the entire system. By introducing XML as an alternative format, MagicAppBuilder provides a more robust option that is less prone to formatting errors.

With this feature, users can choose between YAML and XML depending on their preference and the complexity of their configuration, ensuring greater flexibility and reliability in application setup.

## Technical Enhancement: HTTP Request Fallback

To prevent errors and function failures, MagicAppBuilder now includes a robust fallback mechanism for making HTTP requests. If the **`cURL`** PHP extension is unavailable, the application will automatically switch to using **PHP streams** to communicate with the server.

This ensures that network-dependent features—such as retrieving data from external APIs or other web resources—remain fully functional, even on server environments without cURL. This change significantly improves the stability and portability of the application.

## New Feature: Browser Language Detection

Before a user logs in or when a session expires, the application previously had no information about the user's preferred language and would fall back to the **default language**.
With this update, MagicAppBuilder now detects the **browser's language setting**:

* If the detected language is available in the application, it will be used automatically.
* If the detected language is not available, the system will gracefully fall back to the **default language**.

This improves the user experience by providing localized interfaces without requiring additional setup.

## Bug Fix: Selective Configuration Encryption

Previously, MagicAppBuilder encrypted **all application configuration properties**, which was neither efficient nor flexible.
With this fix, only the properties explicitly designated by the user are encrypted—for example, **database** and **Redis** credentials.

This ensures a better balance between **security, performance, and usability**, while giving users fine-grained control over which sensitive data should be protected.

## Dependency Upgrade: MagicObject 3.19.0

MagicAppBuilder now bundles **MagicObject 3.19.0**, which includes several enhancements, most notably:

* **PicoSession Redis Database Parameter**
  Developers can now specify a **Redis database index** in the session save path, allowing session data to be isolated in different Redis databases.

  Example:

  ```
  tcp://localhost:6379?db=3
  ```

## New Feature: `SqliteSessionHandler`

A new **`SqliteSessionHandler`** class has been introduced under `MagicObject\Session`.
This provides a **persistent session storage** mechanism using **SQLite** as the backend.

### Features

* Stores sessions in a **SQLite database file** instead of filesystem or memory.
* Automatically **creates the session table** if it does not exist.
* Implements the full session lifecycle:

  * **open** — Initializes session.
  * **read** — Reads serialized session data.
  * **write** — Writes or updates session data.
  * **destroy** — Removes a session by ID.
  * **gc** — Garbage collects expired sessions.
* Ensures **safe storage** even when multiple PHP processes are running.

### Why It Matters?

* **Portability:** No dependency on Redis or Memcached — only requires SQLite.
* **Lightweight:** Suitable for shared hosting or small applications.
* **Reliability:** Prevents session loss when PHP restarts, unlike file-based sessions.

## Enhancement: Optimized Filter Operations in Magic Admin

Filtering in **Magic Admin** has been enhanced for improved **performance and accuracy**.

* Previously, all filters used **full-text operations**, which could be inefficient for certain columns.
* With this update:

  * Filters applied to **foreign key columns** now use **exact string matching** instead of full-text search.
  * This results in **faster queries** and more **reliable search results** when dealing with relational data.

This change ensures that data lookups, especially those involving references between entities, are both **quicker** and **more precise**.


# MagicAppBuilder Version 1.23.0

## New Feature: Redis Explorer

MagicAppBuilder now includes a built-in **Redis Explorer**, an interface for exploring and managing data stored in **Redis**.

### Key Features

* **Database Selector**
  Users can switch between Redis databases (0–15) directly from the interface.

* **Key Filtering**
  Supports pattern-based search to display only matching keys.

* **Pagination**
  Keys are displayed with pagination for easier navigation, even with very large datasets.

* **Key Management**

  * **Create**: Add new keys with values.
  * **Update**: Edit values of existing keys.
  * **Delete**: Remove specific keys with confirmation.
  * **Bulk Delete**: Delete all keys that match a selected pattern.

### Benefits

With this feature, developers and system administrators can:

* Quickly inspect Redis contents without relying on the CLI.
* Manage Redis data directly from the MagicAppBuilder panel.
* Speed up debugging and application administration.

### Security Note

The Redis Explorer provides powerful capabilities to view and modify data. For security reasons:

* **Do not enable Redis Explorer in production environments.**
* Use it only in development or testing setups.
* Ensure proper authentication and restricted access to the interface.

## Improvement

* **Improved login form behavior when session expires**
  Previously, the login form was displayed inside the AJAX-loaded content area, which could break the application layout. Now, the login form is displayed as a **modal**, ensuring a consistent and non-intrusive user experience.

## Bug Fix

* **Fixed session cookie lifetime handling**
  Due to differences in configuration naming, the session lifetime value was not being read correctly, causing the application to fall back to the default value. This issue has been resolved through an **upgrade of MagicObject**, which now correctly reads and applies the configured session lifetime.

# MagicAppBuilder Version 1.24.0

## Enhancement: More Flexible System Module Path

Users can now freely define the system module path in the application instead of being restricted to the application root.  
This provides greater flexibility for developing applications and integrating them with other systems.  
Users can create their own files and logic in the application root without worrying about interfering with or mixing them with the system modules.

