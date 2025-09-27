<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The LicenseTrash class represents an entity in the "license_trash" table.
 *
 * This entity maps to the "license_trash" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="license_trash")
 */
class LicenseTrash extends MagicObject
{
	/**
	 * License Trash ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="license_trash_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="License Trash ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $licenseTrashId;

	/**
	 * License ID
	 * 
	 * @NotNull
	 * @Column(name="license_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="License ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $licenseId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $name;

	/**
	 * License Type
	 * 
	 * @NotNull
	 * @Column(name="license_type", type="varchar(100)", length=100, nullable=false)
	 * @Label(content="License Type")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $licenseType;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="text", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Url
	 * 
	 * @Column(name="url", type="text", nullable=true)
	 * @Label(content="Url")
	 * @var string
	 */
	protected $url;

	/**
	 * Allow Commercial Use
	 * 
	 * @Column(name="allow_commercial_use", type="tinyint(1)", length=1, defaultValue="0", nullable=true)
	 * @Label(content="Allow Commercial Use")
	 * @var bool
	 */
	protected $allowCommercialUse;

	/**
	 * Allow Modification
	 * 
	 * @Column(name="allow_modification", type="tinyint(1)", length=1, defaultValue="0", nullable=true)
	 * @Label(content="Allow Modification")
	 * @var bool
	 */
	protected $allowModification;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Creator
	 * 
	 * @JoinColumn(name="admin_create", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Creator")
	 * @var AdminMin
	 */
	protected $creator;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Editor
	 * 
	 * @JoinColumn(name="admin_edit", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Editor")
	 * @var AdminMin
	 */
	protected $editor;

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
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @NotNull
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=false)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

	/**
	 * Admin Delete
	 * 
	 * @Column(name="admin_delete", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Delete")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminDelete;

	/**
	 * IP Delete
	 * 
	 * @Column(name="ip_delete", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Delete")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipDelete;

	/**
	 * Time Delete
	 * 
	 * @Column(name="time_delete", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Delete")
	 * @var string
	 */
	protected $timeDelete;

	/**
	 * Restored
	 * 
	 * @Column(name="restored", type="tinyint(1)", length=1, defaultValue="0", nullable=true)
	 * @Label(content="Restored")
	 * @var bool
	 */
	protected $restored;

	/**
	 * Admin Restore
	 * 
	 * @Column(name="admin_restore", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Restore")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminRestore;

	/**
	 * IP Restore
	 * 
	 * @Column(name="ip_restore", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Restore")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $ipRestore;

	/**
	 * Time Restore
	 * 
	 * @Column(name="time_restore", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Restore")
	 * @var string
	 */
	protected $timeRestore;

}