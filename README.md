# MagicAppBuilder: Low-Code Programming Platform

## History and Introduction


Imagine you're tasked with building a large-scale application composed of **hundreds of modules**—each requiring features such as:

1.  Creation of new data
    
2.  Modification of existing data
    
3.  Deletion of existing data
    
4.  Approval required for creating, modifying, or deleting data
    
5.  A rule that the user who approves the creation, modification, or deletion of data must be different from the user who performs the action
    
6.  Input validation to ensure data integrity before saving, including automatic rule enforcement and user-friendly error handling
    
7.  Data export functionality to Microsoft Excel and CSV formats
    
8.  Support for multiple languages


This project must be completed within a very short time—less than 3 months.

In this situation, the project owner definitely needs a tool that can build applications very quickly, without compromising on accuracy.

**MagicAppBuilder is the answer.**

Why? Because with MagicAppBuilder, a CRUD module—complete with features like form inputs, validation, data filtering, and role-based access—can be created in **less than 10 minutes**. Yes, you read that correctly, and no, it’s not a typo. **Ten minutes** is all it takes for a developer to select columns for a module: define whether each input should be inline text, a textarea, a select box, or a checkbox, and choose the appropriate filter for each column.

There’s still plenty of time left afterward to manually fine-tune or edit the program code as needed.

Even though theoretically one developer could produce more than 40 modules in a day, in practice, considering the need for testing, breaks, discussion, and quality checks, a more realistic target is **20 modules per day**. With that pace, a developer can build up to **100 CRUD modules in one week** (5 working days), each equipped with all the essential features listed above.

Of course, an application isn’t made up entirely of basic CRUD modules. But at the very least, building those modules shouldn’t consume unnecessary time. The time saved can be far better spent on more demanding tasks such as data processing, report generation, and comprehensive testing.

**MagicAppBuilder** is powered by **MagicObject**, an extremely useful underlying library that automates the generation of entity classes from database tables—no manual coding required. Just select a table and name the entity to generate. MagicAppBuilder will automatically create the entity class, mapping columns and data types from the table.

Even though MagicAppBuilder uses a high-level abstraction, developers can still add custom code to gain full control over the application. Customization is completely unrestricted, as developers can freely write native PHP code without being locked into any specific library. MagicAppBuilder is perfect for large projects needing fast delivery without sacrificing control or customization.


## System Requirements

- **Web Server:** Apache Server
- **PHP Runtime Version:** 5.6 or above
- **Database:** SQLite and MariaDB, MySQL or PostgreSQL

## Dependency

- **MagicApp:** The core application that facilitates the rapid generation of CRUD modules.
- **MagicObject:** A library for creating entities from database tables.

## Advantages of MagicAppBuilder

In just under 10 minutes, you can implement a powerful PHP-based data management system that includes a comprehensive set of features essential for managing and manipulating data in a modern application. This system is designed to handle various CRUD (Create, Read, Update, Delete) operations, data validation, and dynamic data presentation. Below is an overview of the key features of the system, which is flexible enough to support both monolithic and microservices-based architectures.

## Key Features Overview

**1. Create New Data**

The system allows users to create new data entries in the database. With minimal configuration, you can insert new records into your tables efficiently. The code is designed to be scalable, allowing easy integration with forms or APIs for creating data.

**2. Update Existing Data**

Updating data is a breeze with this system. Whether you need to update a single record or perform bulk updates, the system provides a flexible API to handle this. You can modify any record's attributes and ensure consistency and integrity during the update process.

**3. Activate Data**

In many business scenarios, records need to be toggled between an "active" and "inactive" state. The system includes an activate function to mark records as active, which can then be used for visibility in front-end applications, reports, or any part of your workflow that requires active data.

**4. Deactivate Data**

Similar to activating data, the system also provides functionality to deactive data. This is especially useful for workflows where data needs to be temporarily hidden or disabled without being permanently deleted. You can mark records as inactive and keep them in the system for later reactivation or auditing purposes.

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

- **Monolithic Architecture**: In this setup, the entire application is managed in one place, and data operations are handled by the same service. This setup is simpler to maintain but may not scale as well as microservices.
- **Microservices Architecture**: With this approach, different parts of the data management system can be isolated in separate services. For example, the creation and update of data might be handled by one service, while data display and export might be managed by another service. This offers scalability and flexibility but requires more complex architecture.

**15. Support for Multiple Languages**

The system is built with localization in mind. It supports multiple languages, allowing you to easily translate the interface and error messages into various languages. Whether your application is being used by a global audience or by users from different regions, this feature ensures that the system can adapt to various language preferences, improving accessibility and user engagement.

**16. Advanced Data Filters**

The system provides dynamic filters that adapt to the data type, ensuring accurate and efficient data querying based on user-defined criteria.


**17. Input Validation**

MagicAppBuilder now features **automatic input validation**, powered by enhancements in **MagicObject version 3.14 and above**. This ensures that all user-submitted data—whether during insert, update, or approval workflows—is strictly validated according to rules defined at the field level.

### Key Capabilities:

-   **Validator Class Generator**: MagicAppBuilder can auto-generate validator classes from entity definitions using field-based annotations like `@Required`, `@Email`, `@Min`, `@Max`, `@ValidEnum`, and many more. These annotations are added per property and aligned with each module's logic for insert and update operations.
    
-   **Rule-Based Validation on Insert and Update**: Before any insert or update action is executed, MagicObject automatically runs validation rules on the input. Only data that passes all checks will be persisted to the database.
    
-   **Exception Handling for Invalid Data**: If any validation rule fails, an exception is thrown immediately to halt execution. This prevents invalid or malicious data from entering the system.
    
-   **Graceful Error Feedback and Form Restoration**: When invalid input is detected, MagicAppBuilder:
    
    -   Highlights the specific field(s) that failed validation
        
    -   Restores the form with previously entered data
        
    -   Displays an appropriate error message
        
    -   Allows the user to correct the input without starting over
        
-   **JavaScript Integration for Client Feedback**: A helper script (`restoreFormData`) is automatically injected to repopulate form fields and visually indicate the field that caused the validation failure, improving the user experience.


## Using MagicAppBuilder

Visit Official user manual on [https://github.com/Planetbiru/MagicAppBuilder/blob/main/manual.md]() 

## Reserved Column Mapping

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
| active          | aktif            |
| draft           | draft            |
| waiting_for     | waiting_for      |
| admin_create    | admin_buat       |
| admin_edit      | admin_ubah       |
| admin_ask_edit  | admin_minta_ubah |
| admin_delete    | admin_hapus      |
| admin_restore   | admin_pemulihan  |
| time_create     | waktu_buat       |
| time_edit       | waktu_ubah       |
| time_ask_edit   | waktu_minta_ubah |
| time_delete     | waktu_delete     |
| time_restore    | waktu_pemulihan  |
| ip_create       | ip_buat          |
| ip_edit         | ip_ubah          |
| ip_ask_edit     | ip_minta_ubah    |
| ip_delete       | ip_delete        |
| ip_restore      | ip_pemulihan     |
| sort_order      | sort_order       |
| approval_id     | approval_id      |
| approval_note   | approval_note    |
| approval_status | approval_status  |
| restored        | dipulihkan       |

Developers for applications that use Indonesian as the native language of the application can use the translated columns to create columns from entities or tables.

Here is an explanation of the reserved columns above.

| Original Key    | Description                                                 |
| --------------- | ----------------------------------------------------------- |
| name            | Column that will represent a single row as a whole in an entity. |
| active          | Column that marks that the data is active or inactive. |
| draft           | Column that marks that the data is new data that has not yet received approval. |
| waiting_for     | Column that specifies what approvals are required by a row. |
| admin_create    | Column for user ID who created the data first. |
| admin_edit      | Column for user ID who last changed the data. |
| admin_ask_edit  | Column for user ID who requested the data change. |
| admin_delete    | Column for the user ID of the person who deleted the data. |
| admin_restore   | Column for the user ID of the person who restored the data. |
| time_create     | Column for time when created the data first. |
| time_edit       | Column for time when last changed the data. |
| time_ask_edit   | Column for time requested the data change. |
| time_delete     | Column for the timestamp when the data was deleted. |
| time_restore    | Column for the timestamp when the data was restored. |
| ip_create       | Column for IP Address from where created the data first. |
| ip_edit         | Column for IP Address from where last changed the data. |
| ip_ask_edit     | Column for IP Address from where requested the data change. |
| ip_delete       | Column for the IP address from where the data was deleted. |
| ip_restore      | Column for the IP address from where the data was restored. |
| sort_order      | Column used for sorting data. |
| approval_id     | Column for ID of the data in the approval table. |
| approval_note   | Column for approval note. |
| approval_status | Column for approval status. |
| restored        | Column that marks whether deleted data has been restored. |


## Offline Usage Guide

**MagicAppBuilder** enables users to create applications offline without requiring an internet connection. In offline mode, the application will not retrieve the latest versions of **MagicApp** or **MagicObject**. Instead, it will use the versions bundled with **MagicAppBuilder**. Composer will only set up the application's namespace during this process.

### Updating Dependencies

To update the application's dependencies, follow these steps:

1.  Navigate to the `inc.lib` directory within your application:

```bash
cd yourapp/inc.lib
```

2. Run the following command to update the dependencies:

```bash
composer update --ignore-platform-reqs
```

If Composer is not installed on your system, you can use the `composer.phar` file included in the application directory:

1.  Navigate to the `inc.lib` directory within your application:

```bash
cd yourapp/inc.lib
```

2. Run the following command to update the dependencies:

```bash
php composer.phar update --ignore-platform-reqs
```

**Notes:**

-   The `--ignore-platform-reqs` flag is used to bypass platform-specific requirements, which may be useful in certain offline scenarios.
-   If your goal is to update only the autoloader (e.g., after changing namespaces), you can use:
```bash
composer dump-autoload --ignore-platform-reqs
```
-   Adding or downloading new dependencies requires an active internet connection.


## User Plan

| Object                 | Community | Pro       |
| ---------------------- | --------- | --------- |
| Application starter    | Yes       | Yes       |
| Module generator       | Yes       | Yes       |
| Entity generator       | Yes       | Yes       |
| Entity translator      | Yes       | Yes       |
| Application translator | Yes       | Yes       |
| Table creator          | Yes       | Yes       |
| Table modifier         | Yes       | Yes       |
| Number of project      | Unlimited | Unlimited |
| Simultaneous projects  | Unlimited | Unlimited |
| Number of table        | Unlimited | Unlimited |
| Number of directory    | Unlimited | Unlimited |
| Number of entity       | Unlimited | Unlimited |
| Number of module       | Unlimited | Unlimited |
| Number of theme        | 1         | 3         |
| Number of user         | 10        | Unlimited |
| User management        | No        | Yes       |
| Push notification      | No        | Yes       |

## Browser Support

| Icon                                                                                                       | Browser          | Minimum Version |
| ---------------------------------------------------------------------------------------------------------- | ---------------- | --------------- |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/firefox-browser-icon.png) | Mozilla Firefox  | 138             |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/edge-browser-icon.png)    | Microsoft Edge   | 136             |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/google-chrome-icon.png)   | Google Chrome    | 136             |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/opera-icon.png)           | Opera            | 134             |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/brave-browser-icon.png)   | Brave            | 1.78            |
| ![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/lib.assets/images/64/vivaldi-icon.png)         | Vivaldi          | 7.4             |


## Conclusion

MagicAppBuilder is a game-changer for developers, enabling them to quickly build data management systems with powerful CRUD functionalities. It automates much of the routine coding required for building applications, drastically reducing development time while maintaining flexibility for further customization. Whether you are creating simple CRUD modules or building complex data-driven systems, MagicAppBuilder accelerates the development process, allowing you to meet project deadlines without compromising on quality.

In the fast-paced world of application development, MagicAppBuilder ensures that developers can focus on building great products, rather than getting bogged down by repetitive coding tasks.

Subscribe to our YouTube channel https://www.youtube.com/@maliktamvan
