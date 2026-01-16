<?php

namespace MagicAppTemplate\Entity\App;

use MagicObject\MagicObject;

/**
 * The AppModuleGroupImpl class represents an entity in the "module_group" table.
 *
 * This entity maps to the "module_group" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="module_group")
 * @package MagicAppTemplate\Entity\App
 */
class AppModuleGroupImpl extends MagicObject
{
	/**
	 * Module Group ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.TIMEBASED)
	 * @Column(name="module_group_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module Group ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $moduleGroupId;

	/**
	 * Name
	 * 
	 * @NotNull
	 * @Column(name="name", type="varchar(100)", length=100, default_value=NULL, nullable=true)
	 * @Label(content="Name")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $name;
	
	/**
	 * URL
	 * 
	 * @NotNull
	 * @Column(name="url", type="varchar(255)", length=255, default_value=NULL, nullable=true)
	 * @Label(content="URL")
	 * @MaxLength(value=255)
	 * @var string
	 */
	protected $url;
	
	/**
	 * Target
	 * 
	 * @NotNull
	 * @Column(name="target", type="varchar(20)", length=20, default_value=NULL, nullable=true)
	 * @Label(content="Target")
	 * @MaxLength(value=20)
	 * @var string
	 */
	protected $target;
	
	/**
	 * Icon
	 * 
	 * @NotNull
	 * @Column(name="icon", type="varchar(40)", length=40, default_value=NULL, nullable=true)
	 * @Label(content="Icon")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $icon;


	/**
	 * Sort Order
	 * 
	 * @NotNull
	 * @Column(name="sort_order", type="int(11)", length=1, default_value="0", nullable=true)
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

	/**
	 * Default Data
	 * 
	 * @NotNull
	 * @Column(name="default_data", type="tinyint(1)", length=1, default_value=false, nullable=true)
	 * @Label(content="Default Data")
	 * @var bool
	 */
	protected $defaultData;
	
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
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $adminCreate;
	
	/**
	 * Creator
	 * 
	 * @JoinColumn(name="admin_create", referenceColumnName="admin_id", referenceTableName="admin")
	 * @Label(content="Creator")
	 * @var AppAdminMinImpl
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
	 * @var AppAdminMinImpl
	 */
	protected $editor;

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
	 * @Column(name="active", type="tinyint(1)", length=1, default_value=true, nullable=true)
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;
}
