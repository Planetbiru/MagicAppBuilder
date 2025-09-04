<?php

namespace MagicAdmin\Entity\Data;

use MagicObject\MagicObject;

/**
 * The Customer class represents an entity in the "customer" table.
 *
 * This entity maps to the "customer" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package MagicAdmin\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="customer")
 */
class Customer extends MagicObject
{
	/**
	 * Customer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="customer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Customer ID")
	 * @MaxLength(value=40)
	 * @var string
	 */
	protected $customerId;

	/**
	 * First Name
	 * 
	 * @Column(name="first_name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="First Name")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $firstName;

	/**
	 * Last Name
	 * 
	 * @Column(name="last_name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Last Name")
	 * @MaxLength(value=50)
	 * @var string
	 */
	protected $lastName;

	/**
	 * Company
	 * 
	 * @Column(name="company", type="varchar(80)", length=80, nullable=true)
	 * @Label(content="Company")
	 * @MaxLength(value=80)
	 * @var string
	 */
	protected $company;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Address")
	 * @MaxLength(value=255)
	 * @var string
	 */
	protected $address;

	/**
	 * City
	 * 
	 * @Column(name="city", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="City")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $city;

	/**
	 * State
	 * 
	 * @Column(name="state", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="State")
	 * @MaxLength(value=100)
	 * @var string
	 */
	protected $state;

	/**
	 * Country ID
	 * 
	 * @Column(name="country_id", type="varchar(5)", length=5, nullable=true)
	 * @Label(content="Country ID")
	 * @MaxLength(value=5)
	 * @var string
	 */
	protected $countryId;

	/**
	 * Postal Code
	 * 
	 * @Column(name="postal_code", type="varchar(10)", length=10, nullable=true)
	 * @Label(content="Postal Code")
	 * @MaxLength(value=10)
	 * @var string
	 */
	protected $postalCode;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(24)", length=24, nullable=true)
	 * @Label(content="Phone")
	 * @MaxLength(value=24)
	 * @var string
	 */
	protected $phone;

	/**
	 * Fax
	 * 
	 * @Column(name="fax", type="varchar(24)", length=24, nullable=true)
	 * @Label(content="Fax")
	 * @MaxLength(value=24)
	 * @var string
	 */
	protected $fax;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(80)", length=80, nullable=true)
	 * @Label(content="Email")
	 * @MaxLength(value=80)
	 * @var string
	 */
	protected $email;

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