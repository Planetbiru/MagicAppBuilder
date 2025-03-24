<?php

namespace AppBuilder\App;

use MagicObject\MagicObject;

/**
 * AppAdminImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 * @package AppBuilder\App
 */
class AppAdminImpl extends MagicObject
{
	/**
	 * Admin ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
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
	 * @Column(name="password", type="varchar(40)", length=40, default_value="NULL", nullable=true)
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
	 * Language ID
	 * 
	 * @NotNull
	 * @Column(name="language_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
	 * @Label(content="Language ID")
	 * @var AppAdminLevelImpl
	 */
	protected $languageId;

	/**
	 * Blocked
	 * 
	 * @NotNull
	 * @Column(name="blocked", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Blocked")
	 * @var bool
	 */
	protected $blocked;

	/**
	 * Active
	 * 
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;
	
}