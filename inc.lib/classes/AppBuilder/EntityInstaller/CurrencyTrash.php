<?php

namespace AppBuilder\EntityInstaller;

use MagicObject\MagicObject;

/**
 * The CurrencyTrash class represents an entity in the "currency_trash" table.
 *
 * This entity maps to the "currency_trash" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\EntityInstaller
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="currency_trash")
 */
class CurrencyTrash extends MagicObject
{
	/**
	 * Currency Trash ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="currency_trash_id", type="varchar(40)", length=40, nullable=false)
	 * @DefaultColumn(value="NULL")
	 * @Label(content="Currency Trash ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $currencyTrashId;

	/**
	 * Currency ID
	 * 
	 * @NotNull
	 * @Column(name="currency_id", type="varchar(5)", length=5, nullable=false)
	 * @Label(content="Currency ID")
	 * @MaxLength(value=5)
	 * @var string
	 */
	protected $currencyId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $name;

	/**
	 * Symbol
	 * 
	 * @Column(name="symbol", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Symbol")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $symbol;

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
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminEdit;

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