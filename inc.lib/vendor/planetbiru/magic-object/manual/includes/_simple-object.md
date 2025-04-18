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
