<?php
namespace MagicObject\Database;

/**
 * Query builder
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseQueryBuilder // NOSONAR
{
	/**
	 * Buffer
	 *
	 * @var string
	 */
	private $buffer = "";

	/**
	 * Has limit and offset
	 *
	 * @var boolean
	 */
	private $limitOffset = false;

	/**
	 * Limit
	 *
	 * @var integer
	 */
	private $limit = 0;

	/**
	 * Offset
	 *
	 * @var integer
	 */
	private $offset = 0;

	/**
	 * Database type
	 *
	 * @var string
	 */
	private $databaseType = "mysql";

	/**
	 * Flag that value has been set
	 *
	 * @var boolean
	 */
	private $hasValues = false;

	/**
	 * Database
	 *
	 * @param PicoDatabase|string $databaseType Database type. See PicoDatabaseType class
	 */
	public function __construct($databaseType)
	{
		if($databaseType instanceof PicoDatabase)
		{
			$databaseType->getDatabaseType();
		}
		else if(is_string($databaseType))
		{
			$this->databaseType = $databaseType;
		}
	}

	/**
	 * Get the value of databaseType
	 */
	public function getDatabaseType()
	{
		return $this->databaseType;
	}

	/**
	 * Check if database type is MySQL or MariaDB
	 *
	 * @return boolean
	 */
	public function isMySql()
	{
		return strcasecmp($this->databaseType, PicoDatabaseType::DATABASE_TYPE_MYSQL) == 0 || strcasecmp($this->databaseType, PicoDatabaseType::DATABASE_TYPE_MARIADB) == 0;
	}

	/**
	 * Check if database type is PostgreSQL
	 *
	 * @return boolean
	 */
	public function isPgSql()
	{
		return strcasecmp($this->databaseType, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) == 0;
	}

	/**
	 * Empty buffer, limit and offset
	 *
	 * @return self
	 */
	public function newQuery()
	{
		$this->buffer = "";
		$this->limitOffset = false;
		$this->hasValues = false;
		return $this;
	}

	/**
	 * Create insert statement
	 *
	 * @return self
	 */
	public function insert()
	{
		$this->buffer = "insert \r\n";
		return $this;
	}

	/**
	 * Create into statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function into($query)
	{
		$this->buffer .= "into $query\r\n";
		return $this;
	}

	/**
	 * Create field statement
	 *
	 * @param mixed $query SQL parameter
	 * @return self
	 */
	public function fields($query)
	{
		if(is_array($query))
		{
			$this->buffer .= "(".implode(", ", $query).") \r\n";
		}
		else
		{
			$this->buffer .= "$query \r\n";
		}
		return $this;
	}

	/**
	 * Create values statement
	 *
	 * @param mixed $query SQL parameter
	 * @return self
	 */
	public function values($query)
	{
		$count = func_num_args();
		$isArray = is_array($query) && $count == 1;
		$values = "";
		if($isArray)
		{
			$vals = array();
			foreach($query as $key=>$val)
			{
				$vals[$key] = $this->escapeValue($val);
			}
			$buffer = "(".implode(", ", $vals).")";
			$values = $buffer;
		}
		else
		{
			if($count > 1)
			{
				$params = array();
				for($i = 0; $i<$count; $i++)
				{
					$params[] = func_get_arg($i);
				}
				$buffer = $this->createMatchedValue($params);
				$values = $buffer;
			}
			else
			{
				$values = $query;
			}
		}

		if($this->hasValues)
		{
			$this->buffer .= ",\r\n$values";
		}
		else
		{
			$this->buffer .= "values $values";
		}

		$this->hasValues = true;
		return $this;
	}

	/**
	 * Create select statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function select($query = "")
	{
		$this->buffer .= "select $query\r\n";
		return $this;
	}

	/**
	 * Create alias statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function alias($query)
	{
		$this->buffer .= "as $query\r\n";
		return $this;
	}

	/**
	 * Create delete statement
	 *
	 * @return self
	 */
	public function delete()
	{
		$this->buffer .= "delete \r\n";
		return $this;
	}

	/**
	 * Create from statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function from($query)
	{
		$this->buffer .= "from $query \r\n";
		return $this;
	}

	/**
	 * Create join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function join($query)
	{
		$this->buffer .= "join $query \r\n";
		return $this;
	}

	/**
	 * Create inner join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function innerJoin($query)
	{
		$this->buffer .= "inner join $query \r\n";
		return $this;
	}

	/**
	 * Create outer join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function outerJoin($query)
	{
		$this->buffer .= "outer join $query \r\n";
		return $this;
	}

	/**
	 * Create left outer join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function leftOuterJoin($query)
	{
		$this->buffer .= "left outer join $query \r\n";
		return $this;
	}

	/**
	 * Create left join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function leftJoin($query)
	{
		$this->buffer .= "left join $query \r\n";
		return $this;
	}

	/**
	 * Create right join statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function rightJoin($query)
	{
		$this->buffer .= "right join $query \r\n";
		return $this;
	}

	/**
	 * Create on statement
	 *
	 * @param mixed $query SQL parameter
	 * @return self
	 */
	public function on($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "on $buffer \r\n";
		}
		else
		{
			$this->buffer .= "on $query \r\n";
		}
		return $this;
	}

	/**
	 * Create update statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function update($query)
	{
		$this->buffer .= "update $query \r\n";
		return $this;
	}

	/**
	 * Create set statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function set($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "set $buffer \r\n";
		}
		else
		{
			$this->buffer .= "set $query \r\n";
		}
		return $this;
	}

	/**
	 * Create where statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function where($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "where $buffer \r\n";
		}
		else
		{
			$this->buffer .= "where $query \r\n";
		}
		return $this;
	}

	/**
	 * Create match value
	 *
	 * @param array $args Arguments
	 * @return string
	 */
	public function createMatchedValue($args)
	{
		$result = "";
		if(count($args) > 1)
		{
			$format = $args[0];
			$formats = explode('?', $format);
			$len = count($args) - 1;
			$values = array();
			for($i = 0; $i<$len; $i++)
			{
				$j = $i + 1;
				$values[$i] = $this->escapeValue($args[$j]);
			}
			for($i = 0; $i<$len; $i++)
			{
				$result .= $formats[$i];
				if($j <= $len)
				{
					$result .= $values[$i];
				}
			}
			$result .= $formats[$i];
		}
		return $result;
	}

	/**
	 * Create insert query
	 *
	 * @param string $table Table name
	 * @param array $data Data to be inserted
	 * @return string
	 */
	public function createInsertQuery($table, $data)
	{
		$fileds = array_keys($data);
		$values = array_values($data);

		$valuesFixed = array();
		foreach($values as $value)
		{
			$valuesFixed[] = $this->escapeValue($value);
		}

		$fieldList = implode(", ", $fileds);
		$valueList = implode(", ", $valuesFixed);
		return "insert into $table \r\n(".$fieldList.")\r\nvalues(".$valueList.")\r\n";
	}

	/**
	 * Create update query
	 *
	 * @param string $table Table name
	 * @param array $data Data to be upadted
	 * @param array $primaryKey Primary keys
	 * @return string
	 */
	public function createUpdateQuery($table, $data, $primaryKey)
	{
		$set = array();
		$condition = array();
		foreach($data as $field=>$value)
		{
			$set[] = $field . " = ". $this->escapeValue($value);
		}

		foreach($primaryKey as $field=>$value)
		{
			if($value === null)
			{
				$condition[] = $field . " is null ";
			}
			else
			{
				$condition[] = $field . " = ". $this->escapeValue($value);
			}
		}

		$sets = implode(", ", $set);
		$where = implode(" and ", $condition);
		return "update $table \r\nset $sets \r\nwhere $where\r\n";
	}

	/**
	 * Escape value
	 * @var mixed $value Value to be escaped
	 * @return string
	 */
	public function escapeValue($value)
	{
		if($value === null)
		{
			// null
			$ret = 'null';
		}
		else if(is_string($value))
		{
			// escape the value
			$ret = "'".$this->escapeSQL($value)."'";
		}
		else if(is_bool($value))
		{
			// true or false
			$ret = $value?'true':'false';
		}
		else if(is_numeric($value))
		{
			// convert number to string
			$ret = $value."";
		}
		else if(is_array($value) || is_object($value))
		{
			// encode to JSON and escapethe value
			$ret = $this->implodeValues($value);
		}
		else
		{
			// force convert to string and escapethe value
			$ret = "'".$this->escapeSQL($value)."'";
		}
		return $ret;
	}

	/**
	 * Convert array to list
	 *
	 * @param array $values Values given
	 * @return string
	 */
	private function implodeValues($values)
	{
		foreach($values as $key=>$value)
		{
			$values[$key] = $this->escapeValue($value);
		}
		return implode(", ", $values);
	}

	/**
	 * Create having statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function having($query)
	{
		$count = func_num_args();
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
			$this->buffer .= "having $buffer \r\n";
		}
		else if(!empty($query))
		{
			$this->buffer .= "having $query \r\n";
		}
		return $this;
	}

	/**
	 * Create order by statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function orderBy($query)
	{
		if(!empty($query))
		{
			$this->buffer .= "order by $query \r\n";
		}
		return $this;
	}

	/**
	 * Create goup by statement
	 *
	 * @param string $query SQL parameter
	 * @return self
	 */
	public function groupBy($query)
	{
		if(!empty($query))
		{
			$this->buffer .= "group by $query \r\n";
		}
		return $this;
	}

	/**
	 * Set limit
	 *
	 * @param integer $limit Result limit
	 * @return self
	 */
	public function limit($limit)
	{
		$this->limitOffset = true;
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set offset
	 *
	 * @param integer $offset Result offset
	 * @return self
	 */
	public function offset($offset)
	{
		$this->limitOffset = true;
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Create lock tables statement
	 *
	 * @param string $tables Table name
	 * @return string|null
	 */
	public function lockTables($tables)
	{
		if($this->isMySql())
		{
			return "lock tables $tables";
		}
		if($this->isPgSql())
		{
			return "lock tables $tables";
		}
		return null;
	}

	/**
	 * Create unlock tables statement
	 *
	 * @return string|null
	 */
	public function unlockTables()
	{
		if($this->isMySql())
		{
			return "unlock tables";
		}
		if($this->isPgSql())
		{
			return "unlock tables";
		}
		return null;
	}

	/**
	 * Create start transaction statement
	 *
	 * @return string|null
	 */
	public function startTransaction()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "start transaction";
		}
		return null;
	}

	/**
	 * Create commit statement
	 *
	 * @return string|null
	 */
	public function commit()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "commit";
		}
		return null;
	}

	/**
	 * Create rollback statement
	 *
	 * @return string|null
	 */
	public function rollback()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "rollback";
		}
		return null;
	}

	/**
	 * Create execute function statement
	 *
	 * @param string $name Function name
	 * @param string $params Function parameters
	 * @return string|null
	 */
	public function executeFunction($name, $params)
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "select $name($params)";
		}
		return null;
	}

	/**
	 * Create execute procedure statement
	 *
	 * @param string $name Procedure name
	 * @param string $params Procedure parameters
	 * @return string|null
	 */
	public function executeProcedure($name, $params)
	{
		if($this->isMySql())
		{
			return "call $name($params)";
		}
		if($this->isPgSql())
		{
			return "select $name($params)";
		}
		return null;
	}

	/**
	 * Create last ID statement
	 *
	 * @return self
	 */
	public function lastID()
	{
		if($this->isMySql())
		{
			$this->buffer .= "last_insert_id()\r\n";
		}
		if($this->isPgSql())
		{
			$this->buffer .= "lastval()\r\n";
		}
		return $this;
	}

	/**
	 * Create current date statement
	 *
	 * @return string|null
	 */
	public function currentDate()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "CURRENT_DATE";
		}
		return null;
	}

	/**
	 * Create current time statement
	 *
	 * @return string
	 */
	public function currentTime()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "CURRENT_TIME";
		}
		return null;
	}

	/**
	 * Create current date time statement
	 *
	 * @return string|null
	 */
	public function currentTimestamp()
	{
		if($this->isMySql() || $this->isPgSql())
		{
			return "CURRENT_TIMESTAMP";
		}
		return null;
	}

	/**
	 * Create now statement
	 *
	 * @param integer $precission Decimal precission of second
	 * @return string
	 */
	public function now($precission = 0)
	{
		if($precission > 0)
		{
			if($precission > 6)
			{
				$precission = 6;
			}
			return "now($precission)";
		}
		else
		{
			return "now()";
		}
	}

	/**
	 * Escape SQL
	 *
	 * @param string $query SQL parameter
	 * @return string
	 */
	public function escapeSQL($query)
	{
		if(stripos($this->databaseType, PicoDatabaseType::DATABASE_TYPE_MYSQL) !== false || stripos($this->databaseType, PicoDatabaseType::DATABASE_TYPE_MARIADB) !== false)
		{
			return str_replace(array("\r", "\n"), array("\\r", "\\n"), addslashes($query));
		}
		if(stripos($this->databaseType, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL) !== false)
		{
			return str_replace(array("\r", "\n"), array("\\r", "\\n"), $this->replaceQuote($query));
		}
		else
		{
			return $query;
		}
	}

	/**
	 * Replace quote
	 * @param string $query SQL parameter
	 * @return string
	 */
	public function replaceQuote($query)
	{
		return str_replace("'", "''", $query);
	}

	/**
	 * Add query parameter
	 *
	 * @param string $query SQL parameter
	 * @return string
	 */
	public function addQueryParameters($query)
	{
		$count = func_num_args();
		$buffer = "";
		if($count > 1)
		{
			$params = array();
			for($i = 0; $i<$count; $i++)
			{
				$params[] = func_get_arg($i);
			}
			$buffer = $this->createMatchedValue($params);
		}
		else
		{
			$buffer = $query;
		}
		return $buffer;
	}

	/**
	 * Get SQL query
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Get SQL query
	 *
	 * @return string
	 */
	public function toString()
	{
		$sql = $this->buffer;
		if($this->limitOffset)
		{
			if($this->isMySql())
			{
				$sql .= "limit ".$this->offset.", ".$this->limit;
			}
			else if($this->isPgSql())
			{
				$sql .= "limit ".$this->limit." offset ".$this->offset;
			}
		}
		return $sql;
	}

}
