<?php

use MagicObject\Database\PicoSqlite;
use MagicObject\MagicObject;
use MagicObject\Util\Database\PicoDatabaseUtilSqlite;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="album")
 * @Cache(enable="true")
 * @package MusicProductionManager\Data\Entity
 */
class Album extends MagicObject
{
    /**
     * Album ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @NotNull
     * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
     * @Label(content="Album ID")
     * @var string
     */
    protected $albumId;

    /**
     * Name
     * 
     * @Column(name="name", type="varchar(50)", length=50, nullable=true)
     * @Label(content="Name")
     * @var string
     */
    protected $name;

    /**
     * Title
     * 
     * @Column(name="title", type="text", nullable=true)
     * @Label(content="Title")
     * @var string
     */
    protected $title;

    /**
     * Description
     * 
     * @Column(name="description", type="longtext", nullable=true)
     * @Label(content="Description")
     * @var string
     */
    protected $description;

    /**
     * Producer ID
     * 
     * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
     * @Label(content="Producer ID")
     * @var string
     */
    protected $producerId;

    /**
     * Release Date
     * 
     * @Column(name="release_date", type="date", nullable=true)
     * @Label(content="Release Date")
     * @var string
     */
    protected $releaseDate;

    /**
     * Number Of Song
     * 
     * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
     * @Label(content="Number Of Song")
     * @var integer
     */
    protected $numberOfSong;

    /**
     * Duration
     * 
     * @Column(name="duration", type="float", nullable=true)
     * @Label(content="Duration")
     * @var double
     */
    protected $duration;

    /**
     * Image Path
     * 
     * @Column(name="image_path", type="text", nullable=true)
     * @Label(content="Image Path")
     * @var string
     */
    protected $imagePath;

    /**
     * Sort Order
     * 
     * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
     * @Label(content="Sort Order")
     * @var integer
     */
    protected $sortOrder;

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
     * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
     * @Label(content="IP Edit")
     * @var string
     */
    protected $ipEdit;

    /**
     * Active
     * 
     * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var boolean
     */
    protected $active;

    /**
     * As Draft
     * 
     * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var boolean
     */
    protected $asDraft;

}

/**
 * AcuanPengawasan is entity of table acuan_pengawasan. You can join this entity to other entity using annotation JoinColumn. 
 * Don't forget to add "use" statement if the entity is outside the namespace.
 * @link https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md#entity
 * 
 * @package Sipro\Entity\Data
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="acuan_pengawasan")
 */
class AcuanPengawasan extends MagicObject
{
	/**
	 * Acuan Pengawasan ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.IDENTITY)
	 * @NotNull
	 * @Column(name="acuan_pengawasan_id", type="bigint(20)", length=20, nullable=false, extra="auto_increment")
	 * @Label(content="Acuan Pengawasan ID")
	 * @var integer
	 */
	protected $acuanPengawasanId;

	/**
	 * Nama
	 * 
	 * @Column(name="nama", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Nama")
	 * @var string
	 */
	protected $nama;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Default Data
	 * 
	 * @Column(name="default_data", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Default Data")
	 * @var boolean
	 */
	protected $defaultData;

	/**
	 * Aktif
	 * 
	 * @Column(name="aktif", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Aktif")
	 * @var boolean
	 */
	protected $aktif;

}

$database = new PicoSqlite(__DIR__ . "/db.sqlite", null, function($sql){
    //echo $sql."\r\n";
});
try
{
    $database->connect();

    $album = new Album(null, $database);
    $acuanPengawasan = new AcuanPengawasan(null, $database);

    // create table if not exists
    $util = new PicoDatabaseUtilSqlite();
    $tableStructure = $util->showCreateTable($album, true);
    echo $tableStructure;

    print_r($util->getColumnList($database, 'album'));
    $database->query($tableStructure);

    $tableStructure2 = $util->showCreateTable($acuanPengawasan, true);
    echo $tableStructure2;

    print_r($util->getColumnList($database, 'acuan_pengawasan'));
    $database->query($tableStructure);
    $database->query($tableStructure2);


    $album->setAlbumId("1235");
    $album->setName("Meraih Mimpi 2 ");
    $album->setTitle("Meraih Mimpi 2");
    $album->setDescription("Album pertama dengan judul Meraih Mimpi 2");
    $album->setProducerId("5678");
    $album->setReleaseDate("2024-09-09");
    $album->setNumberOfSong(10);
    $album->duration(185*60);
    $album->setSortOrder(1);
    $album->setIpCreate("::1");
    $album->setIpEdit("::1");
    $album->setTimeCreate(date("Y-m-d H:i:s"));
    $album->setTimeEdit(date("Y-m-d H:i:s"));
    $album->setAdminCreate("1");
    $album->setAdminEdit("1");
    $album->setIpCreate("::1");
    $album->setActive(true);
    $album->setIpCreate("::1");
    $album->setAsDraf(false);
    $album->save();

    $album2 = new Album(null, $database);
    
    $res = $album2->findAll();
    foreach($res->getResult() as $row)
    {
        echo $row."\r\n";
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}