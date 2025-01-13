# MagicAppBuilder Usage Guide

MagicAppBuilder is a powerful tool for creating applications quickly, allowing users to design and manage both the front-end and back-end of their software with minimal coding. This guide will take you through each step, from installation to generating modules, and ensure you have a smooth experience with the platform.

## Step 1: Install the Server

Before you can begin using MagicAppBuilder, you must install a server. The server should include the following components:

### Required Components:

1.  **Web Server**: Apache
2.  **Database**: MySQL or MariaDB
3.  **PHP**: For server-side scripting

### Installation Options:

There are several options to install these components on your machine:

-   **WAMP** (Windows, Apache, MySQL, PHP)
-   **XAMPP** (Cross-platform Apache, MySQL, PHP)
-   **USBWebServer** (Portable version for Windows)

Choose the one that best suits your operating system and requirements. Each of these tools provides a simple installation process with pre-configured components, so you don’t have to manually configure each one.

### Post Installation:

After installation, ensure that all components are running correctly:

-   Start Apache (web server).
-   Start MySQL or MariaDB (database server).
-   Verify that PHP is working by accessing the default PHP page (usually [http://localhost](http://localhost)) in your browser.

Once you confirm that your server environment is up and running, you can proceed to the next step.

## Step 2: Create a Workspace

A **Workspace** in MagicAppBuilder is a container for all your projects. Each workspace is a directory that can hold multiple applications, which are kept isolated from one another. This feature helps in managing user permissions and organizing projects.

### Steps to Create a Workspace:

1.  **Open MagicAppBuilder**: Launch the MagicAppBuilder software.
2.  **Navigate to Workspace Creation**: You will find an option in the main menu or dashboard to create a new workspace.
3.  **Define Workspace Directory**: Choose or create a directory where your workspace will be located. This will store all your projects and configurations.

Once the workspace is created, MagicAppBuilder allows you to switch between multiple workspaces, ensuring efficient management of different projects. You’re now ready to start building your application!

## Step 3: Create an Application

Creating an application in MagicAppBuilder is simple and involves filling out a form with necessary details. This application will reside in the workspace you just created.

### Application Form Fields:

1.  **Application Name**: Choose a name for your application.
2.  **Application ID**: This is a unique identifier for your application. It will be used internally by MagicAppBuilder.
3.  **Architecture**: Decide whether your application will be a "Monolith" (single, unified structure) or "Microservices" (distributed services) application.
4.  **Description**: Provide a brief description of the application’s functionality.
5.  **MagicApp Version**: The latest version of MagicAppBuilder, which is automatically suggested.
6.  **Workspace**: Select the workspace you created in Step 2.
7.  **Application Directory**: Define a folder where all application files will be stored.
8.  **Base Namespace**: The base namespace for your application’s source code, typically formatted like `AppNamespace`.
9.  **Author**: Specify the author of the application (e.g., your name or company).
10.  **Application Path**: The directory for creating application-specific modules.

Once all fields are filled out, click **Save**. MagicAppBuilder will create a new application entry, which will be displayed as a card on the main dashboard.

## Step 4: Configure the Application

In this step, you’ll configure essential settings for the application, such as database connections and session parameters.

### Steps:

1.  **Click Settings on the Application Card**: Locate and click the settings button on your newly created application card.
2.  **Database Configuration**:
    -   MagicAppBuilder uses SQLite by default for simplicity. However, you can configure it to use MySQL, MariaDB, or any other supported database system by providing connection details (host, username, password, and database name).
    -   If you’re using a custom database system, ensure that your PHP environment has the necessary extensions (like `mysqli` or `pdo_mysql` for MySQL/MariaDB).
3.  **Session Configuration**:
    -   Define parameters for user sessions. This includes the session name, session lifetime, session handler (e.g., file, database), and the session save path.

### Reserved Columns Configuration:

The following **reserved columns** are required for internal MagicAppBuilder functionality and must be set up before creating databases, entities, or modules:

-   `name`
-   `active`
-   `draft`
-   `waiting_for`
-   `admin_create`
-   `admin_edit`
-   `admin_ask_edit`
-   `time_create`
-   `time_edit`
-   `time_ask_edit`
-   `ip_create`
-   `ip_edit`
-   `ip_ask_edit`
-   `sort_order`
-   `approval_id`
-   `approval_note`
-   `approval_status`

These columns should be configured to meet your application’s language needs. Once the database structure is created, these columns cannot be changed. Ensure that these configurations are done properly in this step.

After filling in all the configurations, click **Save** to apply changes.

## Step 5: Set Up the Database Structure

In this step, you’ll define the structure of your database, which includes creating tables and defining relationships between entities.

### Tools Provided:

1.  **Database Explorer**: This tool lets you visualize and manage the database schema.
2.  **Entity Editor**: A graphical interface to define entities (tables) and their relationships.

### Using the Database Explorer and Entity Editor:

1.  **Launch Database Explorer**: Click on the **Database** button in the application card to open the database tools.
2.  **Define Entities**: You can create entities (tables) and relationships directly within the Entity Editor, or you can manually create them in your database using SQL commands.

If you prefer a more visual approach, MagicAppBuilder offers the **Entity Editor** to help you design tables and their structure. Once entities are created, you can export the structure as SQL and apply it to your database.

## Step 6: Create Entities Using the Entity Editor

The **Entity Editor** is a tool within MagicAppBuilder that allows you to create, edit, and manage database entities. Entities correspond to tables in the database and define the fields (columns) that store your data.

### Steps:

1.  **Open the Entity Editor**: In the **Database Explorer**, click on **Entity Editor** to open the editor interface.
2.  **Add a New Entity**: Click **Add New Entity** to create a new table. Name the entity and press **Enter**.
3.  **Add Columns**: For each entity, you’ll define its columns:
    -   Click **Add Column** to define a column for the entity.
    -   For each column, provide a name and data type (e.g., `VARCHAR`, `INT`, `DATE`).
    -   Define additional properties like whether the column is a primary key or required.
4.  **Naming Convention**: The primary key for each entity should follow the naming convention: `[entity_name]_id` (e.g., for an entity `user`, the primary key would be `user_id`).

Once the entities are defined, you can use templates to quickly add reserved columns and standard fields that should be part of all entities, such as `name`, `active`, and `created_at`.

### Preferences:

Click **Preferences** to define additional settings, such as:

-   **Primary Key Type**: Specify whether the primary key will be an `INT`, `UUID`, or another type.
-   **Column Types**: Set default data types for non-primary columns, such as `VARCHAR`, `TEXT`, etc.
-   **Column Lengths**: Specify default length for non-primary columns.

-   Once you’ve made your adjustments, click **OK** to apply the changes, or **Cancel** to discard them.

## Step 7: Apply Entities to the Database

Once your entities are designed, it’s time to apply them to your database:

1.  **Select All Entities**: In the sidebar, check all entities you wish to apply.
2.  **Import Entities**: Click **Import** to load the entities into the database.
3.  **Execute SQL Query**: The Database Explorer will generate a **CREATE TABLE** SQL query based on your entities. Click **Execute** to apply this query to your database.

This will create the necessary tables and relationships in your database.

## Step 8: Create an Application Menu

An application menu allows you to organize and manage navigation within your application.

### Steps:

1.  **Go to Application Card**: Return to the application card and click the **Menu** button.
2.  **Define Menu**: You can either create a new menu or modify an existing one. The menu will manage how users navigate between the various parts of the application.

## Step 9: Create a Module

Modules are the functional units of your application. Each module typically corresponds to a table in the database and provides the ability to create, read, update, and delete (CRUD) data from that table.

### Steps to Create a Module:

1.  **Select Table**: In the **Select Table** tab, choose the table you want to create a module for. Click **Reload Table** if the table list is out of date.
2.  **Module Configuration**: Define the module's options, such as whether it should allow CRUD operations, enable features like sorting or filtering, and configure additional settings.
3.  **Load Columns**: Click **Load Columns** to define which columns will be available in the module interface.

### Generate Module:

Once the configuration is complete, click **Generate Module** to create the module. MagicAppBuilder will automatically generate the necessary code and configuration for the module.

## Step 10: Localization

If you are developing a multilingual application, MagicAppBuilder allows you to create localization by translating module and entity labels into different languages.

## Step 10: Localization

If you are developing a multilingual application, MagicAppBuilder allows you to create localization by translating module and entity labels into different languages.

## Step 11: Creating Localization

You can create localization by translating both modules and entities into other languages as per the user’s needs.

### Translating a Module

1.  **Translate Module**:
    
    -   You can translate all the labels and buttons of the modules into another language in the **"Translate Module"** tab.
    -   Select the module you want to translate and choose the target language. If the target language is not available, click the **"Manage"** button.
2.  **Interface**:
    
    -   The screen is divided into two sections. The left side shows the original labels in the application's default language, and the right side shows the labels in the target language.
    -   You can only modify the labels on the target language side.
3.  **Translate All Modules**:
    
    -   You have the option to translate all modules at once if needed.

### Translating an Entity

1.  **Translate Entity**:
    
    -   You can translate all the labels and buttons of the entities into another language in the **"Translate Entity"** tab.
    -   Select the entity you want to translate and choose the target language. If the target language is not available, click the **"Manage"** button.
2.  **Interface**:
    
    -   The screen is divided into two sections. The left side shows the original labels in the application's default language, and the right side shows the labels in the target language.
    -   You can only modify the labels on the target language side.
3.  **Translate Entities One by One**:
    
    -   You will need to translate each entity individually.
