<?php

namespace AppBuilder\App\Entity\App;

use MagicObject\MagicObject;

/**
 * AppUserRoleImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="app_user_role")
 */
class AppUserRoleImpl extends MagicObject
{
    /**
     * User Role ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @Column(name="user_role_id", type="varchar(40)", length=40, nullable=false)
     * @DefaultColumn(value="NULL")
     * @Label(content="User ID")
     * @var string
     */
    protected $userRoleld;

    /**
     * User Level ID
     * 
     * @NotNull
     * @Column(name="user_level_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="User Level ID")
     * @var string
     */
    protected $userLevelId;

    /**
     * User Level
     * 
     * @NotNull
     * @JoinColumn(name="user_level_id", referenceColumnName="user_level_id")
     * @Label(content="User Level")
     * @var AppUserLevelImpl
     */
    protected $userLevel;

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
     * Active
     *
     * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
     * @Label(content="Active")
     * @var boolean
     */
    protected $active;
}
