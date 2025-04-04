# MagicAppBuilder version 0.34

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

# MagicAppBuilder version 0.35

## What's New

New Features:

-    Integrated File Manager: A new file manager has been added to MagicAppBuilder, providing a seamless interface for managing your application’s files.

Improvements:

-    File Editing Capabilities: Users can now view, edit, and save any files located within the application's directory. This feature makes it easier to work with and manage the content of your project directly within MagicAppBuilder.

