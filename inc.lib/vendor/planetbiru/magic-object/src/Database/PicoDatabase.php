<?php

namespace MagicObject\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use MagicObject\Exceptions\InvalidDatabaseConfiguration;
use MagicObject\Exceptions\NullPointerException;
use MagicObject\SecretObject;
use stdClass;

/**
 * Database connection for MagicObject
 * Developer: Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabase //NOSONAR
{
	const QUERY_INSERT = "insert";
	const QUERY_UPDATE = "update";
	const QUERY_DELETE = "delete";
	const QUERY_TRANSACTION = "transaction";
	const DATABASE_NONECTION_IS_NULL = "Database connection is null";

	/**
	 * Database credential
	 *
	 * @var SecretObject
	 */
	private $databaseCredentials;

	/**
	 * Indicate that database is connected or not
	 *
	 * @var boolean
	 */
	private $connected = false;

	/**
	 * Autocommit
	 *
	 * @var boolean
	 */
	private $autocommit = true;

	/**
	 * Database connection
	 *
	 * @var PDO
	 */
	private $databaseConnection;

	/**
	 * Database type
	 * @var string
	 */
	private $databaseType = "";

	/**
	 * Callback function when execute query when modify data and execute function and procedure
	 *
	 * @var callable
	 */
	private $callbackExecuteQuery = null;

	/**
	 * Callback function when execute query
	 *
	 * @var callable
	 */
	private $callbackDebugQuery = null;

	/**
	 * Constructor
	 *
	 * @param SecretObject $databaseCredentials Database credentials
	 * @param callable $callbackExecuteQuery Callback when execute query that modify data. Parameter 1 is SQL, parameter 2 is one of query type (PicoDatabase::QUERY_INSERT, PicoDatabase::QUERY_UPDATE, PicoDatabase::QUERY_DELETE, PicoDatabase::QUERY_TRANSACTION)
	 * @param callable $callbackDebugQuery Callback when execute all queries. Parameter 1 is SQL
	 */
	public function __construct($databaseCredentials, $callbackExecuteQuery = null, $callbackDebugQuery = null)
	{
		$this->databaseCredentials = $databaseCredentials;
		if($callbackExecuteQuery != null && is_callable($callbackExecuteQuery))
		{
			$this->callbackExecuteQuery = $callbackExecuteQuery;
		}
		if($callbackDebugQuery != null && is_callable($callbackDebugQuery))
		{
			$this->callbackDebugQuery = $callbackDebugQuery;
		}
	}

	/**
	 * Connect to database
	 *
	 * @param boolean $withDatabase Flag to select database when connected
	 * @return boolean true if success and false if failed
	 */
	public function connect($withDatabase = true)
	{
		$databaseTimeZone = $this->databaseCredentials->getTimeZone();
		if($databaseTimeZone != null && !empty($databaseTimeZone))
		{
			date_default_timezone_set($this->databaseCredentials->getTimeZone());
		}
		$timeZoneOffset = date("P");
		$connected = false;
		try
		{
			$connectionString = $this->constructConnectionString($withDatabase);
			if(!$this->databaseCredentials->issetUsername())
			{
				throw new InvalidDatabaseConfiguration("Database username may not be empty. Please check your database configuration!");
			}

			$initialQueries = "SET time_zone = '$timeZoneOffset';";
			if($this->databaseCredentials->getDriver() == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL && $this->databaseCredentials->getDatabaseShema() != null  && $this->databaseCredentials->getDatabaseShema() != "")
			{
				$initialQueries .= "SET search_path TO ".$this->databaseCredentials->getDatabaseShema();
			}

			$this->databaseType = $this->databaseCredentials->getDriver();
			$this->databaseConnection = new PDO(
				$connectionString,
				$this->databaseCredentials->getUsername(),
				$this->databaseCredentials->getPassword(),
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => $initialQueries,
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::MYSQL_ATTR_FOUND_ROWS => true
				)
			);

			$connected = true;
			$this->connected = $connected;
		}
		catch (Exception $e)
		{
			throw new PDOException($e->getMessage(), intval($e->getCode()));
		}
		return $connected;
	}

	/**
	 * Create connection string
	 *
	 * @param boolean $withDatabase Flag to select database when connected
	 * @return string
	 * @throws InvalidDatabaseConfiguration
	 */
	private function constructConnectionString($withDatabase = true)
	{
		$emptyDriver = !$this->databaseCredentials->issetDriver();
		$emptyHost = !$this->databaseCredentials->issetHost();
		$emptyPort = !$this->databaseCredentials->issetPort();
		$emptyName = !$this->databaseCredentials->issetDatabaseName();
		$emptyValue = "";
		$emptyValue .= $emptyDriver ? "{driver}" : "";
		$emptyValue .= $emptyHost ? "{host}" : "";
		$emptyValue .= $emptyPort ? "{port}" : "";
		$invalidParam1 = $emptyDriver || $emptyHost || $emptyPort;

		if($withDatabase)
		{
			if(
				$invalidParam1
				||
				$emptyName
			)
			{
				$emptyValue .= $emptyName ? "{database_name}" : "";
				throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
			}
			return $this->databaseCredentials->getDriver() . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort()) . '; dbname=' . $this->databaseCredentials->getDatabaseName();
		}
		else
		{
			if($invalidParam1)
			{
				throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
			}
			return $this->databaseCredentials->getDriver() . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort());
		}
	}

	/**
	 * Disconnect from database
	 *
	 * @return self
	 */
	public function disconnect()
	{
		$this->databaseConnection = null;
		return $this;
	}

	/**
	 * Set time zone offset
	 * Time zone offset is difference to Greenwich time (GMT) with colon between hours and minutes. See date("P") on PHP manual
	 *
	 * @param string $timeZoneOffset Client time zone
	 * @return self
	 */
	public function setTimeZoneOffset($timeZoneOffset)
	{
		$sql = "SET time_zone='$timeZoneOffset';";
		$this->execute($sql);
		return $this;
	}

	/**
	 * Change database
	 *
	 * @param string $databaseName Database name
	 * @return self
	 */
	public function useDatabase($databaseName)
	{
		$sql = "USE $databaseName;";
		$this->execute($sql);
		return $this;
	}

	/**
	 * Set autocommit ON of OFF
	 * When it set to OFF, user MUST call commit or rollback manualy. Default action is rollback
	 *
	 * @param boolean $autocommit Flag autocommit
	 * @return boolean
	 */
	public function setAudoCommit($autocommit)
	{
		$this->autocommit = $autocommit;
		return $this->databaseConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->autocommit ? 1 : 0);
	}

	/**
	 * Commit
	 *
	 * @return boolean
	 */
	public function commit()
	{
		return $this->databaseConnection->commit();
	}

	/**
	 * Rollback
	 *
	 * @return boolean
	 */
	public function rollback()
	{
		return $this->databaseConnection->rollback();
	}

	/**
	 * Get database connection
	 * @return PDO Represents a connection between PHP and a database server.
	 */
	public function getDatabaseConnection()
	{
		return $this->databaseConnection;
	}

	/**
	 * Execute query
	 *
	 * @param string $sql SQL to be executed
	 * @return PDOStatement|false
	 * @throws PDOException
	 */
	public function query($sql)
	{
		return $this->executeQuery($sql);
	}

	/**
	 * Fetch result
	 *
	 * @param string $sql SQL to be executed
	 * @param integer $tentativeType Tentative type
	 * @param array $defaultValue Default value
	 * @return array|object|stdClass|null
	 */
	public function fetch($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null)
	{
		if($this->databaseConnection == null)
		{
			throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
		}
		$result = array();
		$this->executeDebug($sql);
		$stmt = $this->databaseConnection->prepare($sql);
		try
		{
			$stmt->execute();
			$result = $stmt->rowCount() > 0 ? $stmt->fetch($tentativeType) : $defaultValue;
		}
		catch (PDOException $e)
		{
			$result = $defaultValue;
		}
		return $result;
	}

	/**
	 * Check if record is exists
	 *
	 * @param string $sql SQL to be executed
	 * @return boolean
	 */
	public function isRecordExists($sql)
	{
		if($this->databaseConnection == null)
		{
			throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
		}
		$this->executeDebug($sql);
		$stmt = $this->databaseConnection->prepare($sql);
		try
		{
			$stmt->execute();
			return $stmt->rowCount() > 0;
		}
		catch (PDOException $e)
		{
			throw new PDOException($e->getMessage(), intval($e->getCode()));
		}
	}

	/**
	 * Fetch all result
	 *
	 * @param string $sql SQL to be executed
	 * @param integer $tentativeType Tentative type
	 * @param array $defaultValue Default value
	 * @return array|null
	 */
	public function fetchAll($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null)
	{
		if($this->databaseConnection == null)
		{
			throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
		}
		$result = array();
		$this->executeDebug($sql);
		$stmt = $this->databaseConnection->prepare($sql);
		try
		{
			$stmt->execute();
			$result = $stmt->rowCount() > 0 ? $stmt->fetchAll($tentativeType) : $defaultValue;
		}
		catch (PDOException $e)
		{
			$result = $defaultValue;
		}
		return $result;
	}

	/**
	 * Execute query without return anything
	 * 
	 * @param string $sql Query string to be executed
	 */
	public function execute($sql)
	{
		if($this->databaseConnection == null)
		{
			throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
		}
		$this->executeDebug($sql);
		$stmt = $this->databaseConnection->prepare($sql);
		try
		{
			$stmt->execute();
		}
		catch (PDOException $e)
		{
			// Do nothing
		}
	}

	/**
	 * Execute query
	 * 
	 * @param string $sql Query string to be executed
	 * @return PDOStatement|boolean
	 */
	public function executeQuery($sql)
	{
		if($this->databaseConnection == null)
		{
			throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
		}
		$this->executeDebug($sql);
		$stmt = $this->databaseConnection->prepare($sql);
		try
		{
			$stmt->execute();
		}
		catch (PDOException $e)
		{
			throw new PDOException($e->getMessage(), intval($e->getCode()));
		}
		return $stmt;
	}

	/**
	 * Execute query
	 * 
	 * @param string $sql Query string to be executed
	 * @return PDOStatement|boolean
	 */
	public function executeInsert($sql)
	{
		$stmt = $this->executeQuery($sql);
		$this->executeCallback($sql, self::QUERY_INSERT);
		return $stmt;
	}

	/**
	 * Execute update query
	 * 
	 * @param string $sql Query string to be executed
	 * @return PDOStatement|boolean
	 */
	public function executeUpdate($sql)
	{
		$stmt = $this->executeQuery($sql);
		$this->executeCallback($sql, self::QUERY_UPDATE);
		return $stmt;
	}

	/**
	 * Execute delete query
	 * 
	 * @param string $sql Query string to be executed
	 * @return PDOStatement|boolean
	 */
	public function executeDelete($sql)
	{
		$stmt = $this->executeQuery($sql);
		$this->executeCallback($sql, self::QUERY_DELETE);
		return $stmt;
	}

	/**
	 * Execute transaction query
	 * 
	 * @param string $sql Query string to be executed
	 * @return PDOStatement|boolean
	 */
	public function executeTransaction($sql)
	{
		$stmt = $this->executeQuery($sql);
		$this->executeCallback($sql, self::QUERY_TRANSACTION);
		return $stmt;
	}

	/**
	 * Execute callback query function
	 *
	 * @param string $query SQL to be executed
	 * @param string $type Query type
	 * @return void
	 */
	private function executeCallback($query, $type)
	{
		if($this->callbackExecuteQuery != null && is_callable($this->callbackExecuteQuery))
		{
			call_user_func($this->callbackExecuteQuery, $query, $type);
		}
	}

	/**
	 * Execute debug query function
	 *
	 * @param string $query SQL to be executed
	 * @return void
	 */
	private function executeDebug($query)
	{
		if($this->callbackDebugQuery != null && is_callable($this->callbackDebugQuery))
		{
			call_user_func($this->callbackDebugQuery, $query);
		}
	}

	/**
	 * Generate 20 bytes unique ID
	 * 
	 * @return string 20 bytes
	 */
	public function generateNewId()
	{
		$uuid = uniqid();
		if ((strlen($uuid) % 2) == 1) {
			$uuid = '0' . $uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));
		return sprintf('%s%s', $uuid, $random);
	}

	/**
	 * Get last insert ID
	 *
	 * @param string $name Sequence name (eg. PostgreSQL)
	 * @return string|false
	 */
	public function lastInsertId($name = null)
	{
		return $this->databaseConnection->lastInsertId($name);
	}

	/**
	 * Get the value of databaseCredentials
	 * 
	 * @return SecretObject
	 */
	public function getDatabaseCredentials()
	{
		return $this->databaseCredentials;
	}

	/**
	 * Get indication that database is connected or not
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		return $this->connected;
	}

	/**
	 * Get database type
	 *
	 * @return string
	 */
	public function getDatabaseType()
	{
		return $this->databaseType;
	}

	/**
	 * Magic method to debug object. This method also prevent PHP sho show its attribute when it converted to a string
	 *
	 * @return string
	 */
	public function __toString()
	{
		$val = new stdClass;
		$val->databaseType = $this->databaseType;
		$val->autocommit = $this->autocommit;
		$val->connected = $this->connected;
		return json_encode($val);
	}
}