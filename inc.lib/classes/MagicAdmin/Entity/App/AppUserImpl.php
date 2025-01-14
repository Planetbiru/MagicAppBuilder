<?php

namespace MagicAdmin\Entity\App;

use MagicObject\MagicObject;

/**
 * AppUserImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="app_user")
 */
class AppUserImpl extends MagicObject
{
	/**
	 * User ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="user_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userId;

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
	 * Default Data
	 * 
	 * @NotNull
	 * @Column(name="default_data", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Default Data")
	 * @var boolean
	 */
	protected $defaultData;

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