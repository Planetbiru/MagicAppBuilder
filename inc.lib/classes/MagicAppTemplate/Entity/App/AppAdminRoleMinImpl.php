<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppAdminRoleMinImpl class represents an entity in the "admin_role" table.
 *
 * This entity maps to the "admin_role" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_role")
 * @package MagicAppTemplate\Entity\App
 */
class AppAdminRoleMinImpl extends MagicObject
{
    /**
     * Admin Role ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @Column(name="admin_role_id", type="varchar(40)", length=40, nullable=false)
     * @Label(content="Admin Role ID")
     * @MaxLength(value=40)
     * @var string
     */
    protected $adminRoleId;

    /**
     * Admin Level ID
     * 
     * @NotNull
     * @Column(name="admin_level_id", type="varchar(40)", length=40, default_value=NULL, nullable=true)
     * @Label(content="Admin Level ID")
     * @MaxLength(value=40)
     * @var string
     */
    protected $adminLevelId;

    /**
     * Admin Level
     * 
     * @NotNull
     * @JoinColumn(name="admin_level_id", referenceColumnName="admin_level_id", referenceTableName="admin_level")
     * @Label(content="Admin Level")
     * @var AppAdminLevelImpl
     */
    protected $adminLevel;

    /**
     * Module ID
     * 
     * @Column(name="module_id", type="varchar(40)", length=40, default_value=NULL, nullable=true)
     * @Label(content="Module ID")
     * @MaxLength(value=40)
     * @var string
     */
    protected $moduleId;

    /**
     * Module
     * 
     * @NotNull
     * @JoinColumn(name="module_id", referenceColumnName="module_id", referenceTableName="module")
     * @Label(content="Module")
     * @var AppModuleImpl
     */
    protected $module;

    /**
	 * Module Code
	 * 
	 * @NotNull
	 * @Column(name="module_code", type="varchar(255)", length=255, default_value=NULL, nullable=true)
	 * @Label(content="Module Code")
     * @MaxLength(value=255)
	 * @var string
	 */
	protected $moduleCode;

    /**
     * Allowed show list
     *
     * @Column(name="allowed_list", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed List")
     * @var bool
     */
    protected $allowedList;

    /**
     * Allowed show detail
     *
     * @Column(name="allowed_detail", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Detail")
     * @var bool
     */
    protected $allowedDetail;

    /**
     * Allowed create
     *
     * @Column(name="allowed_create", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Create")
     * @var bool
     */
    protected $allowedCreate;

    /**
     * Allowed update
     *
     * @Column(name="allowed_update", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Update")
     * @var bool
     */
    protected $allowedUpdate;

    /**
     * Allowed delete
     *
     * @Column(name="allowed_delete", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Delete")
     * @var bool
     */
    protected $allowedDelete;

    /**
     * Allowed approve/reject
     *
     * @Column(name="allowed_approve", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Approve")
     * @var bool
     */
    protected $allowedApprove;

    /**
     * Allowed short order
     *
     * @Column(name="allowed_sort_order", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Sort Order")
     * @var bool
     */
    protected $allowedSortOrder;

    /**
     * Allowed export
     *
     * @Column(name="allowed_export", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Export")
     * @var bool
     */
    protected $allowedExport;

    /**
     * Allowed restore
     *
     * @Column(name="allowed_restore", type="tinyint(1)", length=1, default_value=false, nullable=true)
     * @Label(content="Allowed Restore")
     * @var bool
     */
    protected $allowedRestore;
    
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
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipEdit;

    /**
     * Active
     *
     * @Column(name="active", type="tinyint(1)", length=1, default_value=true, nullable=true)
     * @Label(content="Active")
     * @var bool
     */
    protected $active;

    /**
     * All
     *
     * @Label(content="All")
     * @var bool
     */
    protected $all;
}
