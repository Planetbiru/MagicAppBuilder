<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * AppModuleMinImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="module")
 * @package MagicAppTemplate\Entity\App
 */
class AppModuleMinImpl extends MagicObject
{
	/**
	 * Module ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="module_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module ID")
	 * @var string
	 */
	protected $moduleId;

	/**
	 * Module Group ID
	 * 
	 * @NotNull
	 * @Column(name="module_group_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
	 * @Label(content="Module Group ID")
	 * @var string
	 */
	protected $moduleGroupId;

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
	 * URL
	 * 
	 * @NotNull
	 * @Column(name="url", type="longtext", nullable=true)
	 * @Label(content="URL")
	 * @var string
	 */
	protected $url;
	
	/**
	 * Target
	 * 
	 * @NotNull
	 * @Column(name="target", type="varchar(20)", length=20, default_value="NULL", nullable=true)
	 * @Label(content="Target")
	 * @var string
	 */
	protected $target;
	
	/**
	 * Icon
	 * 
	 * @NotNull
	 * @Column(name="icon", type="varchar(40)", length=40, default_value="NULL", nullable=true)
	 * @Label(content="Icon")
	 * @var string
	 */
	protected $icon;
	
	/**
	 * Menu
	 * 
	 * @NotNull
	 * @Column(name="menu", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Menu")
	 * @var bool
	 */
	protected $menu;

	/**
	 * Special Access
	 * 
	 * @NotNull
	 * @Column(name="special_access", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Special Access")
	 * @var bool
	 */
	protected $specialAccess;

	/**
	 * Sort Order
	 * 
	 * @NotNull
	 * @Column(name="sort_order", type="int(11)", length=1, default_value="0", nullable=true)
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

	/**
	 * Default Data
	 * 
	 * @NotNull
	 * @Column(name="default_data", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Default Data")
	 * @var bool
	 */
	protected $defaultData;

	/**
	 * Active
	 * 
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;
	
	
}
