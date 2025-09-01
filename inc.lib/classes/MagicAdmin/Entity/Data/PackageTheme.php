<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The PackageTheme class represents an entity in the "package_theme" table.
 *
 * This entity maps to the "package_theme" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="package_theme")
 */
class PackageTheme extends MagicObject
{
	/**
	 * Package Theme ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="package_theme_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Package Theme ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $packageThemeId;

	/**
	 * Starter Package ID
	 * 
	 * @NotNull
	 * @Column(name="starter_package_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Starter Package ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $starterPackageId;

	/**
	 * Starter Package
	 * 
	 * @JoinColumn(name="starter_package_id", referenceColumnName="starter_package_id", referenceTableName="starter_package")
	 * @Label(content="Starter Package")
	 * @var StarterPackage
	 */
	protected $starterPackage;

	/**
	 * Name
	 * 
	 * @NotNull
	 * @Column(name="name", type="varchar(100)", length=100, nullable=false)
	 * @Label(content="Name")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="text", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * Preview Image
	 * 
	 * @Column(name="preview_image", type="text", nullable=true)
	 * @Label(content="Preview Image")
	 * @var string
	 */
	protected $previewImage;

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
	 * @var AdminCreate
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
	 * @var AdminEdit
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

}