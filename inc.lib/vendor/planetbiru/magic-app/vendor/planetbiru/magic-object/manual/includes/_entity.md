## Entity

Entity is class to access database. Entity is derived from MagicObject. Some annotations required to activated all entity features. 

**Constructor**

Parameters:

1. array|stdClass|object $data

Initial data

2. PicoDatabase $database

Database connection

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
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
```

 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Cache(enable="true")

### Class Parameters

**@Entity**

`@Entity` Indicates that the class represents an entity.

**@JSON**

`@JSON` Configures how the object will be serialized.

Attributes:
1. `property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

Default: `CAMEL_CASE`

2. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default: `false`

**@Table**

`@Table` Provides caching configuration.

Attributes:
`name`

`name` is the table name of the entity.

**@Cache**

`@Cache` Specifies the namespace of the class.

Attributes:
`enable`

`enable` is option to enable or disable cache.

Allowed value:

- `true` Cache is enabled
- `false` Cache is disabled

Default: `false`

**@package**

`@package` is parameter for namespace.

PHP does not provide a native method to retrieve a class's namespace. Earlier versions of MagicObject attempted to obtain this information by reading the PHP script, a method that proved both unsafe and inefficient.

With the addition of package annotations to each entity, MagicObject now offers a safer and more efficient way to join entities. However, if a package annotation is not available on an entity, version 2.1 will still revert to the old method.

### Property Parameters

* @Id
* @GeneratedValue(strategy=GenerationType.UUID)
* @NotNull
* @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
* @Label(content="Album ID")
* @var string

**@Id**

`@Id` indicate that the column is primary key.

**@GeneratedValue**

`@GeneratedValue` indicated that the column is has autogenerated value.

Attributes:
- `strategy`
- `generator`

`strategy` is strategy to generate auto value.

Allowed value:

**1. GenerationType.UUID**

Generate 20 bytes unique ID

- 14 byte hexadecimal of uniqid https://www.php.net/manual/en/function.uniqid.php
- 6 byte hexadecimal or random number

**2. GenerationType.IDENTITY**

Autoincrement using database feature

**3. TABLE** Not implemented yet

**4. SEQUENCE** Not implemented yet

**5. AUTO** Not implemented yet 


MagicObject will not update `time_create`, `admin_create`, and `ip_create` because `updatable=false`. So, even if the application wants to update this value, this column will be ignored when performing an update query to the database.

`generator` is generator of the value.

**@NotNull**

`@NotNull` indicate that the column is not null. MagicObject will use it when user insert or update data with null values.

**@Column**

`@Column` is parameter to store the information of the column.

Attributes:
- `name`
- `type`
- `length`
- `nullable`
- `default_value`
- `insertable`
- `updatable`

`name` is column name.

`type` is column type.

`length` is column length if any.

`nullable` indicate that column value can be `null` or not. Available value of `nullable` is `true` and `false`. 

`default_value` is default value of the column.

`insertable` indicate that column will exists on `INSERT` statement. Available value of `insertable` is `true` and `false`. 

`updatable` indicate that column will exists on `UPDATE` statement. Available value of `updatable` is `true` and `false`. 


**@JoinColumn**

`@JoinColumn` is parameter to store the information of the join column.

Attributes:
- `name`
- `referenceColumnName`

`name` is column name of the master table.

`referenceColumnName` is column name of the join table. If `referenceColumnName` is not exists, MagicObject will use value on `name` as reference column name.

**@Label** is parameter to store label of the column.

Attributes:
- `content`

`content` is the content of the column label. Use quote to create label. For example `@Label(content="Album ID")`.


**@var**

`@var` is native annotation of class field. MagicObject use this annotation to fix the column value given.


### Usage

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    // create new 
  
    $album1 = new Album(null, $database);
    $album1->setAlbumId("123456");
    $album1->setName("Album 1");
    $album1->setAdminCreate("USER1");
    $album1->setDuration(300);
  
  
  
    // other way to create object
    // create object from stdClass or other object with match property (snake case or camel case)
    $data = new stdClass;
    // snake case
    $data->album_id = "123456";
    $data->name = "Album 1";
    $data->admin_create = "USER1";
    $data->duration = 300;
  
    // or camel case
    $data->albumId = "123456";
    $data->name = "Album 1";
    $data->adminCreate = "USER1";
    $data->duration = 300;
  
    $album1 = new Album($data, $database); 
  
    // other way to create object
    // create object from associated array with match property (snake case or camel case)
    $data = array();
    // snake case
    $data["album_id"] "123456";
    $data["name"] = "Album 1";
    $data["admin_create"] = "USER1";
    $data["duration"] = 300;
  
    // or camel case
    $data["albumId"] = "123456";
    $data["name"] = "Album 1";
    $data["adminCreate"] = "USER1";
    $data["duration"] = 300;
    $album1 = new Album($data, $database);
  
  
    // get value from form
    // this way is not safe
    $album1 = new Album($_POST, $database);
  
  
    // we can use other way
    $inputPost = new InputPost();
  
    // we can apply filter
    $inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $inputPost->filterDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
  
    // if property not present in $inputPost, we can set default value
    // please note that user can modify form and add update any unwanted properties to be updated
    $inputPost->checkboxActive(false);
    $inputPost->checkboxAsDraft(true);
  
    // we can remove any property data from object $inputPost before apply it to entity
    // it will not saved to database
    $inputPost->setSortOrder(null);
  
    $album1 = new Album($inputPost, $database);
  
    // insert to database
    $album1->insert();
  
    // insert or update
    $album1->save();
  
    // update
    // NoRecordFoundException if ID not found
    $album1->update();
  
    // convert to JSON
    $json = $album1->toString();
    // or
    $json = $album1 . "";
  
    // send to buffer output
    // automaticaly converted to string
    echo $album1;
  
    // find one by ID
    $album2 = new Album(null, $database);
    $album2->findOneByAlbumId("123456");
  
    // find multiple
    $album2 = new Album(null, $database);
    $albums = $album2->findByAdminCreate("USER1");
    $rows = $albums->getResult();
    foreach($rows as $albumSaved)
    {
        // $albumSaved is instance of Album
  
        // we can update data
        $albumSaved->setAdminEdit("USER1");
        $albumSaved->setTimeEdit(date('Y-m-d H:i:s'));
  
        // this value will not be saved to database because has no column
        $albumSaved->setAnyValue("ANY VALUE");
  
        $albumSaved->update();
    }
  
  
}
catch(Exception $e)
{
    // do nothing
}

```


### Insert

Insert new record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
try
{
	$album->insert();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To insert with any column value `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->insert(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Save

Insert new record if not exists, otherwise update the record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Note: If operation is update, nonupdatable column will not be updated

### Update

Update existing record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->update();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To update any column value to `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->update(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Specific Column

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumId("123456");

	// to update the record

	// update begin
	$album1->setName("Album 1");
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Combination of Columns

Logic `OR`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumIdOrNumbefOfSong("123456", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAdminCreateAndNumbefOfSong("USER1", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select Multiple By Combination of Columns

Logic `OR`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findByAlbumIdOrNumbefOfSong("123456", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findOneByAdminCreateAndNumbefOfSong("USER1", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Find By Specification

Real applications do not always use simple logic to filter database records. Complex logic cannot be done using just one method. MagicObject provides features to make searches more specific.

**Example 1**

```php
$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = new PicoSpecification();

	$predicate1 = new PicoPredicate();
	$predicate1->equals('albumId', $inputGet->getAlbumId());
	$spesification->addAnd($predicate1);

	$predicate2 = new PicoPredicate();
	$predicate2->equals('active', true);
	$spesification->addAnd($predicate2);
	
	// Up to this point we are still using albumId and active

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 2**

Album specification from `$_GET`

```php

/**
 * Create album specification
 * @param PicoRequestBase $inputGet
 * @return PicoSpecification
 */
function createAlbumSpecification($inputGet)
{
	$spesification = new PicoSpecification();

	if($inputGet->getAlbumId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('albumId', $inputGet->getAlbumId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getName() != "" || $inputGet->getTitle() != "")
	{
		$spesificationTitle = new PicoSpecification();
		
		if($inputGet->getName() != "")
		{
			$predicate1 = new PicoPredicate();
			$predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
			$spesificationTitle->addOr($predicate1);
			
			$predicate2 = new PicoPredicate();
			$predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
			$spesificationTitle->addOr($predicate2);
		}
		if($inputGet->getTitle() != "")
		{
			$predicate3 = new PicoPredicate();
			$predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate3);
			
			$predicate4 = new PicoPredicate();
			$predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate4);
		}
		
		$spesification->addAnd($spesificationTitle);
	}
	
	
	if($inputGet->getProducerId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('producerId', $inputGet->getProducerId());
		$spesification->addAnd($predicate1);
	}
	
	return $spesification;
}

$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('albumId', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = createAlbumSpecification(new InputGet());

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 3**

Song specification from `$_GET`

```php
<?php

namespace MusicProductionManager\Utility;

use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoRequestBase;

/**
 * Specification utility
 */
class SpecificationUtil
{
    /**
     * Create MIDI specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createMidiSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getMidiId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('midiId', $inputGet->getMidiId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistVocalistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalId', $inputGet->getArtistVocalistId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createSongSpecification($inputGet, $additional = null) //NOSONAR
    {
        $spesification = new PicoSpecification();

        if($inputGet->getSongId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('songId', $inputGet->getSongId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getProducerId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('producerId', $inputGet->getProducerId());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getProducerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('producer.name', PicoPredicate::generateLikeContains($inputGet->getProducerName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }

        if($inputGet->getSubtitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('subtitle', PicoPredicate::generateLikeContains($inputGet->getSubtitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getVocalist() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalist', $inputGet->getVocalist());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getVocalistName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('vocalist.name', PicoPredicate::generateLikeContains($inputGet->getVocalistName()));
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getComposer() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistComposer', $inputGet->getComposer());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getComposerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('composer.name', PicoPredicate::generateLikeContains($inputGet->getComposerName()));
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getArranger() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistArranger', $inputGet->getArranger());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getArrangerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('arranger.name', PicoPredicate::generateLikeContains($inputGet->getArrangerName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getSubtitleComplete() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('subtitleComplete', $inputGet->getSubtitleComplete());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getVocal() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('vocal', $inputGet->getVocal());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createReferenceSpecification($inputGet, $additional = null)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getReferenceId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('referenceId', $inputGet->getReferenceId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbum() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('album', $inputGet->getAlbum());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createSongDraftSpecification($inputGet, $additional = null) //NOSONAR
    {
        $spesification = new PicoSpecification();

        if($inputGet->getSongId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('songId', $inputGet->getSongId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getFrom() != "" && $inputGet->getTo() != "")
        {
            $to = $inputGet->getTo();
            if(strlen($to) < 11)
            {
                $to = $to . " 23:59:59";
            }
            $predicate1 = new PicoPredicate();
            $predicate1->greaterThanOrEquals('timeCreate', $inputGet->getFrom());
            $spesification->addAnd($predicate1);

            $predicate2 = new PicoPredicate();
            $predicate2->lessThanOrEquals('timeCreate', $to);
            $spesification->addAnd($predicate2);
        }
        else if($inputGet->getFrom())
        {
            $predicate1 = new PicoPredicate();
            $predicate1->greaterThanOrEquals('timeCreate', $inputGet->getFrom());
            $spesification->addAnd($predicate1);
        }
        else if($inputGet->getTo() != "")
        {
            $to = $inputGet->getTo();
            if(strlen($to) < 11)
            {
                $to = $to . " 23:59:59";
            }
            $predicate2 = new PicoPredicate();
            $predicate2->lessThanOrEquals('timeCreate', $to);
            $spesification->addAnd($predicate2);
        }

        if($inputGet->getLyric() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('lyric', PicoPredicate::generateLikeContains($inputGet->getLyric()));
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create album specification
     * @param PicoRequestBase $inputGet
     * @return PicoSpecification
     */
    public static function createAlbumSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }
        
        
        if($inputGet->getProducerId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('producerId', $inputGet->getProducerId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create genre specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createGenreSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create user type specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createUserTypeSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getUserTypeId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('userTypeId', $inputGet->getUserTypeId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create artist specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createArtistSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create user specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createUserSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getUserId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('userId', $inputGet->getUserId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getUsername() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('username', PicoPredicate::generateLikeContains($inputGet->getUsername()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getEmail() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('email', PicoPredicate::generateLikeContains($inputGet->getEmail()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }
}
```

Implementation

```php
$orderMap = array(
    'name'=>'name', 
    'title'=>'title', 
    'rating'=>'rating',
    'albumId'=>'albumId', 
    'album'=>'albumId', 
    'trackNumber'=>'trackNumber',
    'genreId'=>'genreId', 
    'genre'=>'genreId',
    'producerId'=>'producerId',
    'artistVocalId'=>'artistVocalId',
    'artistVocalist'=>'artistVocalId',
    'artistComposer'=>'artistComposer',
    'artistArranger'=>'artistArranger',
    'duration'=>'duration',
    'subtitleComplete'=>'subtitleComplete',
    'vocal'=>'vocal',
    'active'=>'active'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet);

if($pagination->getOrderBy() == '')
{
	$sortable = new PicoSortable();
	$sort1 = new PicoSort('albumId', PicoSort::ORDER_TYPE_DESC);
	$sortable->addSortable($sort1);
	$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);
}
else
{
	$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
}

$pageable = new PicoPageable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new Song(null, $database);
$pageData = $songEntity->findAll($spesification, $pageable, $sortable, true);

$rowData = $pageData->getResult();

if(!empty($rowData))
{
	foreach($rowData $song)
	{
		// do something here
		// $song is instanceof Song class
		// You can use all its features
	}
}
	
```

### Delete

To delete the database record, just invoke the `delete` method of the entity.

**Example 1**

Delete one record without select first

```php
$album1 = new Album(null, $database);
try
{
	$album1->deleteOneByAlbumId("123456");
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 2**

Delete multiple records without select first

```php
$album1 = new Album(null, $database);
try
{
	$deleted = $album1->deleteByAdminCreate("123456");
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 3**

Delete one record with select first

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumId("123456");
	$album1->delete();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 4**

Delete multiple records with select first

```php
$album1 = new Album(null, $database);
try
{
	$pageData = $album1->findByAdminCreate("123456");
	foreach($pageData->getResult() as $album)
	{
		// we can add logic before delete the record
		$album->delete();
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Join the Entity

Users can join entities with other entities. This joining can be done in stages, not limited to just two levels. Please note that using multi-level joins will reduce application performance and waste resource usage. Consider denormalizing the database for applications with large amounts of data.

The following example is a two-level entity join. Users can expand it into three levels, for example by joining the `Album` or `Artist` entity with another new entity.

WARNING:
Don't join entities recursively because it will make the system carry out an endless process.

**EntitySong**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class EntitySong extends MagicObject
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
	 * Random Song ID
	 * 
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Random Song ID")
	 * @var string
	 */
	protected $randomSongId;

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
	 * Album ID
	 * 
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var integer
	 */
	protected $trackNumber;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Composer
	 * 
	 * @JoinColumn(name="artist_composer")
	 * @Label(content="Artist Composer")
	 * @var Artist
	 */
	protected $composer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * Artist Arranger
	 * 
	 * @JoinColumn(name="artist_arranger")
	 * @Label(content="Artist Arranger")
	 * @var Artist
	 */
	protected $arranger;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Type
	 * 
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Type")
	 * @var string
	 */
	protected $fileType;

	/**
	 * File Extension
	 * 
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="File Extension")
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * File Size
	 * 
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @Label(content="File Size")
	 * @var integer
	 */
	protected $fileSize;

	/**
	 * File Md5
	 * 
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @Label(content="File Md5")
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * File Upload Time
	 * 
	 * @Column(name="file_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time")
	 * @var string
	 */
	protected $lastUploadTime;

	/**
	 * File Path Midi
	 * 
	 * @Column(name="file_path_midi", type="text", nullable=true)
	 * @Label(content="File Path Midi")
	 * @var string
	 */
	protected $filePathMidi;

	/**
	 * Last Upload Time Midi
	 * 
	 * @Column(name="last_upload_time_midi", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Midi")
	 * @var string
	 */
	protected $lastUploadTimeMidi;

	/**
	 * File Path Xml
	 * 
	 * @Column(name="file_path_xml", type="text", nullable=true)
	 * @Label(content="File Path Xml")
	 * @var string
	 */
	protected $filePathXml;

	/**
	 * Last Upload Time Xml
	 * 
	 * @Column(name="last_upload_time_xml", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Xml")
	 * @var string
	 */
	protected $lastUploadTimeXml;

	/**
	 * File Path Pdf
	 * 
	 * @Column(name="file_path_pdf", type="text", nullable=true)
	 * @Label(content="File Path Pdf")
	 * @var string
	 */
	protected $filePathPdf;

	/**
	 * Last Upload Time Pdf
	 * 
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Genre ID
	 * 
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Genre ID
	 * 
	 * @JoinColumn(name="genre_id")
	 * @Label(content="Genre ID")
	 * @var Genre
	 */
	protected $genre;

	/**
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var double
	 */
	protected $bpm;

	/**
	 * Time Signature
	 * 
	 * @Column(name="time_signature", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Time Signature")
	 * @var string
	 */
	protected $timeSignature;

	/**
	 * Subtitle
	 * 
	 * @Column(name="subtitle", type="longtext", nullable=true)
	 * @Label(content="Subtitle")
	 * @var string
	 */
	protected $subtitle;

	/**
	 * Subtitle Complete
	 * 
	 * @Column(name="subtitle_complete", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $subtitleComplete;

	/**
	 * Lyric Midi
	 * 
	 * @Column(name="lyric_midi", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi")
	 * @var string
	 */
	protected $lyricMidi;

	/**
	 * Lyric Midi Raw
	 * 
	 * @Column(name="lyric_midi_raw", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi Raw")
	 * @var string
	 */
	protected $lyricMidiRaw;

	/**
	 * Vocal Guide
	 * 
	 * @Column(name="vocal_guide", type="longtext", nullable=true)
	 * @Label(content="Vocal Guide")
	 * @var string
	 */
	protected $vocalGuide;

	/**
	 * Vocal
	 * 
	 * @Column(name="vocal", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $vocal;

	/**
	 * Instrument
	 * 
	 * @Column(name="instrument", type="longtext", nullable=true)
	 * @Label(content="Instrument")
	 * @var string
	 */
	protected $instrument;

	/**
	 * Midi Vocal Channel
	 * 
	 * @Column(name="midi_vocal_channel", type="int(11)", length=11, nullable=true)
	 * @Label(content="Midi Vocal Channel")
	 * @var integer
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var double
	 */
	protected $rating;

	/**
	 * Comment
	 * 
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @Label(content="Comment")
	 * @var string
	 */
	protected $comment;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Last Upload Time Image
	 * 
	 * @Column(name="last_upload_time_image", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

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
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;
}
```

**Album**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
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
```

**Producer**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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

}
```

**Artist**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="artist")
 */
class Artist extends MagicObject
{
	/**
	 * Artist ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="artist_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Artist ID")
	 * @var string
	 */
	protected $artistId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Stage Name
	 * 
	 * @Column(name="stage_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Stage Name")
	 * @var string
	 */
	protected $stageName;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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

}
```

**Genre**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="genre")
 */
class Genre extends MagicObject
{
	/**
	 * Genre ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

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

}
```

To join `song` and `album`, we create property

```php
	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;
```

Because `album_id` is primary key of table `album`, we not need to write reference column name.

To join `song` and `artist`, we create property

```php
	**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

Primary key of table `artist` is `artist_id`, not `artist_vocalist`. We should write `referenceColumnName` in annotation `@JoinColumn`.

```php
	**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist" referenceColumnName="artist_id")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

If entity miss the `referenceColumnName`, MagicObject will search the primary key of table `artist` and will use first primary key. Process will run slower. We are recommended to always write `referenceColumnName` to make it run faster.

```php
	/**
	 * Album
	 * @JoinColumn(name="album_id"  referenceColumnName="artist_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist" referenceColumnName="artist_id")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

### Filter and Order by Join Columns

On real application, user may be filter and order data by column on join table. If the user in the column contains a dot (.) character, then MagicObject will create a select query with a join instead of a regular select query so that filters and orders can work as they should. This way, the process may run slower than with a regular select query.

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
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
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

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
	 * @Label(content="Active")
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
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential);
$database->connect();

$album = new EntityAlbum(null, $database);
try
{
	$spesification = new PicoSpecification();	
	
	$predicate1 = new PicoPredicate();
	// for entity album, just use the colum name
	$predicate1->like('title', '%Album%');
	$spesification->addAnd($predicate1);

	$predicate2 = new PicoPredicate();
	$predicate2->lessThan('producer.birthDay', '2001-01-01');
	$spesification->addAnd($predicate2);

	$predicate3 = new PicoPredicate();
	// type releaseDate instead of release_date
	// because MagicObject use entyty property name, not real table column name 
	$predicate3->greaterThan('releaseDate', '2020-01-01');
	$spesification->addAnd($predicate3);


	$predicate4 = new PicoPredicate();
	$predicate4->equals('active', true);
	$spesification->addAnd($predicate4);
	
	$predicate4 = new PicoPredicate();
	$predicate4->equals('asDraft', false);
	$spesification->addAnd($predicate4);
	
	$sortable = new PicoSortable();
	
	$sortable->addSortable(new PicoSort("producer.birthDay", PicoSort::ORDER_TYPE_ASC));
	$sortable->addSortable(new PicoSort("producer.producerId", PicoSort::ORDER_TYPE_DESC));
	
	
	$pageData = $album->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
	foreach($rowData as $alb)
	{
		//echo $alb."\r\n\r\n";
	}
	
	$pageable = new PicoPageable(new PicoPage(1, 20));
	echo $album->findAllQuery($spesification, $pageable, $sortable, true);
	/**
	 * 	select album.*
		from album
		left join producer producer__jn__1
		on producer__jn__1.producer_id = album.producer_id
		where album.title like '%Album%'
		and producer__jn__1.birth_day < '2001-01-01'
		and album.release_date > '2020-01-01' and album.active = true
		and album.as_draft = false
		order by producer__jn__1.birth_day asc, producer__jn__1.producer_id desc
		limit 0, 20
	 */
	echo "\r\n-----\r\n";
	echo $spesification;
	echo "\r\n-----\r\n";
	echo $sortable;
	echo "\r\n-----\r\n";
	echo $pageable;
}
catch(Exception $e)
{
	echo $e->getMessage();
}
```

```php
	$predicate2 = new PicoPredicate();
	$predicate2->lessThan('producer.birthDay', '2001-01-01');
	$spesification->addAnd($predicate2);
```

`producer` is property of entity that join with other entity, not table name. `birthDay` and `producerId` are is property of entity `producer`, not column name of table `producer`.

### Filter Update


Consider the following case:

We have a query as follows:

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' AND waiting_for = 0;
```

The query above will be executed for each record checked by the user.

With PicoDatabaseQueryBuilder, we can create it easily as follows:

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$query = new PicoDatabaseQueryBuilder($database);
				$query->newQuery()
					->update("album")
					->set("waiting_for = ?, admin_ask_edit = ?, time_ask_edit = ?, ip_ask_edit = ? ", 
						WaitingFor::ACTIVATE, $currentAction->getUserId(), $currentAction->getTime(), $currentAction->getIp())
					->where("album_id = ? and waiting_for = ? ", $rowId, WaitingFor::NOTHING);
				$database->execute($query);
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}					
```

But what about using MagicObject?

Maybe you'll make it like this

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$album->findOneByAlbumIdAndWaitingFor($rowId, WaitingFor::NOTHING);
				$album->setAdminAskEdit($currentAction->getUserId());
				$album->setTimeAskEdit($currentAction->getTime());
				$album->setIpAskEdit($currentAction->getIp());
				$album->setActive(WaitingFor::ACTIVATE)->update();
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}
```

The method above looks very elegant. But have you encountered any problems with the method above?

Yes. By using a query builder, the application only runs one query, for example

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' AND waiting_for = 0;
```

However, by using MagicObject, we actually make two inefficient queries.

```sql
SELECT album.*
WHERE album_id = '1234' AND waiting_for = 0;
```

and

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' ;
```

Of course, if the `Album` entity has joins with other tables, for example `Producer`, `Client` etc., the number of queries that will be executed by the database will be greater and in fact these queries are not needed at all in the application logic.

In large-scale applications, of course this method will cause problems. Imagine if an application interacted with the database 30 to 40 percent more than it should. Of course the user must provide a database server with greater specifications than necessary. This of course will cause unnecessary costs.

MagicObject provides a more efficient way for this case by using the `where` method and specification.

See the following example:

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$album->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->setAlbumId($rowId))
					->addAnd(PicoPredicate::getInstance()->setWaitingFor(WaitingFor::NOTHING))
				)
				->setAdminAskEdit($currentAction->getUserId())
				->setTimeAskEdit($currentAction->getTime())
				->setIpAskEdit($currentAction->getIp())
				->setActive(WaitingFor::ACTIVATE)
				->update();
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}
```

```php
$album
->where(PicoSpecification::getInstance()
->addAnd(PicoPredicate::getInstance()->setAlbumId($rowId))
->addAnd(PicoPredicate::getInstance()->setWaitingFor(WaitingFor::NOTHING))
)
```

will create criteria for the actions to be carried out next. In this case, these actions are

```php
$album
->setAdminAskEdit($currentAction->getUserId())
->setTimeAskEdit($currentAction->getTime())
->setIpAskEdit($currentAction->getIp())
->setActive(WaitingFor::ACTIVATE)
->update();
```

Note that the object returned by the `where` method is instance of `PicoDatabasePersistenceExtended` not instance of `MagicObject`. Of course, we will no longer be able to use the methods in MagicObject. 

### Filter Delete

Just like in the case of updates, deletes with more complicated specifications are also possible using the delete filter. Instead of selecting with specifications and then deleting them, deleting with specifications will be more efficient because the application only performs one query to the database.

```sql
DELETE FROM album
WHERE album_id = '1234' AND waiting_for = 0;
```

```php
$album
->where(PicoSpecification::getInstance()
->addAnd(PicoPredicate::getInstance()->setAlbumId('1234'))
->addAnd(PicoPredicate::getInstance()->setWaitingFor(0))
)
->delete();
```

We'll look at an example of delete with a more complex filter that can't be done with the deleteBy method.

```sql
DELETE FROM album
WHERE album_id = '1234' AND (waiting_for = 0 or waiting_for IS NULL)

```

```php
$specfification = new PicoSpecification();
$specfification->addAnd(new PicoPredicate('albumId', '1234'));
$spec2 = new PicoSpecification();
$predicate1 = new PicoPredicate('waitingFor', 0);
$predicate1 = new PicoPredicate('waitingFor', null);
$spec2->addOr($predicate1);
$spec2->addOr($predicate2);
$specfification->addAnd($spec2);

$album = new Album(null, $database);
$album->where($specfification)->delete();

```

### Subquery

For large data with a very large number of records, using joins, whether inner joins, outer joins, left joins or right joins, will require a lot of resources, which will reduce application and database performance. MagicObject version 1.10 introduces searches using subqueries instead of joins so that the data search process becomes faster.

Using subqueries is not without its drawbacks. The unavoidable disadvantages of subqueries are as follows:

1. just take one column from the reference table
2. Cannot use columns in the reference table either for filter (where) or for sorting (order by).

Users must be aware of these two shortcomings before deciding to use a subquery.

Example:

```php
/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
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
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

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
	 * @Label(content="Active")
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
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$album = new EntityAlbum(null, $database);

$subqueryMap = array(
	'producer'=>array(
		'entityName'=>'Producer',
		'tableName'=>'producer',
		'primaryKey'=>'producer_id',
		'columnName'=>'producer_id',
		'objectName'=>'producer',
		'propertyName'=>'name'
	)
);

$result = $album->findAll(null, null, null, true, $subqueryMap);
	
foreach($result->getResult() as $row)
{
	echo $row;
}
```

### Find All with Option

1. `MagicObject::FIND_OPTION_NO_COUNT_DATA`
2. `MagicObject::FIND_OPTION_NO_FETCH_DATA`

If the `MagicObject::FIND_OPTION_NO_COUNT_DATA` option is provided, the MagicObject will not count data even if a PicoPageable is provided. This will reduce data processing time but the user does not know how much data actually is. This option can be choosed when dealing with very large data.

If the `MagicObject::FIND_OPTION_NO_FETCH_DATA` option is given, MagicObject will not directly fetch data and store it in the object. Users must retrieve data one by one using the fetch method. Every time an application receives data from the database, it sends it directly to either a file or an output buffer instead of collecting it in a list in memory. This option can be choosed when handling very large data so that it does not consume much memory. 

Example:

```php
/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
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
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

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
	 * @Label(content="Active")
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
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$album = new EntityAlbum(null, $database);

$subqueryMap = array(
	'producer'=>array(
		'entityName'=>'Producer',
		'tableName'=>'producer',
		'primaryKey'=>'producer_id',
		'columnName'=>'producer_id',
		'objectName'=>'producer',
		'propertyName'=>'name'
	)
);

$result = $album->findAll(null, null, null, true, null, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);

while($data = $result->fetch())
{
	echo $data;
}
```

### Method

**1. find**

&raquo; search data from database by primary key value given and return one record. This method require database connection.

Method type: native

Parameters:

- mixed $parameters

Parameters can be strings, integers, floats, booleans, or arrays of strings, integers, floats, and booleans. Parameters cannot be null. If the parameter is an array, then the order of the values ​​in the parameter must be the same as the order of the primary key in the entity and the number of elements must be the same as the number of primary keys in the entity. If the number of array elements in the parameter is more than the number of primary keys, then the elements behind will be ignored. If the number of array elements in the parameter is less than the number of primary keys, an exception will be thrown. The data type provided must match the primaty keys.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456';
	$album->find('123456');
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**2. findAll**

&raquo; search multiple record data from database with specification, pagable and sortable. This method require database connection.

Method type: native

Parameters:

- PicoSpecification $specification Specification
- PicoPageable $pageable Pageable
- PicoSortable $sortable Sortable
- boolean $passive Flag that object is passive
- array $subqueryMap Subquery map
- integer $findOption Find option

Return: PicoPageable

**Example**

See Specification, Pageable and Sortable

**3. findOneBy**

&raquo; search data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->findOneByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**4. findOneIfExistsBy**

&raquo; search data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->findOneIfExistsByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**5. deleteOneBy**

&raquo; delete data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// DELETE FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->deleteOneByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**6.findFirstBy**

&raquo; search data from database by any column values and return first record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC LIMIT 1 OFFSET 0;
	// albumId is string
	// active is boolean
	$album->findFirstByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**7. findFirstIfExistsBy**

&raquo; search data from database by any column values and return first record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC LIMIT 1 OFFSET 0;
	// albumId is string
	// active is boolean
	$album->findFirstIfExistsByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**8. findLastBy**

&raquo; search data from database by any column values and return last record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC LIMIT 1 OFFSET 0;
	$album->findLastByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**9. findLastIfExistsBy**

&raquo; search data from database by any column values and return last record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC LIMIT 1 OFFSET 0;
	$album->findLastIfExistsByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**10. findBy**

&raquo; search multiple record data from database by any column values. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true;
	$pageData = $albumFinder->findByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**11. findAscBy**

&raquo; search multiple record data from database order by primary keys ascending. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC;
	$pageData = $albumFinder->findAscByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**12. findDescBy**

&raquo; search multiple record data from database order by primary keys descending. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC;
	$pageData = $albumFinder->findDescByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**13. listBy**

&raquo; search multiple record data from database. Similar to findBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
**14. listAscBy**

&raquo; search multiple record data from database order by primary keys ascending. Similar to findAscBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**15. listDescBy**

&raquo; search multiple record data from database order by primary keys descending. Similar to findDescBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**16. listAllAsc**

&raquo; search multiple record data from database without filter order by primary keys ascending. Similar to findAllAsc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**17. listAllDesc**

&raquo; search multiple record data from database without filter order by primary keys descending. Similar to findAllDesc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**18. countBy**

&raquo; count data from database.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT COUNT(album_id) FROM album WHERE producer_id = '7890123' AND active = true;
	$count = $albumFinder->countByProducerIdAndActive('7890123', true);
	
	// $count is an integer value
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**19. existsBy**

&raquo; check data from database. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT COUNT(album_id) FROM album WHERE producer_id = '7890123' AND active = true;
	$exists = $albumFinder->existsByProducerIdAndActive('7890123', true);
	// $exists is a boolean value
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**20. deleteBy**

&raquo; delete data from database without read it first. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// DELETE FROM album WHERE producer_id = '7890123' AND active = true;
	$albumFinder->deleteByProducerIdAndActive('7890123', true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```
