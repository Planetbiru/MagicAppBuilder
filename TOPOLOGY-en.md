### MagicAppBuilder Architecture and Topology

![](https://github.com/Planetbiru/MagicAppBuilder/blob/main/MagicAppBuilder.svg)

MagicAppBuilder operates with **at least two databases** simultaneously:

1. **Platform Database** – used internally by MagicAppBuilder to store:

   * User accounts and authentication data
   * Workspaces
   * Application metadata and platform configurations

2. **Application Database** – used by the actual application being developed, containing:

   * Business entities and tables
   * End-user data created and modified at runtime

MagicAppBuilder and the generated application can be hosted on **the same or separate web servers**. However, for simplicity and ease of development, it is **recommended to deploy both on the same server**. To do this, ensure that both MagicAppBuilder and the generated application are located within the **web server’s document root**.


### Responsibilities of MagicAppBuilder

MagicAppBuilder plays a central role in the development process and handles tasks such as:

* Generating and updating **module files** and **entity definitions** based on user configurations
* Producing **application configuration files** tailored to the development environment
* Automatically **creating and updating the structure** of the application database
* Managing essential application data, such as:

  * Creating initial user accounts
  * Generating menus
  * Assigning roles and access permissions


### Workspace Access and Entity Parsing

MagicAppBuilder has full access to the entire **workspace**, including:

* Application source code
* Static assets (e.g., images, scripts, stylesheets)
* Entity definition files

These entity files are **parsed into objects**, which are then used for multiple purposes:

* Creating and synchronizing the database schema
* Generating **entity relationship diagrams**
* Producing automatic documentation
* Powering filters, validations, and CRUD operations within the application

