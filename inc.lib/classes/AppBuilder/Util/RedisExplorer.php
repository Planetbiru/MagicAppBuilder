<?php

namespace AppBuilder\Util;

use MagicObject\MagicObject;
use \Redis;

class RedisExplorer
{
    private $redis;

    public function __construct($connection)
    {
        $this->redis = $connection;
    }

    public function insert($key, $type, $data, $ttl)
    {
        // Insert logic here
        $this->redis->set($key, $data);
        if ($ttl > 0) {
            $this->redis->expire($key, $ttl);
        }
    }

    public function findAll($pattern)
    {
        // Find all logic here
        $keys = $this->redis->keys($pattern); // Simplified for example
        $results = [];
        foreach ($keys as $key) {
            $data = $this->redis->get($key);
            $results[] = new MagicObject([
                'key' => $key,
                'data' => $data,
                'type' => 'string', // Simplified for example
                'ttl' => $this->redis->ttl($key)
            ]);
        }
        return new MagicObject([
            'totalResult' => count($results),
            'data' => $results
        ]);
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @return MagicObject
     */
    public function findOne($key)
    {
        // Find one logic here
        $data = $this->redis->get($key);
        if ($data !== false) {
            return new MagicObject([
                'key' => $key,
                'data' => $data,
                'type' => 'string', // Simplified for example
                'ttl' => $this->redis->ttl($key)
            ]);
        }
        return new MagicObject();
    }

    public function delete($key)
    {
        // Delete logic here
        return $this->redis->del($key);
    }

    public function issetKey($key)
    {
        return $this->redis->exists($key);
    }
    public static function renderError($appLanguage, $message)
    {
      $html = '
      <div class="page page-jambi page-insert">
        <div class="jambi-wrapper">
        <div class="alert alert-danger">
          <h4 class="alert-heading">%s</h4>
          <p>%s</p>
          <hr>
          <form method="post">
          <button type="submit" name="logout" value="1" class="btn btn-primary">%s</button>
          </form>
        </div>
        </div>
      </div>
      ';
      echo sprintf($html, htmlspecialchars($appLanguage->getLabelError()), htmlspecialchars($message), $appLanguage->getButtonBack());
    }

    /**
   * Count the total number of keys matching a pattern using SCAN.
   *
   * This method uses the Redis SCAN command to avoid blocking the server when iterating over large datasets.
   *
   * @param Redis  $redis   Redis client instance
   * @param string $pattern Pattern to match keys (e.g., 'user:*')
   * @return int            Total number of matching keys
   */
  public static function countKeysWithScan($redis, $pattern)
  {
    $count = 0;
    $it = null;

    // Iterate over keys in batches using SCAN
    while (($keys = $redis->scan($it, $pattern)) !== false) {
      $count += count($keys);
    }

    return $count;
  }

  /**
   * Get a list of keys for a specific page using SCAN.
   *
   * Useful for paginating through keys without loading all keys into memory at once.
   *
   * @param Redis  $redis   Redis client instance
   * @param string $pattern Pattern to match keys
   * @param int    $offset  Offset from the start of the result set
   * @param int    $limit   Maximum number of keys to return
   * @return array          Array of matching keys for the given page
   */
  public static function getKeysByPageWithScan($redis, $pattern, $offset, $limit)
  {
    $it = null;
    $found = array();

    // Iterate through keys in batches
    while (($keys = $redis->scan($it, $pattern)) !== false) {
      foreach ($keys as $key) {
        // Stop when we have reached the desired page range
        if (count($found) >= $offset + $limit) {
          break 2;
        }
        $found[] = $key;
      }
    }

    // Return only the keys in the requested offset/limit range
    return array_slice($found, $offset, $limit);
  }

  /**
   * Get the value of a Redis key based on its type.
   *
   * Supports STRING, LIST, SET, SORTED SET (ZSET), and HASH.
   *
   * @param Redis  $redis Redis client instance
   * @param string $key   Redis key name
   * @param int    $type  Redis key type (e.g., Redis::REDIS_STRING)
   * @return string       Formatted value as a string or JSON-encoded representation
   */
  public static function getRedisValue($redis, $key, $type)
  {
    $value = "";

    // Retrieve the value depending on the key type
    if ($type === Redis::REDIS_STRING) {
      $value = $redis->get($key);
    } elseif ($type === Redis::REDIS_LIST) {
      $value = implode(', ', $redis->lRange($key, 0, -1));
    } elseif ($type === Redis::REDIS_SET) {
      $value = implode(', ', $redis->sMembers($key));
    } elseif ($type === Redis::REDIS_ZSET) {
      $value = json_encode($redis->zRange($key, 0, -1, true));
    } elseif ($type === Redis::REDIS_HASH) {
      $value = json_encode($redis->hGetAll($key));
    } else {
      $value = '(unknown type)';
    }
    return $value;
  }
}