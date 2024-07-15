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

Construcor:

Parameters:

- string $field = null
- mixed $value = null

If the field is given a value, the constructor will call the `equals` method with the `field` and `value` from the constructor. This will make it easier for users to create specifications in a line of code.

Methods:

equals
isNull
notEquals
isNotNull
like
notLike
lessThan
greaterThan
lessThanOrEquals
greaterThanOrEquals

Static Methods:
generateLeftLike
generateCenterLike
generateRightLike