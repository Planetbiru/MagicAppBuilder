<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The AdminSession class represents an entity in the "admin_session" table.
 *
 * This entity maps to the "admin_session" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="admin_session")
 */
class AdminSession extends MagicObject
{
	/**
	 * Admin Session ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="admin_session_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Admin Session ID")
	 * @var string
	 */
	protected $adminSessionId;

	/**
	 * Admin ID
	 * 
	 * @Column(name="admin_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin ID")
	 * @var string
	 */
	protected $adminId;

	/**
	 * Admin
	 * 
	 * @JoinColumn(name="admin_id", referenceColumnName="admin_id")
	 * @Label(content="Admin")
	 * @var AdminMin
	 */
	protected $admin;

	/**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Application
	 * 
	 * @JoinColumn(name="application_id", referenceColumnName="application_id")
	 * @Label(content="Application")
	 * @var ApplicationMin
	 */
	protected $application;

	/**
	 * User Agent
	 * 
	 * @Column(name="user_agent", type="text", nullable=true)
	 * @Label(content="User Agent")
	 * @var string
	 */
	protected $userAgent;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
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

}