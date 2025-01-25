<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The GeneralCache class represents an entity in the "general_cache" table.
 *
 * This entity maps to the "general_cache" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="general_cache")
 */
class GeneralCache extends MagicObject
{
	/**
	 * General Cache ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="general_cache_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="General Cache ID")
	 * @var string
	 */
	protected $generalCacheId;

	/**
	 * Content
	 * 
	 * @Column(name="content", type="text", nullable=true)
	 * @Label(content="Content")
	 * @var string
	 */
	protected $content;

	/**
	 * Expire
	 * 
	 * @Column(name="expire", type="timestamp", length=19, nullable=true)
	 * @Label(content="Expire")
	 * @var string
	 */
	protected $expire;

}