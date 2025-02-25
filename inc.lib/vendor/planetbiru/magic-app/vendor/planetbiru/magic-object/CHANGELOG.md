
# MagicObject Version 2

## What's New

**MagicObject Version 2** brings several exciting new features and enhancements aimed at increasing flexibility, improving performance, and providing better control over database interactions. Below are the key updates introduced in this version:

### 1. Native Query Support

**Native SQL Queries** are now supported in **MagicObject**, allowing users to execute raw SQL statements directly within the framework. This enhancement gives developers greater control over complex queries, which may not be easily handled by the ORM layer. You can now execute any SQL command directly, enabling the use of advanced SQL features and custom queries that go beyond the capabilities of the built-in ORM.

### 2. Multiple Database Connection Support

The new version of **MagicObject** introduces the ability to configure and manage **multiple database connections** within a single application. This feature allows developers to easily connect to and manage different databases simultaneously, making it ideal for applications that need to interact with multiple databases or implement multi-database architectures. Whether you're working with multiple MySQL instances, different types of databases (e.g., PostgreSQL, SQLite), or managing different environments (development, production), this feature significantly simplifies database management.

### 3. Entity Cache Control on Joins

**MagicObject Version 2** gives developers greater control over **entity caching** when performing **join operations**. The new feature allows you to enable or disable caching specifically for joins, providing fine-tuned control over your caching strategy. This improves performance by reducing unnecessary database hits while still ensuring fresh data retrieval when needed. You can now optimize caching on a per-query basis, making it easier to manage large data sets efficiently.

### 4. Enhanced Documentation

The documentation for **MagicObject** has been thoroughly updated. In this release, we've made significant improvements to the documentation for **classes**, **properties**, **functions**, and **annotations**. The documentation now includes clearer explanations, improved examples, and comprehensive usage guidelines. These changes are designed to make it easier for developers to understand and fully leverage the power of the framework, reducing the learning curve and streamlining the development process.

### 5. Bug Fixes and Stability Enhancements

Several bugs and issues from previous versions have been addressed in **MagicObject Version 2**. This includes improvements to **performance**, **stability**, and the **correction of minor errors** that may have affected the functionality of the framework. With these fixes, users can expect a more reliable and robust framework that performs well across a variety of use cases.

## Additional Features

-   **Improved Error Handling**: We've introduced enhanced mechanisms for detecting and handling errors. The error messages are now more informative, helping developers to troubleshoot and resolve issues faster. This improvement also includes better stack trace information and more specific error types.
    
-   **Performance Optimizations**: Internally, **MagicObject Version 2** has been optimized to improve overall performance. Key database interaction operations have been streamlined, leading to faster query execution times and better resource utilization.
    
-   **Backward Compatibility**: **MagicObject Version 2** maintains **backward compatibility** with **Version 1**, ensuring that existing users can upgrade smoothly without having to make significant changes to their codebase. This allows for an easy transition to the new version while still maintaining compatibility with legacy systems.
    

## Migration Notes

If you are upgrading from **MagicObject Version 1** to **Version 2**, please review the migration notes carefully. The documentation includes detailed guidelines and best practices for handling any potential breaking changes, as well as adjustments that may be necessary to ensure a smooth transition. By following these guidelines, you can ensure that your upgrade process is as seamless as possible, minimizing disruptions to your development workflow.



# MagicObject Version 2.1

## What's New

**MagicObject 2.1** introduces several powerful new features aimed at improving entity management, database interoperability, and overall ease of use. This version builds on the foundational updates from previous releases, making database handling even more efficient and developer-friendly. Here’s a detailed overview of the new additions:

### 1. Package Annotations for Entity Joins

One of the most notable features in **MagicObject 2.1** is the introduction of **package annotations** for entities. These annotations are essential when joining entities, as they provide the necessary namespace information that is critical for the framework to correctly recognize and associate entity classes.

#### Why Package Annotations?

PHP does not natively provide a way to directly retrieve the namespace of a class, which presented a challenge for earlier versions of **MagicObject** when attempting to perform joins. To work around this, **MagicObject** previously attempted to infer namespaces by reading the PHP script, but this method proved to be both inefficient and prone to errors.

With **MagicObject 2.1**, the introduction of package annotations on each entity allows the framework to safely and efficiently join entities by referencing the class's base name, without needing to manually specify or infer the namespace. This makes the process of joining entities more robust and reliable.

#### Backwards Compatibility

If a package annotation is not present on an entity, **MagicObject 2.1** will gracefully revert to the old method of namespace inference, ensuring backwards compatibility with previous versions. However, it is strongly recommended to utilize the new package annotations for better performance and accuracy when performing entity joins.

### 2. Seamless Database Conversion Between PostgreSQL and MySQL

**MagicObject 2.1** introduces a powerful utility that allows developers to seamlessly convert databases between **PostgreSQL** and **MySQL**. This feature greatly simplifies the process of migrating applications and data between these two popular database systems.

#### Key Features of Database Conversion:

-   **Data Type Mapping**: MagicObject handles the conversion of data types between PostgreSQL and MySQL, ensuring that the equivalent types are correctly mapped.
-   **Constraints and Structures**: The conversion tool also accounts for database constraints, indexes, and table structures, ensuring that the integrity of the database schema is maintained during migration.
-   **Error Reduction**: By automating the conversion process, MagicObject reduces the chances of errors that can occur during manual migration, saving time and effort for developers.

This new functionality provides developers with a simple and efficient way to migrate data between PostgreSQL and MySQL, which is particularly useful for projects that need to switch databases or support multiple database systems.

### 3. Parsing Table Structures from SQL Statements

Another significant enhancement in **MagicObject 2.1** is the ability to **parse table structures directly from SQL statements**. Developers no longer need to first dump the schema into a database before they can interact with it. Instead, MagicObject allows you to read and manipulate the structure of a database directly from SQL.

#### Benefits of Table Structure Parsing:

-   **Streamlined Workflow**: This feature eliminates the need for a two-step process (first dumping, then reading the schema) and allows developers to work more directly with SQL code.
-   **Integrate with Third-Party Systems**: Developers can now easily parse and manipulate schemas from third-party systems that provide SQL code, without needing to import the data into a database first.
-   **Improved Efficiency**: This utility speeds up the process of understanding and working with complex database schemas, making it easier to integrate and maintain SQL-driven projects.

By providing a direct way to parse table structures, **MagicObject 2.1** significantly simplifies database schema management and makes it more accessible, especially for developers working with raw SQL or third-party integrations.

## Summary

**MagicObject 2.1** brings a suite of powerful features designed to enhance database management, simplify entity relationships, and improve the overall development process. Key updates include:

-   **Package annotations** for safer and more efficient entity joins.
-   A **seamless database conversion tool** between PostgreSQL and MySQL, simplifying migrations.
-   **Direct parsing of SQL table structures**, eliminating the need for intermediate steps.

These updates significantly improve the flexibility and efficiency of **MagicObject**, making it even easier for developers to manage databases and integrate with various systems. With **MagicObject 2.1**, developers can focus more on building applications and less on wrestling with database compatibility and entity management.


# MagicObject Version 2.7

## What's New

**MagicObject 2.7** brings a set of powerful updates to improve database interaction, query flexibility, and transaction management. The main highlights of this release include support for PDO connections, enhanced native query capabilities with pagination and sorting, and new transactional methods for improved data management.

### 1. PDO Support

One of the most significant changes in **MagicObject 2.7** is the introduction of support for **PDO** (PHP Data Objects). In previous versions, **MagicObject** required the use of its custom database handler, **PicoDatabase**. However, to accommodate developers who prefer working with PDO connections, this new version allows users to pass a PDO connection directly to the **MagicObject** constructor.

#### Why PDO Support?

The decision to include PDO support was driven by the need to make **MagicObject** more versatile for developers who are already using PDO in their applications. By allowing PDO connections, **MagicObject** now supports a broader range of use cases and provides users with the flexibility to integrate with existing PDO-based database connections.

While PDO is supported for the initial connection setup, **MagicObject** continues to use **PicoDatabase** for all subsequent database operations. This ensures that users still benefit from **PicoDatabase**'s advanced features, such as automatic query building, database abstraction, and optimized query execution.

#### How PDO Support Works

In **MagicObject 2.7**, when you pass a **PDO** connection object to the constructor, it is internally converted into a **PicoDatabase** instance via the `PicoDatabase::fromPdo()` static method. This ensures that although PDO is used for establishing the initial connection, **PicoDatabase** manages the actual database interactions. Additionally, **MagicObject** automatically detects the database type based on the PDO driver to ensure smooth operation.

### 2. Pageable and Sortable in Native Queries

Another important enhancement in **MagicObject 2.7** is the introduction of **pageable** and **sortable** support in native queries. Prior to this release, native queries lacked direct support for pagination and sorting. Developers had to manually include `ORDER BY` and `LIMIT OFFSET` clauses in their queries, leading to more cumbersome code that was difficult to maintain and adapt across different database platforms.

With **MagicObject 2.7**, you can now pass **pagination** parameters using the `PicoPageable` type and **sorting** parameters using the `PicoSortable` type directly into your native queries. These parameters can be placed at any point in the query, though it's recommended to position them either at the beginning or end for optimal readability and organization.

This improvement enhances the flexibility of native queries, as the logic for pagination and sorting is handled automatically, reducing the need for manual intervention. By supporting these features, **MagicObject 2.7** allows you to write cleaner, more efficient, and database-agnostic queries. You can now easily handle pagination and sorting logic regardless of the underlying database system.

### 3. Transaction Management

**MagicObject 2.7** introduces enhanced support for transactional database operations, including three new methods: `startTransaction()`, `commit()`, and `rollback()`. These methods provide an easy and efficient way to manage database transactions within **MagicObject**.

-   **startTransaction()**: Begins a new database transaction.
-   **commit()**: Commits the current transaction, saving all changes made during the transaction to the database.
-   **rollback()**: Rolls back the current transaction, undoing any changes made since the transaction began.

These methods are designed to work seamlessly with an active database connection, allowing developers to handle transactions directly within the context of their application. Whether you're managing financial transactions or ensuring data consistency during batch processing, these functions streamline the management of transaction-based operations.


# MagicObject Version 2.11

## What's New

1.  **Dynamic Query Template for Native Queries**
    MagicObject now supports dynamic query templates for native SQL queries. This feature allows developers to build SQL queries dynamically, providing flexibility and improving code reusability. The dynamic query template can be customized to suit specific use cases, making it easier to construct complex queries while maintaining cleaner and more maintainable code.
    
2.  **Added `DROP TABLE IF EXISTS` and `CREATE TABLE IF NOT EXISTS` to SQL Code Generation**  
    MagicObject now generates SQL code with `DROP TABLE IF EXISTS` and `CREATE TABLE IF NOT EXISTS` statements when creating tables from entities. This ensures that the table creation process is more robust, preventing errors when a table already exists.

3.  **Standardization of Annotation Attribute Naming with Camel Case Strategy**  
    The annotation attributes in MagicObject have been standardized to follow the camel case naming convention, ensuring consistency and better readability throughout the codebase.
    
4.  **Refactoring of Methods with Constant Usage**  
    To prevent duplication and errors in the code, several methods have been refactored to utilize constants. This improves maintainability and reduces the chances of mistakes during development.

5.  **Option to Prettify Serialize for `SecretObject`**  
    A new option has been added to allow `SecretObject` to be serialized with pretty formatting. This makes it easier to read and inspect serialized `SecretObject` data, especially useful during development or debugging. The `prettify` flag can be enabled when serializing the object, ensuring the output is more human-readable with proper indentation.

6.  **Updated Documentation**  
    The documentation has been updated to reflect the latest changes in MagicObject. This includes clarifications, examples, and explanations to assist developers in understanding and utilizing the library effectively.


# MagicObject Version 3.0

## What's New

1.  **SQL Server Database Support**  
    This feature expands user options by providing support for SQL Server, offering more flexibility in choosing DBMS.
    
2.  **Time Zone Conversion for SQLite and SQL Server Databases**  
    This feature automatically converts time in databases that do not natively support time zone conversion, such as SQLite and SQL Server.
    
3.  **Database Time Zone Change After Object Construction**  
    Users can now change the database's time zone at any time as needed. This provides greater flexibility when handling data across multiple time zones, especially when the application is used by users from different time zones, all without the need to modify the application configuration.

4.  **Yaml Parser and Dumper**  
    MagicObject version 3.0 no longer depends on external libraries. The Yaml parsing and dumping functions are now fully handled by a class that is part of MagicObject itself, reducing its overall complexity.
5.  **Added `BETWEEN` Filter for Predicate Queries**  
    MagicObject now supports `BETWEEN` filters for predicate-based queries, allowing users to perform range-based filtering efficiently. This improves query performance and simplifies conditions when working with numerical or date ranges.