<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppUserPasswordHistoryImpl class represents an entity in the "user_password_history" table.
 *
 * This entity maps to the "user_password_history" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="user_password_history")
 * @package MagicAppTemplate\Entity\App
 */
class AppUserPasswordHistoryImpl extends MagicObject
{
    /**
	 * User Password History ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_password_history_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="User Password History ID")
	 * @var int
	 */
	protected $userPasswordHistoryId;

    /**
     * Admin ID
     * 
     * @NotNull
     * @Column(name="admin_id", type="varchar(40)", length=40, default_value="NULL", nullable=true)
     * @Label(content="Admin ID")
     * @var string
     */
    protected $adminId;

    /**
	 * Password
	 * 
	 * @Column(name="password", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Password")
	 * @var string
	 */
	protected $password;

    /**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

}