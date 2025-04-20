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

