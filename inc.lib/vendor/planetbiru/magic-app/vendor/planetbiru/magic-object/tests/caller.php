<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__)) . "/test.yml", false, true, true);
$databaseCredential->getDatabase()->setDatabaseName("sipro");
$database = new PicoDatabase($databaseCredential->getDatabase(), null, function($sql){
    echo $sql.";\r\n\r\n";
});
$database->connect();

class Supervisor extends MagicObject
{
    /**
     * Native query 1
     *
     * This method will return null.
     *
     * @param string[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return void
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native1($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 2
     *
     * This method will return the number of affected rows.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return int
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native2($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 3
     *
     * This method will return a single result as an object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native3($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 4
     *
     * This method will return an array of stdClass objects.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native4($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 5
     *
     * This method will return an associative array.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return array
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native5($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 6
     *
     * This method will return a JSON-encoded string.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return string
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native6($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 7
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 8
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native8($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 9
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native9($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 10
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native10($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 11
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native11($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
    
    /**
     * Native query 13
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param DateTime[] $timeCreate The date and time when data is created
     * @return self[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.waktu_buat in :timeCreate 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native13($timeCreate, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

$obj = new Supervisor(null, $database);


$native1 = $obj->native1(1, true);

$native2 = $obj->native2(1, true);
echo "\r\nnative2:\r\n";
print_r($native2);

$native3 = $obj->native3(1, true);
echo "\r\nnative3:\r\n";
print_r($native3);

$native4 = $obj->native4(1, true);
echo "\r\nnative4:\r\n";
print_r($native4);

$native5 = $obj->native5(1, true);
echo "\r\nnative5:\r\n";
print_r($native5);

$native6 = $obj->native6(1, true);
echo "\r\nnative6:\r\n";
print_r($native6);

$native7 = $obj->native7([1, 2, 3, 4], true);
echo "\r\nnative7:\r\n";
print_r($native7->fetchAll(PDO::FETCH_ASSOC));

$native8 = $obj->native8(1, true);
echo "\r\nnative8:\r\n";
print_r($native8);

$native9 = $obj->native9(1, true);
echo "\r\nnative9:\r\n";
print_r($native9);

$native10 = $obj->native10(1, true);
echo "\r\nnative10:\r\n";
print_r($native10);

$native11 = $obj->native11(1, true);
echo "\r\nnative11:\r\n";
print_r($native11);


// For the MagicObject return type, users can utilize the features of the MagicObject except for interacting with the database again because native queries are designed for a different purpose.

echo "Alamat: " . $native8->getTelepon() . "\r\n";
echo "Alamat: " . $native9[0]->getTelepon() . "\r\n";
echo "Alamat: " . $native10->getTelepon() . "\r\n";
echo "Alamat: " . $native11[0]->getTelepon() . "\r\n";

$native13 = $obj->native13([new DateTime('2023-03-27 15:23:47', new DateTimeZone($databaseCredential->getDatabase()->getTimeZone()))], true);
echo "\r\nnative13:\r\n";
print_r($native13);