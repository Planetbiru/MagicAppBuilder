<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user_activity")
 * @package MagicAppTemplate\Entity\App
 */
class AppUserActivityImpl extends MagicObject
{
    /**
	 * User Activity ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_activity_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="User Activity ID")
	 * @var int
	 */
	protected $userActivityId;

    /**
     * Admin ID
     * 
     * @NotNull
     * @Column(name="admin_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="Admin ID")
     * @var string
     */
    protected $adminId;

    /**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_id", referenceColumnName="admin_id")
	 * @Label(content="Admin")
	 * @var AppAdminImpl
	 */
	protected $admin;

    /**
     * User Action
     * 
     * @NotNull
     * @Column(name="user_action", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="User Action")
     * @var string
     */
    protected $userAction;

    /**
     * Username
     * 
     * @NotNull
     * @Column(name="username", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="Username")
     * @var string
     */
    protected $username;

    /**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

    /**
	 * Method
	 * 
	 * @Column(name="method", type="varchar(20)", length=20, nullable=true, updatable=false)
	 * @Label(content="Method")
	 * @var string
	 */
	protected $method;

	/**
	 * Path
	 * 
	 * @Column(name="path", type="longtext", nullable=true, updatable=false)
	 * @Label(content="Path")
	 * @var string
	 */
	protected $path;

    /**
	 * Get Data
	 * 
	 * @Column(name="get_data", type="longtext", nullable=true, updatable=false)
	 * @Label(content="Get Data")
	 * @var string
	 */
	protected $getData;

    /**
	 * Post Data
	 * 
	 * @Column(name="post_data", type="longtext", nullable=true, updatable=false)
	 * @Label(content="Post Data")
	 * @var string
	 */
	protected $postData;

}