<?php

namespace AppBuilder\EntityInstaller;

use MagicObject\MagicObject;

/**
 * The EntityActiveApplicationHistory class represents an entity in the "active_application_history" table.
 *
 * This entity maps to the "active_application_history" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="active_application_history")
 */
class EntityActiveApplicationHistory extends MagicObject
{
	/**
	 * Active Application History ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="active_application_history_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Active Application History ID")
	 * @var string
	 */
	protected $activeApplicationHistoryId;

	/**
	 * Admin ID
	 * 
	 * @NotNull
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_id", referenceColumnName="admin_id")
	 * @Label(content="Admin")
	 * @var AdminMin
	 */
	protected $admin;

	/**
	 * Workspace ID
	 * 
	 * @NotNull
	 * @Column(name="workspace_id", type="varchar(40)", length=40, nullable=true)
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
	 * Application ID
	 * 
	 * @NotNull
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Application
	 * 
	 * @JoinColumn(name="application_id", referenceColumnName="application_id")
	 * @Label(content="Application")
	 * @var ApplicationMin
	 */
	protected $application;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}