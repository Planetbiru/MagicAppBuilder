# MagicAppBuilder Usage Guide

MagicAppBuilder is a powerful tool for creating applications quickly, allowing users to design and manage both the front-end and back-end of their software with minimal coding. 

## User Interface

The application consists of several tabs:

### **1. Administration**

A separate web interface for managing users, user access, workspaces, applications, and other administrative settings.

### **2. Workspace**

This tab allows users to create and view workspaces. Users can also set the active workspace.

### **3. Application**

This tab enables users to create and view applications. Users can also set the active application.

### **4. Select Table**

In this tab, users can select a table and define:

-   Module name
-   Entity name
-   Menu name
-   Configuration options for module creation
-   Whether to load a previously saved configuration for the module

### **5. Generate Module**

This tab is used to configure modules by selecting:

-   Columns to be included in the module
-   UI elements for **Create, Update, Show Detail, Show List, and Export**
-   Relationships between columns and other entities or database tables
-   Data filters and sorting options
-   Features such as:
    -   **Activate/Deactivate**
    -   **Manual sort order**
    -   **Export to CSV**
    -   **Export to Excel**
    -   **Approval workflow**
    -   **Trash (soft delete)**
    -   **AJAX-based list rendering**

### **6. Edit Module**

This tab allows users to manually edit the module's code. Users can also delete the module files.

### **7. Edit Entity**

This tab enables users to manually edit entity code. Users can also delete entity files.

### **8. ERD (Entity Relationship Diagram)**

In this tab, users can generate an ERD diagram for one or more selected entities. Users can also specify the depth level of entity relationships to be displayed.

### **9. Query**

This tab displays database queries for one or more selected entities. The primary function is to generate:

-   **CREATE TABLE** and **ALTER TABLE** queries after entities are created
-   Queries for database generation based on the application's defined entities

Supported database management systems (**DBMS**):

-   **MySQL**
-   **MariaDB**
-   **PostgreSQL**
-   **SQLite**

### **10. Translate Module**

This tab allows users to create localization files for modules, enabling **multi-language support** in applications.

### **11. Translate Entity**

This tab allows users to create localization files for entities, enabling **multi-language support** in applications.

### **12. File Manager**

File Manager allows you to manage files and directories with a variety of easy-to-use features. You can preview images, open and edit text files, and perform various operations on files and directories such as uploading, deleting, and downloading. Below is a complete guide to utilizing the various features within this File Manager.

#### **Main Features**

**1. Image Preview**

You can preview images directly within the File Manager. Simply click on the image, and a preview will appear, allowing you to view it without opening any other applications.

**2. Open Text Files**

Text files such as .txt or other text-based files can be opened directly in the File Manager. Just click on the text file, and its contents will appear in the editor window within the File Manager.

**3. Edit Text Files**

Once a text file is opened, you can edit it directly. Simply modify the content of the file and save it, and the changes will be applied to the original file in the directory.

#### **File Manager Context Menu**

The context menu (right-click) allows you to access various functions related to files or directories. This menu is divided into several categories based on different contexts: **Directory**, **Root Directory**, and **File**.

**Directory Context Menu**

This context menu appears when you right-click on a directory.

1.  **Create New File**
    

-   Choose this option to create a new file inside the selected directory. You will be prompted to enter the name of the new file.
    

2.  **Create New Directory**
    

-   Choose this option to create a new directory inside the selected directory. You will be prompted to name the new directory.
    

3.  **Upload File**
    

-   Choose this option to upload a file from your device to the selected directory. You can choose one or more files to upload.
    

4.  **Expand Directory**
    

-   Choose this option to show or hide subdirectories inside the selected directory. Directories with subdirectories will expand and display their contents.
    

5.  **Reload Directory**
    

-   Choose this option to reload the content of the selected directory, ensuring the latest content is displayed.
    

6.  **Rename Directory**
    

-   Choose this option to rename the selected directory. You will be prompted to enter a new name for the directory.
    

7.  **Download Directory**
    

-   Choose this option to download the entire directory and its contents to your device.
    

8.  **Delete Directory**
    

-   Choose this option to delete the selected directory along with all of its contents.
    

**Root Directory Context Menu**

This context menu appears when you right-click on the root directory (main directory).

1.  **Create New File**
    

-   Choose this option to create a new file inside the root directory. You will be prompted to enter the name of the new file.
    

2.  **Create New Directory**
    

-   Choose this option to create a new directory inside the root directory. You will be prompted to name the new directory.
    

3.  **Upload File**
    

-   Choose this option to upload a file to the root directory. You can select multiple files at once.
    

4.  **Reset Content**
    

-   Choose this option to delete all content in the root directory and restore it to its original state. All files and subdirectories will be removed.
    

5.  **Download All**
    

-   Choose this option to download all the contents of the root directory to your device.
    

**File Context Menu**

This context menu appears when you right-click on a file.

1.  **Open File**
    

-   Choose this option to open the selected file. If it is a text file, you will be able to view and edit its contents within the File Manager. For other file types such as images or PDFs, the file will be opened in preview mode.
    

2.  **Rename File**
    

-   Choose this option to rename the selected file. You will be prompted to enter a new name for the file.
    

3.  **Download File**
    

-   Choose this option to download the file to your device. You will receive the file in its original format as it exists on the server.
    

4.  **Delete File**
    

-   Choose this option to delete the selected file. You will be asked to confirm before the file is permanently deleted.
    

#### **How to Use the File Manager**

**1. Navigating Directories**

-   Click on a directory to open and view its contents.
    
-   Use the context menu to create new files, create new directories, upload files, or perform other operations.
    

**2. Uploading Files**

-   Select the directory where you want to upload the file.
    
-   Right-click on the directory and choose "Upload File".
    
-   Select the file(s) you wish to upload from your device.
    

**3. Creating a New Directory**

-   Right-click on the directory where you want to create a new subdirectory.
    
-   Choose the "Create New Directory" option and name your new directory.
    

**4. Renaming a File or Directory**

-   Right-click on the file or directory you want to rename.
    
-   Choose the "Rename" option and enter a new name.
    

**5. Downloading a File or Directory**

-   Right-click on the file or directory you want to download.
    
-   Choose "Download" for files or "Download Directory" for directories.
    

**6. Deleting a File or Directory**

-   Right-click on the file or directory you want to delete.
    
-   Choose the "Delete" option and confirm the deletion.
    

#### **Conclusion**

This File Manager is designed to simplify the management of files and directories. With features like image previews, text file opening and editing, and easy-to-access file management operations, you can quickly and efficiently manage your files and directories. Use the context menu for various operations such as uploading files, renaming, deleting, and much more.

### **13. Logout**

A link to log out of the administrator session.

This guide will take you through each step, from installation to generating modules, and ensure you have a smooth experience with the platform.

Steps 1 and 2 are preparation. You only need to do them once.

## Step 1: Install the Server

Before you can begin using MagicAppBuilder, you must install a server. The server should include the following components:

### Required Components:

1. **Web Server**: Apache
2. **Database**: MySQL or MariaDB
3. **PHP**: For server-side scripting

### Installation Options:

There are several options to install these components on your machine:

- **WAMP** (Windows, Apache, MySQL, PHP)
- **XAMPP** (Cross-platform Apache, MySQL, PHP)
- **USBWebServer** (Portable version for Windows)

Choose the one that best suits your operating system and requirements. Each of these tools provides a simple installation process with pre-configured components, so you don’t have to manually configure each one.

### Post Installation:

After installing MagicAppBuilder, ensure that all components of the environment are functioning correctly:

1. **Start Apache (Web Server)**

   Ensure that your Apache web server is running. You can start it via the XAMPP control panel (if you're using XAMPP) or through the relevant management tool for your web server.
2. **Start MySQL or MariaDB (Database Server)**

   If you're using MySQL or MariaDB as your database, ensure that the database server is running. You can start it from the XAMPP control panel or your respective database management interface.
3. **Verify PHP is Working**

   To ensure PHP is functioning correctly, access the default PHP page (usually [http://localhost](http://localhost)) in your browser. If you see the expected PHP page, then PHP is installed and working fine.

### Enabling SQLite Driver on Windows:

If you're using **Windows** as your operating system, and **SQLite** is selected as your database in MagicAppBuilder, you must ensure that the **SQLite PHP driver** is enabled in your PHP configuration.

Here are the steps to enable the SQLite driver on a Windows environment:

#### Step 1: Locate the `php.ini` File

The `php.ini` file is the main configuration file for PHP. The location of this file depends on the PHP installation, but for XAMPP, you can typically find it here:

* `C:\xampp\php\php.ini`

#### Step 2: Open the `php.ini` File

Open the `php.ini` file in a text editor (such as Notepad or VSCode).

#### Step 3: Find the SQLite Extension

Search for the following lines in the `php.ini` file (you can use the "Find" function in your text editor, usually by pressing `Ctrl + F`):

* `extension=sqlite3`
* `extension=pdo_sqlite`

#### Step 4: Uncomment the SQLite Lines

In many cases, these lines will be commented out by default (with a semicolon `;` at the beginning of the line). To enable the SQLite extension, simply remove the semicolon at the beginning of each line:

```ini
extension=sqlite3
extension=pdo_sqlite
```

#### Step 5: Save and Close the `php.ini` File

After uncommenting these lines, save the changes to the `php.ini` file and close your text editor.

#### Step 6: Restart Apache

For the changes to take effect, restart the Apache server. You can do this through the XAMPP control panel by clicking the **Stop** button next to Apache, then clicking **Start** again. Alternatively, restart Apache from your preferred web server management tool.

#### Step 7: Verify SQLite is Enabled

To verify that the SQLite extension is enabled, you can create a simple PHP script that shows the loaded PHP extensions:

1. Create a file called `phpinfo.php` in your web server’s root directory (e.g., `C:\xampp\htdocs` if you're using XAMPP).
2. Add the following code inside the file:
   ```php
   <?php
   phpinfo();
   ?>
   ```
3. Access this file in your browser by going to `http://localhost/phpinfo.php`.
4. In the output, search for the **SQLite** section. If the SQLite extension is enabled, you should see entries for **SQLite3** and  **PDO_SQLite** .

After these steps, the SQLite driver will be enabled for PHP on your Windows server. If you have configured MagicAppBuilder to use SQLite, it should now be able to connect to the SQLite database.

## Step 2: Download and Install MagicAppBuilder on Your Server

To set up MagicAppBuilder on your server, follow these steps:

1. **Visit the MagicAppBuilder GitHub Repository**
   
   Go to the official MagicAppBuilder repository at: [https://github.com/Planetbiru/MagicAppBuilder](https://github.com/Planetbiru/MagicAppBuilder).
2. **Download the Latest Release**
   
   Navigate to the release page at: [https://github.com/Planetbiru/MagicAppBuilder/releases](https://github.com/Planetbiru/MagicAppBuilder/releases).
3. **Extract the Files to Your Server’s Document Root**
   
   After downloading the release, extract the ZIP file into the **Document Root** directory of your web server. The Document Root is typically a directory like `/var/www/html` on Linux-based systems or `C:\xampp\htdocs` if you’re using XAMPP on Windows.

   Make sure you extract the files into a directory named **MagicAppBuilder**. This will ensure that the application is easily accessible via the correct URL path.
4. **Verify Web Server Operation**
   
   Ensure that your web server (e.g., Apache or Nginx) is running correctly. You can do this by checking if the web server's status page or other websites hosted on the same server are functioning as expected.
5. **Access MagicAppBuilder from Your Browser**
   
   Open a browser of your choice. We recommend using the latest version of **Mozilla Firefox** for optimal performance and compatibility.

   Enter the following URL into the address bar:

   ```
   http://localhost/MagicAppBuilder
   ```

   This URL assumes you are installing MagicAppBuilder on the same machine as the web server. If the server is hosted on a remote machine, replace "localhost" with the appropriate IP address or domain name of the server.
6. **Log in to the Application**Upon accessing the URL, you should see the login page for MagicAppBuilder. To log in, use the following default credentials:

   - **Username**: `administrator`
   - **Password**: `administrator`

   Make sure to enter the credentials exactly as shown (without quotation marks). Once entered, click the **Login** button to proceed.
7. **Post-Login Actions**
   After successfully logging in, you’ll be directed to the MagicAppBuilder dashboard, where you can start configuring and managing your applications.

### Configuring SQLite as the Default Database

By default, MagicAppBuilder uses **SQLite** as its database. SQLite is a lightweight, serverless database engine that is ideal for smaller applications and easy to set up. When you create a new application, MagicAppBuilder will automatically create a database file within the application's directory, and you do not need to configure a separate database server

### Switching to MySQL or PostgreSQL

By default, MagicAppBuilder uses **SQLite** as its database. However, you can switch to **MySQL** or **PostgreSQL** if you prefer a more scalable database solution. To do this, you will need to modify the `core.yml` configuration file instead of changing settings within the application interface. Here's how you can configure MagicAppBuilder to use MySQL or PostgreSQL:

1. **Locate the `core.yml` File** The `core.yml` file is located in the `inc.cfg` directory within the MagicAppBuilder installation folder. Open this file with a text editor.
2. **Edit the Database Configuration** In the `core.yml` file, you will find a section related to database configuration. You need to change the `driver` setting to the desired database type (MySQL or PostgreSQL) and adjust the connection details. Here’s an example of how to configure the file for different databases:

   ### Example for MySQL:


   ```yaml
   dataLimit: 20
   database:
       driver: mysql
       host: 'localhost'
       port: 3306
       username: 'root'
       password: 'YourPasswordHere'
       databaseName: 'your_database_name'
       databaseSchema: 'public'
       timeZone: 'Asia/Jakarta'
   ```

   ### Example for PostgreSQL:

   ```yaml
   dataLimit: 20
   database:
       driver: postgresql
       host: 'localhost'
       port: 5432
       username: 'postgres'
       password: 'YourPasswordHere'
       databaseName: 'your_database_name'
       databaseSchema: 'public'
       timeZone: 'Asia/Jakarta'
   ```

   ### Default Configuration for SQLite:

   If you want to stick with SQLite, the configuration will look like this:

   ```yaml
   dataLimit: 20
   database:
       driver: sqlite
       host: ''
       port: 3306
       username: root
       password: Cebong2017
       databaseName: sipro
       databaseSchema: public
       timeZone: Asia/Jakarta
       databaseFilePath: 'D:\xampp\htdocs\MagicAppBuilder\inc.database\database.sqlite'
   ```
3. **Database Connection Details**For **MySQL** or **PostgreSQL**, you will need to provide the following information:

   - **Host**: The address of your database server (e.g., `localhost` or an IP address).
   - **Port**: The port number your database server is using (`3306` for MySQL or `5432` for PostgreSQL).
   - **Username**: The username to authenticate with the database (e.g., `root` for MySQL or `postgres` for PostgreSQL).
   - **Password**: The password associated with the database username.
   - **Database Name**: The name of the database you want to use for MagicAppBuilder.
   - **Database Schema**: Typically, this will be `public` for PostgreSQL, but may vary depending on your configuration.
4. **Save the Changes**After modifying the `core.yml` file with the correct database settings, save the file and close the text editor.
5. **Verify Database Connection**After saving the changes, restart your web server and verify that MagicAppBuilder is successfully connecting to the new database. You can do this by testing the application and checking for any connection errors. If the connection fails, double-check the details in the `core.yml` file, such as the hostname, port, username, and password.
6. **Create Database Tables**
   Once the database connection is established, you can start creating tables and managing your application's data using the **Database Explorer** and **Entity Editor** tools within MagicAppBuilder.

By editing the `core.yml` file, you can easily switch between different database types like SQLite, MySQL, or PostgreSQL. If you run into any issues, make sure your database server is running correctly and that the connection credentials in the configuration file are accurate.

### Additional Notes:

- **Security Considerations**: It is important to change the default "administrator" credentials immediately after logging in for the first time. This will enhance the security of your application. You can update the username and password through the system settings or user management section.
  Here’s how to update the instructions based on the use of the `core.yml` file for database configuration:
- **Browser Compatibility**: While MagicAppBuilder is designed to work with most modern browsers, we specifically recommend using the latest version of Mozilla Firefox. This is due to its superior handling of web technologies that MagicAppBuilder relies on.
- **SQLite Performance**: SQLite is a great choice for small to medium-sized applications and does not require a separate database server. However, if your application grows significantly in size and complexity, you might eventually need to consider migrating to a more robust database solution like MySQL or PostgreSQL.

By following these steps, you’ll have MagicAppBuilder set up and ready for configuration with SQLite or your preferred database (MySQL or PostgreSQL) on your server. If you run into any issues, make sure your server environment is properly configured and that the required dependencies are installed correctly.

## Step 3: Create a Workspace

After successfully installing MagicAppBuilder, the next step is to create a **Workspace**. A workspace is a directory that contains multiple projects. Users can access multiple workspaces, but each workspace is isolated from others. This helps manage admin access and privileges effectively.

To create a workspace:

1. Open MagicAppBuilder.
2. Navigate to the workspace creation section.
3. Define a new workspace directory.

Once you’ve created a workspace, you’re ready to move on to the next step of creating an application.

## Step 4: Create an Application

To create an application, you will need to fill out a form with the following required fields:

1. **Application Name**: Choose a name for your application.
2. **Application ID**: This is a unique identifier for your application. It will be used internally by MagicAppBuilder.
3. **Architecture**: Decide whether your application will be a "Monolith" (single, unified structure) or "Microservices" (distributed services) application.
4. **Description**: Provide a brief description of the application’s functionality.
5. **MagicApp Version**: The latest version of MagicAppBuilder, which is automatically suggested.
6. **Workspace**: Select the workspace you created in Step 2.
7. **Application Directory**: Define a folder where all application files will be stored.
8. **Base Namespace**: The base namespace for your application’s source code, typically formatted like `AppNamespace`.
9. **Author**: Specify the author of the application (e.g., your name or company).
10. **Application Path**: The directory for creating application-specific modules.

Once all the fields are filled out, click the **Save** button. MagicAppBuilder will display your newly created application in the form of a card.

## Step 5: Configure the Application

Next, you need to configure your application. To do this:

1. Click the **Settings** button on the application card.
2. **Database Configuration**: By default, MagicAppBuilder uses SQLite with a file located within the application directory. You can configure it to use a different database or DBMS if desired.
3. **Session Configuration**: Set the session parameters such as session name, session lifetime, Session Save Handler, and Session Save Path.

### Reserved Columns Configuration

There is an important section called **Reserved Columns** that must be configured early and cannot be changed once you start creating the database, entities, and modules.

**Reserved columns** include:

- `name`
- `active`
- `draft`
- `waiting_for`
- `admin_create`
- `admin_edit`
- `admin_ask_edit`
- `time_create`
- `time_edit`
- `time_ask_edit`
- `ip_create`
- `ip_edit`
- `ip_ask_edit`
- `sort_order`
- `approval_id`
- `approval_note`
- `approval_status`

Reserved columns can be mapped to other names according to the native language used by the application and the terminology that will be used in each entity. Each entity must consistently use the full name if it is going to use it.

For example:

The `album` entity requires the `sort_order` column to sort the albums. So the `album` entity must use the `sort_order` column and not others to sort the data.

The `album` entity also requires the `active` column to activate and deactivate data. So the `album` entity must use the `active` column and not others to activate and deactivate data.

On the other hand, the `artist` entity only does not need the `sort_order` column because artist data is not sorted by default by the user but still uses the `active` column to activate and deactivate data. So the `artist` entity must use the `active` column and not others to activate and deactivate data.

If the application is built in a language other than English, it would be strange to still use column names such as `active`, `admin_create`, `ip_create` and so on. Therefore, developers are free to choose other names but must create column mappings.

The following is an example of column mapping into Indonesian.

| Original Key    | Translated Key   |
| --------------- | ---------------- |
| name            | nama             |
| sort_order      | sort_order       |
| active          | aktif            |
| draft           | draft            |
| waiting_for     | waiting_for      |
| admin_create    | admin_buat       |
| admin_edit      | admin_ubah       |
| admin_ask_edit  | admin_minta_ubah |
| time_create     | waktu_buat       |
| time_edit       | waktu_ubah       |
| time_ask_edit   | waktu_minta_ubah |
| ip_create       | ip_buat          |
| ip_edit         | ip_ubah          |
| ip_ask_edit     | ip_minta_ubah    |
| approval_id     | approval_id      |
| approval_note   | approval_note    |
| approval_status | approval_status  |

Developers for applications that use Indonesian as the native language of the application can use the translated columns to create columns from entities or tables.

Here is an explanation of the reserved columns above.

| Original Key    | Description                               |
| --------------- | ----------------------------------------- |
| name            | Represents a single row in an entity.     |
| sort_order      | Used for sorting data.                    |
| active          | To activate or deactivate data.           |
| draft           | Marks new data awaiting approval.         |
| waiting_for     | Specifies required approvals.             |
| admin_create    | User ID who created the data.             |
| admin_edit      | User ID who last edited the data.         |
| admin_ask_edit  | User ID who requested the edit.           |
| time_create     | Timestamp when data was created.          |
| time_edit       | Timestamp when data was last modified.    |
| time_ask_edit   | Timestamp when edit was requested.        |
| ip_create       | IP address where data was created.        |
| ip_edit         | IP address where data was last modified.  |
| ip_ask_edit     | IP address where edit was requested.      |
| approval_id     | ID of the data in the approval table.     |
| approval_note   | Notes for approval.                       |
| approval_status | Status of the approval.                   |

An example of `sortOrder` is reference data, such as song genres, which need to be sorted based on the number of genres produced by a studio. Another example is sorting application user types based on their authority level. User types with higher authority can be placed at the top, so when assigning roles, the user can easily identify which types have the highest and lowest authority.

These columns should not be modified once the database structure is created.

After all configurations are filled out, click **Save**. You can always revisit and modify these settings, including the reserved columns, before you start creating the database and modules.

## Step 6: Set Up the Database Structure

Once you’ve configured the application, you need to set up the database structure. MagicAppBuilder provides two main tools for this:

1. **Database Explorer**
2. **Entity Editor**

You can also use third-party applications to create the database structure if you prefer.

### Using Database Explorer and Entity Editor:

To start, click the **Database** button on the application card. MagicAppBuilder will display a **Database Explorer** dialog.

The process of creating the database structure will be explained in a separate section, but using the **Database Explorer** and **Entity Editor**, you can easily manage your database schema and entities.

## Step 7: Creating Entities with the Entity Editor

To create and manage entities using the **Entity Editor** in MagicAppBuilder, follow these detailed steps:

### 1. **Open the Entity Editor**

- Go to the **Database Explorer** and at the bottom, you will find the **Entity Editor** button. Click this button to open the **Entity Editor** interface.

### 2. **Entity Editor Interface**

The **Entity Editor** consists of two main sections:

- **Main Bar**: Located at the top, where you can find various buttons to manage entities.
- **Sidebar**: Displays the structure of the selected entity, showing columns, data types, and other details.

### 3. **Actions Available in the Main Bar and Sidebar**

At the bottom of both the **main bar** and **sidebar**, you will see the following buttons:

- **Add New Entity**: To create a new entity.
- **Upload Entity**: To import an entity from a JSON file.
- **Download Entity**: To export an entity to a JSON file.
- **Upload SQL**: To import an entity from an SQL file.
- **Download SQL**: To export an entity to an SQL file.
- **Download SVG**: To download a diagram of your entity in SVG format.
- **Download PNG**: To download a diagram of your entity in PNG format.
- **Sort Entity**: To sort entities alphabetically.

### 4. **Create a New Entity**

- Click the **Add New Entity** button. This will open a form where you can define the **name** of your new entity.
- Enter the name of your entity, then press **Enter** on your keyboard.

### 5. **Add Columns to Your Entity**

- After defining the entity name, the **Entity Editor** will present a form where you can add **columns** to your entity.
- Click the **Add Column** button to add a new column.
- Define the column name, then press **Enter** on your keyboard to confirm.
- Repeat this process for every column you need to add to the entity.

### 6. **Primary Key Naming Convention**

- The **primary key** of the entity must follow a specific naming convention. The name of the primary key should match the entity name, with the suffix `_id`. For example, if your entity is named `user`, the primary key should be `user_id`.

### 7. **Reserved Columns and Templates**

- Earlier, you configured **reserved columns**. These reserved columns can be used to create templates.
- To create a template, click the **Edit Template** button. This will allow you to edit a template with predefined columns (such as `name`, `active`, etc.).

In the template editor, you will find the following buttons:

- **Add Column**: To add a column to the template.
- **Save Template**: To save the template for later use.
- **Cancel**: To cancel editing the template.

**Templates** are helpful for adding multiple columns that always need to be the same, reducing errors and ensuring consistency in your database structure.

### 8. **Set Preferences**

- Click the **Preferences** button to configure the column settings.
- In the **Preferences** dialog, you can set the following options:

  - **Primary Key Type**: Define the data type of the primary key (e.g., INT, UUID, etc.).
  - **Primary Key Length**: Set the length for the primary key data.
  - **Column Type**: Choose the data type for non-primary key columns (e.g., VARCHAR, INT, DATE).
  - **Column Length**: Define the length for the non-primary key column data.
- Once you’ve made your adjustments, click **OK** to apply the changes, or **Cancel** to discard them.

After you’ve finished adding columns and setting preferences, don’t forget to **Save** the entity by clicking the **Save Entity** button. Do the same to create other entities. The template and preferences do not need to be changed again. You can always come back to edit the entity and its columns as needed.

With these steps, you can effectively create and manage entities in MagicAppBuilder using the **Entity Editor**, streamlining the process of building your application’s database schema.

### 9. **Apply Entities to the Database**

After you have created all the entities, the next step is to apply them to the database.

1. **Select All Entities**: Check all the entities in the sidebar.
2. **Import Entities**: Click the **Import** button at the bottom of the dialog. This will return you to the **Database Explorer**.

In the **Database Explorer**, you will see a **"CREATE TABLE"** query in the query editor.

3. **Execute the Query**: Click the **Execute** button to run the query. MagicAppBuilder will create the tables in the database according to the design you have made.

Make sure that all the entities are successfully imported into the database.

Once the database creation is complete, you can exit the **Database Explorer** and start creating your application modules.

## Step 8: Creating an Application Menu

1. **Go to Application Card**:

   - Return to the application card and click the **Menu** button.
2. **Define Menu**:

   - Choose the application menu you want to create or modify.
   - You can create a new menu, add items to an existing menu, or edit a menu that you have already created.

This allows you to organize and manage the navigation structure for your application.

## Step 9: Creating a Module

1. **Select the Table**:

   - In the **Select Table** tab, click the **Reload Table** button to load the tables that you created in the previous step.
   - Choose one of the tables for which you want to create a module.
2. **Module Option**:

   - In the **Module Option** section, select the target where you want to create the module. If no target exists, click the **Manage** button and create a new path.
3. **Update Entity**:

   - Check the **Update Entity** option if you want to overwrite the entity file that you previously created.
4. **Load Saved Module**:

   - The **Load Saved Module** option will load the configuration you had previously created when making a module. This allows you to edit a module you created earlier without having to manually fill in all the details again.
5. **Select Menu (Optional)**:

   - If you have created a menu, choose where the module will be placed within the menu.
6. **Load Columns**:

   - Next, click the **Load Column** button. This will direct you to the **Generate Module** tab, which will have more options for you to configure.

## Step 10: Generate Module Tab Explanation

The **Generate Module** tab contains a table with multiple columns. These columns are explained as follows:

### Columns in the Table:

- **Field**: Corresponds to the column name from the selected table.
- **Caption**: Title-case version of the column name from the selected table.
- **I**: Check to enable Insert functionality.
- **U**: Check to enable Update functionality.
- **D**: Check to enable Detail functionality.
- **L**: Check to enable List functionality.
- **E**: Check to enable Export functionality.
- **K**: Check to mark the column as the Primary Key.
- **R**: Check to mark the column as Required (Mandatory).

### Input Section:

In this section, the following options are available:

- **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, color, etc.).
- **TA**: Indicates that the input is a textarea.
- **CB**: Indicates that the input is a checkbox.
- **SE**: Indicates that the input is a select field (dropdown).
- **MU**: Indicates that the input has multiple value.


### Filter Section:

In the **Filter** section, the following options are available:

- **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, etc.).
- **SE**: Indicates that the input is a select field (dropdown).
- **MU**: Indicates that the input has multiple value.

### Data Type:

The data type of the text input field.

### Filter Type:

The filter to be applied by the application when receiving input from the user.

### Explanation of the SE Column in Input and Filter

The **SE** column in both the **Input** and **Filter** sections is used to link a column from an entity or table with data from another source. This data can come from another database or a user-defined value. This is typically used to connect a column with data from another table.

#### Example:

- The `song` table has the following columns:
  - `song_id`
  - `name`
  - `artist_id`
  - `recording_date`
- The `artist` table has the following columns:
  - `artist_id`
  - `name`
  - `phone`

When displaying song data, the application will show the artist’s name instead of the artist's code. To do this, the application will perform a **join** between the `song` and `artist` tables.

#### Source for SE in Input and Filter:

When the user selects **SE**, a **Source** button will appear that the user must configure.

### Source Configuration:

#### 1. **Entity**:

If the user selects **Entity**, MagicAppBuilder will show a form with the following fields:

- **Entity Name**: The name of the entity to be created (by default, this is the Pascal-case version of the table name). It is recommended to add the suffix "Min".
- **Table Name**: The name of the source table.
- **Primary Key**: The name of the primary key in the source table.
- **Value Column**: The column name to be used as the label for the select options.
- **Reference Object Name**: The name of the property used for the join in the entity.
- **Reference Property Name**: The name of the property from the reference entity that will appear in both the detail and list views of the module.

#### Option Node:

Contains the following field:

- **Format and Parameters**: The format for the select element label.

#### Specification:

This is used to filter the data that will be displayed in the select element:

- **Column Name**: The property name in the entity.
- **Value**: The expected value.

#### Sortable:

Used to order the data in the select element:

- **Column Name**: The property name in the entity.
- **Value**: The sort order (ASC or DESC).


#### Grouping:

Used to group options in the dropdown.

1.  If the dropdown source is **Entity**, grouping can be done using another entity referenced by the source entity.
    
    -   **Value**: The property from the referenced entity that is referred to by the source entity of the dropdown.
    -   **Label**: The property from the referenced entity that will be used as the label for the option group in the dropdown. The label is only used if the group source is an **Entity**.
    -   **Reference**: The data source for the option group in the dropdown. If the dropdown source is an **Entity**, then **Reference** is the property from the source entity that acts as the source for the option group. If the dropdown source is a **Map**, then **Reference** is a pair of **Value** and **Label**. All dropdown options that have the same **Value** will be grouped under the label **Label**.
2.  If the dropdown source is **Map**, grouping can be done using the **Group** column, which can be filled directly in the map.


#### Additional Output:

Used to display multiple columns in the option label in the select element.

#### 2. **Map**:

If the user selects **Map**, MagicAppBuilder will show a form with the following fields:

- **Value**: The value of the option.
- **Label**: The label for the option.
- **Group**: The label for the option group.

The user can enter multiple options for the map.

#### 3. **Yes/No**, **True/False**, **1/0**:

For these options, the user does not need to fill anything out.

### Explanation of the Buttons Below the Table

Below the table in the **Generate Module** tab, you will find the following buttons:

#### 1. **Data Filter**:

Used to filter the data that will be displayed in the module, whether it's in the list, detail, edit, delete, approve, or reject views. This filter ensures that users can’t access data they are not authorized to view or manipulate, making it ideal for multi-user applications.

#### 2. **Data Order**:

Used to define the order in which data will be displayed in the list view. Users can still change the order by clicking on the column headers.

#### 3. **Module Features**:

This section allows you to configure additional features for the module. The available options include:

- **Activate/Deactivate**: Enables or disables the data.
- **Manual Sort Order**: Allows users to manually order the data.
- **Export to Excel**: Exports data to an Excel file.
- **Export to CSV**: Exports data to a CSV file.
- **Use Temporary File**: Uses a temporary file during the export process.
- **Approval**: Enables approval workflows, allowing users to approve or reject data for insert, update, delete, activation, or deactivation.
- **Approval Note**: This feature is not yet available.
- **Approval Type**: Defines how approval will be displayed. "Separated" shows two buttons (Approve/Reject), while "Combined" displays a single button showing the action (new data, edit, delete, etc.).
- **Approval Position**: Specifies the position of the approval button (before or after the data).
- **Approval by Another User**: Determines if approval must be done by a different user.
- **Trash**: Determines whether deleted data should be stored in the trash table.
- **AJAX Support**: Indicates whether AJAX should be used in the list view.
- **Subquery**: Determines whether a subquery should be used in the list view to improve performance with large datasets.

## Step 11: Submit the Form to Create the Module and Entity

1. **Generate Module**:

   - Below, there is a **"Generate Module"** button. When you click it, MagicAppBuilder will display a confirmation dialog for your action.
   - If you choose **OK**, MagicAppBuilder will create the module and entity based on the settings you configured.
2. **View Created Modules**:

   - You can view the module you created in the **"Edit Module"** tab.
3. **View Created Entities**:

   - You can view the entity you created in the **"Edit Entity"** tab.
4. **View Entity Relationships**:

   - You can view the relationships between the entities you created in the **"ERD"** (Entity Relationship Diagram) tab.
5. **Additional Features (Sort Order, Trash, Approval)**:

   - If you enabled features like **Sort Order**, **Trash**, or **Approval**, MagicAppBuilder may create additional tables and columns that were not previously defined.
   - You can see the **"ALTER TABLE"** queries in the **Query** tab.
   - In the **Query** tab, you can also apply these queries directly to the database by executing them.

## Step 12: Update Database Structure

After creating your module and entities, you may need to update the database structure depending on the features you are using in the module. If you are using features such as activate, deactivate, or sort order, but you don't yet have columns for them, or you are using approval and trash features, MagicAppBuilder will add several columns to the created entities. As a result, you will need to update the database structure.

Open the Query tab, check **Merge queries by table** and **Select all**. By default, **Merge queries by table** is already checked.

MagicAppBuilder will generate a database query to update the database structure. If MagicAppBuilder does not display a query, it means the database structure is already in line with the entities you created. If MagicAppBuilder displays a query, execute the query by selecting the query to execute, then click the **Execute Query** button located below the editor. MagicAppBuilder will display a dialog and copy the query you selected. Proceed by clicking the **Execute** button at the bottom of the dialog. MagicAppBuilder will update the contents of the editor according to the latest conditions.

If you need a query to create the database structure from scratch for a new, empty database, check **Create new**. MagicAppBuilder will generate a database query to create all the tables instead of a query to update the structure.

## Step 13: Creating Localization

You can create localization by translating both modules and entities into other languages as per the user’s needs.

### Translating a Module

1. **Translate Module**:

   - You can translate all the labels and buttons of the modules into another language in the **"Translate Module"** tab.
   - Select the module you want to translate and choose the target language. If the target language is not available, click the **"Manage"** button.
2. **Interface**:

   - The screen is divided into two sections. The left side shows the original labels in the application's default language, and the right side shows the labels in the target language.
   - You can only modify the labels on the target language side.
3. **Translate All Modules**:

   - You have the option to translate all modules at once if needed.

### Translating an Entity

1. **Translate Entity**:

   - You can translate all the labels and buttons of the entities into another language in the **"Translate Entity"** tab.
   - Select the entity you want to translate and choose the target language. If the target language is not available, click the **"Manage"** button.
2. **Interface**:

   - The screen is divided into two sections. The left side shows the original labels in the application's default language, and the right side shows the labels in the target language.
   - You can only modify the labels on the target language side.
3. **Translate Entities One by One**:

   - You will need to translate each entity individually.

## Step 14: Add Favicon to the Application

Go to the Apps tab, and MagicAppBuilder will display the application cards. Click the Icon button on an application. Upload a square image in PNG format. The minimum image size is 512x125 pixels. The application will automatically generate several icon files in the application directory with the following names:

- favicon-16x16.png
- favicon-32x32.png
- favicon-48x48.png
- apple-icon-57x57.png
- apple-icon-60x60.png
- apple-icon-72x72.png
- apple-icon-76x76.png
- apple-icon-114x114.png
- apple-icon-120x120.png
- apple-icon-144x144.png
- apple-icon-152x152.png
- apple-icon-180x180.png
- android-icon-192x192.png
- android-icon-512x512.png
- favicon.ico

The `favicon.ico` file contains 3 images with sizes of 16x16, 32x32, and 48x48 pixels.

MagicAppBuilder also generates a `manifest.json` file containing the following information:

```json
{
    "name": "Application Name",
    "short_name": "AppName",
    "icons": [
        {
            "src": "apple-icon-57x57.png",
            "sizes": "57x57",
            "type": "image\/png"
        },
        {
            "src": "apple-icon-60x60.png",
            "sizes": "60x60",
            "type": "image\/png"
        },
        {
            "src": "android-icon-192x192.png",
            "sizes": "192x192",
            "type": "image\/png"
        }
    ],
    "start_url": "\/",
    "display": "standalone"
}
```

## Step 15: Change the Application Option

**Application Option** is used to configure application access. By default, the application displays a menu sourced from the file `/inc.cfg/menu.yml`, which is automatically generated by **MagicAppBuilder**. This file can be modified by the user.

At this point, the application does not have any registered users in the database. Users can access all modules **without logging in** and **without any permission requirements**. Also, the application can **only be accessed from localhost**.  
**Application Option** allows users to configure all of the above settings.

When a user clicks the **“Option”** button on the application card, the **Application Option** appears as a dialog containing 3 accordion sections:

1.  Application Menu
    
2.  Application User
    
3.  Application Mode
    

### Application Menu

In this accordion section, users can import all menus from the `/inc.cfg/menu.yml` file into the database as module groups and modules.  
To import the menu, click the **“Import Menu”** button. MagicAppBuilder will import all menu data from the YAML file into the database. Successfully imported menu items and submenus will be marked with a checkmark on the right side of the menu.

The menu import process can be repeated whenever there are updates in the `/inc.cfg/menu.yml` file. Users can also clear existing data in the module group and module before re-importing the menu.


### Application User

In this section, users can create application user accounts in the database. The created user account will have the following properties:

-   **Name**: Super User
    
-   **Username**: superuser
    
-   **Password**: superuser
    

To create the user account, click the **“Create User”** button. If the account does not exist, MagicAppBuilder will create it with the **“superuser”** role. If the **“superuser”** role itself doesn’t yet exist, MagicAppBuilder will create it first before creating the user account.

Users can reset the password by checking the user account shown in this accordion and then clicking the **“Reset Password”** button. The password will be reset to match the username.

In addition to resetting passwords, users can also assign the **Superuser** role to a selected account. **Be cautious** when granting the Superuser role, as it allows the user to access **all features in all modules without restriction**.

To assign the Superuser role, check the user account and click the **“Set Superuser Role”** button. MagicAppBuilder will assign Superuser access to all modules in the application database.

### Application Mode

This section contains 3 options:

1.  **Development Mode**
    
2.  **Bypass Role**
    
3.  **Access Localhost Only**
    

#### Development Mode

This option determines the data source for the application menu.

-   If checked (“Yes”), the application will display the menu based on the data in `/inc.cfg/menu.yml`.
    
-   If unchecked, the application will display the menu from the database (module group and module).

#### Bypass Role

This option determines whether users must **log in** to access the application modules.

-   If Bypass Role is **checked**, users can access the application **without logging in**, and there are **no access restrictions**.
    
-   If unchecked, the application will require users to **log in** with valid user credentials. It will then check whether the user has permission to access specific modules.

#### Access Localhost Only

This option **prevents the application from being accessed from other devices**. It is **highly recommended** to enable this option when **Bypass Role** is active.  
If Bypass Role is checked, **Access Localhost Only must also be checked** to prevent unauthorized access from other machines.

In certain situations, users may temporarily allow access from other devices, but it should be **disabled again as soon as it's no longer needed**.

**What are the risks if Bypass Role is enabled but Access Localhost Only is not?**

Accessing the application from another device cannot alter the source code but **can delete application data**, including application modules. This could seriously disrupt the application development process.

