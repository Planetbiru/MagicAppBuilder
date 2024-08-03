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

Parameters:

- PicoPage|PicoLimit|array $page
- PicoSortable|array $sortable

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


Example:


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
