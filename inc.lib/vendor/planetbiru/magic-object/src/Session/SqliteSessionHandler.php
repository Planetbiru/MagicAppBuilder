<?php

namespace MagicObject\Session;

use MagicObject\Exceptions\InvalidFileAccessException;
use PDO;

/**
 * Class SqliteSessionHandler
 *
 * A custom session handler implementation using SQLite as the storage backend.
 * This class manages session lifecycle including create, read, update,
 * destroy, and garbage collection.
 */
class SqliteSessionHandler
{
    /**
     * PDO instance for SQLite connection.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Table name used for storing session data.
     *
     * @var string
     */
    private $table = "sessions";

    /**
     * Constructor.
     *
     * Ensures the database file and its parent directory exist,
     * then initializes the SQLite connection and creates the
     * sessions table if it does not exist.
     *
     * @param string $path Absolute path to the SQLite database file.
     *
     * @throws \RuntimeException If the target directory is not writable.
     */
    public function __construct($path)
    {
        // If path is a directory, append default filename
        if (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "sessions.sqlite";
        }

        // Ensure the parent directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Validate that the directory is writable
        if (!is_writable($dir)) {
            throw new InvalidFileAccessException("Folder not writable: " . $dir);
        }

        // Resolve real path, if file does not exist then create an empty one
        $real = realpath($path);
        if ($real === false) {
            file_put_contents($path, "");
            $real = realpath($path);
        }

        // Build DSN for SQLite connection
        $dsn = "sqlite:" . $real;
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create session table if it does not exist
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id TEXT PRIMARY KEY,
                data BLOB,
                time_creation INTEGER,
                last_access INTEGER
            )
        ");
    }

    /**
     * Open the session.
     *
     * @param string $savePath    Session save path.
     * @param string $sessionName Session name.
     *
     * @return bool Always true.
     */
    public function open($savePath, $sessionName) // NOSONAR
    {
        return true;
    }

    /**
     * Close the session.
     *
     * @return bool Always true.
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data by session ID.
     *
     * @param string $id Session ID.
     *
     * @return string Serialized session data, or empty string if not found.
     */
    public function read($id)
    {
        // Get session data
        $stmt = $this->pdo->prepare("SELECT data FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Update last access time meskipun data tidak berubah
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET last_access = :time WHERE id = :id");
            $stmt->bindValue(':time', time(), PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        }

        // Return session data or empty string if not found
        return isset($row['data']) ? $row['data'] : '';
    }

    /**
     * Write session data.
     *
     * @param string $id   Session ID.
     * @param string $data Serialized session data.
     *
     * @return bool True on success.
     */
    public function write($id, $data)
    {
        // Current timestamp
        $time = time();

        // Use INSERT ... ON CONFLICT untuk SQLite
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (id, data, time_creation, last_access)
            VALUES (:id, :data, :time, :time)
            ON CONFLICT(id) DO UPDATE SET data = :data, last_access = :time
        ");

        // Bind parameters
        $stmt->bindValue(':id', $id, PDO::PARAM_STR); // Use PARAM_STR for text data
        $stmt->bindValue(':data', $data, PDO::PARAM_LOB); // Use PARAM_LOB for binary data
        $stmt->bindValue(':time', $time, PDO::PARAM_INT); // Use PARAM_INT for integer data

        return $stmt->execute();
    }

    /**
     * Destroy a session by ID.
     *
     * @param string $id Session ID.
     *
     * @return bool True on success.
     */
    public function destroy($id)
    {
        // Delete session record
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    /**
     * Perform garbage collection.
     *
     * Removes expired sessions older than max lifetime.
     *
     * @param int $maxlifetime Maximum lifetime in seconds.
     *
     * @return bool True if query executed successfully.
     */
    public function gc($maxlifetime)
    {
        // Calculate the cutoff time
        $old = time() - $maxlifetime;
        return $this->pdo->exec("DELETE FROM {$this->table} WHERE last_access < $old") !== false;
    }
}
