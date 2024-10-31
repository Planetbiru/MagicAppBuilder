
## MagicDto

### Introduction to DTOs

A Data Transfer Object (DTO) is a design pattern used to transfer data between software application subsystems or layers. DTOs encapsulate data, reducing the number of method calls needed to retrieve or send information. JSON (JavaScript Object Notation) has become the standard for data serialization due to its simplicity and ease of integration with various programming languages.

The properties defined in MagicDto adhere strictly to the specifications set forth by the developer, ensuring a well-defined structure. This means that users are prohibited from adding any input or output that falls outside the established DTO framework. As a result, the integrity of the data is maintained, and the application remains predictable and reliable, as any deviation from the predefined structure is not permitted. This strict adherence to the DTO structure promotes clarity and consistency, facilitating better communication between different layers of the application while reducing the risk of errors or unintended behavior.

### The Need for MagicDto

In modern applications, especially those that interact with third-party services, maintaining consistent data formats can be challenging. Different systems may use varying naming conventions, such as camel case (`myProperty`) and snake case (`my_property`). Additionally, inconsistencies can occur with uppercase and lowercase letters, leading to potential mismatches when exchanging data.

**MagicDto** addresses these issues by allowing developers to create DTOs that seamlessly translate property names between different naming conventions. This ensures that data is properly formatted for both internal and external use, enhancing interoperability and reducing errors.

### Features of MagicDto

1.  **Flexible Naming Strategies**:
    
    -   MagicDto supports both camel case and snake case naming strategies. This flexibility is particularly useful when integrating with diverse APIs or legacy systems that may employ different conventions.

2.  **Automatic Property Mapping**:
    
    -   Users can define DTOs that automatically map properties from their internal representation to the expected format of third-party services. This reduces boilerplate code and simplifies maintenance.
 
3.  **Annotations for Clarity**:
    
    -   The MagicDto class utilizes PHP annotations to clarify the purpose of each property. These annotations enhance code readability and provide useful metadata for serialization.

4. **XML Support**
    -   MagicDto provides support for both XML input and output. This feature allows seamless integration with systems that utilize XML as their primary data format, making it easier to work with various data sources and services.

    To parse XML string, use method `MagicTdo::xmlToObject(string $xmlString)`. This method takes an XML string as input and returning it as a stdClass object.

### Class Structure

The `MagicDto` class is designed with properties that have protected access levels, ensuring encapsulation while still allowing derived classes to access these properties. Each property is annotated with `@var`, which specifies its data type. This structured approach enhances type safety and improves code quality.

#### Key Annotations

**Class Annotations**

1.  **@JSON**
    
    The `@JSON` annotation controls whether the JSON format should be prettified. Using `@JSON(prettify=true)` will format the output in a more readable way, while `@JSON(prettify=false)` will minimize the format

2.  **@XML**
    
    The `@XML` annotation controls whether the XML format should be prettified. Using `@XML(prettify=true)` will format the output in a more readable way, while `@XML(prettify=false)` will minimize the format    


**Property Annotations**

1.  **@Source**
    
    The `@Source` annotation indicates the source property that maps to a specific field in the incoming data. If this annotation is omitted, MagicDto will default to using the property name that matches the class property name. This allows for flexibility in cases where the external API may use different naming conventions.

```php
/**
 * @Source("album_name")
 * @var string
 */
protected $title;
```

2.  **@JsonProperty**

    The `@JsonProperty` annotation specifies the output property name when data is serialized to JSON. If this annotation is not provided, MagicDto will serialize the property using its class property name. This ensures that data sent to third-party applications adheres to their expected format.

```php
/**
 * @JsonProperty("album_title")
 * @var string
 */
protected $title;
```

We can put it together

```php
/**
 * @Source("album_name")
 * @JsonProperty("album_title")
 * @var string
 */
protected $title;
```

In this example, `@Source("album_name")` indicates that the incoming data will use `album_name`, while `@JsonProperty("album_title")` specifies that when the data is serialized, it will be output as `album_title`.

To facilitate bidirectional communication, we need two different DTOs. The `@Source` annotation in the first DTO corresponds to the `@JsonProperty` annotation in the second DTO, while the `@JsonProperty` in the first DTO maps to the `@Source` in the second DTO.

3.  **@JsonFormat**

    The @JsonFormat annotation specifies the output date-time format when data is serialized to JSON. The property type must be `DateTime`. It is written as `@JsonFormat(pattern="Y-m-d H:i:s")`. If this annotation is not provided, MagicDto will serialize the property using the default format Y-m-d H:i:s. This ensures that data sent to third-party applications adheres to their expected format.

    Format the date and time according to the conventions used in the PHP programming language. This includes utilizing the built-in date and time functions, which allow for various formatting options to display dates and times in a way that is both readable and compatible with PHP's standards. Ensure that you adhere to formats such as 'Y-m-d H:i:s' for complete timestamps or 'd/m/Y' for more localized representations, depending on the specific requirements of your application.

    MagicDto automatically parses input as both strings and integers. The integer is a unique timestamp, while the string date-time format must be one of the following:
  
    - **'Y-m-d'**,              // ISO 8601: 2024-10-24
    - **'Y-m-d H:i:s'**,        // ISO 8601: 2024-10-24 15:30:00
    - **'Y-m-d\TH:i:s'**,       // ISO 8601: 2024-10-24T15:30:00
    - **'Y-m-d\TH:i:s\Z'**,     // ISO 8601: 2024-10-24T15:30:00Z
    - **'D, d M Y H:i:s O'**,   // RFC 2822: Thu, 24 Oct 2024 15:30:00 +0000
    - **'d/m/Y'**,              // Local format: 24/10/2024
    - **'d F Y'**,              // Format with month name: 24 October 2024
    - **'l, d F Y'**            // Format with day of the week: Thursday, 24 October 2024




**Example:**

DTO on the Input Side

```php
class AlbumDtoInput extends MagicDto
{
    /**
     * @Source("album_id")
     * @JsonProperty("albumId")
     * @var string
     */
    protected $id;

    /**
     * @Source("album_name")
     * @JsonProperty("albumTitle")
     * @var string
     */
    protected $title;

    /**
     * @Source("date_release")
     * @JsonProperty("releaseDate")
     * @JsonFormat(pattern="Y-m-d H:i:s")
     * @var DateTime
     */
    protected $release;

    /**
     * @Source("song")
     * @JsonProperty("numberOfSong")
     * @var string
     */
    protected $songs;
}
```

DTO on the Output Side

```php
class AlbumDtoOutput extends MagicDto
{
    /**
     * @Source("albumId")
     * @JsonProperty("album_id")
     * @var string
     */
    protected $id;

    /**
     * @Source("albumTitle")
     * @JsonProperty("album_name")
     * @var string
     */
    protected $title;

    /**
     * @Source("releaseDate")
     * @JsonProperty("date_release")
     * @var string
     */
    protected $release;

    /**
     * @Source("numberOfSong")
     * @JsonProperty("song")
     * @var string
     */
    protected $songs;
}
```

**Description**

In this example, we have two DTO classes: AlbumDtoInput and AlbumDtoOutput. The AlbumDtoInput class is designed to receive data from external sources, using the @Source annotation to specify the incoming property names and the @JsonProperty annotation to define the corresponding properties in the internal representation.

Conversely, the AlbumDtoOutput class is structured for sending data outwards. Here, the @Source annotation reflects the internal property names, while the @JsonProperty annotation defines the expected property names when the data is serialized for external use. This bidirectional mapping ensures that data flows seamlessly between internal and external systems.

The `@Source` annotation allows a Data Transfer Object (DTO) to inherit properties from an underlying object, enabling seamless data integration across related entities.

### Cross Object Mapping


#### Cross Object Mapping Explanation

1.  **Concept Clarification**:
    
    -   Cross Object Mapping refers to the ability to access and utilize properties from related objects in a hierarchical structure. In your case, the `SongDto` pulls in the agency name associated with the artist of a song.
2.  **DTO Definition**:
    
    -   A DTO is a simple object that carries data between processes. In this context, `SongDto` aggregates data from the `Song`, `Artist`, and `Agency` models without duplicating properties unnecessarily.

For example, we want to directly include properties from the agency within the SongDto.

-   **Song**
    -   **Artist**
        -   **Agency**

When creating a DTO for a `Song`, the user can incorporate properties from the associated `Agency` into the `SongDto`. This is particularly useful for aggregating data from related models without needing to replicate information.

#### Code Implementation

**Song**

```php
class Song extends MagicObject {
    /**
    * Song ID
    * 
    * @Column(name="song_id")
    * @var string
    */
    protected $songId;

    /**
    * Name
    * 
    * @Column(name="name")
    * @var string
    */
    protected $name;

    /**
    * Artist
    * 
    * @JoinColumn(name="artist_id")
    * @var Artist
    */
    protected $artist;
    
    // Additional properties and methods for the Song can be defined here.
}
```

**Artist**

```php
class Artist extends MagicObject {
    /**
    * Artist ID
    * 
    * @Column(name="artist_id")
    * @var string
    */
    protected $artistId;

    /**
    * Name
    * 
    * @Column(name="name")
    * @var string
    */
    protected $name;

    /**
    * Agency
    * 
    * @JoinColumn(name="agency_id")
    * @var Agency
    */
    protected $agency;
    
    // Additional properties and methods for the Artist can be defined here.
}
```

**Agency**

```php
class Agency extends MagicObject {
    /**
    * Agency ID
    * 
    * @Column(name="agency_id")
    * @var string
    */
    protected $agencyId;

    /**
    * Name
    * 
    * @Column(name="name")
    * @var string
    */
    protected $name;
    
    // Additional properties and methods for the Agency can be defined here.
}
```

**SongDto** 

```php
class SongDto extends MagicDto
{
    /**
    * Song ID
    * 
    * @Source("songId")
    * @JsonProperty(name="song_id")
    * @var string
    */
    protected $songId;

    /**
    * Title
    *
    * @Source("title")
    * @JsonProperty("title")
    * @var string
    */
    protected $title;

    /**
    * Artist
    *
    * @Source("artist")
    * @JsonProperty("artist")
    * @var ArtistDto
    */
    protected $artist;

    /**
     * The name of the agency associated with the artist.
     * 
     * This property is sourced from the agency related to the artist of the song.
     * 
     * @Source("artist->agency->name")
     * @JsonProperty("agency_name")
     * @var string
     */
    protected $agencyName;

    // Additional properties and methods for the SongDto can be defined here.
}
```

**ArtistDto** 

```php
class ArtistDto extends MagicDto
{
    /**
    * Artist ID
    * 
    * @Source("artistId")
    * @JsonProperty(name="artist_id")
    * @var string
    */
    protected $artistId;

    /**
    * Name
    *
    * @Source("name")
    * @JsonProperty("name")
    * @var string
    */
    protected $name;

    /**
    * Agency
    *
    * @Source("agency")
    * @JsonProperty("agency")
    * @var AgencyDto
    */
    protected $agency;

    /**
     * The name of the agency associated with the artist.
     * 
     * This property is sourced from the agency related to the artist of the song.
     * 
     * @Source("artist->agency->name")
     * @JsonProperty("agency_name")
     * @var string
     */
    protected $agencyName;

    // Additional properties and methods for the SongDto can be defined here.
}
```

**AgencyDto** 

```php
class AgencyDto extends MagicDto
{
    /**
    * Agency ID
    * 
    * @Source("agencyId")
    * @JsonProperty(name="agency_id")
    * @var string
    */
    protected $agencyId;

    /**
    * Name
    *
    * @Source("name")
    * @JsonProperty("name")
    * @var string
    */
    protected $name;
}

```

**Usage**

1. JSON Format

```php
$song = new Song(null, $database);
$song->find("1234");
$songDto = new SongDto($song);

header("Content-type: application/json");
echo $songDto;
```

2. XML Format

```php
$song = new Song(null, $database);
$song->find("1234");
$songDto = new SongDto($song);

header("Content-type: application/xml");
echo $songDto->toXml("root");
```

3. Parse XML

```php
$albumDto = new AlbumDto();
$obj = $albumDto->xmlToObject($xmlString);
// $obj is stdClass
header("Content-type: application/json");
echo json_encode($obj);
```

3. Load from XML

```php
$albumDto = new AlbumDtoInput();
$albumDto->loadXml($xmlString);
header("Content-type: application/json");
echo $albumDto;
```

`loadXml` method will load data from XML to `AlbumDtoInput`. `AlbumDtoInput` is the inverse of `AlbumDto`, where values of the `@JsonProperty` and `@Source` annotations are swapped. This inversion also applies to the objects contained within it.


#### Explanation

-   **@Source**: This annotation specifies the path to the property within the nested object structure. In this case, `artist->agency->name` indicates that the `agencyName` will pull data from the `name` property of the `Agency` object linked to the `Artist`.
    
-   **@JsonProperty**: This annotation maps the `agencyName` property to a different key in the JSON representation of the DTO. Here, it will be serialized as `agency_name`.
    
-   **protected $agencyName**: This declares the `agencyName` property with protected visibility, ensuring that it can only be accessed within the class itself and by subclasses.

This approach enhances data encapsulation and promotes cleaner code by allowing DTOs to automatically gather necessary data from related entities.

### Benefits of Using MagicDto

-   **Reduced Complexity**: By automating property translation, MagicDto minimizes the need for manual mapping code, reducing complexity and potential errors.
-   **Improved Maintainability**: With clearly defined annotations and a structured approach, developers can easily understand and maintain the DTOs, even as systems evolve.
-   **Enhanced Interoperability**: MagicDto ensures that data exchanged between different systems is consistent and correctly formatted, leading to smoother integrations and fewer runtime issues.

### Conclusion

MagicDto is a powerful solution for managing data transfer in applications that need to communicate with external systems. By leveraging flexible naming strategies and clear annotations, it simplifies the process of creating and maintaining DTOs, ensuring seamless data exchange. Whether you’re building a new application or integrating with legacy systems, MagicDto can help you navigate the complexities of data serialization and improve overall application reliability.
