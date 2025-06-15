## Property Validation

The `ValidationUtil` class has been significantly enhanced to provide a robust and flexible object property validation mechanism. Inspired by Jakarta Bean Validation (JSR 380), developers can now apply a comprehensive set of annotations directly in property docblocks to enforce data integrity.

The following validation annotations are now supported, grouped by their function:

### Presence & Nullability
-   **`@Required(message="...")`**: Ensures the property value is not `null`.
-   **`@NotEmpty(message="...")`**: Checks if a string is not empty (`""`) or an array is not empty.
-   **`@NotBlank(message="...")`**: Validates that a string is not empty and not just whitespace characters.

### Value Range & Size
-   **`@Min(value=X, message="...")`**: Asserts that a numeric property's value is greater than or equal to a minimum value.
-   **`@Max(value=X, message="...")`**: Asserts that a numeric property's value is less than or equal to a maximum value.
-   **`@DecimalMin(value="...", message="...")`**: Validates that a numeric property (can be float/string) is greater than or equal to a specified decimal value.
-   **`@DecimalMax(value="...", message="...")`**: Validates that a numeric property (can be float/string) is less than or equal to a specified decimal value.
-   **`@Range(min=X, max=Y, message="...")`**: Validates that a numeric property's value falls within an inclusive range.
-   **`@Size(min=X, max=Y, message="...")`**: Verifies that the length of a string or the count of an array is within a specified range.
-   **`@Length(min=X, max=Y, message="...")`**: Similar to `@Size`, specifically for string lengths within a range.
-   **`@Digits(integer=X, fraction=Y, message="...")`**: Checks that a numeric property has at most `X` integer digits and `Y` fractional digits.

### Numeric Sign
-   **`@Positive(message="...")`**: Ensures a numeric value is positive (> 0).
-   **`@PositiveOrZero(message="...")`**: Ensures a numeric value is positive or zero (>= 0).
-   **`@Negative(message="...")`**: Ensures a numeric value is negative (< 0).
-   **`@NegativeOrZero(message="...")`**: Ensures a numeric value is negative or zero (<= 0).

### Pattern & Format
-   **`@Pattern(regexp="...", message="...")`**: Validates a string property against a specified regular expression.
-   **`@Email(message="...")`**: Checks if a string property is a well-formed email address.
-   **`@Url(message="...")`**: Ensures a string is a valid URL.
-   **`@Ip(message="...")`**: Ensures a string is a valid IP address.
-   **`@DateFormat(format="...", message="...")`**: Ensures a string matches a specific date format.
-   **`@Phone(message="...")`**: Ensures a string is a valid phone number.
-   **`@NoHtml(message="...")`**: Checks if a string property contains any HTML tags.

### Date & Time
-   **`@Past(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the past.
-   **`@Future(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future.
-   **`@PastOrPresent(message="...")`**: Ensures a date/time is in the past or present.
-   **`@FutureOrPresent(message="...")`**: Ensures a `DateTimeInterface` property represents a date/time in the future or the present.
-   **`@BeforeDate(date="...", message="...")`**: Ensures a date is before a specified date.
-   **`@AfterDate(date="...", message="...")`**: Ensures a date is after a specified date.

### String Content & Structure
-   **`@Alpha(message="...")`**: Ensures a string contains only alphabetic characters.
-   **`@AlphaNumeric(message="...")`**: Ensures a string contains only alphanumeric characters.
-   **`@StartsWith(prefix="...", caseSensitive=true|false, message="...")`**: Ensures a string starts with a specified prefix, with optional case sensitivity.
-   **`@EndsWith(suffix="...", caseSensitive=true|false, message="...")`**: Ensures a string ends with a specified suffix, with optional case sensitivity.
-   **`@Contains(substring="...", caseSensitive=true|false, message="...")`**: Ensures a string contains a specified substring, with optional case sensitivity.

### Boolean
-   **`@AssertTrue(message="...")`**: Asserts that a boolean property's value is strictly `true`.

### Enum & Allowed Values
-   **`@Enum(message="...", allowedValues={...}, caseSensitive=true|false)`**: Ensures a string property's value is one of a predefined set of allowed values, with an option for case-sensitive or case-insensitive comparison.

### Nested Validation
-   **`@Valid`**: Recursively validates nested `MagicObject` instances.

These new validation capabilities provide a declarative and robust way to ensure data consistency and reduce boilerplate validation code in your MagicObject entities.


**Class `UserProfile`**

```php
// Define an example entity class demonstrating various validations
class UserProfile extends MagicObject
{
    /**
     * @Required(message="Username cannot be null")
     * @NotBlank(message="Username cannot be blank")
     * @Length(min=4, max=20, message="Username must be 4-20 characters long")
     * @Pattern(regexp="^[a-zA-Z0-9_]+$", message="Username can only contain letters, numbers, and underscores")
     * @var string
     */
    protected $username;

    /**
     * @Email(message="Invalid email address format")
     * @Required(message="Email cannot be null")
     * @var string
     */
    protected $email;

    /**
     * @Min(value=18, message="Age must be at least 18")
     * @Max(value=99, message="Age cannot exceed 99")
     * @var int
     */
    protected $age;

    /**
     * @NoHtml(message="About Me field contains unsupported HTML tags")
     * @Size(max=500, message="About Me cannot exceed 500 characters")
     * @var string
     */
    protected $aboutMe;

    /**
     * @Past(message="Birth date must be in the past")
     * @var DateTime
     */
    protected $birthDate;

    /**
     * @Enum(message="Gender must be 'Male' or 'Female'", allowedValues={"Male", "Female"})
     * @var string
     */
    protected $gender;

    /**
     * @Enum(message="Status must be 'active', 'inactive', or 'pending'", allowedValues={"active", "inactive", "pending"}, caseSensitive=false)
     * @var string
     */
    protected $status;

    // A nested object to demonstrate @Valid
    /**
     * @Valid
     * @var Address
     */
    protected $address;
```

**Class `Address`**

```php
class Address extends MagicObject
{
    /**
     * @Required(message="Street cannot be null")
     * @NotBlank(message="Street cannot be blank")
     * @var string
     */
    protected $street;

    /**
     * @Required(message="City cannot be null")
     * @NotBlank(message="City cannot be blank")
     * @var string
     */
    protected $city;
}
```

**Validation**

```php
// Test 1: Valid user profile
try {
    $user = new UserProfile();
    $user->setUsername("john_doe");
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe. I like programming.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 1: Valid User Profile - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 1: FAILED (Unexpected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 2: Invalid username (too short)
try {
    $user = new UserProfile();
    $user->setUsername("joh"); // Too short
    $user->setEmail("john.doe@example.com");
    $user->setAge(30);
    $user->setAboutMe("Hello, I am John Doe.");
    $user->setBirthDate(new DateTime('1995-01-15'));
    $user->setGender("Male");
    $user->setStatus("active");
    
    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 2: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 2: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 3: Invalid email format
try {
    $user = new UserProfile();
    $user->setUsername("jane_doe");
    $user->setEmail("invalid-email"); // Invalid format
    $user->setAge(25);
    $user->setAboutMe("Hello, I am Jane Doe.");
    $user->setBirthDate(new DateTime('1999-03-20'));
    $user->setGender("Female");
    $user->setStatus("inactive");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 3: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 3: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 4: Invalid Enum value (case-sensitive)
try {
    $user = new UserProfile();
    $user->setUsername("test_user");
    $user->setEmail("test@example.com");
    $user->setAge(40);
    $user->setAboutMe("Some text.");
    $user->setBirthDate(new DateTime('1980-05-01'));
    $user->setGender("male"); // Should fail due to case-sensitivity (expected "Male")
    $user->setStatus("pending");

    $address = new Address();
    $address->setStreet("123 Main St");
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 4: Valid User Profile (should have failed) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 4: FAILED (Expected) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}

// Test 5: Nested validation failure
try {
    $user = new UserProfile();
    $user->setUsername("test_nest");
    $user->setEmail("nest@example.com");
    $user->setAge(22);
    $user->setAboutMe("Testing nested validation.");
    $user->setBirthDate(new DateTime('2000-01-01'));
    $user->setGender("Female");
    $user->setStatus("active");

    $address = new Address();
    $address->setStreet(""); // Blank street, should fail @NotBlank
    $address->setCity("Anytown");
    $user->setAddress($address);

    ValidationUtil::getInstance()->validate($user);
    echo "Test 5: Valid User Profile (should have failed due to nested object) - PASSED.\n";
} catch (InvalidValueException $e) {
    echo "Test 5: FAILED (Expected due to nested object) - " . $e->getPropertyName() . ": " . $e->getValidationMessage() . "\n";
}
```

MagicObject allows users to validate an entity or object using a **reference class**. This approach is useful for validating form inputs during `INSERT` and `UPDATE` operations directly on the same entity, but when different validation rules are required for each operation.

For example:

**Class `Album`**

```php
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
     * @Column(name="name", type="varchar(50)", length=50, nullable=true)
     * @Label(content="Name")
     * @var string
     */
    protected $name;

    /**
     * Title
     * @Column(name="title", type="text", nullable=true)
     * @Label(content="Title")
     * @var string
     */
    protected $title;

    /**
     * Description
     * @Column(name="description", type="longtext", nullable=true)
     * @Label(content="Description")
     * @var string
     */
    protected $description;

    /**
     * Producer ID
     * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
     * @Label(content="Producer ID")
     * @var string
     */
    protected $producerId;
    
    /**
     * Producer
     * @JoinColumn(name="producer_id", referenceColumnName="producer_id")
     * @Label(content="Producer")
     * @var Producer
     */ 
    protected $producer;

    /**
     * Release Date
     * @Column(name="release_date", type="date", nullable=true)
     * @Label(content="Release Date")
     * @var string
     */
    protected $releaseDate;

    /**
     * Number Of Song
     * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
     * @Label(content="Number Of Song")
     * @var int
     */
    protected $numberOfSong;

    /**
     * Duration
     * @Column(name="duration", type="float", nullable=true)
     * @Label(content="Duration")
     * @var float
     */
    protected $duration;

    /**
     * Image Path
     * @Column(name="image_path", type="text", nullable=true)
     * @Label(content="Image Path")
     * @var string
     */
    protected $imagePath;

    /**
     * Sort Order
     * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
     * @Label(content="Sort Order")
     * @var int
     */
    protected $sortOrder;

    /**
     * Time Create
     * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
     * @Label(content="Time Create")
     * @var string
     */
    protected $timeCreate;

    /**
     * Time Edit
     * @Column(name="time_edit", type="timestamp", length=26, nullable=true)
     * @Label(content="Time Edit")
     * @var string
     */
    protected $timeEdit;

    /**
     * Admin Create
     * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
     * @Label(content="Admin Create")
     * @var string
     */
    protected $adminCreate;

    /**
     * Admin Edit
     * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
     * @Label(content="Admin Edit")
     * @var string
     */
    protected $adminEdit;

    /**
     * IP Create
     * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
     * @Label(content="IP Create")
     * @var string
     */
    protected $ipCreate;

    /**
     * IP Edit
     * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
     * @Label(content="IP Edit")
     * @var string
     */
    protected $ipEdit;

    /**
     * Active
     * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var bool
     */
    protected $active;

    /**
     * As Draft
     * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var bool
     */
    protected $asDraft;

}
```

**Class `Producer`**

```php
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
	 * @var bool
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
	 * @Column(name="image_update", type="timestamp", length=26, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

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
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $active;

}
```

**Class `AlbumValidatorForInsert`**

```php
class AlbumValidatorForInsert extends MagicObject
{
    /**
     * @Required
     * @var string
     */
    protected $name;

    /**
     * @Required
     * @var string
     */
    protected $producerId;
    
    /**
     * @Valid
     * @var ProducerValidator
     */ 
    protected $producer;

    /**
     * @Required
     * @var string
     */
    protected $releaseDate;

    /**
     * @Positive
     * @var int
     */
    protected $numberOfSong;

    /**
     * @Positive
     * @var float
     */
    protected $duration;

    /**
     * @Positive
     * @var int
     */
    protected $sortOrder;

    /**
     * @Required
     * @var bool
     */
    protected $active;

}
```

**Class `AlbumValidatorForUpdate`**

```php
class AlbumValidatorForUpdate extends MagicObject
{
    /**
     * @Required
     * @NotBlank
     * @var string
     */
    protected $albumId;
    
    /**
     * @Required
     * @var string
     */
    protected $name;

    /**
     * @Required
     * @var string
     */
    protected $producerId;
    
    /**
     * @Valid
     * @var ProducerValidator
     */ 
    protected $producer;

    /**
     * @Required
     * @var string
     */
    protected $releaseDate;

    /**
     * @Positive
     * @var int
     */
    protected $numberOfSong;

    /**
     * @Positive
     * @var float
     */
    protected $duration;

    /**
     * @Positive
     * @var int
     */
    protected $sortOrder;

    /**
     * @Required
     * @var bool
     */
    protected $active;

}
```

**Class `ProducerValidator`**

```php
class ProducerValidator extends MagicObject
{
    /**
	 * @Required
     * @NotBlank
	 * @var string
	 */
	protected $producerId;

	/**
	 * @Size(min=2, max=100)
	 * @var string
	 */
	protected $name;

	/**
	 * @Enum(allowedValues={"M", "F"})
	 * @var string
	 */
	protected $gender;

	/**
	 * @Past
	 * @var string
	 */
	protected $birthDay;

	/**
	 * @Pattern(regexp="^(0|\\+62)[0-9]{8,15}$")
	 * @var string
	 */
	protected $phone;

	/**
	 * @Email
	 * @var string
	 */
	protected $email;

	/**
	 * @Required
	 * @var bool
	 */
	protected $active;
}
```

Kelas referensi tidak perlu mengandung semua properti dari input yang akan divalidasi. Cukup property yang akan divalidasi saja yang harus ada pada kelas referensi. Setiap properti boleh mengandung lebih dari satu anotasi validasi.

**Insert Validation**

```php
$album = new Album(null, $database);
$album->setName("New Album");
$album->setTitle("The Journey Begins");
$album->setDescription("This is the debut album of the artist.");
$album->setProducerId("prod-9876");
$album->setReleaseDate("2025-06-07"); // format YYYY-MM-DD
$album->setNumberOfSong(10);
$album->setDuration(42.5); // in minutes, for example
$album->setImagePath("/uploads/albums/123456.jpg");
$album->setSortOrder(1);
$album->setTimeCreate(date("Y-m-d H:i:s")); // usually auto-generated
$album->setAdminCreate("admin001");
$album->setIpCreate($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
$album->setActive(true);
$album->setAsDraft(false);

try
{
    $album->validate(null, null, new AlbumValidatorForInsert());

    // Save to database (e.g. using ORM method `insert()`)
    $album->insert();
}
catch(Exception $e)
{
    error_log($e->getMessage());
}
```

**Insert Validation**

```php
$album = new Album(null, $database);
$album->setAlbumId("123456");
$album->setName("New Album");
$album->setTitle("The Journey Begins");
$album->setDescription("This is the debut album of the artist.");
$album->setProducerId("prod-9876");
$album->setReleaseDate("2025-06-07"); // format YYYY-MM-DD
$album->setNumberOfSong(10);
$album->setDuration(42.5); // in minutes, for example
$album->setImagePath("/uploads/albums/123456.jpg");
$album->setSortOrder(1);
$album->setTimeCreate(date("Y-m-d H:i:s")); // usually auto-generated
$album->setAdminCreate("admin001");
$album->setIpCreate($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
$album->setActive(true);
$album->setAsDraft(false);

try
{
    $album->validate(null, null, new AlbumValidatorForUpdate());

    // Save to database (e.g. using ORM method `update()`)
    $album->update();
}
catch(Exception $e)
{
    error_log($e->getMessage());
}
```

In the example above, `$producer` isn't validated because its value isn't set via input. The value that _is_ validated is `$producerId`, which refers to `producer_id` in the `producer` table. The **`@Valid annotation`** will validate the properties of the `$producer` object (an instance of the `Producer` class) by referencing the `ProducerValidator` class. When either `$album->validate(null, null, new AlbumValidatorForInsert())` or `$album->validate(null, null, new AlbumValidatorForUpdate())` is executed, MagicObject will also validate `$producer`. The `@Valid` annotation is processed first, and if it's found, other validation annotations won't be processed.


The `validate` method has 3 parameters, as follows:
1. string|null $parentPropertyName The name of the parent property, if applicable (for nested validation).
2. array|null $messageTemplate Optional custom message templates for validation errors.
3. MagicObject $reference Optional reference object for validation context.

Users can create their own validation message templates as follows:

```php
$messageTemplate = array(
    'required' => "Field '\${property}' cannot be null",
    'notEmpty' => "Field '\${property}' cannot be empty",
    'notBlank' => "Field '\${property}' cannot be blank",
    'size' => "Field '\${property}' must be between \${min} and \${max} characters",
    'min' => "Field '\${property}' must be at least \${min}",
    'max' => "Field '\${property}' must be less than \${max}",
    'pattern' => "Invalid format for field '\${property}'",
    'email' => "Invalid email address for field '\${property}'",
    'past' => "Date for field '\${property}' must be in the past",
    'future' => "Date for field '\${property}' must be in the future",
    'decimalMin' => "Value for field '\${property}' must be at least \${min}",
    'decimalMax' => "Value for field '\${property}' must be less than \${max}",
    'digits' => "Value for field '\${property}' must have at most \${integer} integer digits and \${fraction} fractional digits",
    'assertTrue' => "Field '\${property}' must be true",
    'futureOrPresent' => "Date for field '\${property}' cannot be in the past",
    'length' => "Field '\${property}' must be between \${min} and \${max} characters",
    'range' => "Value for field '\${property}' must be between \${min} and \${max}",
    'noHtml' => "Field '\${property}' contains HTML tags and must be removed",
    'positive' => "Field '\${property}' must be a positive number",
    'positiveOrZero' => "Field '\${property}' must be zero or a positive number",
    'negative' => "Field '\${property}' must be a negative number",
    'negativeOrZero' => "Field '\${property}' must be zero or a negative number",
    'pastOrPresent' => "Date for field '\${property}' must be in the past or present",
    'url' => "Field '\${property}' must be a valid URL",
    'ip' => "Field '\${property}' must be a valid IP address",
    'dateFormat' => "Field '\${property}' must match the date format '\${format}'",
    'phone' => "Field '\${property}' must be a valid phone number",
    'enum' => "Field '\${property}' has an invalid value. Allowed values: \${allowedValues}.",
    'alpha' => "Field '\${property}' must contain only alphabetic characters",
    'alphaNumeric' => "Field '\${property}' must contain only alphanumeric characters",
    'startsWith' => "Field '\${property}' must start with '\${prefix}'",
    'endsWith' => "Field '\${property}' must end with '\${suffix}'",
    'contains' => "Field '\${property}' must contain '\${substring}'",
    'beforeDate' => "Field '\${property}' must be before '\${date}'",
    'afterDate' => "Field '\${property}' must be after '\${date}'",
);
```

This template can be saved in localization so that it can be translated into various languages. Please note that the %s and %d formatters must match the example above. If the format is wrong, the validation message cannot be generated correctly.