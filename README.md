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

Of course. Because with MagicAppBuilder, a CRUD module that has the features mentioned above can be created in less than 30 minutes. Yes, you didn't read it wrong and I didn't write it wrong. 30 minutes is the time needed for developers to select columns from a module. Is the input in the form of inline text, textarea, select or checkbox and what filter is appropriate for that column. Of course, there is still plenty of time left and enough to edit the program code manually if necessary.

If a module can be created in 30 minutes, then in one day, a developer can create at least 16 new CRUD modules. Within 2 weeks, a developer can create 160 standard CRUD modules with the features above.

Of course, an application cannot contain only simple CRUD modules. But at least, a simple CRUD module won't take much time to create. Available time can be maximized for other tasks such as data processing, report creation and application testing.

MagicAppBuilder uses MagicObject as its library. MagicObjects is very useful for creating entities from a table without having to type code. Just select the table and specify the name of the entity to be created. Entities will be created automatically by MagicAppBuilder according to the names and column types of a table.

## Requirement

1. Apache Server
2. PHP Runtime version 5.6 or above
3. MariaDB or MySQL database

## Dependency

1. MagicObject

## CRUD Example

The following PHP code was created in less than 5 minute and already has the following features:

1. create new data
2. update existing data
3. activate data
4. disable data
5. delete data
6. move the deleted data to the trash table
7. approve to the creation, update and deletion of data
8. reject creation, update and deletion of data
9. display data using filters and sorting data
10. update sort order
11. join entity
12. select control with entity and map
13. data can be exported to Microsoft Excel and CSV formats.
14. multiple language support

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

Steps to create an application with MagicAppBuilder

1. Create a complete entity relationship diagram with the following rules:
a. The column for the primary key of a table must be the same as the table name and added with the suffix _id.
b. Columns that are foreign keys that refer to other tables are strongly recommended to be given the same name as the primary key of the table in question.
d. If there are several columns that must be given a specific name that refers to the primary key of a table, then this is a note when creating a module.
c. Columns with the same purpose of use must have the same name in all tables.
d. Application features should be determined before the entity relationship diagram is created.
2. Export the entity relationship diagram into a database. Currently only supports MySQL and MariaDB.
3. Install MagicAppBuilder on your server.
4. Create MagicAppBuilder settings.
5. Add the application to be created in MagicAppBuilder.
6. Create application settings and column mapping.
7. Click the "Load Table" button to load all tables from the specified database.
8. Select a table from one of the tables. MagicAppBuilder will automatically fill in some inputs from the form. You can change some inputs before continuing.
9. Click the "Load Column" button. MagicAppBuilder will display a new tab containing fields or columns from the table.
10. Check the check boxes and radio buttons according to how the module will be created.
11. If you choose "select" in the data column or filter column, MagicAppBuilder will display the "Source" button for reference. Click the "Source" button to determine the reference you will create. This section will be explained separately.
12. Click the "Data Filter" button to determine the data filter.
13. Click the "Data Order" button to determine the order of the data.
14. Click the "Module Filter" button to determine the module features.
15. Click the "Generate Script" button to create a script automatically. MagicAppBuilder will create a module script and some entity scripts required by the module. If in step number 8 you check "Update Entity", then MagicAppBuilder will update the existing entity. Be careful if you have defined the entity before.

### Reserved Column Mapping

Reserved columns can be mapped to other names according to the native language used by the application and the terminology that will be used in each entity. Each entity must consistently use the full name if it is going to use it.

For example:

The `album` entity requires the `sort_order` column to sort the albums. So the `album` entity must use the `sort_order` column and not others to sort the data.

The `album` entity also requires the `active` column to activate and deactivate data. So the `album` entity must use the `active` column and not others to activate and deactivate data.

On the other hand, the `artist` entity only does not need the `sort_order` column because artist data is not sorted by default by the user but still uses the `active` column to activate and deactivate data. So the `artist` entity must use the `active` column and not others to activate and deactivate data.

If the application is built in a language other than English, it would be strange to still use column names such as `active`, `admin_create`, `ip_create` and so on. Therefore, developers are free to choose other names but must create column mappings.

The following is an example of column mapping into Indonesian.

| Original Key    | Translated Key |
| --------------- | -------------- | 
| name            | nama |
| sort_order      | sort_order |
| active          | aktif |
| draft           | draft |
| waiting_for     | waiting_for |
| admin_create    | admin_buat |
| admin_edit      | admin_ubah |
| admin_ask_edit  | admin_minta_ubah |
| time_create     | waktu_buat |
| time_edit       | waktu_ubah |
| time_ask_edit   | waktu_minta_ubah |
| ip_create       | ip_buat |
| ip_edit         | ip_ubah |
| ip_ask_edit     | ip_minta_ubah |
| approval_id     | approval_id |
| approval_note   | approval_note |
| approval_status | approval_status |

Developers for applications that use Indonesian as the native language of the application can use the translated columns to create columns from entities or tables.

Here is an explanation of the reserved columns above.

| Original Key    | Description |
| --------------- | -------------- | 
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
| Number of user                          | 5          | 100        |
| User management                         | No         | Yes        |
| Collaboration                           | No         | Yes        |
| Push notification                       | No         | Yes        |

