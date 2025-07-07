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

-   Added support for importing entity structures directly from `.xlsx`, `xls` and `.csv` files in the Entity Editor.
    
-   The import feature reads column headers and sample data to automatically infer table schema, including data types.
    
-   When importing Excel files (`.xlsx` or `.xls`), users will be prompted to select the sheet they want to import before the entity structure is generated.


#### Benefits:

-   Speeds up entity creation by allowing developers to generate database tables directly from spreadsheet data.
    
-   Simplifies the conversion of structured spreadsheet content into database-ready formats using file input.
    

## What's Changed

### Enhancement: Improved Sortable Handler UI

The sortable handler in data tables has been updated for a cleaner and more consistent user experience.

#### Changes:

-   Previously, the sortable feature used a dark-colored column background to indicate draggable areas.
    
-   This approach has been replaced with a minimalist **⠿ (three-dot vertical)** character to clearly mark sortable rows without affecting the column layout or color scheme.
    

#### Benefits:

-   Visually cleaner and more intuitive interface.
    
-   Consistent across different themes or background colors.
    
-   Easier for users to identify and interact with the sortable elements.
    

### Bug Fix: Multi-Level Menu Display in Development Mode

-   Fixed an issue where multi-level menus were not displayed when `developmentMode=true`.
    
-   Previously, the application would fail to show menus in development mode because the menu data did not exist in the database and was only available in `application.yml`.
    

#### Benefits:

-   Developers can now preview and navigate full menu structures during development without needing to import menu data into the database.
    

## Library Update: MagicObject 3.14.5

### Enhancement: Flexible Nested Retrieval in `retrieve()` Method

The `retrieve(...$keys)` method now supports multiple input formats for accessing nested object properties:

- Dot notation: `$obj->retrieve('user.profile.name')`
- Arrow notation: `$obj->retrieve('user->profile->name')`
- Multiple arguments: `$obj->retrieve('user', 'profile', 'name')`

Each key is automatically camelized for consistent property access.  
If any key in the chain does not exist or returns `null`, the method will return `null`.

This enhancement improves developer ergonomics when working with deeply nested data structures.

### Improvement: `@TimeRange` Validation Now Supports Both `HH:MM` and `HH:MM:SS`

- The `@TimeRange` validator now accepts time formats in either `HH:MM` or `HH:MM:SS`.
- Input values are automatically normalized to `HH:MM:SS` before comparison.
- Developers can now write annotations like `@TimeRange(min="08:00", max="17:00")` or `@TimeRange(min="08:00:00", max="17:00:00")` interchangeably.

This improvement ensures more flexibility while maintaining precision down to the second.