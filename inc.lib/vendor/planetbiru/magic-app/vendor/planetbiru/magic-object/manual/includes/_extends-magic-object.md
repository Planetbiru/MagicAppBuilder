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
