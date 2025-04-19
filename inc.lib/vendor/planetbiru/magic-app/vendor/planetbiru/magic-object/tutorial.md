# Introduction

## What Is MagicObject

MagicObject is a powerful library designed to simplify object management and data handling in PHP applications. It provides a set of features that enhance flexibility, efficiency, and maintainability, making it easier for developers to create and manage complex applications.

In modern application development, managing objects efficiently is paramount. The **MagicObject** library provides an extensive suite of features that simplify object creation, data handling, and database interactions. Below, we’ll explore some of the core functionalities that make MagicObject a powerful tool for developers.

## Key Features

### Dynamic Object Creation

MagicObject allows for **dynamic object creation**, enabling developers to instantiate objects at runtime easily. This flexibility is crucial for applications that require variable object structures based on user input or external data sources.

### Setters and Getters

The library simplifies property management with **automatic setters and getters**. This feature streamlines how properties are accessed and modified, promoting cleaner code and reducing boilerplate.

### Multi-Level Objects

Support for **multi-level objects** enables developers to create nested structures seamlessly. This capability is essential for representing complex data models that mirror real-world relationships.

### Object-Relational Mapping

MagicObject streamlines interactions with databases through **ORM (Object-Relational Mapping)**, making it easier to manage and manipulate data models. This feature is particularly beneficial in applications with complex relationships between entities, as it promotes organized and efficient access.

### Filtering and Pagination

With built-in methods for **filtering and pagination**, developers can manage data display effectively. This feature enhances user experience by allowing the retrieval of specific data sets and controlling how much information is presented at once.

### Native Query

Defining **native queries** within the library increases flexibility and resource efficiency in database access. This feature allows developers to leverage the full power of SQL while benefiting from MagicObject's abstraction layer.

### Multiple Database Connections

MagicObject supports the configuration of **multiple database connections**, enabling applications to interact with different databases simultaneously. This capability is vital for applications that aggregate data from various sources or require connections to multiple environments.

### Database Dumping

Efficiently exporting database contents is made possible through **database dumping** features. This functionality aids in backup processes, data migrations, and reporting.

### Serialization/Deserialization

Handling JSON and YAML formats is straightforward with built-in **serialization and deserialization** methods. This feature is essential for applications that communicate with APIs or require data interchange formats.

### Data Importing

MagicObject provides robust support for **data importing**, allowing users to bring data into the application even if the source and destination schemas differ. This flexibility is crucial in data migration and integration scenarios.

### File Reading

The library supports reading configuration files in various formats, including **INI, YAML, and JSON**. This feature simplifies application configuration management, making it easier to maintain different environments.

### Environment Variable Access

MagicObject enables easy fetching of **environment variable values**, promoting better configuration management and enhancing security by separating sensitive information from code.

### Configuration Encryption

To secure application settings, MagicObject offers **configuration encryption** features. This ensures that sensitive data, such as API keys and database credentials, are protected from unauthorized access.

### HTTP Data Handling

Creating objects from global request variables (e.g., POST, GET) is simplified with built-in **HTTP data handling** capabilities. This feature enhances the ease of integrating with web forms and APIs.

### Session Management

Integrating with **PHP sessions** allows MagicObject to maintain state effectively across user interactions. This functionality is crucial for applications that rely on user sessions for personalized experiences.

### Object Labeling

**Object labeling** refers to the metadata or description of an entity's properties, making it easier to identify and manage them. For example, an entity like "Client" might have properties such as **Name**, **Address**, and **Phone**. The labels here would be:

-   **Name** is the label for the "name" field,
-   **Address** is the label for the "address" field,
-   **Phone** is the label for the "phone" field.

These labels can **easily be modified** to meet specific needs without changing the field names themselves. For example, you could display "Full Name" instead of "Name" or "Contact Number" instead of "Phone" without altering the underlying database schema or object structure.

### Multi-Language Support

Facilitating **localization** is a key feature of MagicObject, allowing developers to create applications that can serve multiple languages and regions. This capability is essential for reaching a global audience.

### File Uploads

Handling file uploads efficiently is another strength of MagicObject. The library provides tools to manage file uploads securely and effectively, making it easier to incorporate user-generated content.

### Annotations

Adding **metadata to objects** through annotations allows for better structure and organization within the code. This feature can be leveraged for documentation, validation, and configuration purposes.

### Debugging

Finally, MagicObject includes comprehensive **debugging tools** to inspect and analyze objects during development. This feature is invaluable for troubleshooting and ensuring that applications run smoothly.

# MagicObject Installation

## Installing MagicObject

To install **MagicObbject**

```
composer require planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar require planetbiru/magic-object
```

## Removing MagicObject

To remove **MagicObbject**

```
composer remove planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar remove planetbiru/magic-object
```

## Installing Composer

To install composer on your PC or download latest composer.phar, click https://getcomposer.org/download/ 
# MagicObject Implementation
## Simple Object

### Set and Get Properties Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with error_log
error_log($someObject);
```

### Unset Properties

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
echo "Phone     : " . $someObject->getPhone() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

### Check if Properties has Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
if($someObject->hasValuePhone())
{
    echo "Phone     : " . $someObject->getPhone() . "\r\n";
}
else
{
    echo "Phone value is not set\r\n";
}
// another way
if($someObject->issetPhone())
{
    echo "Phone     : " . $someObject->getPhone() . "\r\n";
}
else
{
    echo "Phone value is not set\r\n";
}
// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

### Push

Push is present in MagicObject version 1.22. Push is used to add array elements from a MagicObject property. The `push` method basically uses the `array_push` function which is a built-in PHP function.

As with the `set` method, users can use the `push` method in two ways:

1. using a subfix in the form of a property name written in camelcase style with one parameter, namely the value of the element to be added.
2. using two parameters, namely the property name written in camelcase style and the value of the element to be added.

**Warning!**

Be careful when using the `push` method. If a property has a value other than an array and the user calls the `push` method on that property, MagicObject will ignore it and nothing will change the property value..

```
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();

$someObject->pushData("Text 1");
$someObject->pushData("Text 2");
$someObject->pushData(3);
$someObject->pushData(4.0);
$someObject->pushData(true);

/*
or

$someObject->push("data", "Text 1");
$someObject->push("data", "Text 2");
$someObject->push("data", 3);
$someObject->push("data", 4.1);
$someObject->push("data", true);
*/

echo $someObject;
```

Output will be

```json
{"data":["Text 1","Text 2",3,4.1,true]}
```

### Pop

Pop is present in MagicObject version 1.22. Pop is used to remove the last element of an array from a MagicObject property. The `pop` method basically uses the `array_pop` function which is a built-in PHP function.

As with the `unset` method, users can use the `pop` method in two ways:

1. using a subfix in the form of a property name written in camelcase style.
2. using one parameter, namely the property name.

**Warning!**

The `pup` method only applies if the property is a traditional array. It does not apply to scalar, object, and associated array properties.

```
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();

$someObject->setData(["Text 1", "Text 2", 3, 4.1, true]);
echo $someObject."\r\n\r\n";

echo "Pop\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
```

Output will be:

```
{"data":["Text 1","Text 2",3,4.1,true]}

Pop
1
After Pop
{"data":["Text 1","Text 2",3,4.1]}

4.1
After Pop
{"data":["Text 1","Text 2",3]}

3
After Pop
{"data":["Text 1","Text 2"]}
```

`push` and `pop` example:

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";
$someObject = new MagicObject();


$someObject->pushData("Text 1");
$someObject->pushData("Text 2");
$someObject->pushData(3);
$someObject->pushData(4.1);
$someObject->pushData(true);

/*
or

$someObject->push("data", "Text 1");
$someObject->push("data", "Text 2");
$someObject->push("data", 3);
$someObject->push("data", 4.1);
$someObject->push("data", true);
*/


echo "After Push\r\n";

echo $someObject."\r\n\r\n";

echo "Pop\r\n";
echo $someObject->popData()."\r\n";
// or echo $someObject->pop("data")."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
// or echo $someObject->pop("data")."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
// or echo $someObject->pop("data")."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
```

Output will be:

```
After Push
{"data":["Text 1","Text 2",3,4.1,true]}

Pop
1
After Pop
{"data":["Text 1","Text 2",3,4.1]}

4.1
After Pop
{"data":["Text 1","Text 2",3]}

3
After Pop
{"data":["Text 1","Text 2"]}
```

`push` and `pop` only apply to properties that are arrays.

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";
$someObject = new MagicObject();

$someObject->setData(8); // push and pop will not change this
$someObject->pushData("Text 1");
$someObject->pushData("Text 2");
$someObject->pushData(3);
$someObject->pushData(4.1);
$someObject->pushData(true);

echo "After Push\r\n";

echo $someObject."\r\n\r\n";

echo "Pop\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
```

The following are the magic methods of the MagicObject class.

### 1. **hasValue**

-   **Description**: Checks if a property has a value.
    
-   **Example**:

```php
$object->hasValuePropertyName();
```

### 2. **isset**

-   **Description**: Checks if a property is set.
    
-   **Example**:

```php
$object->issetPropertyName();
```

### 3. **is**

-   **Description**: Retrieves the property value as a boolean.
    
-   **Example**:

```php
$isActive = $object->isActive();
```

### 4. **equals**

-   **Description**: Checks if the property value equals the given value.
    
-   **Example**:

```php
$isEqual = $object->equalsPropertyName($value);
```

### 5. **get**

-   **Description**: Retrieves the property value.
    
-   **Example**:

```php
$value = $object->getPropertyName();
```

### 6. **trim**

-   **Description**: Retrieves the property value and trims any leading and trailing whitespace.
    
-   **Example**:

```php
$value = $object->trimPropertyName();
```

### 7. **upper**

-   **Description**: Retrieves the property value and transforms it to uppercase.
    
-   **Example**:

```php
$value = $object->upperPropertyName();
```

### 8. **lower**

-   **Description**: Retrieves the property value and transforms it to lowercase.
    
-   **Example**:

```php
$value = $object->lowerPropertyName();
```

### 9. **set**

-   **Description**: Sets the property value.
    
-   **Example**:

```php
$object->setPropertyName($value);
```

### 10. **unset**

-   **Description**: Unsets the property value.
    
-   **Example**:

```php
$object->unsetPropertyName();
```

### 11. **push**

-   **Description**: Adds array elements to a property at the end.
    
-   **Example**:

```php
$object->pushPropertyName($newElement);
```

### 12. **append**

-   **Description**: Appends array elements to a property at the end.
    
-   **Example**:

```php
$object->appendPropertyName($newElement);
```

### 13. **unshift**

-   **Description**: Adds array elements to a property at the beginning.
    
-   **Example**:

```php
$object->unshiftPropertyName($newElement);
```

### 14. **prepend**

-   **Description**: Prepends array elements to a property at the beginning.
    
-   **Example**:

```php
$object->prependPropertyName($newElement);
```

### 15. **pop**

-   **Description**: Removes the last element from the property.
    
-   **Example**:

```php
$removedElement = $object->popPropertyName();
```

### 16. **shift**

-   **Description**: Removes the first element from the property.
    
-   **Example**:

```php
$removedElement = $object->shiftPropertyName();
```

### 17. **findOneBy**

-   **Description**: Searches for data in the database and returns one record.
    
-   **Example**:

```php
$record = $object->findOneByPropertyName($columnName);
```
**Note**: Requires a database connection.

### 18. **findOneIfExistsBy**

-   **Description**: Searches for data in the database by any column values and returns one record if it exists.
    
-   **Example**:

```php
$record = $object->findOneIfExistsByPropertyName($columnName, $sortable);
```

**Note**: Requires a database connection.

### 19. **deleteOneBy**

-   **Description**: Deletes data from the database by any column values and returns one record.
    
-   **Example**:

```php
$deletedRecord = $object->deleteOneByPropertyName($columnName, $sortable);
```

**Note**: Requires a database connection.

### 20. **findFirstBy**

-   **Description**: Searches for data in the database by any column values and returns the first record.
    
-   **Example**:

```php
$firstRecord = $object->findFirstByColumnName($columnName);
```

**Note**: Requires a database connection.

### 21. **findFirstIfExistsBy**

-   **Description**: Similar to `findFirstBy`, but returns the first record if it exists.
    
-   **Example**:

```php
$firstRecord = $object->findFirstIfExistsByPropertyName($columnName, $sortable);
```

**Note**: Requires a database connection.

### 22. **findLastBy**

-   **Description**: Searches for data in the database by any column values and returns the last record.
    
-   **Example**:

```php
$lastRecord = $object->findLastByColumnName($columnName);
```

**Note**: Requires a database connection.

### 23. **findLastIfExistsBy**

-   **Description**: Similar to `findLastBy`, but returns the last record if it exists.
    
-   **Example**:

```php
$lastRecord = $object->findLastIfExistsByPropertyName($columnName, $sortable);
```

**Note**: Requires a database connection.

### 24. **findBy**

-   **Description**: Searches for multiple records in the database by any column values.
    
-   **Example**:

```php
$records = $object->findByColumnName($columnName);
```

**Note**: Requires a database connection.

### 25. **countBy**

-   **Description**: Counts the data from the database.
    
-   **Example**:

```php
`$count = $object->countByColumnName();`
```

**Note**: Requires a database connection.

### 26. **existsBy**

-   **Description**: Checks for data in the database.
    
-   **Example**:

```php
`$exists = $object->existsByColumn($column);`
```

**Note**: Requires a database connection.

### 27. **deleteBy**

-   **Description**: Deletes data from the database without reading it first.
    
-   **Example**:

```php
$object->deleteByPropertyName($columnName);
```

**Note**: Requires a database connection.

### 28. **booleanToTextBy**

-   **Description**: Converts a boolean value to "yes/no" or "true/false" based on the given parameters.
    
-   **Example**:

```php
$result = $object->booleanToTextByActive("Yes", "No");
```

**Note**: If `$obj->active` is `true`, `$result` will be "Yes", otherwise "No".

### 29. **startsWith**

-   **Description**: Checks if the value starts with a given string.
    
-   **Example**:

```php
$startsWith = $object->startsWithPropertyName("prefix");
```

### 30. **endsWith**

-   **Description**: Checks if the value ends with a given string.
    
-   **Example**:

```php
$endsWith = $object->endsWithPropertyName("suffix");
```

### 31. **label**

-   **Description**: Retrieves the label associated with the given property. If the label is not set, it attempts to fetch it from annotations.
    
-   **Example**:

```php
$label = $object->labelPropertyName();
```

### 32. **option**

-   **Description**: Returns the first parameter if the property is set to `true` or equals `1`; otherwise, returns the second parameter.
    
-   **Example**:

```php
$option = $object->optionPropertyName("Yes", "No");
```

### 33. **notNull**

-   **Description**: Checks if the specified property is set (not null).
    
-   **Example**:

```php
$isNotNull = $object->notNullPropertyName();
```

### 34. **notEmpty**

-   **Description**: Checks if the specified property is set and not empty.
    
-   **Example**:

```php
$isNotEmpty = $object->notEmptyPropertyName();
```

### 35. **notZero**

-   **Description**: Checks if the specified property is set and not equal to zero.
    
-   **Example**:

```php
$isNotZero = $object->notZeroPropertyName();
```

### 36. **notEquals**

-   **Description**: Checks if the specified property is set and does not equal the given value.
    
-   **Example**:

```php
$isNotEqual = $object->notEqualsPropertyName($value);
```

### 37. **mask**

-   **Description**: Masks the value of the property by replacing certain characters with a masking character.
    
-   **Parameters**:
    
    -   `$position` (int): Starting position for the mask (default is 1).
        
    -   `$maskLength` (int): Number of characters to mask (default is 3).
        
    -   `$maskChar` (string): Character used for masking (default is `*`).
        
-   **Example**:

```php
$object->maskPropertyName(1, 3, '*');
```

**Description**: If `maskPropertyName(1, 3, '*')` is called, it masks the first 3 characters of the property value starting from position 1 with `*`.

### 38. **dateFormat**

-   **Description**: Format a date value into a specified format.
    
-   **Example**:

```php
$formattedDate = $object->dateFormatDate("j F Y H:i:s");
```

### 39. **numberFormat**

-   **Description**: Format a number with grouped thousands.
    
-   **Example**:

```php
$numberFormat = $object->numberFormatData(6, ".", ",");
```

### 40. **format**

-   **Description**: Format a date value into a specified format.
    
-   **Example**:

```php
$formattedData = $object->formatData("%7.3f");
```

### 41. **dms**

-   **Description**: Convert decimal to DMS (Degree, Minute, Second) format.
    
-   **Parameters**:
    
    -   `$inSeconds` (bool): Whether to convert in seconds (default is false).
        
    -   `$decimal` (string): Separator (default is ":").
        
    -   `$decimalPlaces` (int): Number of decimal places (default is 0).
        
    -   `$withSign` (bool): Whether to include the sign (default is false).
        
    -   `$zeroPadding` (int): Number of zero padding (default is 0).
        
    -   `$trimDegreeMinute` (bool): Whether to trim degree and minute if they are 0 (default is false).
        
-   **Example**:

```php
$formattedData = $object->dmsData(";");
```

### 42. **retrieve**

-   **Description**: Enables access to deeply nested properties within objects. This feature accepts multiple keys as arguments, allowing efficient traversal of complex nested structures.

-   **Example**:


```php
$object = new  MagicObject();
$yaml = '
  prop1:
    prop2:
      prop3: Test
';
$object->loadYamlString($yaml, false, true, true);
echo  $object->retrieve('prop1', 'prop2', 'prop3');
```

In this example:
-   The method will first check `prop1`, then move to `prop2`, and finally `prop3`.
-   If any of these keys do not exist, it will return `null`.    

### 43. **mergeWith**

-   **Description**: Merges properties from another `MagicObject` instance into the current one. If a property does not exist, it will be added. If it does:
    
    -   When both values are `MagicObject` instances, they will be merged recursively.
        
    -   Otherwise, the existing value will be overwritten.
        
-   **Example**:

```php
$obj1 = new MagicObject();
$obj1->loadYamlString('
user:
  name: ALice
client:
  address: Jakarta
', false, true, true);

$obj2 = new MagicObject();
$obj2->loadYamlString('
user:
  email: alice@example.com
client:
  name: Ana
', false, true, true);

$obj1->mergeWith($obj2);

echo $obj1;
// {"user":{"name":"ALice","email":"alice@example.com"},"client":{"address":"Jakarta","name":"Ana"}}
```


In this example:
-   Properties in `$obj2` are merged into `$obj1`.
-   Nested `MagicObject` instances are merged recursively.    
-   Non-object values with the same key are replaced.

## Extends MagicObject

User can extend `MagicObject` to many classes.

```php
<?php

use MagicObject\MagicObject;

/**
 * Example to extends MagicObject
 * 
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 */
class MyObject extends MagicObject
{
    
}
```

### Class Parameters

**@JSON**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:

1. `propertyNamingStrategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

Default value: `CAMEL_CASE`

2. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default value: `false`

**@Yaml**

`@Yaml` is parameter to inform how the object will be serialized.

Attributes:
1. `propertyNamingStrategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `dumpYaml()` method called.
- `CAMEL_CASE` all properties will be camel case when `dumpYaml()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

Default value: `CAMEL_CASE`

## Multilevel Object

In the first code snippet, you are creating a multilevel object structure to represent a car with its components, specifically the tire and body.

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
$tire = new MagicObject();
$body = new MagicObject();

$tire->setDiameter(12);
$tire->setPressure(60);

$body->setLength(320);
$body->setWidth(160);
$body->Height(140);
$body->setColor("red");

$car->setTire($tire);
$car->setBody($body);

echo $car;

/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

**Explanation**

1. **Creating Objects**: Instances of `MagicObject` are created for the car, tire, and body.
2. **Setting Properties**: You set properties on the tire (diameter and pressure) and the body (length, width, height, and color) using the `set` methods.
3. **Nested Objects**: The tire and body are associated with the car using `setTire()` and `setBody()`.
4. **JSON Output**: When you output `$car`, it returns a JSON representation of the object structure.
5. **Accessing Properties**: You can retrieve the color of the car's body through the method chaining `getBody()->getColor()`.

### Parse Yaml

The second code snippet demonstrates how to load and manipulate structured data from a YAML string.

```php

$song = new MagicObject();
$song->loadYamlString(
"
songId: 1234567890
title: Lagu 0001
duration: 320
album:
  albumId: 234567
  name: Album 0001
genre:
  genreId: 123456
  name: Pop
vocalist:
  vovalistId: 5678
  name: Budi
  agency:
    agencyId: 1234
    name: Agency 0001
    company:
      companyId: 5678
      name: Company 1
      pic:
        - name: Kamshory
          gender: M
        - name: Mas Roy
          gender: M
timeCreate: 2024-03-03 12:12:12
timeEdit: 2024-03-03 13:13:13
",
false, true, true
);

// to get company name
echo $song->getVocalist()->getAgency()->getCompany()->getName();
echo "\r\n";
// add company properties
$song->getVocalist()->getAgency()->getCompany()->setCompanyAddress("Jalan Jendral Sudirman Nomor 1");
// get agency
echo $song->getVocalist()->getAgency();
echo "\r\n";

// please note that $song->getVocalist()->getAgency()->getCompany()->getPic() is an array, not a MagicObject
// to get pic
foreach($song->getVocalist()->getAgency()->getCompany()->getPic() as $pic)
{
	echo $pic;
	echo "\r\n----\r\n";
}
```

**Explanation**

1. **Loading YAML**: The `loadYamlString()` method is used to load a YAML string into the `MagicObject`. This creates a structured object based on the YAML data.
2. **Accessing Nested Data**: You can navigate through the object hierarchy to retrieve values, like getting the company name with `getVocalist()->getAgency()->getCompany()->getName()`.
3. **Adding Properties**: You can also set additional properties, like the company address.
4. **Iterating Over Arrays**: The `pic` property is an array of objects, so you can loop through it to access individual picture details.

### Conclusion

Both examples demonstrate how MagicObject allows you to create complex object structures and manage nested data efficiently. The use of YAML for configuration or data storage makes it easier to define complex structures in a human-readable format. The ability to set and get properties through method chaining enhances the flexibility and readability of your code.
## Object from Yaml

### From Yaml String

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load yaml string
// will not replace value with environment variable
// load as object instead of associated array
$car->loadYamlString("
tire: 
  diameter: 12
  pressure: 60
body: 
  length: 320
  width: 160
  height: 140
  color: red
", false, true, true);

echo $car;
/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

### From Yaml File

```yaml
# config.yml

tire: 
  diameter: 12
  pressure: 60
body: 
  length: 320
  width: 160
  height: 140
  color: red

```

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will not replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", false, true, true);

echo $car;
/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

## Object from INI


When working with configuration files, the INI format is a popular choice due to its simplicity and readability. However, PHP's native INI parsing functions come with significant limitations, particularly concerning reserved words. These restrictions can hinder developers' ability to create flexible and user-defined content in INI files. To address this issue, MagicObject has developed its own custom INI parser, providing greater freedom and functionality.

### The Limitations of Native INI Parsing in PHP

PHP's built-in functions for parsing INI files, such as `parse_ini_file()`, are convenient but have notable drawbacks:

1.  **Reserved Words**: PHP defines certain keywords as reserved words, which cannot be used as keys in INI files. This limitation can frustrate developers who want to use these words in their configuration settings.
    
2.  **Data Types**: The native parser treats all values as strings, making it difficult to handle different data types without additional processing.
    
3.  **Error Handling**: The native functions offer limited error reporting, making it challenging to debug issues in INI files.
    
4.  **Lack of Flexibility**: PHP’s native INI parser does not support advanced features like sections or comments beyond basic syntax, which can be a hindrance for complex configurations.
    

### The MagicObject Solution

To provide developers with a more robust and flexible solution, MagicObject introduces its own custom INI parser. Here are some of the benefits of using MagicObject's INI parser:

**1. Freedom from Reserved Words**

One of the most significant advantages of the custom parser is its ability to handle reserved words. Developers can define keys in their INI files without worrying about conflicts with PHP's reserved keywords, leading to greater flexibility in configuration design.

**2. Enhanced Data Type Support**

MagicObject's INI parser can intelligently handle various data types. This means that values can be parsed as integers, booleans, or arrays, reducing the need for manual type conversion after reading the configuration.

**3. Comprehensive Error Handling**

The custom parser includes robust error handling features, providing detailed feedback when issues arise. This improved debugging capability allows developers to quickly identify and resolve problems in their INI files.

**4. Advanced Features**

The MagicObject INI parser supports advanced features such as:

-   **Nested Sections**: Allowing developers to create hierarchical configurations.
-   **Comments**: Supporting inline comments to enhance readability and maintainability.
-   **Dynamic Keys**: Enabling the creation of dynamic keys that can change based on specific conditions.

**5. Improved Performance**

The custom parser is optimized for performance, ensuring that reading and processing INI files is efficient, even for larger configurations. This enhancement is crucial for applications that rely on quick access to configuration settings.

INI not support multilevel object. If multilevel object needed, use Yaml instead.

### From INI String

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new MagicObject();
$cfg->loadIniString("
app_name = MusicProductionManager
base_song_path = /var/www/songs
", false);

```

### From INI File

Load config from `config.ini` file

```ini
app_name = MusicProductionManager
base_song_path = /var/www/songs
```

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new MagicObject();
$cfg->loadIniFile(__DIR__ . "/config.ini", false);

```
## Environment Variable

Environment variables play a crucial role in modern application development, providing a flexible way to manage configuration settings. Instead of hardcoding values directly into your code, you can utilize environment variables to keep sensitive information secure and maintain a clean separation between your application's logic and its configuration. This approach not only enhances security but also makes your application more portable and easier to manage.


### Benefits of Using Environment Variables

1.  **Security**: Sensitive information such as API keys, database credentials, and configuration settings can be kept out of your codebase, reducing the risk of accidental exposure.
    
2.  **Portability**: Environment variables allow you to run the same code across different environments (development, staging, production) without needing to modify the codebase.
    
3.  **Simplicity**: Managing configurations through environment variables simplifies deployment processes, particularly in cloud environments where configurations can be set per instance.

### Example Configuration Using YAML and Environment Variables

Here's a basic example of how you can define application settings using a YAML file that references environment variables.

**Sample YAML Configuration**

```yaml
# config.yml

tire: 
  diameter: ${TIRE_DIAMETER}
  pressure: ${TIRE_PRESSURE}
body: 
  length: ${BODY_LENGTH}
  width: ${BODY_WIDTH}
  height: ${BODY_HEIGHT}
  color: ${BODY_COLOR}

```

### Setting Environment Variables

Before running your application, ensure that you have set the appropriate environment variables for `TIRE_DIAMETER`, `TIRE_PRESSURE`, `BODY_LENGTH`, `BODY_WIDTH`, `BODY_HEIGHT`, and `BODY_COLOR` based on the operating system you are using.

**Example PHP Code**

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", true, true);

echo $car;

// to get color

echo $car->getBody()->getColor();

```

**Another Sample YAML Configuration**

In addition to the previous example, here's another configuration file that utilizes environment variables for various settings:

### Create Yaml File

```yaml
result_per_page: 20
song_base_url: ${SONG_BASE_URL}
song_base_path: ${SONG_BASE_PATH}
song_draft_base_url: ${SONG_DRAFT_BASE_URL}
song_draft_base_path: ${SONG_DRAFT_BASE_PATH}
proxy_provider: cloudflare
app_name: Music Production Manager
user_image:
  width: 512
  height: 512
album_image:
  width: 512
  height: 512
song_image:
  width: 512
  height: 512
database:
  time_zone_system: Asia/Jakarta
  default_charset: utf8
  driver: ${APP_DATABASE_TYPE}
  host: ${APP_DATABASE_SERVER}
  port: ${APP_DATABASE_PORT}
  username: ${APP_DATABASE_USER}
  password: ${APP_DATABASE_PASSWORD}
  database_name: ${APP_DATABASE_NAME}
  database_schema: public
  time_zone: ${APP_DATABASE_TIME_ZONE}
  salt: ${APP_DATABASE_SALT}
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
vocal_guide_instrument: piano
```

### Setting Up Environment Variables

On Windows, users can directly create environment variables either via the graphical user interface (GUI) or the `setx` command line. PHP can immediately read environment variables after Windows is restarted.

On Linux, users must create a configuration on the Apache server by creating a file with the .conf extension in the `/etc/httpd/conf.d` folder then restart Apache web server.

**Windows**

Setup environtment variable on Windows using command lines.

```bash
SETX SONG_BASE_URL "https://domain.tld/path"
SETX APP_DATABASE_TYPE "mariadb"
SETX APP_DATABASE_SERVER "localhost"
SETX APP_DATABASE_PORT "3306"
SETX APP_DATABASE_USER "user"
SETX APP_DATABASE_PASSWORD "pass"
SETX APP_DATABASE_NAME "music"
SETX APP_DATABASE_TIME_ZONE "Asia/Jakarta"
SETX APP_DATABASE_SALT "GaramDapur"
```

**Linux**

Setup environtment variable on Linux using command lines create new file configuration used by Apache web server and consumed by PHP.

```bash
echo -e '' > /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv SONG_BASE_URL "https://domain.tld/path"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_TYPE "mariadb"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_SERVER "localhost"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_PORT "3306"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_USER "user"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_PASSWORD "pass"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_NAME "music"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_TIME_ZONE "Asia/Jakarta"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_SALT "GaramDapur"' >> /etc/httpd/conf.d/mpm.conf

service httpd restart
```

### Conclusion

Utilizing environment variables for configuration management is an essential practice for modern application development. By separating configuration from code, you can enhance security, portability, and maintainability. MagicObject’s ability to integrate with environment variables through YAML configuration files allows for a flexible and powerful setup that can adapt to various environments seamlessly. This approach empowers developers to build more secure and robust applications while simplifying deployment and management processes.
## Secret Object

### Definition

Secret Objects are very important in applications that use very sensitive and secret configurations. This configuration must be encrypted so that it cannot be seen either when someone tries to open the configuration file, environment variables, or even when the developer accidentally debugs an object related to the database so that the properties of the database object are exposed including the host name, database name, username and even password.


```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var int
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Database schema
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

### Class Parameters

**@JSON**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:

1. `propertyNamingStrategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

2. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default value: `false`

**@Yaml**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:
`propertyNamingStrategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `dumpYaml()` method called.
- `CAMEL_CASE` all properties will be camel case when `dumpYaml()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

### Property Parameters

1. `@EncryptIn` annotation will encrypt the value before it is assigned to the associated property with the `set` method. 
2. `@DecryptIn` annotation will decrypt the value before it is assigned to the associated property with the `set` method. 
3. `@EncryptOut` annotation will encrypt the property when application call `get` method. 
4. `@DecryptOut` annotation will decrypt the property when application call `get` method. 

### Create Secure Config

When creating a secure application configuration, users can simply use the `@EncryptOut` annotation. MagicObject will load the configuration as entered but will encrypt it when dumped to a file. For configurations that will not be encrypted, do not use `@EncryptIn`, `@DecryptIn`, `@EncryptOut`, or `@DecryptOut`. 

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class SecretGenerator extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * Database server host
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $host;

	/**
	 * Database server port
	 * @var int
	 */
	protected $port;

	/**
	 * Database username
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $username;

	/**
	 * Database user password
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databaseName;

	/**
	 * Database schema
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databaseSchema;

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone;
}
```

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml);
$generator = new SecretGenerator($config);

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

Do not use standard encryption keys when creating or using SecretObjects. Always use your own key. The encryption key must be generated using a callback function. Do not enter it as an object property or constant.

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

or you can also call another function. 

```php

function getSecure()
{
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
}

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return getSecure();
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

### Implement Secure Config

Application configuration is usually written in a file or environment variable after being encrypted. This configuration cannot be read by anyone without decrypting it first. MagicObject will retrieve the encrypted value. If a user accidentally dumps an object using `var_dump` or `print_r`, then PHP will only display the encrypted value. When PHP makes a connection to the database using a credential, MagicObject will decrypt it but the value will not be stored in the object's properties.

Thus, to create an application configuration, it is enough to use the `@DecryptOut` annotation. Thus, MagicObject will only decrypt the configuration when it is ready to be used.

**Example 1**

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var int
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

### Multilevel Object Secure

MagicObject also support Multilevel Yaml.

**Example**

```php
<?php

use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret1 extends SecretObject
{
    /**
     * Database
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $session;
}

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret2 extends SecretObject
{
    /**
     * Database
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $session;
}

$secret1 = new ConfigSecret1(null, function(){
    return bin2hex("You secret here");
});

$yaml1 = "
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: Music Production Manager
user_image:
  width: 512
  height: 512
album_image:
  width: 512
  height: 512
song_image:
  width: 512
  height: 512
database:
  time_zone_system: Asia/Jakarta
  default_charset: utf8
  driver: mysql
  host: localhost
  port: 3306
  username: user
  password: pass
  database_name: music
  database_schema: public
  time_zone: Asia/Jakarta
  salt: Asia/Jakarta
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: files
  save_path: /tmp/session
vocal_guide_instrument: piano
";
$secret1->loadYamlString(
$yaml1,
false, true, true
);

echo $secret1->dumpYaml(null, 4);

$yaml2 = "
database:
    time_zone_system: UEMGavyLkN7rFAmoXBsdDKwuGC+zFttpPTAaqeMH0XUEZaAMKbyvykNtfqT+F8FcAbQCUHV66qjYfjArzgrHlA==
    default_charset: dawURojqYqXdvt2YdZ+kWsq47bcA7FKWnEfGHPMxJr3KUyKxAC0VrH8Khfqcm5iIzQFHE/1wQbdgxJNiffkayw==
    driver: Ur4FCOYvXGPpoMpHm3fwhdK5D3SaP0+MGe4IuPAvpzhAvRcjOW7EZe5VvDAf+0CLeus9tCqqE1sTXj/dxfmkaA==
    host: zyfIMxYi/qQAbazR+nOaLnXFUN9qDYiapxlvocKYxkL8uuN6zRrP3Jsj0mlj6UnLOlvhfDgF3Pq0PrP2ZORGNg==
    port: Cs3NTbxIXJ0lf1umQuuDXbPqdBPlVg+jeXi6UqqUUvVUQWgAhbfenfP8g81cONoY2dXof+P1V5Gr/q+iDLNv5Q==
    username: AAAA5NImv2VEL1Wcqx7gi/8f158SYtssj74zTN2fRrIDsGOxsnEa8+50H3Y1MCaJV7SnZo851dnjEhm38Tzjsg==
    password: WWWE/1SK1m9qVjbWnGa6/xa2OWvzXeErRvtQ0RYceGsr3fvAfGfqcDSY6pq8KoXg6MmtJS0FLyjXUq8dftM7yQ==
    database_name: ZQ0PTeDl7AOtjDBV1PHzlwMbPBVZmhXuO65O1pDYYCGQSMNf3GpQJi9SkvGApQ8kBKUgPAmchCTYb8ChL2szrQ==
    database_schema: 628aqGw8hLV8Malwg6jninVfNRgDUgANtXdPfIuz8IohoMQ4NROIuj7Y28/SeLD74NPoiBYFZpaON9+jV8QQug==
    time_zone: zfpl4IxctWh2Y/UnrANjuCKlnevw8MmoSboMtzV3oBzKKN0gg+TT/Zz/QieHkVd+pn7OHx2OXvLadkzzWQTMUQ==
    salt: hdsBgcmvq/rzfNUuhgQiBop4Hp4wqd5w03Il9zaOshK0LgGtaEK1WTcPx5OdL+9VvhmZvs7g/jDGYjYoHwXjIg==
session:
    name: 4u5xAE2K74pUxZyIVsphoYUka3vpnzUx7op6CnELtdWNEepz/jQLaKynItFt5dx6bv7wjBUFL1AaZaA4ypw/CP6xtR5WFQy+RV6V8VqYM8o=
    max_life_time: cspEenRp8+kwUY1RNvmEdWcLqmsRZ+UJZVjY4JwRsuIIfkr+J0w1SYCuRMzMtHG4/hZ5tlXhtGdRZyM7quCbZg==
    save_handler: LIlCCaWHVqg9R4G6ghxDZnuenMLgSI6HjiW+tVGNDa7UyIA7FkFtOgOJtvT/EolUc+kkJSXiMo+76QhvFFq8Dg==
    save_path: CZijzyucTzrj3tZ1M9PbQ6Hky1+4Gz3RnXwZNSe9/SL+9QZdpK4PoW2TSLsuQ+cKBgKgkncd7JXWgA3CFg0f1A==
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: 'Music Production Manager'
user_image:
    width: 512
    height: 512
album_image:
    width: 512
    height: 512
song_image:
    width: 512
    height: 512
vocal_guide_instrument: piano
";

$secret2 = new ConfigSecret2(null, function(){
    return bin2hex("You secret here");
});$secret2->loadYamlString($yaml2, false, true, true);

echo $secret2->dumpYaml(null, 4);
```

### Secure Config from DynamicObject

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\App\ShutdownManager;
use MusicProductionManager\Config\ConfigApp;
use MagicObject\SecretObject;
namespace MagicObject\Database;

require_once __DIR__ . "/vendor/autoload.php";

/**
 * PicoDatabaseCredentials class
 * The SecretObject will encrypt all attributes to prevent unauthorized user read the database configuration
 */
class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var int
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";
	
	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";

	/**
	 * Charset
	 *
	 * @var string
	 */
	protected $charset = "utf8";
}

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());

$database = new PicoDatabase($databaseCredentials, 
    function($sql, $type) // NOSONAR
    {
        // callback when execute query that modify data
    }, 
    function($sql) // NOSONAR
    {
        // callback when execute all query
    }
);

try
{
    $database->connect();
    $shutdownManager = new ShutdownManager($database);
    $shutdownManager->registerShutdown();
}
catch(Exception $e)
{
    // do nothing
}

```
## MagicDto

### Introduction to DTOs

A Data Transfer Object (DTO) is a design pattern used to transfer data between software application subsystems or layers. DTOs encapsulate data, reducing the number of method calls needed to retrieve or send information. JSON (JavaScript Object Notation) has become the standard for data serialization due to its simplicity and ease of integration with various programming languages.

The properties defined in MagicDto adhere strictly to the specifications set forth by the developer, ensuring a well-defined structure. This means that users are prohibited from adding any input or output that falls outside the established DTO framework. As a result, the integrity of the data is maintained, and the application remains predictable and reliable, as any deviation from the predefined structure is not permitted. This strict adherence to the DTO structure promotes clarity and consistency, facilitating better communication between different layers of the application while reducing the risk of errors or unintended behavior.

### The Need for MagicDto

In modern applications, especially those that interact with third-party services, maintaining consistent data formats can be challenging. Different systems may use varying naming conventions, such as camel case (`myProperty`) and snake case (`my_property`). Additionally, inconsistencies can occur with uppercase and lowercase letters, leading to potential mismatches when exchanging data.

**MagicDto** addresses these issues by allowing developers to create DTOs that seamlessly translate property names between different naming conventions. This ensures that data is properly formatted for both internal and external use, enhancing interoperability and reducing errors.

### Benefits of Using MagicDto

- **Reduced Complexity**: By automating property translation, MagicDto minimizes the need for manual mapping code, reducing complexity and potential errors.
- **Improved Maintainability**: With clearly defined annotations and a structured approach, developers can easily understand and maintain the DTOs, even as systems evolve.
- **Enhanced Interoperability**: MagicDto ensures that data exchanged between different systems is consistent and correctly formatted, leading to smoother integrations and fewer runtime issues.

### Features of MagicDto

1. **Flexible Naming Strategies**:

   - MagicDto supports both camel case and snake case naming strategies. This flexibility is particularly useful when integrating with diverse APIs or legacy systems that may employ different conventions.
2. **Automatic Property Mapping**:

   - Users can define DTOs that automatically map properties from their internal representation to the expected format of third-party services. This reduces boilerplate code and simplifies maintenance.
3. **Annotations for Clarity**:

   - The MagicDto class utilizes PHP annotations to clarify the purpose of each property. These annotations enhance code readability and provide useful metadata for serialization.
4. **XML Support**

   - MagicDto provides support for both XML input and output. This feature allows seamless integration with systems that utilize XML as their primary data format, making it easier to work with various data sources and services.

   To parse XML string, use method `MagicTdo::xmlToObject(string $xmlString)`. This method takes an XML string as input and returning it as a stdClass object.

### Class Structure

The `MagicDto` class is designed with properties that have protected access levels, ensuring encapsulation while still allowing derived classes to access these properties. Each property is annotated with `@var`, which specifies its data type. This structured approach enhances type safety and improves code quality.

#### Key Annotations

**Class Annotations**

1. **@JSON**

   The `@JSON` annotation controls whether the JSON format should be prettified. Using `@JSON(prettify=true)` will format the output in a more readable way, while `@JSON(prettify=false)` will minimize the format
2. **@XML**

   The `@XML` annotation controls whether the XML format should be prettified. Using `@XML(prettify=true)` will format the output in a more readable way, while `@XML(prettify=false)` will minimize the format

**Property Annotations**

1. **@Source**

   The `@Source` annotation indicates the source property that maps to a specific field in the incoming data. If this annotation is omitted, MagicDto will default to using the property name that matches the class property name. This allows for flexibility in cases where the external API may use different naming conventions.

```php
/**
 * @Source("album_name")
 * @var string
 */
protected $title;
```

2. **@JsonProperty**

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

3. **@JsonFormat**

   The @JsonFormat annotation specifies the output date-time format when data is serialized to JSON. The property type must be `DateTime`. It is written as `@JsonFormat(pattern="Y-m-d H:i:s")`. If this annotation is not provided, MagicDto will serialize the property using the default format `Y-m-d H:i:s`. This ensures that data sent to third-party applications adheres to their expected format.

   Format the date and time according to the conventions used in the PHP programming language. This includes utilizing the built-in date and time functions, which allow for various formatting options to display dates and times in a way that is both readable and compatible with PHP's standards. Ensure that you adhere to formats such as `Y-m-d H:i:s` for complete timestamps or `d/m/Y` for more localized representations, depending on the specific requirements of your application.

   MagicDto automatically parses input as both strings and integers. The integer is a unique timestamp, while the string date-time format must be one of the following:


#### Date Format Examples

Below are various date format strings that you can use in PHP, along with their descriptions and typical use cases:

-   **`'Y-m-d'`**  
    _Format:_ ISO 8601  
    _Example:_ `2024-10-24`  
    _Description:_ This format represents the date in a standard way that is widely used in APIs and databases. It is particularly useful for storing dates in a consistent format.
    
-   **`'Y-m-d H:i:s'`**  
    _Format:_ ISO 8601 with Time  
    _Example:_ `2024-10-24 15:30:00`  
    _Description:_ This format includes both the date and time, making it suitable for logging events or recording timestamps in applications where precision is required.
    
-   **`'Y-m-d\TH:i:s'`**  
    _Format:_ ISO 8601 with 'T' Separator  
    _Example:_ `2024-10-24T15:30:00`  
    _Description:_ This format uses a 'T' to separate the date and time, conforming to the strict ISO 8601 standard. It is commonly used in JSON data and web APIs.
    
-   **`'Y-m-d\TH:i:s\Z'`**  
    _Format:_ ISO 8601 with UTC Indicator  
    _Example:_ `2024-10-24T15:30:00Z`  
    _Description:_ Similar to the previous format but includes a 'Z' at the end to indicate that the time is in UTC (Coordinated Universal Time). Useful in global applications where time zone context is important.
    
-   **`'D, d M Y H:i:s O'`**  
    _Format:_ RFC 2822  
    _Example:_ `Thu, 24 Oct 2024 15:30:00 +0000`  
    _Description:_ This format is often used in email headers and HTTP headers. It provides a readable representation of the date and time along with the time zone offset.
    
-   **`'d/m/Y'`**  
    _Format:_ Local Format  
    _Example:_ `24/10/2024`  
    _Description:_ A simple date format that is commonly used in various countries. It is easily readable and useful for forms and local applications.
    
-   **`'d F Y'`**  
    _Format:_ Date with Full Month Name  
    _Example:_ `24 October 2024`  
    _Description:_ This format spells out the month, making it very human-readable. Ideal for generating reports or documents where clarity is essential.
    
-   **`'l, d F Y'`**  
    _Format:_ Date with Day of the Week  
    _Example:_ `Thursday, 24 October 2024`  
    _Description:_ This format includes the full name of the day, providing a complete and easily readable date string. Perfect for calendars, invitations, or any context where a formal date presentation is needed.
    
-   **`'U'`**  
    _Format:_ Unix Timestamp  
    _Example:_ `1698151800`  
    _Description:_ This format represents the date as a Unix timestamp, which is the number of seconds that have elapsed since January 1, 1970 (UTC). It is useful for database storage and calculations involving time, as well as for interoperability with systems that utilize Unix timestamps.


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

In this example, we have two DTO classes: `AlbumDtoInput` and `AlbumDtoOutput`. The `AlbumDtoInput` class is designed to receive data from external sources, using the `@Source` annotation to specify the incoming property names and the `@JsonProperty` annotation to define the corresponding properties in the internal representation.

Conversely, the `AlbumDtoOutput` class is structured for sending data outwards. Here, the `@Source` annotation reflects the internal property names, while the `@JsonProperty` annotation defines the expected property names when the data is serialized for external use. This bidirectional mapping ensures that data flows seamlessly between internal and external systems.

The `@Source` annotation allows a Data Transfer Object (DTO) to inherit properties from an underlying object, enabling seamless data integration across related entities.

### Cross Object Mapping

#### Cross Object Mapping Explanation

1. **Concept Clarification**:

   - Cross Object Mapping refers to the ability to access and utilize properties from related objects in a hierarchical structure. In your case, the `SongDto` pulls in the agency name associated with the artist of a song.
2. **DTO Definition**:

   - A DTO is a simple object that carries data between processes. In this context, `SongDto` aggregates data from the `Song`, `Artist`, and `Agency` models without duplicating properties unnecessarily.

For example, we want to directly include properties from the agency within the SongDto.

- **Song**
  - **Artist**
    - **Agency**

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

### Customizing Data Transfer Objects (DTOs) in MagicDto

When creating a Data Transfer Object (DTO) using `MagicDto`, users have the flexibility to define override methods that can manipulate input data before it is loaded into the DTO. This allows for customization and validation of the data being processed.

#### Example of an Override Method

```php
public function onBeforeLoad($data)
{
    // This method can be overridden to manipulate the incoming data
    return $data;
}
```

By overriding the onBeforeLoad method, users can modify the input data directly before it is handled by the DTO. This can be useful for tasks such as data sanitization or transformation.

#### Handling Various Data Types

`MagicDto` is designed to accept parameters of various data types, such as objects, arrays, or scalar values. To prevent errors during operations, it is crucial for users to check the data type before executing any logic on the input data.

#### Example of Validating Input Data

```php
public function onLoadData($data)
{
    // Check if the input data is an instance of EntityAlbum
    if ($data instanceof EntityAlbum) {
        // Modify properties of the EntityAlbum object
        $data->setName("Album Name");
        $data->setProducerName($data->getProducer()->getName());
    }

    // Return the modified data
    return $data;
}
```

In the ``onLoadData` method, we first verify whether the incoming `$data` is an instance of `EntityAlbum`. If it is, we proceed to set specific properties on the `EntityAlbum` object. This ensures that the operations performed are valid and appropriate for the type of data being processed.

#### Example of Throwing Exceptions

```php
public function onBeforeLoad($data)
{
    if ($data instanceof EntityAlbum) {
        throw new InvalidArgumentException("Data must be an EntityAlbum.");
    }

    // Further manipulation of data can occur here
    return $data;
}
```

In this example, if the incoming data is not an `EntityAlbum`, an `InvalidArgumentException` is thrown, preventing further processing of invalid data.

#### Explanation

- **@Source**: This annotation specifies the path to the property within the nested object structure. In this case, `artist->agency->name` indicates that the `agencyName` will pull data from the `name` property of the `Agency` object linked to the `Artist`.
- **@JsonProperty**: This annotation maps the `agencyName` property to a different key in the JSON representation of the DTO. Here, it will be serialized as `agency_name`.

This approach enhances data encapsulation and promotes cleaner code by allowing DTOs to automatically gather necessary data from related entities.

### Conclusion

MagicDto is a powerful solution for managing data transfer in applications that need to communicate with external systems. By leveraging flexible naming strategies and clear annotations, it simplifies the process of creating and maintaining DTOs, ensuring seamless data exchange. Whether you’re building a new application or integrating with legacy systems, MagicDto can help you navigate the complexities of data serialization and improve overall application reliability.

## Input POST/GET/COOKIE/REQUEST/SERVER

In PHP, handling user input can be done through various superglobals, such as `$_POST`, `$_GET`, `$_COOKIE`, `$_REQUEST`, and `$_SERVER`. Each of these superglobals serves a specific purpose for gathering data from different types of requests.

### Input POST

The `$_POST` superglobal is used to collect data sent via HTTP POST requests. In the provided code snippet:

```php

use MagicObject\Request\InputPost;

require_once __DIR__ . "/vendor/autoload.php";

$inputPost = new InputPost();

$name = $inputPost->getRealName();
// equivalen to 
$name = $_POST['real_name'];

```

This snippet utilizes a class called `InputPost` to retrieve the value associated with the key `'real_name'` from the `POST` request.

### Input GET

The `$_GET` superglobal retrieves data sent via URL parameters in a GET request. The corresponding code is:

```php

use MagicObject\Request\InputGet;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName();
// equivalen to 
$name = $_GET['real_name'];

```

Here, the `InputGet` class is used similarly to fetch the `'real_name'` from the `GET` request.

### Input COOKIE

The `$_COOKIE` superglobal accesses cookies sent by the client. The code example is:

```php

use MagicObject\Request\InputCookie;

require_once __DIR__ . "/vendor/autoload.php";

$inputCookie = new InputCookie();

$name = $inputCookie->getRealName();
// equivalen to 
$name = $_COOKIE['real_name'];

```

### Input REQUEST

The `$_REQUEST` superglobal can collect data from both POST and GET requests, as well as cookies. The example is:

```php

use MagicObject\Request\InputRequest;

require_once __DIR__ . "/vendor/autoload.php";

$inputRequest = new InputRequest();

$name = $inputRequest->getRealName();
// equivalen to 
$name = $_REQUEST['real_name'];

```

This allows for a unified approach to retrieve the `'real_name'` regardless of its source.

### Input SERVER

```php

use MagicObject\Request\InputServer;

require_once __DIR__ . "/vendor/autoload.php";

$inputServer = new InputServer();

$remoteAddress = $inputServer->getRemoteAddr();
// equivalen to 
$remoteAddress = $_SERVER_['REMOTE_ADDR'];

```

This retrieves the remote address of the client making the request.

### Filter Input

Filtering user input is crucial for security, as it helps prevent malicious data from affecting your application. The code snippet for filtering input looks like this:

```php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
// equivalen to 
$name =  filter_input(
    INPUT_GET,
    'real_name',
    FILTER_SANITIZE_SPECIAL_CHARS
);


// another way to filter input
// it will update value taken from $_GET['real_name']
$inputGet->filterRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

// so you can use it later
$name = $inputGet->getRealName();

```

In this example, the getRealName method can take a filtering constant to sanitize the input value. The filter `FILTER_SANITIZE_SPECIAL_CHARS` is used to escape special characters, preventing XSS (Cross-Site Scripting) attacks.

List of filter

```
PicoFilterConstant::FILTER_DEFAULT                      = 516;
PicoFilterConstant::FILTER_SANITIZE_NO_DOUBLE_SPACE     = 512;
PicoFilterConstant::FILTER_SANITIZE_PASSWORD            = 511;
PicoFilterConstant::FILTER_SANITIZE_ALPHA               = 510;
PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERIC        = 509;
PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERICPUNC    = 506;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_UINT         = 508;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT          = 519;
PicoFilterConstant::FILTER_SANITIZE_URL                 = 518;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT        = 520;
PicoFilterConstant::FILTER_SANITIZE_STRING_NEW          = 513;
PicoFilterConstant::FILTER_SANITIZE_ENCODED             = 514;
PicoFilterConstant::FILTER_SANITIZE_STRING_INLINE       = 507;
PicoFilterConstant::FILTER_SANITIZE_STRING_BASE64       = 505;
PicoFilterConstant::FILTER_SANITIZE_IP                  = 504;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_OCTAL        = 503;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_HEXADECIMAL  = 502;
PicoFilterConstant::FILTER_SANITIZE_COLOR               = 501;
PicoFilterConstant::FILTER_SANITIZE_POINT               = 500;
PicoFilterConstant::FILTER_SANITIZE_BOOL                = 600;
PicoFilterConstant::FILTER_VALIDATE_URL                 = 273;
PicoFilterConstant::FILTER_VALIDATE_EMAIL               = 274;
PicoFilterConstant::FILTER_SANITIZE_EMAIL               = 517;
PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS       = 515;
PicoFilterConstant::FILTER_SANITIZE_ASCII               = 601;
```


### Conclusion

In summary, handling input in PHP through superglobals is straightforward but requires careful filtering to ensure security. Using classes like `InputPost`, `InputGet`, `InputCookie`, `InputRequest`, and `InputServer` can abstract the underlying superglobal accesses, making the code cleaner and potentially more secure by enforcing consistent input handling and sanitization practices.
## Session

Session variables keep information about one single user, and are available to all pages in one application.

### Session with File

**Yaml File**

```yaml
session:
  name: ${SESSION_NAME}
  max_life_time: ${SESSION_MAX_LIFE_TIME}
  save_handler: ${SESSION_SAVE_HANDLER}
  save_path: ${SESSION_SAVE_PATH}
  cookie_lifetime: ${SESSION_COOKIE_LIFETIME}
  cookie_path: ${SESSION_COOKIE_PATH}
  cookie_secure: ${SESSION_COOKIE_SECURE}
  cookie_httponly: ${SESSION_COOKIE_HTTPONLY}
  cookie_domain: ${SESSION_COOKIE_DOMAIN}
```

### Explanation:
  
-   **name**: Specifies the name of the session. The value `${SESSION_NAME}` refers to an environment variable or a parameter that should be defined elsewhere in the application or server environment. It determines the session's name identifier.
    
-   **max_life_time**: Defines the maximum lifetime of the session in seconds. The value `${SESSION_MAX_LIFE_TIME}` is expected to come from an environment variable or configuration setting, controlling how long the session will last before it expires.
    
-   **save_handler**: Specifies the handler used to save session data. In this case, `${SESSION_SAVE_HANDLER}` likely refers to the session handler, such as `redis`, `files`, or `database`, depending on the server configuration.
    
-   **save_path**: Defines the path where session data will be stored. The value `${SESSION_SAVE_PATH}` is an environment variable or configuration parameter, which could specify a directory or Redis connection string (if `redis` is used as the save handler).
    
-   **cookie_lifetime**: Specifies the lifetime of the session cookie in seconds, which controls how long the cookie will persist in the user's browser. `${SESSION_COOKIE_LIFETIME}` is the environment variable or configuration value that sets this duration.
    
-   **cookie_path**: The path on the server where the session cookie is valid. `${SESSION_COOKIE_PATH}` determines the directory or URL prefix for which the session cookie is set.
    
-   **cookie_secure**: A boolean flag that determines whether the session cookie should be transmitted over HTTPS only. If `${SESSION_COOKIE_SECURE}` is set to `true`, the cookie will only be sent over secure connections.
    
-   **cookie_httponly**: A boolean flag that indicates whether the session cookie is accessible only through the HTTP protocol (i.e., not accessible via JavaScript). If `${SESSION_COOKIE_HTTPONLY}` is `true`, it increases security by preventing client-side access to the cookie.
    
-   **cookie_domain**: Specifies the domain to which the session cookie is valid. `${SESSION_COOKIE_DOMAIN}` can define the domain for which the session cookie should be sent, allowing cross-subdomain access to the session.


**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

### Session with Redis

**Yaml File**

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: tcp://127.0.0.1:6379?auth=myredispass
```

or

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: tcp://127.0.0.1:6379?auth=${REDIS_AUTH}
```

**WARNING!**

You can not encrypt the `${REDIS_AUTH}` value. If you want to secure the config, encrypt entire `save_path` instead.

For example:

```yaml
session:
  name: ${SESSION_NAME}
  max_life_time: ${SESSION_MAX_LIFE_TIME}
  save_handler: ${SESSION_SAVE_HANDLER}
  save_path: ${SESSION_SAVE_PATH}
  cookie_lifetime: ${SESSION_COOKIE_LIFETIME}
  cookie_path: ${SESSION_COOKIE_PATH}
  cookie_secure: ${SESSION_COOKIE_SECURE}
  cookie_httponly: ${SESSION_COOKIE_HTTPONLY}
  cookie_domain: ${SESSION_COOKIE_DOMAIN}
```

`${SESSION_SAVE_PATH}` contains entire of `save_path` that encrypted with you secure key.

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

If you want to encrypt `session.save_path`, you must define your own class.

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

class SessionSecureConfig extends SecretObject
{
  /**
   * Session save path
   * save_path is encrypted and stored in ${SESSION_SAVE_PATH}. To decrypt it, use anotation DecryptOut
   * 
   * @DecryptOut
   * @var string
   */ 
  protected $savePath;
}

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SessionSecureConfig($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

This setup ensures that the session save path is securely managed and decrypted at runtime.

### Conclusion

This implementation provides a robust framework for session management in a PHP application, allowing flexibility in storage options (files or Redis) while emphasizing security through encryption. The use of YAML for configuration keeps the setup clean and easily adjustable. By encapsulating session configuration in dedicated classes, you enhance maintainability and security.
## Database 


### Overview

`PicoDatabase` is a PHP class designed for simplified database interactions using PDO (PHP Data Objects). It provides methods to connect to a database, execute SQL commands, manage transactions, and fetch results in various formats. This manual outlines how to use the class, its features, and provides examples for reference.

### Features

-   **Connection Management**: Establish and manage database connections.
-   **SQL Execution**: Execute various SQL commands such as INSERT, UPDATE, DELETE, and SELECT.
-   **Transaction Handling**: Support for committing and rolling back transactions.
-   **Result Fetching**: Fetch results in different formats (array, object, etc.).
-   **Callbacks**: Support for custom callback functions for query execution and debugging.
-   **Unique ID Generation**: Generate unique identifiers for database records.

### Database Support

MagicObject supports the following databases:

1. **MySQL**
    
    One of the most popular open-source relational databases, known for its speed, reliability, and ease of use. MySQL is widely used in web applications and offers strong performance, security features, and support for SQL standards.

2. **MariaDB**
    
    A fork of MySQL, created by the original developers of MySQL after concerns over Oracle’s acquisition of MySQL. MariaDB is designed to maintain compatibility with MySQL while adding new features and optimizations. It is fully open-source and highly regarded for its performance and stability.

3. **PostgreSQL**
    
    A powerful, open-source relational database system known for its robustness, SQL compliance, and extensive feature set, including ACID compliance, JSON support, and advanced indexing mechanisms.

4. **SQLite**
    
    A lightweight, serverless, self-contained SQL database engine that is highly portable. It is often used for embedded systems or small-scale applications due to its minimal setup and resource usage. Despite its simplicity, SQLite supports a wide range of SQL features and is widely used in mobile apps and other local storage scenarios.

MagicObject’s compatibility with these databases enables flexible, scalable, and efficient data management across different platforms and environments.

### Installation

To use the `PicoDatabase` class, ensure you have PHP with PDO support. Include the class file in your project, and you can instantiate it with your database credentials.

```php
use MagicObject\Database\PicoDatabase;

// Example credentials setup
$credentials = new SecretObject();
$db = new PicoDatabase($credentials);
```

**Credentials**

To create database credentials, please see the `SecretObject` section.

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

/**
 * PicoDatabaseCredentials class
 * 
 * This class encapsulates database credentials and utilizes the SecretObject to encrypt all attributes,
 * ensuring the security of database configuration details from unauthorized access.
 * 
 * It provides getter methods to retrieve database connection parameters such as driver, host, port,
 * username, password, database name, schema, and application time zone.
 * 
 * Example usage:
 * ```php
 * $credentials = new PicoDatabaseCredentials();
 * $credentials->setHost('localhost');
 * $credentials->setUsername('user');
 * $credentials->setPassword('password');
 * ```
 * 
 * The attributes are automatically encrypted when set, providing a secure way to handle sensitive
 * information within your application.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseCredentials extends SecretObject
{
    /**
     * Database driver (e.g., 'mysql', 'pgsql').
     *
     * @var string
     */
    protected $driver = 'mysql';

    /**
     * Database file path for SQLite database only
     *
     * @var string
     */
    protected $databaseFilePath;

    /**
     * Database server host.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $host = 'localhost';

    /**
     * Database server port.
     *
     * @var int
     */
    protected $port = 3306;

    /**
     * Database username.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $username = "";

    /**
     * Database user password.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $password = "";

    /**
     * Database name.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseName = "";

    /**
     * Database schema (default: 'public').
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseSchema = "public"; 

    /**
     * Application time zone.
     *
     * @var string
     */
    protected $timeZone = "Asia/Jakarta";

    /**
     * Charset
     *
     * @var string
     */
    protected $charset = "utf8";
}
```

### Class Methods

#### Constructor

```php
public function __construct($databaseCredentials, $callbackExecuteQuery = null, $callbackDebugQuery = null)
```

**Parameters:**

-   `SecretObject $databaseCredentials`: Database credentials object.
-   `callable|null $callbackExecuteQuery`: Optional callback for executing modifying queries.
    -   If the callback has **3 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being executed.
        -   `$params`: The parameters used in the SQL query.
        -   `$type`: The type of query (e.g., `PicoDatabase::QUERY_INSERT`).
    -   If the callback has **2 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being executed.
        -   `$type`: The type of query.
-   `callable|null $callbackDebugQuery`: Optional callback for debugging queries.
    -   If the callback has **2 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being debugged.
        -   `$params`: The parameters used in the SQL query.
    -   If the callback has **1 parameter**, it will be:
        -   `$sqlQuery`: The SQL query being debugged.

To use setter methods for configuring the `callbackExecuteQuery` and `callbackDebugQuery` in the PicoDatabase class, follow the example below. This allows you to set the callbacks after instantiation

**Setting Callbacks with Setters**

Here's how you can set the callbacks using setter methods:

```php
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;

// Example credentials setup
$credentials = new PicoDatabaseCredentials();
$credentials->setHost('localhost');
$credentials->setUsername('user');
$credentials->setPassword('password');

// Instantiate PicoDatabase
$db = new PicoDatabase($credentials);

// Set callback for executing queries
$db->setCallbackExecuteQuery(function($sqlQuery, $params, $type) {
    echo "Executing query: $sqlQuery\n";
    echo "Parameters: " . json_encode($params) . "\n";
});

// Set callback for debugging queries
$db->setCallbackDebugQuery(function($sqlQuery, $params) {
    echo "Debugging query: $sqlQuery\n";
    echo "Parameters: " . json_encode($params) . "\n";
});

```

**Disabling Callbacks Using Setters**

You can also disable the callbacks by setting them to `null`:

```php
// Instantiate PicoDatabase
$dbWithoutCallbacks = new PicoDatabase($credentials);

// Disable callbacks
$dbWithoutCallbacks->setCallbackExecuteQuery(null);
$dbWithoutCallbacks->setCallbackDebugQuery(null);

```

#### Connecting to the Database

```php
public function connect($withDatabase = true): bool
```

**Parameters**:

-   `bool $withDatabase`: Whether to select the database upon connection.

**Returns**: `true` if connection is successful, `false` otherwise.

#### Disconnecting from the Database

```php
public function disconnect(): self
```

**Returns**: Current instance for method chaining.

#### Query Execution

```php
public function query($sql, $params = null)
```


**Parameters**:
-   `string $sql`: SQL command to be executed.
-   `array|null $params`: Optional parameters for the SQL query.
**Returns**: PDOStatement object or `false` on failure.

#### Fetch a Single Result

```php
public function fetch($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
```

**Parameters**:
-   `string $sql`: SQL command.
-   `int $tentativeType`: Fetch mode (default is `PDO::FETCH_ASSOC`).
-   `mixed $defaultValue`: Default value if no results found.
-   `array|null $params`: Optional parameters.
**Returns**: Fetched result or default value.

#### Fetch All Results

```php
public function fetchAll($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
```

Similar to fetch, but returns all matching results as an array.

### Transaction Management

#### Commit Transaction

```php
public function commit(): bool
```

**Returns:** true if successful.

#### Rollback Transaction

```php
public function rollback(): bool
```

**Returns:** true if successful.

#### Unique ID Generation

```php
public function generateNewId(): string
```

**Returns:** A unique 20-byte ID.

#### Last Inserted ID

```php
public function lastInsertId($name = null): string|false
```

**Parameters:**

- string|null $name: Sequence name (for PostgreSQL).

**Returns:** The last inserted ID or false on error.

### Connection Status

#### Check Connection

```php
public function isConnected(): bool
```

### Example Usage

#### Connecting and Fetching Data

```php
// Instantiate PicoDatabase
$db = new PicoDatabase($credentials);

// Connect to the database
if ($db->connect()) {
    // Fetch a user by ID
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", 1);
    print_r($user);
    
    // Disconnect
    $db->disconnect();
}
```

#### Executing a Transaction

```php
$db->connect();
$db->setAudoCommit(false); // Disable autocommit

try {
    $db->executeInsert("INSERT INTO users (name) VALUES (?)", ['John Doe']);
    $db->commit(); // Commit the transaction
} catch (Exception $e) {
    $db->rollback(); // Rollback on error
}
```

### Conclusion

`PicoDatabase` is a robust class for managing database operations in PHP applications. By following the examples and method descriptions provided in this manual, you can effectively utilize its features for your database interactions. For further assistance, refer to the source code and documentation available at [MagicObject GitHub](https://github.com/Planetbiru/MagicObject).

## MagicObject with PDO

### Overview

With the release of **MagicObject 2.7**, a significant update has been introduced to allow users to leverage **PDO** (PHP Data Objects) for database connections. In previous versions, **MagicObject** required the use of **PicoDatabase**, its custom database handling class. However, recognizing that many developers are accustomed to establishing database connections via traditional PDO, this new version introduces flexibility by allowing PDO connections to be passed directly to the **MagicObject** constructor.

This update aims to bridge the gap between traditional PDO-based database management and the advanced features provided by **MagicObject**, thus enhancing compatibility while retaining all the powerful functionality of the framework.

### Why PDO Support?

The decision to support **PDO** was made to accommodate users who have already established database connections in their applications using PDO, instead of relying on **PicoDatabase** from the start. By supporting PDO, **MagicObject** allows users to continue working with their preferred method of connecting to the database while still benefiting from the full range of features and utilities **MagicObject** offers.

While PDO is now an option for initializing **MagicObject**, it is used only in the constructor. Once the object is initialized, **MagicObject** continues to use **PicoDatabase** for all subsequent database interactions, ensuring that users can still benefit from **PicoDatabase**'s advanced features like automatic query building, database abstraction, and optimized query execution.

### How PDO Support Works

In **MagicObject 2.7**, when you pass a **PDO** connection object to the constructor, it is automatically converted into a **PicoDatabase** instance using the `PicoDatabase::fromPdo()` static method. This ensures that even though PDO is used to establish the initial connection, the object will still operate using **PicoDatabase** for all subsequent database operations. The constructor of **MagicObject** ensures that the database connection is properly initialized and the type of database is correctly detected based on the PDO driver.

### Benefits of PDO Support in MagicObject 2.7

-   **Compatibility**: This change makes **MagicObject** more compatible with existing applications that are already using PDO for database connections. Developers can continue to use PDO for initializing connections while taking advantage of **PicoDatabase**'s advanced database features for the rest of the application.
    
-   **Flexibility**: Developers now have the flexibility to choose between traditional PDO connections and **PicoDatabase**, depending on their needs. This is especially useful for applications transitioning to **MagicObject** but needing to maintain compatibility with existing database handling code.
    
-   **Ease of Transition**: By supporting PDO in the constructor, **MagicObject** makes it easier for developers to gradually adopt its features without the need to refactor existing database handling code.

### Conclusion

Version 2.7 of **MagicObject** introduces an important enhancement by allowing PDO connections to be used alongside **PicoDatabase**. This update provides greater flexibility for developers, allowing them to work with traditional PDO connections if they choose, while still benefiting from the advanced features of **MagicObject** for database interactions. This change aligns with the goal of making **MagicObject** more accessible to a wider range of developers, whether they are just starting with **MagicObject** or are looking to transition from an existing PDO-based application.

## Object-Relational Mapping (ORM)

ORM (Object-Relational Mapping) is a class used to access the database. ORM is derived from MagicObject. Some annotations are required to activate all ORM features.

MagicObject version 2.7 introduces new features for transactional database management, namely `startTransaction()`, `commit()`, and `rollback()`. These functions allow ORMs to directly initiate and manage transactions within their scope. The `startTransaction()` function begins a new transaction, while `commit()` ensures that all changes made during the transaction are permanently saved to the database. On the other hand, `rollback()` can be used to revert any changes made during the transaction in case of an error or interruption. These functions require an active database connection to operate, providing a streamlined way for ORMs to manage data consistency and integrity within their transactions.

This revision aligns with your request, replacing "Entity" with "ORM" and maintaining the context around database management and transactional features.

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
	 * @var int
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var int
	 */
	protected $sortOrder;

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

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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

`@Entity` indicates that the class represents an entity.

**@JSON**

`@JSON` configures how the object will be serialized.

Attributes:

1. `propertyNamingStrategy`

Allowed value:

- `SNAKE_CASE`: All properties will be snake case when `__toString()` method called.
- `CAMEL_CASE`: All properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE`: All properties will be camel case with capitalize first character when `__toString()` method called.

Default: `CAMEL_CASE`

2. `prettify`

Allowed value:

- `true`: JSON string will be prettified
- `false`: JSON string will not be prettified

Default: `false`

**@Table**

`@Table` provides table information.

Attributes:
`name`

`name` The name of the table associated with the entity.

**@Cache**

`@Cache` provides caching configuration.

Attributes:
`enable`

`enable` Option to enable or disable caching.

Allowed value:

- `true`: Cache is enabled
- `false`: Cache is disabled

Default: `false`

**@package**

`@package` specifies the namespace of the class.

PHP does not provide a native method to retrieve a class's namespace. Earlier versions of MagicObject attempted to obtain this information by reading the PHP script, a method that proved both unsafe and inefficient.

With the addition of the `@package` annotation to each entity, MagicObject now offers a safer and more efficient way to associate entities. However, if a `@package` annotation is not available on an entity, version 2.1 will still revert to the old method.

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
- `defaultValue`
- `insertable`
- `updatable`

`name` is column name.

`type` is column type.

`length` is column length if any.

`nullable` indicate that column value can be `null` or not. Available value of `nullable` is `true` and `false`. 

`defaultValue` is default value of the column.

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
    public static function createSongSpecification($inputGet, $additional = null) // NOSONAR
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
    public static function createSongDraftSpecification($inputGet, $additional = null) // NOSONAR
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
	 * @var int
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
	 * @var int
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
	 * @Column(name="file_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_midi", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_xml", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var float
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
	 * @var bool
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
	 * @var bool
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
	 * @var int
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var float
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
	 * @Column(name="last_upload_time_image", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

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
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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
	 * @var int
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var int
	 */
	protected $sortOrder;

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

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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
	 * @var int
	 */
	protected $sortOrder;

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
	 * @var int
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var int
	 */
	protected $sortOrder;

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
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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
	 * @Label(content="Active")
	 * @var bool
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
	 * @var int
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var int
	 */
	protected $sortOrder;

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
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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
	 * @Label(content="Active")
	 * @var bool
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

### Specification

Specifications are implemented in the PicoSpecification and PicoPredicate classes. PicoSpecification is a framework that can contain one or more PicoPredicate.

For example, we have the following query:

```sql
DELETE FROM album
WHERE album_id = '1234' AND (waiting_for = 0 or waiting_for IS NULL)

```

We can write it as follows:

```php
$specfification = new PicoSpecification();
$specfification->addAnd(new PicoPredicate('albumId', '1234'));
$spec2 = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', 0);
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', null);
$spec2->addOr($predicate1);
$spec2->addOr($predicate2);
$specfification->addAnd($spec2);

$album = new Album(null, $database);
$album->where($specfification)->delete();

```

**PicoSpecification**

Method:

1. addAnd

Parameters:

- PicoSpecification|PicoPredicate|array|string

2. addOr

Parameters:

- PicoSpecification|PicoPredicate|array|string

We can form specifications in an unlimited number of stages. Note that users need to simplify the logic before implementing it into the specification.

**PicoPredicate**

Constructor:

Parameters:

- string $field = null
- mixed $value = null

If the field is given a value, the constructor will call the `equals` method with the `field` and `value` from the constructor. This will make it easier for users to create specifications in a line of code.

Methods:

- equals(string $fieldName, mixed $value)
- isNull(string $fieldName)
- notEquals(string $fieldName, mixed $value)
- isNotNull(string $fieldName)
- like(string $fieldName, mixed $value)
- notLike(string $fieldName, mixed $value)
- in(string $fieldName, mixed[] $value)
- notIn(string $fieldName, mixed[] $value)
- lessThan(string $fieldName, string|integer|float $value)
- greaterThan(string $fieldName, string|integer|float $value)
- lessThanOrEquals(string $fieldName, string|integer|float $value)
- greaterThanOrEquals(string $fieldName, string|integer|float $value)
- between(string $fieldName, string|integer|float $min, string|integer|float $max)
- inRange(string $fieldName, mixed[] $value)

Static Methods:
- getInstance()
- generateLikeStarts(string $value)
- generateLikeEnds(string $value)
- generateLikeContains(string $value)
- functionUpper(string $value)
- functionLower(string $value)
- functionAndValue(string $value)

Example:

We want to create a query like this:

`SELECT * FROM album WHERE producer_id = 'asdf'`

Instead of using the `findByProducerId` method, we will use a specification. The advantage of using a specification is that we can easily add parameters to the specification for specific needs.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addAnd($predicate1)
```

**Logic AND**

Since we are only using one predicate, we use the `addAnd` method. We can add predicates to the specification. For example, we will add the following condition:

`SELECT * FROM album WHERE producer_id = 'asdf' AND active = true`

So we can write it as

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addAnd($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->equals('active', true);
$specification->addAnd($predicate2)
```

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->equals('producerId', 'asdf'))
    ->addAnd(PicoPredicate::getInstance()->equals('active', true))
;
```

For predicates with `equals` comparison, we can specify them by passing parameters into the constructor, so we can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(new PicoPredicate('producerId', 'asdf'))
    ->addAnd(new PicoPredicate('active', true))
;
```

And because `addAnd` and `addOr` can accept parameters in the form of `PicoSpecification`, `PicoPredicate` and also `array`, then our code can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['producerId', 'asdf'])
    ->addAnd(['active', true])
;
```

will be:

```sql
SELECT * FROM producer WHERE producer_id = 'asdf' AND active = true
```

When a user passes an array as a parameter to the `addAnd` and `addOr` methods, MagicObject will convert it to an instance of `PicoPredicate` with `equals` comparison. If using `array` is easier, feel free to use it but it is recommended to use `PicoPredicate` so that it can be used directly by MagicObject. If the second parameter is an array, then the comparison logic becomes `in` instead of `equals`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['producerId', ['asdf', 'qwerty']])
    ->addAnd(['active', true])
;
```

will be:

```sql
SELECT * FROM producer WHERE producer_id IN ('asdf', 'qwerty') AND active = true
```

For comparisons other than `equals`, we must specify them explicitly. Here are some examples of specifying AND logic.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->notEquals('producerId', 'asdf');
$specification->addAnd($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->notEquals('active', true);
$specification->addAnd($predicate2)
```

`$predicate1->notEquals('active', true)` does not automatically become `$predicate1->equals('active', false)`. Don't forget about `null` values ​​that may exist in some rows of data.

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->notEquals('producerId', 'asdf'))
    ->addAnd(PicoPredicate::getInstance()->notEquals('active', true))
;
```

Since the comparison used is not `equals`, there is no shorter way to write the code above.

MagicObject will use `is` instead of `=` and `is not` instead of `!=` if the given value is `null`. If the supplied values ​​are a list, use `in` and `notIn` instead of `equals` and `notEquals`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addAnd(PicoPredicate::getInstance()->notEquals('active', null))
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addAnd(PicoPredicate::getInstance()->isNotNull('active'))
;
```

To search for partial text in a row, we can use `like`. In MySQL and MariaDB, `like` comparison is `case insensitive` but in other databases it is `case sensitive`. For that, `like` comparison is usually combined with `upper` or `lower` function to make the comparison `case insensitive`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionAndValue('lower', 'name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like('lower(name)', PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like('lower(name)', '%kamshory%'))
    ->addAnd(['active', true])
;
```

The first writing style is more recommended.

**Logic OR**

The `OR` logic requires at least two criteria. Never use `OR` logic if it only uses one criterion because it will not mean anything. For example, we will add the following condition:

`SELECT * FROM album WHERE producer_id = 'asdf' OR active = true`

So we can write it as

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addOr($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->equals('active', true);
$specification->addOr($predicate2)
```

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->equals('producerId', 'asdf'))
    ->addOr(PicoPredicate::getInstance()->equals('active', true))
;
```

For predicates with `equals` comparison, we can specify them by passing parameters into the constructor, so we can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(new PicoPredicate('producerId', 'asdf'))
    ->addOr(new PicoPredicate('active', true))
;
```

And because `addAnd` and `addOr` can accept parameters in the form of `PicoSpecification`, `PicoPredicate` and also `array`, then our code can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['producerId', 'asdf'])
    ->addOr(['active', true])
;
```

As in `addAnd`, if the second parameter is an array, then the comparison logic becomes `in` instead of `equals`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['producerId', ['asdf', 'qwerty']])
    ->addOr(['active', true])
;
```

For comparisons other than `equals`, we must specify them explicitly. Here are some examples of specifying OR logic.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->notEquals('producerId', 'asdf');
$specification->addOr($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->notEquals('active', true);
$specification->addOr($predicate2)
```

`$predicate1->notEquals('active', true)` does not automatically become `$predicate1->equals('active', false)`. Don't forget about `null` values ​​that may exist in some rows of data.

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->notEquals('producerId', 'asdf'))
    ->addOr(PicoPredicate::getInstance()->notEquals('active', true))
;
```

Since the comparison used is not `equals`, there is no shorter way to write the code above.

MagicObject will use `is` instead of `=` and `is not` instead of `!=` if the given value is `null`. If the supplied values ​​are a list, use `in` and `notIn` instead of `equals` and `notEquals`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addOr(PicoPredicate::getInstance()->notEquals('active', null))
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addOr(PicoPredicate::getInstance()->isNotNull('active'))
;
```

To search for partial text in a row, we can use `like`. In MySQL and MariaDB, `like` comparison is `case insensitive` but in other databases it is `case sensitive`. For that, `like` comparison is usually combined with `upper` or `lower` function to make the comparison `case insensitive`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionAndValue('lower', 'name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like('lower(name)', PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like('lower(name)', '%kamshory%'))
    ->addOr(['active', true])
;
```

The first writing style is more recommended.

**Nested Logic**

In real applications, logic is not always simple. Even very simple applications often have nested and complex logic. Nested logic is `AND` inside `OR` or `OR` inside `AND`.

1. **OR inside AND**

Example:

`SELECT * FROM album WHERE active = true and (lower(name) like '%jakarta%' or lower(title) like '%jakarta%')`

We can create specifications within specifications.

```php
$specification = new PicoSpecification();

// create predicate1
$predicate1 = new PicoPredicate();
$predicate1->equals('active', true);

// create predicate2
$predicate2 = new PicoPredicate();
$predicate2->equals(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta'));

// create predicate3
$predicate3 = new PicoPredicate();
$predicate3->equals(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta'));


// create specification2
$specification2 = new PicoSpecification();

// add predicate2 into specification2 with logic OR
$specification2->addOr($predicate2);

// add predicate3 into specification2 with logic OR
$specification2->addOr($predicate3);

// add predicate1 into specification with logic AND
$specification->addAnd($predicate1)

// add specification2 into specification with logic AND
$specification->addAnd($specification2)
```

A shorter code to create the above specification is as follows

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->equals('active', true))
    ->addAnd(
        PicoSpecification::getInstance()
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta')))
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta')))
    )
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['active', true])
    ->addAnd(
        PicoSpecification::getInstance()
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta')))
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta')))
    )
;
```

2. **AND inside OR**

Example:

`SELECT * FROM album WHERE waiting_for = 0 or (waiting_for is null and approval_id is null)`

We can create specifications within specifications.

```php
$specification = new PicoSpecification();

// create predicate1
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', 0);

// create predicate2
$predicate2 = new PicoPredicate();
$predicate2->equals('waitingFor', null);

// create predicate3
$predicate3 = new PicoPredicate();
$predicate3->equals('approvalId', null);


// create specification2
$specification2 = new PicoSpecification();

// add predicate2 into specification2 with logic AND
$specification2->addAnd($predicate2);

// add predicate3 into specification2 with logic AND
$specification2->addAnd($predicate3);

// add predicate1 into specification with logic OR
$specification->addOr($predicate1)

// add specification2 into specification with logic OR
$specification->addOr($specification2)
```

A shorter code to create the above specification is as follows

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->equals('waitingFor', 0))
    ->addOr(
        PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals('waitingFor', null))
            ->addAnd(PicoPredicate::getInstance()->equals('approvalId', null))
    )
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['waitingFor', 0])
    ->addOr(
        PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals('waitingFor', null))
            ->addAnd(PicoPredicate::getInstance()->equals('approvalId', null))
    )
;
```

MagicObject version 1.20 offers the simplest way to create specifications with `AND` logic and `equal` or `in` comparisons.

For example:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = '::1';
$specs->ipEdit = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

will be:

```sql
select album.* 
from album
where album.name in ('Album 1', 'Album 2') and album.number_of_song = 11
and album.active = true and album.as_draft = false and album.ip_create = '::1'
and album.ip_edit is null
```

Instead of writing very long code to create a specification, users can simply write a few very short lines of code. However, it should be noted that this method only applies to `AND` logic with `equals` and `in` comparisons.

When the user assigns the value of `active` to `true`, then MagicObject will add the predicate `active = true`, likewise when the user assigns the value of `asDraft` to `false`. Since `name` is assigned an array value, the comparison used is `in`. It should be noted that the specification is not an object that stores the given properties as its own properties but rather it will add the predicate each time the predicate is entered.

For example, the code is as follows:

```php

$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = '::1';
$specs->ipCreate = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

You have given the value `$specs->ipCreate = '::1'` and you don't if change that value to `null` for example. So the above code is wrong because it stumbles with the wrong logic i.e. `ip_create = '::1' ,
album.ip_create is null`.

If you mean `ipCreate = '::1' or ipCreate = null`, then you can use the following way:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = ['::1', null];

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

or

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->addAnd(
	PicoSpecification::getInstance()
		->addOr(['ipCreate', '::1'])
		->addOr(['ipCreate', null])
);

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

If you want to set a column from a reference table, you can use the `set` method. Suppose you want to expect MagicObject to make the following query:

```sql
SELECT album.* FROM album
INNER JOIN producer ON producer.producer_id = album.producer_id
WHERE album.active = true AND producer.active = true
```

So you can write

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->active = true;
$specs->set('producer.active', true); // use `set` method instead

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

What if you want to use full `OR` logic instead of `AND` logic? You can use the following way:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->setDefaultLogicOr();

$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

When you set any predicate to a specification, MagicObject will always add the predicate with `OR` logic instead of `AND` logic. Please note that you must call the `setDefaultLogicOr` method before you set a predicate. If you call the `setDefaultLogicOr` method after you set a predicate, you will end up with a logical mess. 

To avoid errors when calling the `setDefaultLogicOr` method, it is recommended to use the following method:

```php
$album = new EntityAlbum(null, $database);

$specs = PicoSpecification::getInstance()->setDefaultLogicOr();

$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->setIpCreate(null);

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

**String-Based Specification**

Since version **3.6**, **MagicObject** has supported specifications in the form of strings, which are used as part of the `WHERE` clause. The introduction of string-based specifications aims to address limitations that cannot be handled using predicates alone. This feature provides users with greater flexibility, allowing them to write `WHERE` clauses directly, tailored to the syntax and capabilities of their specific DBMS.

Example:

```php

$album = new EntityAlbum(null, $database);

// Debug query callback to log the executed SQL
$database->setCallbackDebugQuery(function($sql){
    error_log($sql);
});

// Creating a specification object
$specs = new PicoSpecification();

// Adding conditions using an array
$specs->name = ['Album 1', 'Album 2'];

/*
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
*/

// Adding conditions using string specifications
$specs->addAnd("number_of_song = 11");
$specs->addAnd("active = true");
$specs->addAnd("as_draft = false");

try {
    // Executing the query based on the specifications
    $res = $album->findAll($specs);

    // Displaying the results
    foreach ($res->getResult() as $row) {
        echo $row . "\r\n\r\n";
    }
} catch (Exception $e) {
    error_log($e);
}

```

Important Notes:

-    Clause additions must use the functions `add`, `addAnd`, or `addOr`.
-    Column names must exactly match those in the database without any mapping.
-    Values must be manually escaped to prevent SQL injection.

**Escaping Values in SQL WHERE Clause**

When constructing SQL queries, it is crucial to properly escape values to prevent SQL injection and ensure data integrity. You can use the `bindSqlParams` function from the `PicoDatabaseQueryBuilder` class to safely replace placeholders (`?`) with actual values.

Example usage:

```php
$specs->addAnd((string) (new PicoDatabaseQueryBuilder($database))->bindSqlParams('lyric LIKE ?', "%O'ben%"));
```

If you are using MySQL or MariaDB, you can use PHP's built-in `addslashes` function:

```php
$specs->addAnd('lyric LIKE ?', addslashes("%O'ben%"));
```

For other databases, you can use PHP's built-in `str_replace` function:

```php
$specs->addAnd('lyric LIKE ?', str_replace("'", "''", "%O'ben%"));
```

-   **Creating a `PicoDatabaseQueryBuilder` instance**
    
    -   A new instance of `PicoDatabaseQueryBuilder` is created with the `$database` connection.
-   **Calling `bindSqlParams`**
    
    -   The function replaces the `?` placeholder in `'lyric LIKE ?'` with the provided value `"%O'ben%"`.
    -   This ensures proper escaping of special characters like the single quote (`'`) in `"O'ben"`.
-   **Ensuring Type Safety**
    
    -   The `(string)` cast ensures the output is properly converted to a string before being used in the SQL query.
-   **Using `addAnd` to Append Conditions**
    
    -   The escaped SQL condition is safely added to the filter specifications.

### Pageable and Sortable

In MagicObject, pageable is used to divide data rows into several pages. This is required by the application to display a lot of data per page. While sortable is used to sort data before the data is divided per page.

Pageable can stand alone without sortable. However, this method is not recommended because the data sequence is not as expected. If new data is entered, users will have difficulty finding where it is located in the list and on which page the data will appear. The solution is to add a sortable that will sort the data based on certain columns. For example, the time of data creation is descending, then the new data will be on the first page. Conversely, if sorted based on the time of data creation is ascending, then the new data will be on the last page.

Sortable can use multiple columns. The order in which the columns are determined will determine the priority order in sorting the data.

An example of implementing pageable and sortable in MagicObject.

```php
$pageNumber = 1;
$pageSize = 20;

$albumFinder = new Album(null, $database);
$specification = null;
$sortable = new PicoSortable();
$sort1 = new PicoSort('releaseDate', PicoSort::ORDER_TYPE_DESC);
$sortable->addSortable($sort1);
$page = new PicoPage($pageNumber, $pageSize);
$pageable = new PicoPageable($page);

try
{
    $albumFinder->findAll($specification, $pagable, $sortable);
    $pageData = $albumFinder->getResult();
    foreach($pageData as $album)
    {
        echo $album."\r\n";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}

```

or

```php
$pageNumber = 1;
$pageSize = 20;

$albumFinder = new Album(null, $database);
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('releaseDate', PicoSort::ORDER_TYPE_DESC))
;
$pageable = new PicoPageable(new PicoPage($pageNumber, $pageSize));

try
{
    $albumFinder->findAll(null, $pagable, $sortable);
    $pageData = $albumFinder->getResult();
    foreach($pageData as $album)
    {
        echo $album."\r\n";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}

```

**PicoPageable**

Constructor:

```php
public function __construct(
    PicoPage|PicoLimit|array|null $page = null,
    PicoSortable|array|null $sortable = null
)
{
}
```

Parameters:

- PicoPage|PicoLimit|array|null $page
- PicoSortable|array|null $sortable

Method:

- getSortable
- setSortable
- addSortable
- createOrderBy
- getPage
- setPage
- getOffsetLimit
- setOffsetLimit

**PicoPage**

Constructor:

```php
public function __construct(
    int $pageNumber = 1,
    int $pageSize = 1
)
{
}
```

Parameters:

- integer $pageNumber
- integer $pageSize

Metods:

- getPageNumber
- setPageNumber
- getPageSize
- setPageSize

**PicoLimit**

Constructor:

Parameters:

- integer $offset
- integer $limit

Metods:

- getOffset
- setOffset
- getLimit
- setLimit

**PicoSortable**

Constructor:

```php
public function __construct()
{
}
```

Parameters:

Users can enter an even number of parameters where odd numbered parameters are columns while even numbered parameters are methods.

Example:

```php
$sortable = new PicoSortable("name", "asc", "phone", "desc");
```

Method:

- add
- addSortable
- createSortable
- createOrderBy
- isEmpty
- getSortable

Static methods:

- getInstance

addSortable

Parameters:

- PicoSort|array

**PicoSort**

Constructor:

Parameters:

- string $sortBy
- string $sortType

Metods:

- getSortBy
- setSortBy
- getSortType
- setSortType
- __call

Static methods:

- getInstance
- fixSortType

#### Using String-Based Sorting

Since version **3.6**, **MagicObject** supports **string-based sorting**, allowing users to define custom `ORDER BY` clauses dynamically. This feature provides greater flexibility when ordering query results.


Users can define sorting criteria in the following ways:

**Sortable with String**

```php
$sortable = new PicoSortable();
$sortable->add("LOWER(TRIM(title)) ASC");
```

or

**Sortable with Array**

```php
$sortable = new PicoSortable();
$sortable->add(["LOWER(TRIM(title))", "ASC"], true);
```

#### Understanding the Second Parameter

The second parameter (`true` in this case) **indicates that the first element of the array in the first parameter is a raw SQL expression**. The default value is `false`. When set to `true`, **MagicObject will not modify or escape the value**, allowing full control over custom sorting logic.

#### Security Considerations

When using **string-based sorting**, always consider security risks. Manually escape the parameters you include using the `PicoDatabaseQueryBuilder::bindSqlParams()` function, as shown in the example below:

```php
$inputGet = new InputGet();
$sortType = $inputGet->getOrderBy(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

$queryBuilder = new PicoDatabaseQueryBuilder($database);
$sortBy = $queryBuilder->bindSqlParams('SIN(angle * ?)', $factor);
$sortable->add([$sortBy, $sortType], true);
```

This ensures that the sorting parameters are properly sanitized and protected against SQL injection.

#### Pageable with Sortable and without Sortable

**Pageable without Sortable**

```php
$pageable = new PicoPageable(new Page(1, 100));
// page number = 1
// page size = 100
// no sortable
```

**Pageable with Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

**With Limit and Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = new PicoSortable(
    'userName', 'asc',
    'email', 'desc',
    'phone', 'asc'
);

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(PicoSort::getInstance()->sortByUserName(PicoSort::ORDER_TYPE_ASC))
    ->add(PicoSort::getInstance()->sortByEmail(PicoSort::ORDER_TYPE_DESC))
    ->add(PicoSort::getInstance()->sortByPhone(PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

1. Construtor with page as PicoPageable and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoPage(1, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(3, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 200`


2. Construtor with page as PicoPage and sortable as array

`$pageable = new PicoPageable(new PicoPage(1, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(5, 50), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 50 OFFSET 200`

3. Construtor with page as PicoLimit and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoLimit(0, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(50, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 50`

4. Construtor with page as PicoLimit and sortable as array

`$pageable = new PicoPageable(new PicoLimit(0, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(20, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 20`

5. Construtor with page as array and sortable as PicoSortable

`$pageable = new PicoPageable(array(10, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 900`

6. Construtor with page as array and sortable as array

`$pageable = new PicoPageable(array(3, 200), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 200 OFFSET 400`

## Find All with Option

1. `MagicObject::FIND_OPTION_NO_COUNT_DATA`
2. `MagicObject::FIND_OPTION_NO_FETCH_DATA`

If the `MagicObject::FIND_OPTION_NO_COUNT_DATA` option is provided, the MagicObject will not count the data, even if a PicoPageable is provided. This will reduce data processing time, but the user will not know how much data there actually is. This option can be chosen when dealing with very large datasets.

If the `MagicObject::FIND_OPTION_NO_FETCH_DATA` option is given, the MagicObject will not directly fetch data and store it in the object. Users must retrieve data one by one using the fetch method. Every time the application receives data from the database, it can process it and send it directly to a file or an output buffer, instead of collecting it in a list in memory. This option can be chosen when handling very large datasets to prevent excessive memory consumption.

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
	 * @var int
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var int
	 */
	protected $sortOrder;

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
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
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
	 * @Label(content="Active")
	 * @var bool
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
## Filtering, Ordering and Pagination

MagicObject will filter data according to the given criteria. On the other hand, MagicObject will only retrieve data on the specified page by specifying `limit` and `offset` data in the `select` query.

Example parameters:

`genre_id=0648d4e176da4df4472d&album_id=&artist_vocal_id=&name=&vocal=&lyric_complete=&active=&page=2&orderby=title&ordertype=asc`

Create entity according to database

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
	 * @var int
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
	 * @var int
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
	 * @Column(name="file_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_midi", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_xml", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * @var float
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
	 * @var bool
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
	 * @var bool
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
	 * @var int
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var float
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
	 * @Column(name="last_upload_time_image", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

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
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $active;
}
```

Filtering and pagination

```php
<?php
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Pagination\PicoPagination;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Response\Generated\PicoSelectOption;
use MagicObject\Util\Dms;
use MusicProductionManager\Constants\ParamConstant;
use MusicProductionManager\Data\Entity\Album;
use MusicProductionManager\Data\Entity\Artist;
use MusicProductionManager\Data\Entity\EntitySong;
use MusicProductionManager\Data\Entity\EntitySongComment;
use MusicProductionManager\Data\Entity\Genre;
use MusicProductionManager\Data\Entity\Producer;
use MusicProductionManager\Utility\SpecificationUtil;
use MusicProductionManager\Utility\UserUtil;

require_once "inc/auth-with-login-form.php";
require_once "inc/header.php";

$inputGet = new InputGet();

$allowChangeVocalist = UserUtil::isAllowSelectVocalist($currentLoggedInUser);
$allowChangeComposer = UserUtil::isAllowSelectComposer($currentLoggedInUser);
$allowChangeArranger = UserUtil::isAllowSelectArranger($currentLoggedInUser);

?>
<div class="filter-container">
<form action="" method="get">
<div class="filter-group">
	<span>Genre</span>
	<select class="form-control" name="genre_id" id="genre_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Genre(null, $database), array('value'=>'genreId', 'label'=>'name'), $inputGet->getGenreId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Album</span>
	<select class="form-control" name="album_id" id="album_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Album(null, $database), array('value'=>'albumId', 'label'=>'name'), $inputGet->getAlbumId(), null, new PicoSortable('sortOrder', PicoSort::ORDER_TYPE_DESC)); ?>
	</select>
</div>
<div class="filter-group">
	<span>Producer</span>
	<select class="form-control" name="producer_id" id="producer_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Producer(null, $database), array('value'=>'producerId', 'label'=>'name'), $inputGet->getProducerId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Composer</span>
	<select class="form-control" name="composer" id="composer">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getComposer()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Arranger</span>
	<select class="form-control" name="arranger" id="arranger">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getArranger()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Vocalist</span>
	<select class="form-control" name="vocalist" id="vocalist">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getVocalist()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Title</span>
	<input class="form-control" type="text" name="title" id="title" autocomplete="off" value="<?php echo $inputGet->getTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>
<div class="filter-group">
	<span>Subtitle</span>
	<input class="form-control" type="text" name="subtitle" id="subtitle" autocomplete="off" value="<?php echo $inputGet->getSubtitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>

<div class="filter-group">
	<span>Subtitle Complete</span>
	<select class="form-control" name="subtitle_complete" id="subtitle_complete">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedSubtitleComplete("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedSubtitleComplete("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Vocal</span>
	<select class="form-control" name="vocal" id="vocal">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedVocal("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedVocal("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Active</span>
	<select class="form-control" name="active" id="active">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedActive("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedActive("0");?>>No</option>
	</select>
</div>

<input class="btn btn-success" type="submit" value="Show">

</form>
</div>
<?php
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

$songEntity = new EntitySong(null, $database);
$rowData = $songEntity->findAll($spesification, $pageable, $sortable, true);

$result = $rowData->getResult();

?>

<script>
$(document).ready(function(e){
	let pg = new Pagination('.pagination', '.page-selector', 'data-page-number', 'page');
	pg.init();
	$(document).on('change', '.filter-container form select', function(e2){
		$(this).closest('form').submit();
	});
});
</script>

<?php
if(!empty($result))
{
?>
<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>
<div class="table-list-container" style="overflow-x:auto">
<table class="table text-nowrap">
<thead>
	<tr>
	<th scope="col" width="20"><i class="ti ti-edit"></i></th>
	<th scope="col" width="20"><i class="ti ti-player-play"></i></th>
	<th scope="col" width="20"><i class="ti ti-download"></i></th>
	<th scope="col" width="20">#</th>
	<th scope="col" class="col-sort" data-name="name">Name</th>
	<th scope="col" class="col-sort" data-name="title">Title</th>
	<th scope="col" class="col-sort" data-name="rating">Rate</th>
	<th scope="col" class="col-sort" data-name="album_id">Album</th>
	<th scope="col" class="col-sort" data-name="producer_id">Producer</th>
	<th scope="col" class="col-sort" data-name="track_number">Trk</th>
	<th scope="col" class="col-sort" data-name="genre_id">Genre</th>
	<th scope="col" class="col-sort" data-name="artist_vocalist">Vocalist</th>
	<th scope="col" class="col-sort" data-name="artist_composer">Composer</th>
	<th scope="col" class="col-sort" data-name="artist_arranger">Arranger</th>
	<th scope="col" class="col-sort" data-name="duration">Duration</th>
	<th scope="col" class="col-sort" data-name="vocal">Vocal</th>
	<th scope="col" class="col-sort" data-name="subtitle_complete">Sub</th>
	<th scope="col" class="col-sort" data-name="active">Active</th>
	</tr>
</thead>
<tbody>
	<?php
	$no = $pagination->getOffset();
	foreach($result as $song)
	{
	$no++;
	$songId = $song->getSongId();
	$linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&song_id=".$songId;
	$linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&song_id=".$songId;
	$linkDelete = basename($_SERVER['PHP_SELF'])."?action=delete&song_id=".$songId;
	$linkDownload = "read-file.php?type=all&song_id=".$songId;
	?>
	<tr data-id="<?php echo $songId;?>">
	<th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
	<th scope="row"><a href="#" class="play-data" data-url="<?php echo $cfg->getSongBaseUrl()."/".$song->getSongId()."/".basename($song->getFilePath());?>?hash=<?php echo str_replace(array(' ', '-', ':'), '', $song->getLastUploadTime());?>"><i class="ti ti-player-play"></i></a></th>
	<th scope="row"><a href="<?php echo $linkDownload;?>"><i class="ti ti-download"></i></a></th>
	<th class="text-right" scope="row"><?php echo $no;?></th>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $song->getName();?></a></td>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-title"><?php echo $song->getTitle();?></a></td>
	<td class="text-data text-data-rating text-nowrap"><?php echo $song->hasValueRating() ? $song->getRating() : "";?></td>
	<td class="text-data text-data-album-name text-nowrap"><?php echo $song->hasValueAlbum() ? $song->getAlbum()->getName() : "";?></td>
	<td class="text-data text-data-producer-name text-nowrap"><?php echo $song->hasValueProducer() ? $song->getProducer()->getName() : "";?></td>
	<td class="text-data text-data-track-number text-nowrap"><?php echo $song->hasValueTrackNumber() ? $song->getTrackNumber() : "";?></td>
	<td class="text-data text-data-genre-name text-nowrap"><?php echo $song->hasValueGenre() ? $song->getGenre()->getName() : "";?></td>
	<td class="text-data text-data-artist-vocal-name text-nowrap"><?php echo $song->hasValueVocalist() ? $song->getVocalist()->getName() : "";?></td>
	<td class="text-data text-data-artist-composer-name text-nowrap"><?php echo $song->hasValueComposer() ? $song->getComposer()->getName() : "";?></td>
	<td class="text-data text-data-artist-arranger-name text-nowrap"><?php echo $song->hasValueArranger() ? $song->getArranger()->getName() : "";?></td>
	<td class="text-data text-data-duration text-nowrap"><?php echo (new Dms())->ddToDms($song->getDuration() / 3600)->printDms(true, true); ?></td>
	<td class="text-data text-data-vocal text-nowrap"><?php echo $song->isVocal() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-subtitle-complete text-nowrap"><?php echo $song->isSsubtitleComplete() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-active text-nowrap"><?php echo $song->isActive() ? 'Yes' : 'No';?></td>
	</tr>
	<?php
	}
	?>
	
</tbody>
</table>
</div>

<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>

<?php
}
?>

<script>
let playerModal;


$(document).ready(function(e){
let playerModalSelector = document.querySelector('#songPlayer');
playerModal = new bootstrap.Modal(playerModalSelector, {
	keyboard: false
});

$('a.play-data').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio').attr('src', $(this).attr('data-url'));
	playerModal.show();
});
$('.close-player').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio')[0].pause();
	playerModal.hide();
});
});
</script>

<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="songPlayer" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="songPlayerLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="addAlbumDialogLabel">Play Song</h5>
			<button type="button" class="btn-primary btn-close close-player" aria-label="Close"></button>
		</div>
		<div class="modal-body">
			<audio style="width: 100%; height: 40px;" controls></audio>
		</div>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-success close-player">Close</button>
		</div>
	</div>
</div>
</div>

<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/song-update-dialog.php"></div>

<script>
let updateSongModal;

$(document).ready(function(e){

$(document).on('click', '.edit-data', function(e2){
	e2.preventDefault();
	e2.stopPropagation();
	
	let songId = $(this).closest('tr').attr('data-id') || '';
	let dialogSelector = $('.modal-update-data');
	dialogSelector.load(dialogSelector.attr('data-url')+'?song_id='+songId, function(data){
	
	let updateSongModalElem = document.querySelector('#updateSongDialog');
	updateSongModal = new bootstrap.Modal(updateSongModalElem, {
		keyboard: false
	});
	updateSongModal.show();
	downloadForm('.lazy-dom-container', function(){
		if(!allDownloaded)
		{
			initModal2();
			console.log('loaded')
			allDownloaded = true;
		}
		loadForm();
	});
	})
});

$(document).on('click', '.save-update-song', function(){
	if($('.song-dialog audio').length > 0)
	{
	$('.song-dialog audio').each(function(){
		$(this)[0].pause();
	});
	}
	let dataSet = $(this).closest('form').serializeArray();
	$.ajax({
	type:'POST',
	url:'lib.ajax/song-update.php',
	data:dataSet, 
	dataType:'json',
	success: function(data)
	{
		updateSongModal.hide();
		let formData = getFormData(dataSet);
		let dataId = data.song_id;
		$('[data-id="'+dataId+'"] .text-data.text-data-name').text(data.name);
		$('[data-id="'+dataId+'"] .text-data.text-data-title').text(data.title);
		$('[data-id="'+dataId+'"] .text-data.text-data-rating').text(data.rating);
		$('[data-id="'+dataId+'"] .text-data.text-data-track-number').text(data.track_number);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-vocal-name').text(data.artist_vocal_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-composer-name').text(data.artist_composer_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-arranger-name').text(data.artist_arranger_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-album-name').text(data.album_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-genre-name').text(data.genre_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-duration').text(data.duration);
		$('[data-id="'+dataId+'"] .text-data.text-data-vocal').text(data.vocal === true || data.vocal == 1 || data.vocal == "1" ?'Yes':'No');
		$('[data-id="'+dataId+'"] .text-data.text-data-active').text(data.active === true || data.active == 1 || data.active == "1" ?'Yes':'No');
	}
	})
});
});
</script>
<?php
require_once "inc/footer.php";
?>
```

## Native Query

In MagicObject version 2, native queries have been introduced as an efficient way to interact with the database.

Native queries offer significant performance improvements when handling large volumes of data, allowing users to craft highly efficient queries that meet diverse requirements.

Native queries can perform several tasks such as:
1. performing INSERT operations
2. performing SELECT operations with complex queries such as JOIN, UNION, IF-ELSE, CASE, and others
3. counting the number of rows without reading the data in detail
4. performing UPDATE operations
5. performing DELETE operations
6. calling functions
7. calling procedures and stored procedures

Native queries do not support multiple database connections. This means that all operations performed using native queries must be executed within a single, active database connection. This design choice ensures data consistency and integrity by preventing potential conflicts that may arise from attempting to manage multiple connections simultaneously. Users should ensure that their application logic accommodates this limitation, potentially leveraging connection pooling or other techniques to optimize database interaction when needed.

### Parameters

The parameters accepted by the native query function are as follows:

1. `string`
2. `int` or `integer`
3. `float`
4. `bool` or `boolean`
5. `null`
6. `DateTime`
7. `array` of `string`, `int`, `bool` and `DateTime`

For columns with data type `DATETIME` and `TIMESTAMP`, users can use either `string` or `DateTime` parameters. `DateTime` will be first converted to 'Y-m-d H:i:s' format automatically by MagicObject. Don't forget to define DateTimeZone for DateTime object. Also note the time resolution for the `in` and `=` criteria.

### Return Type

This method executes a database query using the parameters and annotations defined in the caller function.
It leverages reflection to access the query string specified in the caller's docblock, binds the relevant parameters,
and then runs the query against the database.

By analyzing the parameters and return type of the calling function, this method enables dynamic query execution tailored to the parameters and return type specified in the @return annotation. Supported return types include:

- **void**: The method will return `null`.
- **int** or **integer**: The method will return the number of affected rows.
- **object** or **stdClass**: The method will return a single result as an object.
- **stdClass[]**: The method will return all results as an array of `stdClass` objects, similar to `PDO::fetchAll(PDO::FETCH_OBJ)`.
- **array**: The method will return all results as an associative array, similar to `PDO::fetchAll(PDO::FETCH_ASSOC)`.
- **string**: The method will return the result as a JSON-encoded string.
- **PDOStatement**: The method will return a prepared statement for further operations if necessary.
- **MagicObject** and its derived classes: If the return type is a class name or an array of class names, instances of the specified class will be created for each row fetched.

MagicObject also supports return types `self` and `self[]` which will represent the respective class.

The method returns a mixed type result, which varies based on the caller function's return type:
- It will return `null` for void types.
- An integer representing the number of affected rows for int types.
- An object for single result types.
- An array of associative arrays for array types.
- A JSON string for string types.
- Instances of a specified class for class name matches.

If there is an error executing the database query, a **PDOException** will be thrown.

Native query must be a function of a class that extends from the MagicObject class. In its definition, this method must call `$this->executeNativeQuery()`. `MagicObject::executeNativeQuery()` will analyze the docblock, parameters, and return type to process the given query. For ease and flexibility in writing code, the `MagicObject::executeNativeQuery()` function call does not pass parameters. Instead, the `MagicObject::executeNativeQuery()` function takes parameters from the calling function. Thus, changes to the parameters of the calling function do not require changes to the function definition.

Native queries can be created on entities used by the application. If in the previous version the entity only contained properties, then in version 2.0, the entity can also contain functions for native queries. However, entities in versions 1 and 2 both support functions but functions with native queries are only supported in version 2.0.

### Pagination and Sorting

In **MagicObject version 2.7**, support for **pageable** and **sortable** functionality has been added to native queries. Previously, native queries did not support pagination and sorting directly. Instead, users had to manually include `SORT BY` and `LIMIT OFFSET` clauses in their queries, which made them less flexible. This approach was problematic because each Database Management System (DBMS) has its own syntax for writing queries, making it cumbersome to adapt queries for different platforms.

With the introduction of pageable and sortable support in version 2.7, users can now easily pass **pagination** parameters using the `PicoPageable` type and **sorting** parameters using the `PicoSortable` type directly into their native queries. These parameters can be placed anywhere within the query, but it is recommended to position them either at the beginning or the end of the query for optimal readability and organization.

This enhancement makes native queries more flexible and easier to maintain, as the logic for pagination and sorting is handled automatically, without requiring manual intervention for each DBMS. As a result, users can now write cleaner, more efficient, and database-agnostic native queries.

### Debug Query

MagicObject checks if the database connection has a debugging function for queries. If available, it sends the executed query along with the parameter values to this function, aiding users in identifying errors during query definition and execution.

**Example:**

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__)) . "/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential->getDatabase(), null, function($sql){
    error_log($sql); // Debug query here
});
$database->connect();

class Supervisor extends MagicObject
{
    /**
     * Native query 1
     *
     * This method will return null.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return void
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native1($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 2
     *
     * This method will return the number of affected rows.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return int
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native2($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 3
     *
     * This method will return a single result as an object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native3($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 4
     *
     * This method will return an array of stdClass objects.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native4($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 5
     *
     * This method will return an associative array.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return array
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native5($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 6
     *
     * This method will return a JSON-encoded string.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return string
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native6($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 7
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 8
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native8($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 9
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native9($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 10
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native10($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 11
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native11($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
    
    /**
     * Native query 12
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 13
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param PicoPagebale $pageable
     * @param PicoSortable $sortable
     * @param bool $aktif The active status to filter results.
     * @return MagicObject[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native13($pageable, $sortable, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

$obj = new Supervisor(null, $database);

$native1 = $obj->native1(1, true);

$native2 = $obj->native2(1, true);
echo "\r\nnative2:\r\n";
print_r($native2);

$native3 = $obj->native3(1, true);
echo "\r\nnative3:\r\n";
print_r($native3);

$native4 = $obj->native4(1, true);
echo "\r\nnative4:\r\n";
print_r($native4);

$native5 = $obj->native5(1, true);
echo "\r\nnative5:\r\n";
print_r($native5);

$native6 = $obj->native6(1, true);
echo "\r\nnative6:\r\n";
print_r($native6);

$native7 = $obj->native7(1, true);
echo "\r\nnative7:\r\n";
print_r($native7->fetchAll(PDO::FETCH_ASSOC));

$native8 = $obj->native8(1, true);
echo "\r\nnative8:\r\n";
print_r($native8);

$native9 = $obj->native9(1, true);
echo "\r\nnative9:\r\n";
print_r($native9);

$native10 = $obj->native10(1, true);
echo "\r\nnative10:\r\n";
print_r($native10);

$native11 = $obj->native11(1, true);
echo "\r\nnative11:\r\n";
print_r($native11);

// For the MagicObject return type, users can utilize the features of the MagicObject except for 
// interacting with the database again because native queries are designed for a different purpose.

echo "Phone: " . $native8->getTelepon() . "\r\n";
echo "Phone: " . $native9[0]->getTelepon() . "\r\n";
echo "Phone: " . $native10->getTelepon() . "\r\n";
echo "Phone: " . $native11[0]->getTelepon() . "\r\n";


$sortable = new PicoSortable();
$sortable->addSortable(new PicoSort("nama", PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(3, 20));

try
{
    $native13 = $obj->native13($pageable, $sortable, true);
    echo "\r\nnative13:\r\n";
    foreach($native13 as $sup)
    {
        echo $sup."\r\n\r\n";
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}
```

For the purpose of exporting large amounts of data, use the PDOStatement return type. PDOStatement allows users to read one by one and process it immediately, allowing PHP to release memory from the previous process. PHP does not need to store very large data in a variable.

Example 12 shows how to use array parameters.

For example:

```sql
SELECT supervisor.*
FROM supervisor
WHERE supervisor.supervisor_id in (1, 2, 3, 4)
AND supervisor.aktif = true;
```

So the method is:

```php
    /**
     * Native query 12
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
```

**Example 1**

Using MagicObject

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateHeader($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data->get($column);
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_OBJ))
{
    $data = new SupervisorExport($row);
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $data, $iteration);
}
fclose($fp);
```

**Example 2**

Using stdClass

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateData($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data->{$column};
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_OBJ))
{
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $row, $iteration);
}
fclose($fp);
```

**Example 3**

Using associated array

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateHeader($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data[$column];
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $row, $iteration);
}
fclose($fp);
```

### Dynamic Query Template for Native Query

MagicObject version **2.11** introduces a new feature for native queries called the **dynamic query template**, allowing users to dynamically pass a query template to the native query method.

**Example 4**

```php
<?php

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @return PDOStatement The result of the executed query.
     * @query("
           SELECT supervisor.* 
           FROM supervisor 
           WHERE supervisor.aktif = :aktif
      ")
    */
    public function exportActive($aktif, $pageable, $sortable)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}
```

In certain cases, developers need to dynamically insert a query template. This cannot be done with the above method. Therefore, MagicObject provides a way to pass the query template as a parameter. Since every parameter besides `PicoPageable` and `PicoSortable` is considered as a value for the query, MagicObject uses the `PicoDatabaseQueryTemplate` data type to pass the query template within the parameter. With this, the native query method definition becomes as follows:

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseQueryTemplate;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @param PicoDatabaseQueryTemplate $template Query template.
     * @return PDOStatement The result of the executed query.
    */
    public function exportActive($aktif, $pageable, $sortable, $template)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}
```

Similar to `PicoPageable` and `PicoSortable`, parameters of type `PicoDatabaseQueryTemplate` can be placed anywhere.

To call this method, use the following approach:


```php
<?php
$explort = new SupervisorExport(null, $database);

$aktif = true;
$sortable = new PicoSortable();
$sortable->add(new PicoSort('name', PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(1, 1), $sortable);

$builder = new PicoDatabaseQueryBuilder($database);
$builder->newQuery()
    ->select("supervisor.*")
    ->from("supervisor")
    ->where("supervisor.aktif = :aktif");

$template = new PicoDatabaseQueryTemplate($builder);
$result = $explort->exportActive($aktif, $pageable, $sortable, $template);
```

In the example above, the `PicoDatabaseQueryTemplate` object is initialized using a `PicoDatabaseQueryBuilder` object.

```php
<?php
$explort = new SupervisorExport(null, $database);

$aktif = true;
$sortable = new PicoSortable();
$sortable->add(new PicoSort('name', PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(1, 1), $sortable);

$builder = "SELECT supervisor.* FROM supervisor WHERE supervisor.aktif = :aktif";

$template = new PicoDatabaseQueryTemplate($builder);
$result = $explort->exportActive($aktif, $pageable, $sortable, $template);
```

In the example above, the `PicoDatabaseQueryTemplate` object is initialized using a string.

### Query Trimming for Native Query

MagicObject **3.7** introduces a new feature that allows users to **trim left spaces and asterisks** (`*`) from multiline SQL queries written in **docblock annotations**. This feature enhances **readability** and **ensures better formatting** of queries extracted from docblocks.

#### Feature Overview

-   **Automatic Query Trimming:** Removes leading `*` and spaces from SQL queries in docblocks.
    
-   **Improved Readability:** Prevents unwanted indentation when parsing queries.
    
-   **Easy Activation:** Just add `trim=true` in the `@query` annotation.
    

#### Before MagicObject 3.7 (Without Trim)

When defining queries in docblocks, the indentation and leading `*` may cause unnecessary formatting issues:

```
<?php

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @return PDOStatement The result of the executed query.
     * @query("
           SELECT supervisor.*
           FROM supervisor
           WHERE supervisor.aktif = :aktif
      ")
    */
    public function exportActive($aktif, $pageable, $sortable)
    {
        return $this->executeNativeQuery();
    }
}
```

#### After MagicObject 3.7 (With Trim Enabled)

With the `trim=true` option, MagicObject **automatically removes leading** `*` **and spaces**, keeping the query **clean and properly formatted**:

```
<?php

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @return PDOStatement The result of the executed query.
     * @query("
     *      SELECT supervisor.*
     *      FROM supervisor
     *      WHERE supervisor.aktif = :aktif
     * ", trim=true)
    */
    public function exportActive($aktif, $pageable, $sortable)
    {
        return $this->executeNativeQuery();
    }
}
```

#### Configuration & Parameters

The `trim` attribute is added inside the `@query` annotation:

| Attribute   | Allowed Values     | Description |
| ----------- | ------------------ | ----------- |
| `trim`      | `true` or `false`  | Removes leading `*` and spaces from multiline queries in docblocks. Default value is `true`. |

#### Notes & Limitations

-   `trim=true` **does not modify the SQL syntax** itself, meaning `SELECT * FROM table` remains unchanged.
-   This feature only affects how queries are **extracted** from docblocks, improving clarity without changing functionality.


### Best Practices

1. **Utilize Prepared Statements**: Always prefer using prepared statements for security against SQL injection.
2. **Error Handling**: Wrap database calls in try-catch blocks to handle exceptions gracefully.
3. **Efficient Data Retrieval**: Use PDOStatement for large datasets to process rows one by one.
4. **Debugging**: Implement logging for SQL queries to troubleshoot issues more effectively.
5. **Keep Queries Simple**: Break down complex queries into simpler components if possible, making them easier to maintain and debug.

By leveraging the native query feature in MagicObject, you can create efficient and maintainable database interactions, enhancing your application's performance and security.
## Multiple Database Connections

MagicObject version 2 introduces support for multiple database connections, enabling users to manage entities stored across different databases seamlessly. When performing operations such as JOINs with entities from multiple databases, it is essential to define a database connection for each entity involved.

### Example Scenario

In this example, we will illustrate how to manage entities stored in different databases:

- **Album**, **Producer** and **Song** entities are stored in **Database 1**.
- **Artist** entity is stored in **Database 2**.

### Entity Definitions

Here are the definitions for the `Album`, `Artist`, `Producer`, and `Song` classes:

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

You can set the database entities for `Album`, `Producer`, and `Artist` associated with a `Song` instance in several ways:

**Method 1: Chaining Method Calls**

```php
$song->databaseEntity($album)->databaseEntity($producer)->databaseEntity($artist);
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

## Dump Database

We can dump database to another database type. We do not need any database converter. Just define the target database type when we dump the database.

### Database Dump Overview

1. Dumping Database Structure

You can dump the structure of a database without needing to connect to a real database. Specify the target database type when calling the dump method.

**Code Example:**

```php
<?php

use MagicObject\Database\PicoDatabaseType;
use MagicObject\Generator\PicoDatabaseDump;
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
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var int
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
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

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
	 * @var int
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
	 * @Column(name="file_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_midi", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_xml", type="timestamp", length=26, nullable=true)
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
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
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
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var float
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
	 * @Label(content="Subtitle Complete")
	 * @var bool
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
	 * @Label(content="Vocal")
	 * @var bool
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
	 * @var int
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var float
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
	 * @Column(name="last_upload_time_image", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

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
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var bool
	 */
	protected $active;

}

```

### Dump Structure

We can dump database structure without connect to real database. Just define the target database type. If we will dump multiple table, we must use dedicated instance of `PicoDatabaseDump`.

```php
$song = new Song();
$dumpForSong = new PicoDatabaseDump();
echo $dumpForSong->dumpStructure($song, PicoDatabaseType::DATABASE_TYPE_MYSQL, true, true);
```

**Parameters:**

-    **$song:** An instance of the Song class, representing the table structure you want to dump.
-    **PicoDatabaseType::DATABASE_TYPE_MYSQL:** The type of database you are targeting (e.g., MySQL, PostgreSQL).
-    **true (createIfNotExists):** Whether to include the "IF NOT EXISTS" clause in the CREATE statement.
-    **true (dropIfExists):** Whether to include the "DROP TABLE IF EXISTS" statement before creating the table.

### Dump Data

We can dump data by connecting to real database. Don't forget to define the target database type. If we will dump multiple table, we must use dedicated instance of `PicoDatabaseDump`.

```php
$song = new Song(null, $database);
$pageData = $song->findAll();
$dumpForSong = new PicoDatabaseDump();
echo $dumpForSong->dumpData($pageData, PicoDatabaseType::DATABASE_TYPE_MYSQL);
```

**Important Note:**

When exporting a table with a large amount of data, the above method may not be suitable, as it can consume a significant amount of memory while trying to accommodate all the data before writing it to a file.

To efficiently handle large datasets, you can use the following approach:

```php
$song = new Song(null, $database);
/*
$speficication = null
$pageable = null
$sortable = null
$passive = true
$subqueryMap = null
$findOption = MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA
*/
$pageData = $song->findAll(null, null, null, true, null, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
$dumpForSong = new PicoDatabaseDump();

$dumpForSong->dumpData($pageData, PicoDatabaseType::DATABASE_TYPE_MYSQL, new Song(), $maxRecord, function($sql){
    $fp = fopen("dump.sql", "a");
    fputs($fp, $sql);
    fclose($fp);
});
```

**Explanation:**

-    **findAll Parameters:** This allows you to customize your query. The options provided (e.g., FIND_OPTION_NO_COUNT_DATA, FIND_OPTION_NO_FETCH_DATA) ensure that only the necessary data is fetched, thus reducing memory consumption.
-    **Callback Function:** The anonymous function passed as a parameter to dumpData handles the SQL output, appending it to dump.sql in an efficient manner, thereby avoiding excessive memory usage.

By following these guidelines, you can effectively manage both the structure and data dumping processes while optimizing for performance and resource utilization.

### Summary

- **Structure Dumping**: Use `dumpStructure` to get the schema of the database without a real connection.
- **Data Dumping**: Use `dumpData` to extract data from an actual database connection.

This approach allows developers to quickly switch between database types and manage their database schemas and data efficiently. The use of dedicated instances of PicoDatabaseDump for multiple tables ensures clarity and organization in your database operations.
## Object Label

```
<?php

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="user_type")
 */
class UserType extends MagicObject
{
	/**
	 * User Type ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_type_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="User Type ID")
	 * @var string
	 */
	protected $userTypeId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Admin
	 * 
	 * @Column(name="admin", type="tinyint(1)", length=1, nullable=true)
	 * @var bool
	 */
	protected $admin;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var int
	 */
	protected $sortOrder;

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

```php

require_once dirname(__DIR__) . "/vendor/autoload.php";

$userType = new UserType();

// print label of adminCreate property
echo $userType->labelAdminCreate();
// it will print "Admin Create"
```

## Database Query Builder

Database Query Builder is a feature for creating object-based database queries. The output of the Database Query Builder is a query that can be directly executed by the database used.

Database Query Builder is actually designed for all relational databases but is currently only available in two languages, namely MySQL and PostgreSQL. MagicObject internally uses the Database Query Builder to create queries based on given methods and parameters.

Database Query Builder is required to create native queries that cannot be created automatically by MagicObject. Apart from that, Database Query Builder is also used for performance reasons where native queries will run faster because they only retrieve the desired columns and do not perform unnecessary joins.

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;

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
  
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
  
    $queryBuilder
        ->newQuery()
        ->select("u.*")
        ->from("user")
        ->alias("u")
        ->where("u.username = ? and u.password = ? and u.active = ?", $username, $password, true)
        ;
    $stmt = $database->executeQuery($queryBuilder);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $user)
    {
        var_dump($user);
    }
  
}
catch(Ecxeption $e)
{
  
}
```

### Methods

**newQuery()**

`newQuery()` is method to clear all properties from previous query. Allways invoke this method before create new query to ensure the query is correct.

**insert()**

`insert()` is method to start the `INSERT` query.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

**into($query)**

`into($query)` is method for `INTO`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

**fields($query)**

`fields($query)` is method to send field on query `INSERT`. The parameter can be an array or string.

**values($query)**

`values($query)` is method to send values on query `INSERT`. The parameter can be an array or string.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("(?, ?, ?, ?)", '123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12');
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.

Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array("song_id", "name", "title", "time_create"))
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 5:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$data = array(
    "song_id"=>'123456', 
    "name"=>'Lagu 0001', 
    "title"=>'Membendung Rindu', 
    "time_create"=>'2024-03-03 10:11:12'
    );
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array_keys($data))
    ->values(array_values($data));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.


**select($query)**

`select($query)` is metod for query `SELECT`

**alias($query)**

`alias($query)` is method for query `AS`

**delete()**

`delete` is method for query `DELETE`

**from($query)**

`from($query)` is method for query `FROM`

**where($query)**

`from($query)` is method for query `WHERE`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("*")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select *
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->detele()
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be escaped before being executed by the database. You don't need to escape it first.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-00 12:12:12'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
delete
from song
where time_create > '2023-01-00 12:12:12'
*/
```

**join($query)**

**leftJoin($query)**

**rightJoin($query)**

**innerJoin($query)**

**outerJoin($query)**

Example

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$active = true;
$queryBuilder->newQuery()
    ->select("song.*, album.name as album_name")
    ->from("song")
    ->leftJoin("album")
    ->on("album.album_id = song.album_id")
    ->where("song.active = ? ", $active);
/*
select song.*, album.name as album_name
from song
left join album
on album.album_id = song.album_id
where song.active = true
*/
```

This way, `$active` will be escaped before being executed by the database. You don't need to escape it first.

## PicoSqlite

### Overview

`PicoSqlite` is a PHP class designed for simplified interactions with SQLite databases using PDO (PHP Data Objects). This class extends `PicoDatabase` and provides methods for connecting to the database, creating tables, and performing basic CRUD (Create, Read, Update, Delete) operations.

Here are some advantages of using SQLite:

1.  **Lightweight**: SQLite is a serverless, self-contained database engine that requires minimal setup and uses a single file to store the entire database, making it easy to manage and deploy.
    
2.  **Easy to Use**: Its simple API allows for straightforward integration with PHP, enabling quick database operations without the overhead of complex configurations.
    
3.  **No Server Required**: Unlike other database systems, SQLite does not require a separate server process, which simplifies the development process and reduces resource usage.
    
4.  **Cross-Platform**: SQLite databases are cross-platform and can be used on various operating systems without compatibility issues.
    
5.  **Fast Performance**: For smaller databases and applications, SQLite often outperforms more complex database systems, thanks to its lightweight architecture.
    
6.  **ACID Compliance**: SQLite provides full ACID (Atomicity, Consistency, Isolation, Durability) compliance, ensuring reliable transactions and data integrity.
    
7.  **Rich Feature Set**: Despite being lightweight, SQLite supports many advanced features like transactions, triggers, views, and complex queries.
    
8.  **No Configuration Required**: SQLite is easy to set up and requires no configuration, allowing developers to focus on building applications rather than managing the database server.
    
9.  **Great for Prototyping**: Its simplicity makes it ideal for prototyping applications before moving to a more complex database system.
    
10.  **Good for Read-Heavy Workloads**: SQLite performs well in read-heavy scenarios, making it suitable for applications where data is frequently read but rarely modified.
    

These features make SQLite a popular choice for many PHP applications, especially for smaller projects or for applications that need a lightweight database solution.

SQLite has a slightly different method for determining whether a SELECT query returns matching rows. While other databases often utilize the rowCount() method to get this information, SQLite does not support this functionality in the same way. To address this limitation, MagicObject has implemented a solution that seamlessly handles row checking for users. With MagicObject, developers can interact with SQLite without needing to worry about the intricacies of row counting. This allows for a more intuitive and efficient experience when working with SQLite, enabling users to focus on their application logic rather than the underlying database mechanics.

### Requirements

-    PHP 7.0 or higher
-    PDO extension enabled

### Installation

To use the `PicoSqlite` class, include it in your PHP project. Ensure that your project structure allows for proper namespace loading.

```php
use MagicObject\Database\PicoSqlite;

// Example usage:
$db = new PicoSqlite('path/to/database.sqlite');
```

### Class Methods

#### Constructor

```php
public function __construct($databaseFilePath)
```

**Parameters:**

    string $databaseFilePath: The path to the SQLite database file.

**Throws:** PDOException if the connection fails.

**Usage Example:**

```php
$sqlite = new PicoSqlite('path/to/database.sqlite');
```

#### Connecting to the Database

```php
public function connect($withDatabase = true)
```

**Parameters:**
-    bool $withDatabase: Optional. Default is true. Indicates whether to select the database when connecting.

**Returns:** `bool` - True if the connection is successful, false otherwise.

**Usage Example:**

```php
if ($sqlite->connect()) {
    echo "Connected to database successfully.";
} else {
    echo "Failed to connect.";
}
```

#### Check Table

```php
public function tableExists($tableName) : bool
```

**Parameters:**

-    string $tableName: The name of the table to check.

**Returns:** `bool` - True if the table exists, false otherwise.

**Usage Example:**

```php
if ($sqlite->tableExists('users')) {
    echo "Table exists.";
} else {
    echo "Table does not exist.";
}
```

#### Create Table

```php
public function createTable($tableName, $columns) : int|false
```

**Parameters:**

-    string $tableName: The name of the table to create.
-    string[] $columns: An array of columns in the format 'column_name TYPE'.

**Returns:** `int|false` - Number of rows affected or false on failure.

**Usage Example:**

```php
$columns = ['id INTEGER PRIMARY KEY', 'name TEXT', 'email TEXT'];
$sqlite->createTable('users', $columns);
```

#### Insert

```php
public function insert($tableName, $data) : array 
```

**Parameters:**

-    string $tableName: The name of the table to insert into.
-    array $data: An associative array of column names and values to insert.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Doe', 'email' => 'john@example.com'];
$sqlite->insert('users', $data);
```

```php
public function update($tableName, $data, $conditions) : bool
```

**Parameters:**

-    string $tableName: The name of the table to update.
    array $data: An associative array of column names and new values.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Smith'];
$conditions = ['id' => 1];
$sqlite->update('users', $data, $conditions);
```

#### Delete

```php
public function delete($tableName, $conditions) : bool 
```

**Parameters:**

-    string $tableName: The name of the table to delete from.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$conditions = ['id' => 1];
$sqlite->delete('users', $conditions);
```

### Entity with PicoSqlite

```php
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
     * @var int
     */
    protected $numberOfSong;

    /**
     * Duration
     * 
     * @Column(name="duration", type="float", nullable=true)
     * @Label(content="Duration")
     * @var float
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
     * @var int
     */
    protected $sortOrder;

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

    /**
     * As Draft
     * 
     * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var bool
     */
    protected $asDraft;

}

$database = new PicoSqlite(__DIR__ . "/db.sqlite", null, function($sql){
    //echo $sql."\r\n";
});
try
{
    $database->connect();

    $album = new Album(null, $database);

    // create table if not exists
    $util = new PicoDatabaseUtilSqlite();
    $tableStructure = $util->showCreateTable($album, true);
    $database->query($tableStructure);

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
    $album->setAsDraft(false);
    echo $album."\r\n--------\r\n";
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
```

### Error Handling

If an operation fails, `PicoSqlite` may throw exceptions or return false. It is recommended to implement error handling using try-catch blocks to catch `PDOException` for connection-related issues.

### Conclusion

`PicoSqlite` provides an efficient way to interact with SQLite databases. Its straightforward API allows developers to perform common database operations with minimal code. For more advanced database operations, consider extending the class or using additional PDO features.
## File Upload

### Overview

Uploading files can be challenging, especially for novice developers. This guide explains how to manage single and multiple file uploads in PHP, highlighting the differences and providing straightforward examples.

### Key Features

-    **Easy File Handling:** The `PicoUploadFile` class simplifies the process of retrieving uploaded files.
-    **Unified Retrieval:** The `getAll` method allows you to handle files uploaded through both single and multiple forms without needing separate logic.

### Uploading Files

#### Single File Upload

To upload a single file, use the following HTML form:

```html
<!-- single file -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload" type="file" />
     <input type="submit" />
</form>
```

#### Multiple File Upload

To enable multiple file uploads, modify the input name to include brackets (`[]`) and use the `multiple` attribute:

```html
<!-- multiple files -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload[]" type="file" webkitdirectory multiple />
     <input type="submit" />
</form>
```

- For single uploads, the input field is named `myupload`.
- For multiple uploads, the input field name is `myupload[]`, which allows multiple files to be uploaded at once.

### PHP Backend Handling

To handle the uploaded files on the server side, you can use the PicoUploadFile class. Here’s how to do it:

```php
<?php

use MagicObject\File\PicoUploadFile;

require_once "vendor/autoload.php";

$files = new PicoUploadFile();

$file1 = $files->get('myupload');
// or alternatively
// $file1 = $files->myupload;
// or
// $file1 = $files->getMyupload();

$targetDir = __DIR__;

foreach ($file1->getAll() as $fileItem) {
    $temporaryName = $fileItem->getTmpName();
    $name = $fileItem->getName();
    $size = $fileItem->getSize();
    
    echo "$name | $temporaryName | $size\r\n";
    move_uploaded_file($temporaryName, $targetDir . "/" . $name);
}
```

### Checking Upload Type

Developers simply retrieve data using the `getAll` method and developers will get all files uploaded by users either via single file or multiple file forms. If necessary, the developer can check whether the file was uploaded using a single file or multiple file form with the `isMultiple()` method

```php

if($file1->isMultiple())
{
	// do something here
}
else
{
	// do something here
}
```

### Summary

This implementation offers a straightforward way to manage file uploads in PHP, abstracting complexities for developers. By using methods like `getAll()` and `isMultiple()`, developers can seamlessly handle both types of uploads without needing to write separate logic for each scenario. This approach not only improves code maintainability but also enhances the developer experience.

## Resumable File Download

### Namespace

`MagicObject\File`

### Description

The `PicoDownloadFile` class is designed to facilitate efficient file downloading in PHP, supporting **partial content** (range requests) for large files. It ensures that requested files exist, handles errors gracefully, and enables downloading in chunks to minimize server load and bandwidth consumption, particularly for large files.

The class supports the following:

-   Verifying the existence of the file.
-   Handling byte-range requests for resuming downloads.
-   Sending appropriate HTTP headers to manage the download.
-   Streaming the file to the client in manageable chunks (default size: 8 KB).
-   Returning relevant HTTP status codes and error messages.

This class is ideal for scenarios where large files need to be served to clients and you want to offer functionality like resuming interrupted downloads.


### Constructor

```php
__construct($filepath, $filename = null)
```

**Parameters**:

-   `$filepath` (string): The full path to the file that should be downloaded.
-   `$filename` (string|null, optional): The name of the file for download. If not provided, the filename is extracted from the `filepath` using `basename()`.

**Description**: Initializes the `PicoDownloadFile` object with the path of the file to be downloaded and an optional filename for the download response. If the filename is not specified, the base name of the file is used.

**Example**:

```php
$file = new PicoDownloadFile("/path/to/large-file.zip", "downloaded-file.zip");
```

### Method

```php
download($exit = false)
```

**Parameters**:

-   `$exit` (bool, optional): Whether to terminate the script after sending the file. Default is `false`.

**Returns**:

-   `bool`: Returns `true` if the entire file was successfully sent, `false` if only part of the file was sent (due to range requests).

**Description**: This method is responsible for initiating the file download process. It performs the following:

1.  Verifies the existence of the file.
2.  Handles byte-range requests for partial downloads (useful for resuming interrupted downloads).
3.  Sends the appropriate HTTP headers for the file download.
4.  Streams the file to the client in chunks of 8 KB (by default).

If `$exit` is set to `true`, the script will terminate after the file is sent.

**Example 1**

```php
<?php
require 'vendor/autoload.php'; // Include the PicoDownloadFile class
$path = "/path/to/large-file.zip";
$localName = "downloaded-file.zip";
$file = new PicoDownloadFile($path, $localName);
$file->download(true); // Initiate download and terminate the script after sending
```

**Example 2**

```php
<?php
require 'vendor/autoload.php'; // Include the PicoDownloadFile class
$path = "/path/to/large-file.zip";
$localName = "downloaded-file.zip";
$file = new PicoDownloadFile($path, $localName);
$finished = $file->download(false); // Initiate download without terminate the script after sending
if($finished && file_exists($path))
{
	unlink($path); // Delete file when finish
}
```

### Error Handling

-   **404 - File Not Found**: If the file does not exist at the specified path, a 404 error is returned.
-   **416 - Range Not Satisfiable**: If an invalid byte range is requested (e.g., the start byte is larger than the end byte), a 416 error is returned.
-   **500 - Internal Server Error**: If there is an issue opening the file for reading (e.g., permissions issues), a 500 error is returned.


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


## Database Migration

MagicObject allows users to import data from a database with different table names and column names between the source database and the destination database. This feature is used by developers who develop applications that are already used in production environments.

On the one hand, the application requires a new database structure according to what is defined by the developer. On the other hand, users want to use existing data.

Instead of altering tables from an existing database according to the new data, users can use the data import feature provided by MagicObject. Users can define column mappings and also define queries that will be executed after the database import is performed.

Users simply create an import data configuration file dan import script as follows:

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
table:
  - source: modul
    target: modul
    map: 
    - 'default_data : default'
    - 'sort_order : order'
    pre_import_script: 
    - "truncate modul"
    maximum_record: 2000
  - source: hak_akses
    target: hak_akses
    map:
    - 'allowed_detail : view'
    - 'allowed_create : insert'
    - 'allowed_update : update'
    - 'allowed_delete : delete'
    pre_import_script: 
    - "truncate hak_akses"
    post_import_script: 
    - "update hak_akses set allowed_list = true, allowed_approve = true, allowed_sort_order = true"
    maximum_record: 50
```

**Explanation**

- `database_source` is the source database configuration
- `database_target` is the target database configuration
- `table` is an array containing all the tables to be imported. Tables not listed in `table` will not be imported.
- `maximum_record` is the maximum number of records in a single insert query. Note that MagicObject does not care about the size of the data in bytes. If you need to adjust the maximum records per table, specify `maximum_record` on the table you want to set.

1. `source` (required)

Table name of the source database

2. `target` (required)

Table name of the target database

3. `maximum_record` (optional)

`maximum records` on a table is used to reset the number of records per `insert` query on a table for that table. This setting will override the global setting.

Table name of the target database

4. `map` (optional)

`map` is an array of text separated by colons. On the left side of the colon are the column names in the target table and database while on the right side of the colon are the column names in the source table and database. 

5. `pre_import_script` (optional)

`pre_import_script` is an array of queries that will be executed before the data import begins. `pre_import_script` is usually used to clear data from a table and reset all sequence or auto increment values ​​from the target table.

6. `post_import_script` (optional)

`post_import_script` is an array of queries that will be executed after the data import is complete. `post_import_script` can be used for various purposes such as fixing some data on the target table including taking values ​​from other tables. Therefore post_script must be run after all tables have been successfully imported.

**Import Script**

File `import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$fp = fopen(__DIR__.'/db.sql', 'w');
fclose($fp);
$sql = PicoDatabaseUtilMySql::importData($config, function($sql, $source, $target){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
});
```

**Executing Script**

```bash
php import.php
```

MagicObject will create a database query that is saved into a file named `db.sql`. The data is taken from the `database_source` but the table and column names have been adjusted to the `database_target`. This query can be run in the `database_target`. If you want to empty a table before importing data, you can add a pre_import_script to each table. Keep in mind that all pre_import_scripts will be executed before MagicObject starts importing data.

If the database is too complex, users can use the PicoDatabaseUtilMySql::autoConfigureImportData() method to create a configuration template to avoid missing table and column names. Users simply specify the source database and the target database. MagicObject will check the tables and columns in both databases. If a table exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this table. In the same table, if a column exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this column.

Here is an example of how to create a database import configuration template.

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
  charset: utf8
database_source:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
  charset: utf8
maximum_record: 100
```

**Import Template Script**

File `configure-import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

PicoDatabaseUtilMySql::autoConfigureImportData($config);
file_put_contents('import.yml', $config->dumpYaml(0, 2));
```

**Executing Script**

```bash
php configure-import.php
```
