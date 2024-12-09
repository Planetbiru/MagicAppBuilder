# MagicAppBuilder

## History

Imagine a large application consisting of dozens of CRUD (Create, Read, Update, Delete) modules. Each module has the following mechanism:

1. create new data
2. change existing data
3. delete existing data
4. requires approval to create new data, change data and delete data
5. have a rule that the user who approves the creation, change and deletion of data must be different from the user who creates, changes and deletes data
6. Data can be exported to Microsoft Excel and CSV formats.

This project must be created in a very fast time, even less than 3 months.

In this situation, the project owner definitely needs a tool to create applications very quickly but without errors.

MagicAppBuilder is the answer to all this.

Of course. Because with MagicAppBuilder, a CRUD module that has the features mentioned above can be created in **less than 30 minutes**. Yes, you didn't read it wrong and I didn't write it wrong. 30 minutes is the time needed for developers to select columns from a module. Is the input in the form of inline text, textarea, select or checkbox and what filter is appropriate for that column. Of course, there is still plenty of time left and enough to edit the program code manually if necessary.

If a module can be created in 30 minutes, then in one day, a developer can create at least 16 new CRUD modules. Within 2 weeks, a developer can create 160 standard CRUD modules with the features above.

Of course, an application cannot contain only simple CRUD modules. But at least, a simple CRUD module won't take much time to create. Available time can be maximized for other tasks such as data processing, report creation and application testing.

MagicAppBuilder uses MagicObject as its library. MagicObjects is very useful for creating entities from a table without having to type code. Just select the table and specify the name of the entity to be created. Entities will be created automatically by MagicAppBuilder according to the names and column types of a table.

## Requirement

1. Apache Server
2. PHP Runtime version 5.6 or above
3. MariaDB or MySQL database

## Dependency

1. MagicObject
2. MagicApp

## Advantages

In just under five minutes, you can implement a powerful PHP-based data management system that includes a comprehensive set of features essential for managing and manipulating data in a modern application. This system is designed to handle various CRUD (Create, Read, Update, Delete) operations, data validation, and dynamic data presentation. Below is an overview of the key features of the system, which is flexible enough to support both monolithic and microservices-based architectures.

## Features Overview

**1. Create New Data**

The system allows users to create new data entries in the database. With minimal configuration, you can insert new records into your tables efficiently. The code is designed to be scalable, allowing easy integration with forms or APIs for creating data.

**2. Update Existing Data**

Updating data is a breeze with this system. Whether you need to update a single record or perform bulk updates, the system provides a flexible API to handle this. You can modify any record's attributes and ensure consistency and integrity during the update process.

**3. Activate Data**

In many business scenarios, records need to be toggled between an "active" and "inactive" state. The system includes an activate function to mark records as active, which can then be used for visibility in front-end applications, reports, or any part of your workflow that requires active data.

**4. Disable Data**

Similar to activating data, the system also provides functionality to disable data. This is especially useful for workflows where data needs to be temporarily hidden or disabled without being permanently deleted. You can mark records as inactive and keep them in the system for later reactivation or auditing purposes.

**5. Delete Data**

Deleting data is handled securely and efficiently. You can delete records from the primary data table, ensuring that the database remains clean and accurate. However, the delete operation is not permanent without further confirmation or approval (explained below).

**6. Move Deleted Data to the Trash Table**

Rather than completely removing deleted records, the system moves deleted data to a "trash" table. This feature adds an additional layer of safety to the data management process, allowing you to recover deleted records if needed. It is ideal for situations where you might need to restore or audit deleted entries later.

**7. Approve Creation, Update, and Deletion of Data**

The system includes an approval workflow for managing data changes. Whether you are creating, updating, or deleting data, these operations can be configured to require approval from authorized personnel before they are executed. This ensures data integrity and accountability within the system.

**8. Reject Creation, Update, and Deletion of Data**

Just as data changes can be approved, they can also be rejected. If there is a need to halt a specific data change, the system provides functionality for rejecting creations, updates, or deletions. Rejected actions are logged for transparency, and the system ensures that no unwanted changes are made.

**9. Display Data Using Filters and Sorting**

Presenting data to the user is essential, and this system has built-in support for filtering and sorting. You can display data based on specific criteria (e.g., filter by date, category, status) and sort the results by different attributes (e.g., ascending or descending order). This feature helps users find and navigate through the data easily, enhancing the user experience.

**10. Update Sort Order**

The sort order of records can be dynamically updated. This is particularly useful for applications that rely on ordering data in a specific sequence, such as product listings, tasks, or blog posts. The system allows you to change the sort order of records easily, maintaining consistency across views.

**11. Join Entity**

The system supports the ability to join multiple entities together. This feature enables you to link related data across different tables (e.g., joining a users table with a posts table). You can efficiently fetch and display related data without needing to manually handle multiple queries, which is a common requirement in complex database systems.

**12. Select Control with Entity and Map**

In many web applications, dropdowns, select boxes, or multi-select controls are used to choose values from a set of entities. This system includes a powerful select control feature, which allows you to populate these controls with data from entities and maps, providing a seamless experience for users who need to select or filter by entity data.

**13. Export Data to Microsoft Excel and CSV Formats**

Data export is a crucial feature for many applications that require reporting and data analysis. This system supports exporting data to both Microsoft Excel and CSV formats, allowing users to download datasets for further analysis or integration into external tools. The export process is simple and can be triggered by the user with just a few clicks.

**14. Support for Both Monolith and Microservices Architectures**

Whether you are building a monolithic application or developing a microservices-based system, this data management system is flexible enough to support both architectures. It can be integrated into a traditional monolith where everything is handled within a single application, or it can be deployed in a microservices architecture, where different services manage distinct pieces of functionality.

-    **Monolithic Architecture**: In this setup, the entire application is managed in one place, and data operations are handled by the same service. This setup is simpler to maintain but may not scale as well as microservices.
-    **Microservices Architecture**: With this approach, different parts of the data management system can be isolated in separate services. For example, the creation and update of data might be handled by one service, while data display and export might be managed by another service. This offers scalability and flexibility but requires more complex architecture.

**15. Support for Multiple Languages**

The system is built with localization in mind. It supports multiple languages, allowing you to easily translate the interface and error messages into various languages. Whether your application is being used by a global audience or by users from different regions, this feature ensures that the system can adapt to various language preferences, improving accessibility and user engagement.

Apart from the features above, the module is also equipped with data filters that are adjusted to the data type.

## Using MagicAppBuilder

First of all, developers must create an entity relationship diagram or ERD. In a database, table relationships are not created explicitly. This means that a column from one table that refers to a column in another table does not have to be associated with a foreign key explicitly in the database. This relationship will be formed by the application.

After the ERD has been created, the developer then exports the ERD to SQL format to be executed on the target database. Some tables may need to be filled in for development needs.

Once the application data structure is ready, next the developer connects MagicAppBuilder to the target database then starts and creates the application configuration.

Next, the developer starts creating application modules by selecting tables from the database.

MagicAppBuilder will display all columns of the selected table. When a developer uses certain features of MagicAppBuilder, MagicAppBuilder may add several columns needed by the application. In this case, developers don't need to be afraid because AppBuilder will create a query to alter tables in the database. Developers can copy the query to run on the database.

As long as all database access by the application is done using entities only, MagicAppBuilder can make queries from automatically created entities. If the developer uses native queries to access the database and adds tables or columns that are not in the existing entity, then the developer must alter the table manually by creating the required alter query himself. Applications generated using MagicAppBuilder almost do not have native queries due to the fact that MagicAppBuilder never uses native queries in applications. Native queries may only be created by developers in conditions where they are needed. Using entities allows developers to create application installers without explicitly including SQL scripts. The installer will create an application script according to the database engine selected by the user.

Users can display Entity Relationship Diagram or ERD based on the entity file that has been created. This ERD can be a guide for developers in the application development phase and also as a guide for users about the relationship between entities in the application. By knowing this relationship, users can determine the sequence in creating data and know the impact of changes to data.

Users can select the entities to be displayed on the ERD, determine how many levels of relationships will be displayed, set the number of entities horizontally to limit the width of the ERD to be created, set the distance between entities and set the width of the ERD edge for aesthetic purposes.

The image format of ERD is SVG. This format can be converted to PNG if needed. Please note that the image quality in PNG format will decrease if rescaled. Therefore, set the appropriate zoom before converting it to PNG format.

### Steps

#### MagicAppBuilder Preparation

1. Prepare the server, which should include:
	    -   A Web Server, such as Apache Server
	    -   PHP
	    -   A Database, such as MySQL, MariaDB, or PostgreSQL For Windows users, it is recommended to use XAMPP or Wamp Server, but a portable web server that includes the web server, PHP, and the database can also be used.
2. Download the MagicAppBuilder source code.
3. Place the MagicAppBuilder source code into a directory under the document root in a separate folder, as the document root will also contain the directories for the applications to be created.
4. Open MagicAppBuilder in a browser using the appropriate server name, port, and path.
5. Ensure that MagicAppBuilder is running correctly.
6. Create MagicAppBuilder settings.

#### Project Preparation

Steps to create an application with MagicAppBuilder


1.   Create a complete entity relationship diagram (ERD) with the following rules:
    
    -   The column for the primary key of a table must be the same as the table name, with the suffix `_id`.
    -   Columns that are foreign keys referring to other tables should ideally have the same name as the primary key of the referenced table.
    -   If there are multiple columns that refer to a primary key of a table, this should be noted when creating a module.
    -   Columns with the same purpose across different tables must have the same name.
    -   Application features should be defined before creating the entity relationship diagram.
2.   Export the entity relationship diagram into SQL.
    
3.   Add the application to be created in MagicAppBuilder.
    
4.   Create application settings and column mapping.
    
5.   Open the Database Manager from MagicAppBuilder and import the SQL from the entity into the selected database type. Currently, MagicAppBuilder supports MySQL, MariaDB, PostgreSQL, and SQLite.
    
6.   Execute the SQL that has been converted.
    
7.   Click the "Reload Table" button to load all tables from the specified database.
    
8.   Select a table from the list. MagicAppBuilder will automatically fill in some fields in the form. You can modify these inputs before continuing.
    
9   Click the "Load Column" button. MagicAppBuilder will display a new tab containing fields or columns from the table.
    
10.   Check the checkboxes and radio buttons according to how the module will be created.
    
11.   If you choose "select" for the data column or filter column, MagicAppBuilder will display the "Source" button for reference. Click the "Source" button to define the reference you will create. This section will be explained separately.
    
12.   Click the "Data Filter" button to define the data filter.
    13.   Click the "Data Order" button to define the order of the data.
    
14.   Click the "Module Filter" button to configure the module features.
    
14.   Click the "Generate Script" button to automatically generate the script. MagicAppBuilder will create a module script and some entity scripts required by the module. If you check "Update Entity" in step 8, MagicAppBuilder will update the existing entity. Be cautious if you have already defined the entity.

### Reserved Column Mapping

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

| Original Key    | Description      |
| --------------- | ---------------- | 
| name            | A column that will represent a single row as a whole in an entity. |
| sort_order      | Columns for sorting data. For example, reference data such as song genres need to be sorted based on the number of genres produced by a studio. Another example is the type of application user that needs to be sorted based on authority in the application. The user type that has higher authority can be placed at the top so that when the user will set the role of the user, the user is already aware of which user type has the highest and lowest authority. |
| active          | Columns to activate and deactivate data |
| draft           | Column that marks that the data is new data that has not yet received approval. |
| waiting_for     | Column that specifies what approvals are required by a row. |
| admin_create    | Column for user ID who created the data first |
| admin_edit      | Column for user ID who last changed the data |
| admin_ask_edit  | Column for user ID who requested the data change |
| time_create     | Column for time when created the data first |
| time_edit       | Column for time when last changed the data |
| time_ask_edit   | Column for time requested the data change |
| ip_create       | Column for IP Address from where created the data first |
| ip_edit         | Column for IP Address from where last changed the data |
| ip_ask_edit     | Column for IP Address from where requested the data change |
| approval_id     | Column for ID of the data in the approval table |
| approval_note   | Column for approval note |
| approval_status | Column for approval status |

# User Plan

| Object                                  | Free       | Pro        |
| --------------------------------------- | ---------- | ---------- |
| Application starter                     | Yes        | Yes        |
| Module generator                        | Yes        | Yes        |
| Entity generator                        | Yes        | Yes        |
| Entity translator                       | Yes        | Yes        |
| Application translator                  | Yes        | Yes        |
| Table creator                           | Yes        | Yes        |
| Table modifier                          | Yes        | Yes        |
| Number of project                       | Unlimited  | Unlimited  |
| Simultaneous projects                   | 1          | Unlimited  |
| Number of table                         | Unlimited  | Unlimited  |
| Number of directory                     | Unlimited  | Unlimited  |
| Number of entity                        | Unlimited  | Unlimited  |
| Number of module                        | Unlimited  | Unlimited  |
| Number of theme                         | 2          | 3          |
| Number of user                          | 1          | 100        |
| User management                         | No         | Yes        |
| Collaboration                           | No         | Yes        |
| Push notification                       | No         | Yes        |

Subscribe to our YouTube channel https://www.youtube.com/@maliktamvan 