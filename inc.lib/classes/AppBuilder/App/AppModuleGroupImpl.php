<?php

namespace AppBuilder\App;

use MagicObject\MagicObject;

/**
 * AppModuleGroupImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="module_group")
 * @package AppBuilder\App
 */
class AppModuleGroupImpl extends MagicObject
{
	/**
	 * Module Group ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="module_group_id", type="varchar(40)", length=40, nullable=false)
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
