## Language

MagicObject supports multilingual applications. MagicObject allows developers to create entities that support a wide variety of languages that users can choose from. At the same time, different users can use different languages.

To create table with multiple language, create new class from `DataTable` object. We can copy data from aother object to `DataTable` easly.

```php
<?php

use MagicObject\DataTable;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class Song extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
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
	 * Composer
	 * 
	 * @Column(name="composer", type="text", nullable=true)
	 * @Label(content="Composer")
	 * @var string
	 */
	protected $composer;
    
    /**
	 * Vocalist
	 * 
	 * @Column(name="vocalist", type="text", nullable=true)
	 * @Label(content="Vocalist")
	 * @var string
	 */
	protected $vocalist;
}

/**
 * House
 * 
 * @Attributes(id="house" width="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Language")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class Multibahasa extends DataTable
{
    
}

$song = new Song();
$song
    ->setSongId("11111")
    ->setTitle("Lagu Satu")
    ->setComposer("Kamshory")
    ->setVocalist("Roy")
    ;

$translated = new Multibahasa($song);
echo $translated;


// add language from array
$translated->addLanguage("id", 
    array(
        "songId" => "ID Lagu",
        "title" => "Judul",
        "composer" => "Pengarang",
        "vocalist" => "Penyanyi"
    )
);
$translated->selectLanguage('id');
echo $translated;

// add language from stdClass
$translator1 = new stdClass;

$translator1->songId = "ID Lagu";
$translator1->title = "Judul";
$translator1->composer = "Pengarang";
$translator1->vocalist = "Penyanyi";

$translated->addLanguage("id", $translator1);
$translated->selectLanguage('id');
echo $translated;

// add language from specific class
class Bahasa
{
    public $songId = "ID Lagu";
    public $title = "Judul";
    public $composer = "Pengarang";
    public $vocalist = "Penyanyi";
}

$translator2 = new Bahasa();

$translated->addLanguage("id", $translator2);
$translated->selectLanguage('id');
echo $translated;

``` 

```php
<?php

use MagicObject\DataTable;
use MagicObject\Util\ClassUtil\PicoObjectParser;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * House
 * 
 * @Attributes(id="house" width="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Language")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class House extends DataTable
{
    /**
     * ID
     *
     * @Label(content="ID")
     * @Column(name="id")
     * @var string
     */
    protected $id;
    
    /**
     * Address
     *
     * @Label(content="Address")
     * @Column(name="address")
     * @var string
     */
    protected $address;
    
    /**
     * Color
     *
     * @Label(content="Color")
     * @Column(name="color")
     * @var string
     */
    protected $color;

    /**
     * Time Create
     *
     * @Label(content="Time Create")
     * @Column(name="timeCreate")
     * @var DateTime
     */
    protected $timeCreate;
    
}

class BahasaIndonesia extends stdClass
{
    public $id = "ID";
    
    public $address = "Alamat";
    
    public $color = "Warna";

    public $timeCreate = "Waktu Buat";
}

$data = PicoObjectParser::parseYamlRecursive(
"id: 1
address: Jalan Inspeksi no 9
color: blue
"
);

$language = new BahasaIndonesia();

$rumah = new House($data);
$rumah->addLanguage('id', $language);
$rumah->selectLanguage('id');
$rumah->addClass('table');

$apa = $rumah;
echo $apa;
```

