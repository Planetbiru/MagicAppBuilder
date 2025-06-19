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
    

## Improvements

-   **Enhanced Captions and Tooltips:** Captions and tooltips on the main module and entity generation forms have been improved for clarity.
    
-   **Enhanced Language Localization:** Entity development has been improved for more effective language localization.
    
-   **Enhanced File Manager:** The file manager now offers a better user experience.
    
-   **Reorganized Module Tabs:** Module tabs have been reorganized for a more intuitive workflow.
   

## Fixes

-   Display improvements.
-   General bug fixes.

