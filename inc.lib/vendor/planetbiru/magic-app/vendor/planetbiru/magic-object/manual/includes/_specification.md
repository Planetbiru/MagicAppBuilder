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

- PicoSpecification|PicoPredicate|array

2. addOr

Parameters:

- PicoSpecification|PicoPredicate|array

We can form specifications in an unlimited number of stages. Note that users need to simplify the logic before implementing it into the specification.

**PicoPredicate**

Constructor:

Parameters:

- string $field = null
- mixed $value = null

If the field is given a value, the constructor will call the `equals` method with the `field` and `value` from the constructor. This will make it easier for users to create specifications in a line of code.

Methods:

- equals
- isNull
- notEquals
- isNotNull
- like
- notLike
- in
- notIn
- lessThan
- greaterThan
- lessThanOrEquals
- greaterThanOrEquals

Static Methods:
- getInstance
- generateLikeStarts
- generateLikeEnds
- generateLikeContains
- functionUpper
- functionLower
- functionAndValue

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

When a user passes an array as a parameter to the `addAnd` and `addOr` methods, MagicObject will convert it to an instance of `PicoPredicate` with `equals` comparison. If using `array` is easier, feel free to use it but it is recommended to use `PicoPredicate` so that it can be used directly by MagicObject.

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
