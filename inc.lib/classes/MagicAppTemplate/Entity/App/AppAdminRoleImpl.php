<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * AppAdminRoleImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_role")
 * @package Music\Entity\App
 */
class AppAdminRoleImpl extends MagicObject
{
    /**
     * Admin Role ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @Column(name="admin_role_id", type="varchar(40)", length=40, nullable=false)
     * @Label(content="Admin Role ID")
     * @var string
     */
    protected $adminRoleId;

    /**
     * Admin Level ID
     * 
     * @NotNull
     * @Column(name="admin_level_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="Admin Level ID")
     * @var string
     */
    protected $adminLevelId;

    /**
     * Admin Level
     * 
     * @NotNull
     * @JoinColumn(name="admin_level_id", referenceColumnName="admin_level_id")
     * @Label(content="Admin Level")
     * @var AppAdminLevelImpl
     */
    protected $adminLevel;

    /**
     * Module ID
     * 
     * @Column(name="module_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="Module ID")
     * @var string
     */
    protected $moduleId;

    /**
     * Module
     * 
     * @NotNull
     * @JoinColumn(name="module_id", referenceColumnName="module_id")
     * @Label(content="Module")
     * @var AppModuleImpl
     */
    protected $module;

    /**
	 * Module Code
	 * 
	 * @NotNull
	 * @Column(name="module_code", type="varchar(255)", length=255, default_value="NULL", nullable=true)
	 * @Label(content="Module Code")
	 * @var string
	 */
	protected $moduleCode;

    /**
     * Allowed show list
     *
     * @Column(name="allowed_list", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed List")
     * @var bool
     */
    protected $allowedList;

    /**
     * Allowed show detail
     *
     * @Column(name="allowed_detail", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Detail")
     * @var bool
     */
    protected $allowedDetail;

    /**
     * Allowed create
     *
     * @Column(name="allowed_create", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Create")
     * @var bool
     */
    protected $allowedCreate;

    /**
     * Allowed update
     *
     * @Column(name="allowed_update", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Update")
     * @var bool
     */
    protected $allowedUpdate;

    /**
     * Allowed delete
     *
     * @Column(name="allowed_delete", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Delete")
     * @var bool
     */
    protected $allowedDelete;

    /**
     * Allowed approve/reject
     *
     * @Column(name="allowed_approve", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Approve")
     * @var bool
     */
    protected $allowedApprove;

    /**
     * Allowed short order
     *
     * @Column(name="allowed_sort_order", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Sort Order")
     * @var bool
     */
    protected $allowedSortOrder;

    /**
     * Allowed export
     *
     * @Column(name="allowed_export", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Export")
     * @var bool
     */
    protected $allowedExport;

    /**
     * Active
     *
     * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
     * @Label(content="Active")
     * @var bool
     */
    protected $active;
}
