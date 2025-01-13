# MagicAppBuilder Usage Tutorial

## Step 1: Install Server

Before you begin using MagicAppBuilder, you need to install a server that consists of the following components:

1.  **Web Server**: Apache
2.  **Database**: MySQL or MariaDB
3.  **PHP**

There are several options available to install these components, such as **WAMP**, **XAMPP**, or **USBWebServer**. Choose the one that best fits your needs and install it accordingly.

## Step 2: Create a Workspace

After successfully installing MagicAppBuilder, the next step is to create a **Workspace**. A workspace is a directory that contains multiple projects. Users can access multiple workspaces, but each workspace is isolated from others. This helps manage admin access and privileges effectively.

To create a workspace:

1.  Open MagicAppBuilder.
2.  Navigate to the workspace creation section.
3.  Define a new workspace directory.

Once you’ve created a workspace, you’re ready to move on to the next step of creating an application.

## Step 3: Create an Application

To create an application, you will need to fill out a form with the following required fields:

1.  **Application Name**: Name of your application.
2.  **Application ID**: Unique identifier for your application.
3.  **Architecture**: Choose between "Monolith Application" or "Microservices Application".
4.  **Description**: Brief description of your application.
5.  **MagicApp Version**: The latest version of MagicApp (this is automatically suggested).
6.  **Workspace**: Select the workspace you just created.
7.  **Application Directory**: Define the directory for your application.
8.  **Base Namespace**: The base namespace for the application code.
9.  **Author**: Name of the author.
10.  **Application Path**: The directory where you will create modules for your application.

Once all the fields are filled out, click the **Save** button. MagicAppBuilder will display your newly created application in the form of a card.

## Step 4: Configure the Application

Next, you need to configure your application. To do this:

1.  Click the **Settings** button on the application card.
2.  **Database Configuration**: By default, MagicAppBuilder uses SQLite with a file located within the application directory. You can configure it to use a different database or DBMS if desired.
3.  **Session Configuration**: Set the session parameters such as session name, session lifetime, Session Save Handler, and Session Save Path.

### Reserved Columns Configuration

There is an important section called **Reserved Columns** that must be configured early and cannot be changed once you start creating the database, entities, and modules.

**Reserved columns** include:

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

It is essential to configure these reserved columns in line with the application's language requirements. These columns should not be modified once the database structure is created.

After all configurations are filled out, click **Save**. You can always revisit and modify these settings, including the reserved columns, before you start creating the database and modules.

## Step 5: Set Up the Database Structure

Once you’ve configured the application, you need to set up the database structure. MagicAppBuilder provides two main tools for this:

1.  **Database Explorer**
2.  **Entity Editor**

You can also use third-party applications to create the database structure if you prefer.

### Using Database Explorer and Entity Editor:

To start, click the **Database** button on the application card. MagicAppBuilder will display a **Database Explorer** dialog.

The process of creating the database structure will be explained in a separate section, but using the **Database Explorer** and **Entity Editor**, you can easily manage your database schema and entities.

## Step 6: Creating Entities with the Entity Editor

To create and manage entities using the **Entity Editor** in MagicAppBuilder, follow these detailed steps:

### 1. **Open the Entity Editor**

-   Go to the **Database Explorer** and at the bottom, you will find the **Entity Editor** button. Click this button to open the **Entity Editor** interface.

### 2. **Entity Editor Interface**

The **Entity Editor** consists of two main sections:

-   **Main Bar**: Located at the top, where you can find various buttons to manage entities.
-   **Sidebar**: Displays the structure of the selected entity, showing columns, data types, and other details.

### 3. **Actions Available in the Main Bar and Sidebar**

At the bottom of both the **main bar** and **sidebar**, you will see the following buttons:

-   **Add New Entity**: To create a new entity.
-   **Upload Entity**: To import an entity from a JSON file.
-   **Download Entity**: To export an entity to a JSON file.
-   **Upload SQL**: To import an entity from an SQL file.
-   **Download SQL**: To export an entity to an SQL file.
-   **Download SVG**: To download a diagram of your entity in SVG format.
-   **Download PNG**: To download a diagram of your entity in PNG format.
-   **Sort Entity**: To sort entities alphabetically.

### 4. **Create a New Entity**

-   Click the **Add New Entity** button. This will open a form where you can define the **name** of your new entity.
-   Enter the name of your entity, then press **Enter** on your keyboard.

### 5. **Add Columns to Your Entity**

-   After defining the entity name, the **Entity Editor** will present a form where you can add **columns** to your entity.
-   Click the **Add Column** button to add a new column.
-   Define the column name, then press **Enter** on your keyboard to confirm.
-   Repeat this process for every column you need to add to the entity.

### 6. **Primary Key Naming Convention**

-   The **primary key** of the entity must follow a specific naming convention. The name of the primary key should match the entity name, with the suffix `_id`. For example, if your entity is named `user`, the primary key should be `user_id`.

### 7. **Reserved Columns and Templates**

-   Earlier, you configured **reserved columns**. These reserved columns can be used to create templates.
-   To create a template, click the **Edit Template** button. This will allow you to edit a template with predefined columns (such as `name`, `active`, etc.).

In the template editor, you will find the following buttons:

-   **Add Column**: To add a column to the template.
-   **Save Template**: To save the template for later use.
-   **Cancel**: To cancel editing the template.

**Templates** are helpful for adding multiple columns that always need to be the same, reducing errors and ensuring consistency in your database structure.

### 8. **Set Preferences**

-   Click the **Preferences** button to configure the column settings.
    
-   In the **Preferences** dialog, you can set the following options:
    
    -   **Primary Key Type**: Define the data type of the primary key (e.g., INT, UUID, etc.).
    -   **Primary Key Length**: Set the length for the primary key data.
    -   **Column Type**: Choose the data type for non-primary key columns (e.g., VARCHAR, INT, DATE).
    -   **Column Length**: Define the length for the non-primary key column data.
-   Once you’ve made your adjustments, click **OK** to apply the changes, or **Cancel** to discard them.

After you’ve finished adding columns and setting preferences, don’t forget to **Save** the entity by clicking the **Save Entity** button. Do the same to create other entities. The template and preferences do not need to be changed again. You can always come back to edit the entity and its columns as needed. 

With these steps, you can effectively create and manage entities in MagicAppBuilder using the **Entity Editor**, streamlining the process of building your application’s database schema.

### 9. **Apply Entities to the Database**

After you have created all the entities, the next step is to apply them to the database.

1.  **Select All Entities**: Check all the entities in the sidebar.
2.  **Import Entities**: Click the **Import** button at the bottom of the dialog. This will return you to the **Database Explorer**.

In the **Database Explorer**, you will see a **"CREATE TABLE"** query in the query editor.

3.  **Execute the Query**: Click the **Execute** button to run the query. MagicAppBuilder will create the tables in the database according to the design you have made.

Make sure that all the entities are successfully imported into the database.

Once the database creation is complete, you can exit the **Database Explorer** and start creating your application modules.

## Step 7: Creating an Application Menu

1.  **Go to Application Card**:
    
    -   Return to the application card and click the **Menu** button.
2.  **Define Menu**:
    
    -   Choose the application menu you want to create or modify.
    -   You can create a new menu, add items to an existing menu, or edit a menu that you have already created.

This allows you to organize and manage the navigation structure for your application.

## Step 8: Creating a Module

1.  **Select the Table**:
    
    -   In the **Select Table** tab, click the **Reload Table** button to load the tables that you created in the previous step.
    -   Choose one of the tables for which you want to create a module.
2.  **Module Option**:
    
    -   In the **Module Option** section, select the target where you want to create the module. If no target exists, click the **Manage** button and create a new path.
3.  **Update Entity**:
    
    -   Check the **Update Entity** option if you want to overwrite the entity file that you previously created.
4.  **Load Saved Module**:
    
    -   The **Load Saved Module** option will load the configuration you had previously created when making a module. This allows you to edit a module you created earlier without having to manually fill in all the details again.
5.  **Select Menu (Optional)**:
    
    -   If you have created a menu, choose where the module will be placed within the menu.
6.  **Load Columns**:
    
    -   Next, click the **Load Column** button. This will direct you to the **Generate Module** tab, which will have more options for you to configure.

## Step 9: Generate Module Tab Explanation

The **Generate Module** tab contains a table with multiple columns. These columns are explained as follows:

### Columns in the Table:

-   **Field**: Corresponds to the column name from the selected table.
-   **Caption**: Title-case version of the column name from the selected table.
-   **I**: Check to enable Insert functionality.
-   **U**: Check to enable Update functionality.
-   **D**: Check to enable Detail functionality.
-   **L**: Check to enable List functionality.
-   **E**: Check to enable Export functionality.
-   **K**: Check to mark the column as the Primary Key.
-   **R**: Check to mark the column as Required (Mandatory).

### Element Type Section:

In this section, the following options are available:

-   **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, color, etc.).
-   **TA**: Indicates that the input is a textarea.
-   **CB**: Indicates that the input is a checkbox.
-   **SE**: Indicates that the input is a select field (dropdown).

### Search Section:

In the **Search** section, the following options are available:

-   **TE**: Indicates that the input is a text field (e.g., text, number, date, datetime-local, time, tel, email, url, etc.).
-   **SE**: Indicates that the input is a select field (dropdown).

### Data Type:

The data type of the text input field.

### Filter Type:

The filter to be applied by the application when receiving input from the user.

### Explanation of the SE Column in Element Type and Search

The **SE** column in both the **Element Type** and **Search** sections is used to link a column from an entity or table with data from another source. This data can come from another database or a user-defined value. This is typically used to connect a column with data from another table.

#### Example:

-   The `song` table has the following columns:
    -   `song_id`
    -   `name`
    -   `artist_id`
    -   `recording_date`
-   The `artist` table has the following columns:
    -   `artist_id`
    -   `name`
    -   `phone`

When displaying song data, the application will show the artist’s name instead of the artist's code. To do this, the application will perform a **join** between the `song` and `artist` tables.

#### Source for SE in Element Type and Search:

When the user selects **SE**, a **Source** button will appear that the user must configure.

### Source Configuration:

#### 1. **Entity**:

If the user selects **Entity**, MagicAppBuilder will show a form with the following fields:

-   **Entity Name**: The name of the entity to be created (by default, this is the Pascal-case version of the table name). It is recommended to add the suffix "Min".
-   **Table Name**: The name of the source table.
-   **Primary Key**: The name of the primary key in the source table.
-   **Value Column**: The column name to be used as the label for the select options.
-   **Reference Object Name**: The name of the property used for the join in the entity.
-   **Reference Property Name**: The name of the property from the reference entity that will appear in both the detail and list views of the module.

#### Option Node:

Contains the following field:

-   **Format and Parameters**: The format for the select element label.

#### Specification:

This is used to filter the data that will be displayed in the select element:

-   **Column Name**: The property name in the entity.
-   **Value**: The expected value.

#### Sortable:

Used to order the data in the select element:

-   **Column Name**: The property name in the entity.
-   **Value**: The sort order (ASC or DESC).

#### Additional Output:

Used to display multiple columns in the option label in the select element.

#### Selection:

Indicates whether the select element will allow single or multiple selections.

#### 2. **Map**:

If the user selects **Map**, MagicAppBuilder will show a form with the following fields:

-   **Value**: The value of the option.
-   **Label**: The label for the option.

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

-   **Activate/Deactivate**: Enables or disables the data.
-   **Manual Sort Order**: Allows users to manually order the data.
-   **Export to Excel**: Exports data to an Excel file.
-   **Export to CSV**: Exports data to a CSV file.
-   **Use Temporary File**: Uses a temporary file during the export process.
-   **Approval**: Enables approval workflows, allowing users to approve or reject data for insert, update, delete, activation, or deactivation.
-   **Approval Note**: This feature is not yet available.
-   **Approval Type**: Defines how approval will be displayed. "Separated" shows two buttons (Approve/Reject), while "Combined" displays a single button showing the action (new data, edit, delete, etc.).
-   **Approval Position**: Specifies the position of the approval button (before or after the data).
-   **Approval by Another User**: Determines if approval must be done by a different user.
-   **Trash**: Determines whether deleted data should be stored in the trash table.
-   **AJAX Support**: Indicates whether AJAX should be used in the list view.
-   **Subquery**: Determines whether a subquery should be used in the list view to improve performance with large datasets.

## Step 10: Submit the Form to Create the Module and Entity

1.  **Generate Module**:
    
    -   Below, there is a **"Generate Module"** button. When you click it, MagicAppBuilder will display a confirmation dialog for your action.
    -   If you choose **OK**, MagicAppBuilder will create the module and entity based on the settings you configured.
2.  **View Created Modules**:
    
    -   You can view the module you created in the **"Edit Module"** tab.
3.  **View Created Entities**:
    
    -   You can view the entity you created in the **"Edit Entity"** tab.
4.  **View Entity Relationships**:
    
    -   You can view the relationships between the entities you created in the **"ERD"** (Entity Relationship Diagram) tab.
5.  **Additional Features (Sort Order, Trash, Approval)**:
    
    -   If you enabled features like **Sort Order**, **Trash**, or **Approval**, MagicAppBuilder may create additional tables and columns that were not previously defined.
    -   You can see the **"ALTER TABLE"** queries in the **Query** tab.
    -   In the **Query** tab, you can also apply these queries directly to the database by executing them.

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

