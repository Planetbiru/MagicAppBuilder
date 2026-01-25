<?php

namespace AppBuilder\EntityInstaller;

use MagicObject\MagicObject;

/**
 * The EntityStarWorkspace class represents an entity in the "star_workspace" table.
 *
 * This entity maps to the "star_workspace" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\EntityInstaller
 * @Entity
 * @JSON(propertyNamingStrategy=SNAKE_CASE, prettify=false)
 * @Table(name="star_workspace")
 */
class EntityStarWorkspace extends MagicObject
{
	/**
	 * Star Workspace ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @NotNull
	 * @Column(name="star_workspace_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Star Workspace ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $starWorkspaceId;

	/**
	 * Workspace ID
	 * 
	 * @Column(name="workspace_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Workspace ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $workspaceId;

	/**
	 * Admin ID
	 * 
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
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