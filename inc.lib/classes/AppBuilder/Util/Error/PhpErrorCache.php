<?php

namespace AppBuilder\Util\Error;

use MagicObject\MagicObject;

/**
 * The PhpErrorCache class represents an entity in the "error_cache" table.
 *
 * This entity maps to the "error_cache" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="error_cache")
 */
class PhpErrorCache extends MagicObject
{
	/**
	 * Error Cache ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @NotNull
	 * @Column(name="error_cache_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Error Cache ID")
	 * @var string
	 */
	protected $errorCacheId;
	
	/**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(512)", length=512, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="varchar(512)", length=512, nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * Modification Time
	 * 
	 * @Column(name="modification_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="Modification Time")
	 * @var string
	 */
	protected $modificationTime;

	/**
	 * Error Code
	 * 
	 * @Column(name="error_code", type="int(11)", length=11, nullable=true)
	 * @Label(content="Error Code")
	 * @var int
	 */
	protected $errorCode;

	/**
	 * Message
	 * 
	 * @Column(name="message", type="text", nullable=true)
	 * @Label(content="Message")
	 * @var string
	 */
	protected $message;

	/**
	 * Line Number
	 * 
	 * @Column(name="line_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Line Number")
	 * @var int
	 */
	protected $lineNumber;

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
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

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
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="true", nullable=true)
	 * @DefaultColumn(value="true")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}