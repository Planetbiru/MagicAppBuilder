<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The StarApplication class represents an entity in the "star_application" table.
 *
 * This entity maps to the "star_application" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify=false)
 * @Table(name="star_application")
 */
class StarApplication extends MagicObject
{
	/**
	 * Star Application ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="star_application_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Star Application ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $starApplicationId;

	/**
	 * Application ID
	 * 
	 * @NotNull
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Application ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Admin ID
	 * 
	 * @NotNull
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminId;

	/**
	 * Star
	 * 
	 * @Column(name="star", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Star")
	 * @var bool
	 */
	protected $star;

}