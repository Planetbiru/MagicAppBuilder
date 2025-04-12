<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * AppAdminLevelMinImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_level")
 * @package MagicAppTemplate\Entity\App
 */
class AppAdminLevelMinImpl extends MagicObject
{
	/**
	 * Admin Level ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="admin_level_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminLevelId;

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
