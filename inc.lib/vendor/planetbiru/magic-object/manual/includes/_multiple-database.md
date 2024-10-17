## Multiple Database Connections

MagicObject version 2 introduces support for multiple database connections, enabling users to manage entities stored across different databases seamlessly. When performing operations such as JOINs with entities from multiple databases, it is essential to define a database connection for each entity involved.

### Example Scenario

In this example, we will illustrate how to manage entities stored in different databases:

- **Album**, **Producer** and **Song** entities are stored in **Database 1**.
- **Artist** entity is stored in **Database 2**.

### Entity Definitions

Here are the definitions for the Album, Artist, and Song classes:

**Album Class**

```php
class Album
{
    /**
     * @Id
     * @Column(name="album_id")
     * @var string
     */
    private $albumId;
    
    /**
     * @Column(name="name")
     * @var string
     */
    private $name;
    
    // add property and annotation here
}
```

**Artist Class**

```php
class Artist
{
    /**
     * @Id
     * @Column(name="artist_id")
     * @var string
     */
    private $artistId;
    
    /**
     * @Column(name="name")
     * @var string
     */
    private $name;

    // add property and annotation here
}
```

**Producer Class**

```php
class Producer
{
    /**
     * @Id
     * @Column(name="producer_id")
     * @var string
     */
    private $producerId;
    
    /**
     * @Column(name="name")
     * @var string
     */
    private $name;

    // add property and annotation here
}
```

**Song Class**

```php
class Song
{
    /**
     * @Id
     * @Column(name="song_id")
     * @var string
     */
    private $songId;
    
    /**
     * @Column(name="name")
     * @var string
     */
    private $name;
    
    /**
     * @Column(name="album_id")
     * @var string
     */
    private $albumId;
    
    /**
     * @JoinColumn(name="album_id")
     * @var Album
     */
    private $album;
    
    /**
     * @Column(name="producer_id")
     * @var string
     */
    private $producerId;
    
    /**
     * @JoinColumn(name="producer_id")
     * @var Producer
     */
    private $producer;
    
    /**
     * @Column(name="artist_id")
     * @var string
     */
    private $artistId;
    
    /**
     * @JoinColumn(name="album_id")
     * @var Artist
     */
    private $artist;
    
    // add property and annotation here
}
```

### Creating Instances

To demonstrate how to create instances of these entities and associate them with their respective databases:

```php
$song = new Song(null, $database1);
$album = new Album(null, $database1);
$producer = new Producer(null, $database1);
$artist = new Artist(null, $database2);
```

### Setting Database Entities

You can set the database entities for Album and Artist associated with a Song instance in several ways:

**Method 1: Chaining Method Calls**

```php
$song->databaseEntity($album)->->databaseEntity($producer)->databaseEntity($artist);
```

**Method 2: Using a DatabaseEntity Instance**

```php
$databaseEntity = new DatabaseEntity();
$databaseEntity->add($album, $database1);
$databaseEntity->add($producer, $database1);
$databaseEntity->add($artist, $database2);
$song->databaseEntity($databaseEntity);
```

**Method 3: Automatic Database Association**

```php
$databaseEntity = new DatabaseEntity();
$databaseEntity->add($album); // Automatically uses $database1 for $album
$databaseEntity->add($producer); // Automatically uses $database1 for $producer
$databaseEntity->add($artist); // Automatically uses $database2 for $artist
$song->databaseEntity($databaseEntity);
```

Since `$album` and `$producer` are stored in the same database as `$song`, user can skip to set `DatabaseEntity` for `$album` and `$producer`. MagicObject will use `$database1` as the default database connection. Thus, we can write a shorter code as follows:

```php
$song = new Song(null, $database1);
$artist = new Artist(null, $database2);
```

**Method 1: Chaining Method Calls**

```php
$song->databaseEntity($artist);
```

**Method 2: Using a DatabaseEntity Instance**

```php
$databaseEntity = new DatabaseEntity();
$databaseEntity->add($artist, $database2);
$song->databaseEntity($databaseEntity);
```

**Method 3: Automatic Database Association**

```php
$databaseEntity = new DatabaseEntity();
$databaseEntity->add($artist); // Automatically uses $database2 for $artist
$song->databaseEntity($databaseEntity);
```

### Using the Song Object

Once the entities are set up, you can perform operations on the Song object. For example, to retrieve all songs:

```php
try {
    $pageData = $song->findAll();
} catch (Exception $e) {
    // Handle exception here (e.g., logging or displaying an error message)
}
```

### Conclusion

With MagicObject version 2, managing entities across multiple database connections is straightforward. By defining the correct associations and utilizing the provided methods, users can effectively work with complex data structures that span multiple databases. Make sure to handle exceptions properly to ensure robustness in your application.
