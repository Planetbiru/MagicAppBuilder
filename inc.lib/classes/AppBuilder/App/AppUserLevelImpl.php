<?php

namespace AppBuilder\App\Entity\App;

use MagicApp\Entity\AppUserLevel;

/**
 * AppUserLevelImpl 
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user_level")
 */
class AppUserLevelImpl extends AppUserLevel
{
	/**
	 * User Level ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="user_level_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="User ID")
	 * @var string
	 */
	protected $userLevelId;

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
