<?php

namespace AppBuilder\App\Entity\App;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="app_notification")
 */
class AppNotificationImpl extends MagicObject
{
	/**
	 * Notification ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="notification_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Notification ID")
	 * @var integer
	 */
	protected $notification;

	/**
	 * User Group
	 * 
	 * @Column(name="user_group", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="User Group")
	 * @var string
	 */
	protected $userGroup;

	/**
	 * User Group 1 ID
	 * 
	 * @Column(name="user_group_1", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="User Group 1 ID")
	 * @var string
	 */
	protected $userGroup1Id;
	
	/**
	 * User Group 2 ID
	 * 
	 * @Column(name="user_group_2", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="User Group 2 ID")
	 * @var string
	 */
	protected $userGroup2Id;
	
	/**
	 * User Group 3 ID
	 * 
	 * @Column(name="user_group_3", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="User Group 3 ID")
	 * @var string
	 */
	protected $userGroup3Id;
	
	/**
	 * User Group 1
	 * 
	 * @JoinColumn(name="user_group_1", referenceColumnName="user_group_1")
	 * @Label(content="User Group 1")
	 * @var AppUserImpl
	 */
	protected $userGroup1;
	
	/**
	 * User Group 2
	 * 
	 * @JoinColumn(name="user_group_2", referenceColumnName="user_group_2")
	 * @Label(content="User Group 2")
	 * @var AppUserImpl
	 */
	protected $userGroup2;
	
	/**
	 * User Group 3
	 * 
	 * @JoinColumn(name="user_group_3", referenceColumnName="user_group_3")
	 * @Label(content="User Group 3")
	 * @var AppUserImpl
	 */
	protected $userGroup3;
	
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
	protected $subjek;

	/**
	 * Content
	 * 
	 * @Column(name="content", type="text", nullable=true)
	 * @Label(content="Teks")
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
	protected $waktuBuat;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

}