<?php

namespace AppBuilder\EntityInstaller;

use MagicObject\MagicObject;

/**
 * The EntityReference class represents an entity in the "reference" table.
 *
 * This entity maps to the "reference" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\EntityInstaller
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="reference")
 */
class EntityReference extends MagicObject
{
	/**
	 * Reference ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="reference_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Reference ID")
	 * @var string
	 */
	protected $referenceId;

	/**
	 * Admin ID
	 * 
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
	 * @var EntityAdmin
	 */
	protected $admin;

	/**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Application
	 * 
	 * @JoinColumn(name="application_id", referenceColumnName="application_id")
	 * @Label(content="Application")
	 * @var EntityApplication
	 */
	protected $application;

	/**
	 * Column Name
	 * 
	 * @Column(name="column_name", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="Column Name")
	 * @var string
	 */
	protected $columnName;

	/**
	 * Reference Key
	 * 
	 * @Column(name="reference_key", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="Reference Key")
	 * @var string
	 */
	protected $referenceKey;

	/**
	 * Reference Value
	 * 
	 * @Column(name="reference_value", type="text", nullable=true)
	 * @Label(content="Reference Value")
	 * @var string
	 */
	protected $referenceValue;

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

}