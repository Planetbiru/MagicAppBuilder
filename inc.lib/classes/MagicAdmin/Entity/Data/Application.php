<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The Application class represents an entity in the "application" table.
 *
 * This entity maps to the "application" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="application")
 */
class Application extends MagicObject
{
	/**
	 * Application ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Workspace ID
	 * 
	 * @Column(name="workspace_id", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Workspace ID")
	 * @var string
	 */
	protected $workspaceId;

	/**
	 * Workspace
	 * 
	 * @JoinColumn(name="workspace_id", referenceColumnName="workspace_id")
	 * @Label(content="Workspace")
	 * @var WorkspaceMin
	 */
	protected $workspace;

	/**
	 * Architecture
	 * 
	 * @Column(name="architecture", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Architecture")
	 * @var string
	 */
	protected $architecture;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="text", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Project Directory
	 * 
	 * @Column(name="project_directory", type="text", nullable=true)
	 * @Label(content="Project Directory")
	 * @var string
	 */
	protected $projectDirectory;

	/**
	 * Base Application Directory
	 * 
	 * @Column(name="base_application_directory", type="text", nullable=true)
	 * @Label(content="Base Application Directory")
	 * @var string
	 */
	protected $baseApplicationDirectory;

	/**
	 * Author
	 * 
	 * @Column(name="author", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Author")
	 * @var string
	 */
	protected $author;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, defaultValue="NULL", nullable=true)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="true", nullable=true)
	 * @DefaultColumn(value="true")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}