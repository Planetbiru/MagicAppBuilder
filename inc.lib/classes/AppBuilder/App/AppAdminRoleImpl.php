<?php

namespace AppBuilder\App\Entity\App;

use MagicObject\MagicObject;

/**
 * AppAdminRoleImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_role")
 * @package AppBuilder\App\Entity\App
 */
class AppAdminRoleImpl extends MagicObject
{
    /**
     * Admin Role ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @Column(name="admin_role_id", type="varchar(40)", length=40, nullable=false)
     * @DefaultColumn(value="NULL")
     * @Label(content="Admin ID")
     * @var string
     */
    protected $adminRoleld;

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
     * Allowed show list
     *
     * @Column(name="allowed_list", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed List")
     * @var boolean
     */
    protected $allowedList;

    /**
     * Allowed show detail
     *
     * @Column(name="allowed_detail", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Detail")
     * @var boolean
     */
    protected $allowedDetail;

    /**
     * Allowed create
     *
     * @Column(name="allowed_create", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Create")
     * @var boolean
     */
    protected $allowedCreate;

    /**
     * Allowed update
     *
     * @Column(name="allowed_update", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Update")
     * @var boolean
     */
    protected $allowedUpdate;

    /**
     * Allowed delete
     *
     * @Column(name="allowed_delete", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Delete")
     * @var boolean
     */
    protected $allowedDelete;

    /**
     * Allowed approve/reject
     *
     * @Column(name="allowed_approve", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Approve")
     * @var boolean
     */
    protected $allowedApprove;

    /**
     * Allowed short order
     *
     * @Column(name="allowed_sort_order", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Sort Order")
     * @var boolean
     */
    protected $allowedSortOrder;

    /**
     * Allowed export
     *
     * @Column(name="allowed_export", type="tinyint(1)", length=1, default_value="0", nullable=true)
     * @Label(content="Allowed Export")
     * @var boolean
     */
    protected $allowedSortExport;

    /**
     * Active
     *
     * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
     * @Label(content="Active")
     * @var boolean
     */
    protected $active;
}
