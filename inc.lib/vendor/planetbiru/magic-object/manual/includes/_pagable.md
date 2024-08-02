### Pageable


Constructor:

Parameters:

- PicoPage|PicoLimit|array $page
- PicoSortable|array $sortable

Example:

1. `$pageable = new Pageable(array(0, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`
2. `$pageable = new Pageable(new PicoPage(0, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`
3. `$pageable = new Pageable(array(0, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`
4. `$pageable = new Pageable(new PicoPage(0, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`