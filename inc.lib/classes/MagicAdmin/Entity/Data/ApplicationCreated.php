<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The ApplicationCreated class represents an entity in the "application_created" table.
 *
 * This entity maps to the "application_created" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify=false)
 * @Table(name="application_created")
 */
class ApplicationCreated extends MagicObject
{
	/**
	 * Period
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @Column(name="period", type="varchar(6)", length=6, nullable=true)
	 * @Label(content="Period")
	 * @MaxLength(value=6)
	 * @var string
	 */
	protected $period;

	/**
	 * Total
	 * 
	 * @NotNull
	 * @Column(name="total", type="int", nullable=false)
	 * @Label(content="Total")
	 * @var int
	 */
	protected $total;

}