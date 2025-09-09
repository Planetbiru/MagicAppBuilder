<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The ModuleHistory class represents an entity in the "module" table.
 *
 * This entity maps to the "module_history" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="module")
 */
class ModuleHistory extends MagicObject
{
	/**
	 * Module History ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="module_history_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module ID")
	 * @var string
	 */
	protected $moduleHistoryId;
    
    /**
	 * Module ID
	 * 
	 * @Column(name="module_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module ID")
	 * @var string
	 */
	protected $moduleId;
	
	/**
	 * Module
	 * 
	 * @JoinColumn(name="module_id", referenceColumnName="module_id")
	 * @Label(content="Module")
	 * @var Module
	 */
	protected $module;

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
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;
	
	/**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_create", referenceColumnName="admin_id")
	 * @Label(content="Admin")
	 * @var Admin
	 */
	protected $admin;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

}