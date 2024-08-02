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

