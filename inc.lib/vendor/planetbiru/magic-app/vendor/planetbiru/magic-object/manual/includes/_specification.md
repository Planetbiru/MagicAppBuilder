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

**Always TRUE Specification**

In certain scenarios, developers may need to update or delete all records in a database table. To achieve this, it is common to use a native SQL query with the condition `WHERE 1 = 1`, which effectively applies no filtering and includes all rows. This approach can also be used when retrieving all records from a table.

To simplify this pattern and make it reusable in a more structured way, MagicObject provides a built-in utility method:

`PicoSpecification::alwaysTrue()`

This method returns a specification that translates to a SQL condition that is always true. It is particularly useful as a default or fallback filter when no specific criteria are applied.

**Use Case Example:**

```php
$specs = PicoSpecification::alwaysTrue();

$user = new UserMin(null, $database);
try
{
$pageData = $user->findAll($specs);
}
catch(Exception $e)
{
    // Do nothing
}
```

In this example, the `findAll()` method retrieves all user records from the database without applying any filtering conditions, thanks to the `alwaysTrue()` specification.

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
