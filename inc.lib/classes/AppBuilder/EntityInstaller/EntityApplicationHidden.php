<?php

namespace AppBuilder\EntityInstaller;

use MagicObject\MagicObject;

/**
 * The EntityApplicationHidden class represents an entity in the "application_hidden" table.
 *
 * This entity maps to the "application_hidden" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\EntityInstaller
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="application_hidden")
 */
class EntityApplicationHidden extends MagicObject
{
	/**
	 * Application Hidden ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @NotNull
	 * @Column(name="application_hidden_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Application Hidden ID")
	 * @var string
	 */
	protected $applicationHiddenId;

	/**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Admin ID
	 * 
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Hidden
	 * 
	 * @Column(name="hidden", type="tinyint(1)", length=1, defaultValue="true", nullable=true)
	 * @DefaultColumn(value="true")
	 * @Label(content="Hidden")
	 * @var bool
	 */
	protected $hidden;

}