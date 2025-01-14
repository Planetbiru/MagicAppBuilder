# MagicAppBuilder Usage Guide

MagicAppBuilder is a powerful tool for creating applications quickly, allowing users to design and manage both the front-end and back-end of their software with minimal coding. This guide will take you through each step, from installation to generating modules, and ensure you have a smooth experience with the platform.

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

To begin setting up MagicAppBuilder on your server, follow these steps:

1. **Visit the MagicAppBuilder GitHub Repository**Go to the official MagicAppBuilder repository at: [https://github.com/Planetbiru/MagicAppBuilder](https://github.com/Planetbiru/MagicAppBuilder).
2. **Download the Latest Release**Once on the GitHub page, navigate to the **Releases** section. Here, you’ll find the most recent stable version of MagicAppBuilder. Click on the release to download the ZIP file containing the latest version.
3. **Extract the Files to Your Server’s Document Root**After downloading the release, extract the ZIP file into the **Document Root** directory of your web server. The Document Root is typically a directory like `/var/www/html` on Linux-based systems or `C:\xampp\htdocs` if you’re using XAMPP on Windows.

   Make sure you extract the files into a directory named **MagicAppBuilder**. This will ensure that the application is easily accessible via the correct URL path.
4. **Verify Web Server Operation**Ensure that your web server (e.g., Apache or Nginx) is running correctly. You can do this by checking if the web server's status page or other websites hosted on the same server are functioning as expected.
5. **Access MagicAppBuilder from Your Browser**Open a browser of your choice. We recommend using the latest version of **Mozilla Firefox** for optimal performance and compatibility.

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
       databaseFilePath: 'D:\xampp\htdocs\MagicAppBuilder\inc.cfg\database.sqlite'
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

It is essential to configure these reserved columns in line with the application's language requirements. These columns should not be modified once the database structure is created.

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

### Element Type Section:

In this section, the following options are available:

- **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, color, etc.).
- **TA**: Indicates that the input is a textarea.
- **CB**: Indicates that the input is a checkbox.
- **SE**: Indicates that the input is a select field (dropdown).

### Search Section:

In the **Search** section, the following options are available:

- **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, etc.).
- **SE**: Indicates that the input is a select field (dropdown).

### Data Type:

The data type of the text input field.

### Filter Type:

The filter to be applied by the application when receiving input from the user.

### Explanation of the SE Column in Element Type and Search

The **SE** column in both the **Element Type** and **Search** sections is used to link a column from an entity or table with data from another source. This data can come from another database or a user-defined value. This is typically used to connect a column with data from another table.

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

#### Source for SE in Element Type and Search:

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

#### Additional Output:

Used to display multiple columns in the option label in the select element.

#### Selection:

Indicates whether the select element will allow single or multiple selections.

#### 2. **Map**:

If the user selects **Map**, MagicAppBuilder will show a form with the following fields:

- **Value**: The value of the option.
- **Label**: The label for the option.

The user can enter multiple options for the map.

#### Selection:

Indicates whether the select element will allow single or multiple selections.

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

## Step 12: Creating Localization

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
