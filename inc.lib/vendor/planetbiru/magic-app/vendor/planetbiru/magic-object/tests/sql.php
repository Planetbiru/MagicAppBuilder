<?php
// SQL statement
$sql = "CREATE TABLE user_type (
    user_type_id varchar(50) NOT NULL,
    name varchar(255) DEFAULT NULL,
    admin tinyint(1) DEFAULT '0',
    sort_order int(11) DEFAULT NULL,
    time_create timestamp NULL DEFAULT NULL,
    time_edit timestamp NULL DEFAULT NULL,
    admin_create varchar(40) DEFAULT NULL,
    admin_edit varchar(40) DEFAULT NULL,
    ip_create varchar(50) DEFAULT NULL,
    ip_edit varchar(50) DEFAULT NULL,
    active tinyint(1) DEFAULT '1',
    PRIMARY KEY (user_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Define the Table and Column classes
class Column {
    public $name;
    public $type;
    public $length;
    public $null;
    public $default;

    public function __construct($name, $type, $length = null, $null = null, $default = null) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->null = $null;
        $this->default = $default;
    }
}

class Table {
    public $name;
    public $columns = [];
    public $primaryKey;

    public function __construct($name) {
        $this->name = $name;
    }

    public function addColumn(Column $column) {
        $this->columns[] = $column;
    }

    public function setPrimaryKey($column) {
        $this->primaryKey = $column;
    }
}

// Function to parse the SQL statement
function parseCreateTable($sql) {
    // Extract table name
    preg_match("/CREATE TABLE (\w+)/i", $sql, $matches);
    $tableName = $matches[1];
    
    // Extract columns
    preg_match_all("/(\w+)\s+(\w+)(\((\d+)\))?\s*(NULL|NOT NULL)?\s*(DEFAULT\s+([^,]+))?/i", $sql, $matches, PREG_SET_ORDER);
    
    // Create Table object
    $table = new Table($tableName);
    print_r($matches);

    foreach ($matches as $match) {
        
        if (strtoupper($match[1]) == 'PRIMARY') {
            // Set primary key
            preg_match('/(PRIMARY|UNIQUE) KEY[a-zA-Z_0-9\s]+\\(([a-zA-Z_0-9,\\s]+)\\)/i', $match[0], $pkMatches);
            
            if(isset($pkMatches) && isset($pkMatches[1]))
            {
                $table->setPrimaryKey($pkMatches[1]);
            }
        } else {
            // Add column
            $name = $match[1];
            $type = $match[2];
            if(!strcasecmp($name, 'create') == 0 || !strcasecmp($type, 'table') == 0)
            {
                $length = isset($match[4]) ? $match[4] : null;
                $null = isset($match[5]) ? $match[5] : null;
                $default = isset($match[7]) ? $match[7] : null;
                $column = new Column($name, $type, $length, $null, $default);
                $table->addColumn($column);
            }
        }
    }
    
    return $table;
}

// Parse the SQL statement
$table = parseCreateTable($sql);

// Print the table object
//print_r($table);
