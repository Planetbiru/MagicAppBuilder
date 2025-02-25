<?php

namespace AppBuilder\App\Entity\App;

use MagicObject\MagicObject;

/**
 * AppModuleImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="module")
 * @package AppBuilder\App\Entity\App
 */
class AppModuleImpl extends MagicObject
{
	/**
	 * Module ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="module_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
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
	 * Module Group
	 * 
	 * @NotNull
	 * @JoinColumn(name="module_group_id", referenceColumnName="module_group_id")
	 * @Label(content="Module Group")
	 * @var AppModuleGroupImpl
	 */
	protected $moduleGroup;

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
	 * Special Access
	 * 
	 * @NotNull
	 * @Column(name="special_access", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Special Access")
	 * @var boolean
	 */
	protected $specialAccess;

	/**
	 * Sort Order
	 * 
	 * @NotNull
	 * @Column(name="sort_order", type="int(11)", length=1, default_value="0", nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

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
	 * Active
	 * 
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="0", nullable=true)
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;
}
