<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * AppAdminImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin")
 * @package MagicAppTemplate\Entity\App
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
	 * Language
	 * 
	 * @NotNull
	 * @JoinColumn(name="language_id", referenceColumnName="language_id")
	 * @Label(content="Language")
	 * @var AppLanguageImpl
	 */
	protected $language;

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
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

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