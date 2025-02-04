<?php

namespace AppBuilder\App\Entity\App;

use MagicObject\MagicObject;

/**
 * AppAdminImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 */
class AppAdminImpl extends MagicObject
{
	/**
	 * Admin ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Name
	 * 
	 * @NotNull
	 * @Column(name="name", type="varchar(40)", length=40, default_value="NULL", nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

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
	 * Password
	 * 
	 * @NotNull
	 * @Column(name="name", type="varchar(40)", length=40, default_value="NULL", nullable=true)
	 * @Label(content="Password")
	 * @var string
	 */
	protected $password;
	
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
	 * Blocked
	 * 
	 * @NotNull
	 * @Column(name="blocked", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Blocked")
	 * @var boolean
	 */
	protected $blocked;

	/**
	 * Active
	 * 
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;
	
}