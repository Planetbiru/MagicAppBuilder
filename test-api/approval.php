<?php

use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoOutputFieldApproval;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputApproval;
use MagicObject\MagicObject;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

/**
 * The EntityModule class represents an entity in the "module" table.
 *
 * This entity maps to the "module" table in the database and supports ORM (Object-Relational Mapping) operations. 
 * You can establish relationships with other entities using the JoinColumn annotation. 
 * Ensure to include the appropriate "use" statement if related entities are defined in a different namespace.
 * 
 * For detailed guidance on using the MagicObject ORM, refer to the official tutorial:
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#orm
 * 
 * @package AppBuilder\Entity
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="module")
 */
class EntityModule extends MagicObject
{
	/**
	 * Module ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="module_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Module ID")
	 * @var string
	 */
	protected $moduleId;

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
	 * @var EntityAdmin
	 */
	protected $admin;

	/**
	 * Application ID
	 * 
	 * @Column(name="application_id", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Application ID")
	 * @var string
	 */
	protected $applicationId;

	/**
	 * Application
	 * 
	 * @JoinColumn(name="application_id", referenceColumnName="application_id")
	 * @Label(content="Application")
	 * @var EntityApplication
	 */
	protected $application;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * Directory Name
	 * 
	 * @Column(name="directory_name", type="varchar(1024)", length=1024, nullable=true)
	 * @Label(content="Directory Name")
	 * @var string
	 */
	protected $directoryName;

	/**
	 * Reference Value
	 * 
	 * @Column(name="reference_value", type="text", nullable=true)
	 * @Label(content="Reference Value")
	 * @var string
	 */
	protected $referenceValue;

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

}

$entity = new EntityModule();
$entity->setUserId("anyId");
$data = new PicoUserFormOutputApproval();

$data->addOutput(
	new PicoOutputFieldApproval(
		new PicoInputField("userId", $entity->label("userId")), 
		"string", 
		new PicoInputField($entity->get("userId"), $entity->get("userId")),
		new PicoInputField($entity->get("userId"), $entity->get("userId"))
	)
);

$data->setWaitingfor(new PicoFieldWaitingFor(1, "new", "new"));

$picoModule = new PicoModuleInfo("any", "Any", "detail", "code", "namespace");

$picoModule
->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

$appModule = new EntityModule();
$appModule->setModuleId("123");
$body = PicoResponseBody::getInstance()
  	->setModule($picoModule)
    ->setData($data)
    ->setEntity($appModule, true)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
	->switchCaseTo("snake_case")
    ;
	
PicoResponse::sendResponse($body);