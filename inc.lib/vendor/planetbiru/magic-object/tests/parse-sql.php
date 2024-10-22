<?php

class TableParser {
    private $sql;

    public function __construct($sql) {
        $this->sql = $sql;
    }

    public function parse() {
        $tables = [];

        // Match CREATE TABLE statement
        preg_match('/CREATE TABLE `?(\w+)`? \((.*?)\)/is', $this->sql, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $tableName = $matches[1];
            $columns = $this->parseColumns($matches[2]);
            $tables[$tableName] = $columns;
        }

        return $tables;
    }

    private function parseColumns($columnsString) {
        $columns = [];
        // Split columns by commas not inside parentheses
        $columnDefinitions = preg_split('/,\s*(?![^()]*\))/', $columnsString);
        foreach ($columnDefinitions as $definition) {
            $definition = trim($definition);
            
            // Regex to capture column name, data type, and attributes
            preg_match('/`?(\w+)`?\s+([\w\(\) ]+)(.*?)$/', $definition, $colMatches);
            if ($colMatches) {
                $columnName = $colMatches[1];
                $dataType = trim($colMatches[2]);
                $attributes = trim($colMatches[3]);

                $columns[$columnName] = [
                    'type' => $dataType,
                    'attributes' => $this->parseAttributes($attributes)
                ];
            }
        }

        return $columns;
    }

    private function parseAttributes($attributes) {
        $attrArray = [];
        // Split attributes by spaces
        $parts = preg_split('/\s+/', trim($attributes));
        foreach ($parts as $part) {
            if (!empty($part)) {
                $attrArray[] = $part;
            }
        }
        return $attrArray;
    }
}

// Example usage
$sqlQuery = "
CREATE TABLE article (
  article_id varchar(40) NOT NULL,
  type varchar(20) DEFAULT NULL,
  title text,
  content longtext,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  draft tinyint(1) DEFAULT '1',
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
";

$parser = new TableParser($sqlQuery);
$tableInfo = $parser->parse();

print_r($tableInfo);
