<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppMenuCacheImpl class represents an entity in the "menu_cache" table.
 *
 * This entity maps to the "menu_cache" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MyApplication\Entity\Data
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify=false)
 * @Table(name="menu_cache")
 */
class AppMenuCacheImpl extends MagicObject
{
	/**
	 * Menu Cache ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="menu_cache_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Menu Cache ID")
	 * @var string
	 */
	protected $menuCacheId;

	/**
	 * Admin Level ID
	 * 
	 * @Column(name="admin_level_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Level ID")
	 * @var string
	 */
	protected $adminLevelId;

	/**
	 * Data
	 * 
	 * @Column(name="data", type="text", nullable=true)
	 * @Label(content="Data")
	 * @var string
	 */
	protected $data;

}