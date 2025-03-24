<?php

namespace AppBuilder\App;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="message")
 * @package AppBuilder\App
 */
class AppMessageImpl extends MagicObject
{
	/**
	 * Message ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="message_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Message ID")
	 * @var integer
	 */
	protected $messageId;

	/**
	 * Admin Group
	 * 
	 * @Column(name="admin_group", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Group")
	 * @var string
	 */
	protected $adminGroup;

	/**
	 * Admin Group 1 ID
	 * 
	 * @Column(name="admin_group_1", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Group 1 ID")
	 * @var string
	 */
	protected $adminGroup1Id;
	
	/**
	 * Admin Group 2 ID
	 * 
	 * @Column(name="admin_group_2", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Group 2 ID")
	 * @var string
	 */
	protected $adminGroup2Id;
	
	/**
	 * Admin Group 3 ID
	 * 
	 * @Column(name="admin_group_3", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Group 3 ID")
	 * @var string
	 */
	protected $adminGroup3Id;
	
	/**
	 * Admin Group 1
	 * 
	 * @JoinColumn(name="admin_group_1", referenceColumnName="admin_group_1")
	 * @Label(content="Admin Group 1")
	 * @var AppAdminImpl
	 */
	protected $adminGroup1;
	
	/**
	 * Admin Group 2
	 * 
	 * @JoinColumn(name="admin_group_2", referenceColumnName="admin_group_2")
	 * @Label(content="Admin Group 2")
	 * @var AppAdminImpl
	 */
	protected $adminGroup2;
	
	/**
	 * Admin Group 3
	 * 
	 * @JoinColumn(name="admin_group_3", referenceColumnName="admin_group_3")
	 * @Label(content="Admin Group 3")
	 * @var AppAdminImpl
	 */
	protected $adminGroup3;
	
	/**
	 * Icon
	 * 
	 * @Column(name="icon", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="Icon")
	 * @var string
	 */
	protected $icon;

	/**
	 * Subject
	 * 
	 * @Column(name="subject", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Subject")
	 * @var string
	 */
	protected $subject;

	/**
	 * Content
	 * 
	 * @Column(name="content", type="text", nullable=true)
	 * @Label(content="Content")
	 * @var string
	 */
	protected $content;

	/**
	 * Link
	 * 
	 * @Column(name="link", type="text", nullable=true)
	 * @Label(content="Link")
	 * @var string
	 */
	protected $link;

	/**
	 * Read
	 * 
	 * @Column(name="read", type="int(11)", length=11, nullable=true)
	 * @Label(content="Read")
	 * @var integer
	 */
	protected $read;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
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

	/**
	 * Time Read
	 * 
	 * @Column(name="time_read", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Read")
	 * @var string
	 */
	protected $timeRead;

	/**
	 * IP Read
	 * 
	 * @Column(name="ip_read", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Read")
	 * @var string
	 */
	protected $ipRead;

}