<?php


/**
 * Class TableParser
 * 
 * This class is used to parse SQL table definitions and extract information about columns,
 * data types, primary keys, and other attributes from SQL CREATE TABLE statements.
 * 
 * Usage example:
 * $parser = new TableParser($sql);
 * $result = $parser->getResult();
 * 
 * @property array $typeList List of valid data types.
 * @property array $tableInfo Information about the parsed table.
 * @link https://github.com/Planetbiru/ERD-Maker/tree/main
 */
class TableParser
{
    /**
     * Type list
     *
     * @var array
     */
    private $typeList = [];

    /**
     * Table info
     *
     * @var array
     */
    private $tableInfo = [];

    /**
     * TableParser constructor.
     * 
     * @param string|null $sql SQL statement to be parsed (optional).
     */
    public function __construct($sql = null)
    {
        $this->init();
        if ($sql !== null) {
            $this->parseAll($sql);
        }
    }

    /**
     * Checks if a specific element exists in the array.
     * 
     * @param array $haystack Array to search in.
     * @param mixed $needle Element to search for.
     * @return bool True if the element is found, false otherwise.
     */
    private function inArray($haystack, $needle)
    {
        return in_array($needle, $haystack);
    }

    /**
     * Parses the CREATE TABLE statement to extract table information.
     * 
     * @param string $sql SQL statement to be parsed.
     * @return array Information about the table, columns, and primary key.
     */
    public function parseTable($sql)
    {
        $arr = explode(";", $sql);
        $sql = $arr[0];
        
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s+(?<tb>.*)\s+\(/i';
        $rg_fld = '/(\w+\s+key.*|\w+\s+bigserial|\w+\s+serial4|\w+\s+tinyint.*|\w+\s+bigint.*|\w+\s+text.*|\w+\s+varchar.*|\w+\s+char.*|\w+\s+real.*|\w+\s+float.*|\w+\s+integer.*|\w+\s+int.*|\w+\s+datetime.*|\w+\s+date.*|\w+\s+double.*|\w+\s+bigserial.*|\w+\s+serial.*|\w+\s+timestamp .*)/i';
        $rg_fld2 = '/(?<fname>\w+)\s+(?<ftype>\w+)(?<fattr>.*)/i';
        $rg_not_null = '/not\s+null/i';
        $rg_pk = '/primary\s+key/i';
        $rg_fld_def = '/default\s+(.+)/i';
        $rg_pk2 = '/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+\(([a-zA-Z_0-9,\s]+)\)/i';

        preg_match($rg_tb, $sql, $result);
        $tableName = $result['tb'];

        $fld_list = [];
        $primaryKey = null;
        $columnList = [];

        preg_match_all($rg_fld, $sql, $matches);
        foreach ($matches[0] as $f) {
            $rg_fld2_result = [];
            preg_match($rg_fld2, $f, $rg_fld2_result);
            $dataType = $rg_fld2_result[2];
            $is_pk = false;

            if ($this->isValidType(strtolower($dataType))) {
                $attr = trim(str_replace(',', '', $rg_fld2_result['fattr']));
                $nullable = !preg_match($rg_not_null, $attr);
                $attr2 = preg_replace($rg_not_null, '', $attr);
                $is_pk = preg_match($rg_pk, $attr2);

                $def = null;
                preg_match($rg_fld_def, $attr2, $def);
                $comment = null;

                if ($def) {
                    $def = trim($def[1]);
                    if (stripos($def, 'comment') !== false) {
                        $comment = substr($def, strpos($def, 'comment'));
                    }
                }

                $length = $this->getLength($attr);
                $columnName = trim($rg_fld2_result['fname']);

                if (!$this->inArray($columnList, $columnName)) {
                    $fld_list[] = [
                        'Column Name' => $columnName,
                        'Type' => trim($rg_fld2_result['ftype']),
                        'Length' => $length,
                        'Primary Key' => $is_pk,
                        'Nullable' => $nullable,
                        'Default' => $def
                    ];
                    $columnList[] = $columnName;
                }
            } elseif (stripos($f, 'primary') !== false && stripos($f, 'key') !== false) {
                preg_match('/\((.*)\)/', $f, $matches);
                $primaryKey = isset($matches[1]) ? trim($matches[1]) : null;
            }

            if ($primaryKey !== null) {
                foreach ($fld_list as &$column) {
                    if ($column['Column Name'] === $primaryKey) {
                        $column['Primary Key'] = true;
                    }
                }
            }

            if (preg_match($rg_pk2, $f) && preg_match($rg_pk, $f)) {
                $x = preg_replace('/(PRIMARY|UNIQUE) KEY\s+[a-zA-Z_0-9\s]+/', '', $f);
                $x = str_replace(['(', ')'], '', $x);
                $pkeys = array_map('trim', explode(',', $x));
                foreach ($fld_list as &$column) {
                    if ($this->inArray($pkeys, $column['Column Name'])) {
                        $column['Primary Key'] = true;
                    }
                }
            }
        }
        return [
            'tableName' => $tableName, 
            'columns' => $fld_list, 
            'primaryKey' => $primaryKey
        ];
    }

    /**
     * Gets the length of the column data type if there is a length definition.
     * 
     * @param string $text Text containing the data type definition.
     * @return string|null Length of the data type or null if not present.
     */
    private function getLength($text)
    {
        if (strpos($text, '(') !== false && strpos($text, ')') !== false) {
            preg_match('/\((.*)\)/', $text, $matches);
            return isset($matches[1]) ? $matches[1] : null;
        }
        return '';
    }

    /**
     * Checks if the data type is valid.
     * 
     * @param string $dataType Data type to check.
     * @return bool True if the data type is valid, false otherwise.
     */
    private function isValidType($dataType)
    {
        return in_array($dataType, $this->typeList);
    }

    /**
     * Returns the result of the table parsing.
     * 
     * @return array Information about the parsed table.
     */
    public function getResult()
    {
        return $this->tableInfo;
    }

    /**
     * Initializes the list of valid data types.
     */
    public function init()
    {
        $typeList = 'timestamp,serial4,bigserial,int2,int4,int8,tinyint,bigint,text,varchar,char,real,float,integer,int,datetime,date,double';
        $this->typeList = explode(',', $typeList);
    }

    /**
     * Parses all CREATE TABLE statements in the SQL text.
     * 
     * @param string $sql SQL statement to be parsed.
     */
    public function parseAll($sql)
    {
        $inf = [];
        $rg_tb = '/(create\s+table\s+if\s+not\s+exists|create\s+table)\s+(?<tb>.*)\s+\(/i';
        
        preg_match_all($rg_tb, $sql, $matches);
        foreach ($matches[0] as $match) {
            $sub = substr($sql, strpos($sql, $match));
            $info = $this->parseTable($sub);
            $inf[] = $info;
        }
        
        $this->tableInfo = $inf;
    }

    /**
     * Get type list
     *
     * @return  array
     */ 
    public function getTypeList()
    {
        return $this->typeList;
    }

    /**
     * Get table info
     *
     * @return  array
     */ 
    public function getTableInfo()
    {
        return $this->tableInfo;
    }
}


$sqlDump = "

-- MySQL dump 10.14  Distrib 5.5.68-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: music
-- ------------------------------------------------------
-- Server version	5.5.68-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table album
--

DROP TABLE IF EXISTS album;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE album (
  album_id varchar(50) NOT NULL,
  name varchar(50) DEFAULT NULL,
  title text,
  description longtext,
  producer_id varchar(40) DEFAULT NULL,
  release_date date DEFAULT NULL,
  number_of_song int(11) DEFAULT NULL,
  duration float DEFAULT NULL,
  image_path text,
  sort_order int(11) DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  locked tinyint(1) DEFAULT '0',
  as_draft tinyint(1) DEFAULT '1',
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table article
--

DROP TABLE IF EXISTS article;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table artist
--

DROP TABLE IF EXISTS artist;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE artist (
  artist_id varchar(40) NOT NULL,
  name varchar(100) DEFAULT NULL,
  stage_name varchar(100) DEFAULT NULL,
  gender varchar(2) DEFAULT NULL,
  birth_day date DEFAULT NULL,
  phone varchar(50) DEFAULT NULL,
  phone2 varchar(50) DEFAULT NULL,
  phone3 varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  email2 varchar(100) DEFAULT NULL,
  email3 varchar(100) DEFAULT NULL,
  website text,
  address text,
  picture tinyint(1) DEFAULT NULL,
  image_path text,
  image_update timestamp NULL DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (artist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table draft_rating
--

DROP TABLE IF EXISTS draft_rating;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE draft_rating (
  draft_rating_id varchar(40) NOT NULL,
  user_id varchar(40) DEFAULT NULL,
  song_draft_id varchar(40) DEFAULT NULL,
  rating float DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  PRIMARY KEY (draft_rating_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table genre
--

DROP TABLE IF EXISTS genre;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE genre (
  genre_id varchar(50) NOT NULL,
  name varchar(255) DEFAULT NULL,
  image_path text,
  sort_order int(11) DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (genre_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table midi
--

DROP TABLE IF EXISTS midi;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE midi (
  midi_id varchar(50) NOT NULL,
  random_midi_id varchar(50) DEFAULT NULL,
  title text,
  album_id varchar(50) DEFAULT NULL,
  artist_vocal varchar(50) DEFAULT NULL,
  artist_composer varchar(50) DEFAULT NULL,
  artist_arranger varchar(50) DEFAULT NULL,
  file_path text,
  file_name varchar(100) DEFAULT NULL,
  file_type varchar(100) DEFAULT NULL,
  file_extension varchar(20) DEFAULT NULL,
  file_size bigint(20) DEFAULT NULL,
  file_md5 varchar(32) DEFAULT NULL,
  file_upload_time timestamp NULL DEFAULT NULL,
  duration float DEFAULT NULL,
  genre_id varchar(50) DEFAULT NULL,
  lyric longtext,
  comment longtext,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  admin_create varchar(50) DEFAULT NULL,
  admin_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (midi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table producer
--

DROP TABLE IF EXISTS producer;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE producer (
  producer_id varchar(40) NOT NULL,
  name varchar(100) DEFAULT NULL,
  gender varchar(2) DEFAULT NULL,
  birth_day date DEFAULT NULL,
  phone varchar(50) DEFAULT NULL,
  phone2 varchar(50) DEFAULT NULL,
  phone3 varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  email2 varchar(100) DEFAULT NULL,
  email3 varchar(100) DEFAULT NULL,
  website text,
  address text,
  picture tinyint(1) DEFAULT NULL,
  image_path text,
  image_update timestamp NULL DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (producer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table rating
--

DROP TABLE IF EXISTS rating;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE rating (
  rating_id varchar(40) NOT NULL,
  user_id varchar(40) DEFAULT NULL,
  song_id varchar(40) DEFAULT NULL,
  rating float DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  PRIMARY KEY (rating_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table reference
--

DROP TABLE IF EXISTS reference;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE reference (
  reference_id varchar(50) NOT NULL,
  title varchar(255) DEFAULT NULL,
  genre_id varchar(50) DEFAULT NULL,
  album varchar(255) DEFAULT NULL,
  artist_id varchar(50) DEFAULT NULL,
  year year(4) DEFAULT NULL,
  url text,
  url_provider varchar(100) DEFAULT NULL,
  subtitle text,
  description longtext,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  admin_create varchar(50) DEFAULT NULL,
  admin_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song
--

DROP TABLE IF EXISTS song;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song (
  song_id varchar(50) NOT NULL,
  random_song_id varchar(50) DEFAULT NULL,
  name varchar(100) DEFAULT NULL,
  title text,
  album_id varchar(50) DEFAULT NULL,
  track_number int(11) DEFAULT NULL,
  producer_id varchar(40) DEFAULT NULL,
  artist_vocalist varchar(50) DEFAULT NULL,
  artist_composer varchar(50) DEFAULT NULL,
  artist_arranger varchar(50) DEFAULT NULL,
  file_path text,
  file_name varchar(100) DEFAULT NULL,
  file_type varchar(100) DEFAULT NULL,
  file_extension varchar(20) DEFAULT NULL,
  file_size bigint(20) DEFAULT NULL,
  file_md5 varchar(32) DEFAULT NULL,
  file_upload_time timestamp NULL DEFAULT NULL,
  first_upload_time timestamp NULL DEFAULT NULL,
  last_upload_time timestamp NULL DEFAULT NULL,
  file_path_midi text,
  last_upload_time_midi timestamp NULL DEFAULT NULL,
  file_path_xml text,
  last_upload_time_xml timestamp NULL DEFAULT NULL,
  file_path_pdf text,
  last_upload_time_pdf timestamp NULL DEFAULT NULL,
  duration float DEFAULT NULL,
  genre_id varchar(50) DEFAULT NULL,
  bpm float DEFAULT NULL,
  time_signature varchar(40) DEFAULT NULL,
  subtitle longtext,
  subtitle_complete tinyint(1) DEFAULT '0',
  lyric_midi longtext,
  lyric_midi_raw longtext,
  vocal_guide longtext,
  vocal tinyint(1) DEFAULT '0',
  instrument longtext,
  midi_vocal_channel int(11) DEFAULT NULL,
  rating float DEFAULT NULL,
  comment longtext,
  image_path text,
  last_upload_time_image timestamp NULL DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  admin_create varchar(50) DEFAULT NULL,
  admin_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (song_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song_attachment
--

DROP TABLE IF EXISTS song_attachment;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song_attachment (
  song_attachment_id varchar(40) NOT NULL,
  song_id varchar(40) DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  path text,
  file_size bigint(20) DEFAULT NULL,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (song_attachment_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song_comment
--

DROP TABLE IF EXISTS song_comment;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song_comment (
  song_comment_id varchar(40) NOT NULL,
  song_id varchar(40) DEFAULT NULL,
  user_id varchar(40) DEFAULT NULL,
  time_start decimal(10,3) DEFAULT NULL,
  time_end decimal(10,3) DEFAULT NULL,
  comment longtext,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (song_comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song_draft
--

DROP TABLE IF EXISTS song_draft;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song_draft (
  song_draft_id varchar(40) NOT NULL,
  parent_id varchar(40) DEFAULT NULL,
  random_id varchar(40) DEFAULT NULL,
  artist_id varchar(40) DEFAULT NULL,
  name varchar(100) DEFAULT NULL,
  title text,
  lyric longtext,
  rating float DEFAULT NULL,
  duration float DEFAULT NULL,
  file_path text,
  file_size bigint(20) DEFAULT NULL,
  sha1_file varchar(40) NOT NULL,
  read_count int(11) NOT NULL DEFAULT '0',
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (song_draft_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song_draft_comment
--

DROP TABLE IF EXISTS song_draft_comment;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song_draft_comment (
  song_draft_comment_id varchar(40) NOT NULL,
  song_draft_id varchar(40) DEFAULT NULL,
  comment longtext,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (song_draft_comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table song_update_history
--

DROP TABLE IF EXISTS song_update_history;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE song_update_history (
  song_update_history_id varchar(40) NOT NULL,
  song_id varchar(40) DEFAULT NULL,
  user_id varchar(40) DEFAULT NULL,
  user_activity_id varchar(40) DEFAULT NULL,
  action varchar(20) DEFAULT NULL,
  time_update timestamp NULL DEFAULT NULL,
  ip_update varchar(50) DEFAULT NULL,
  PRIMARY KEY (song_update_history_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table user
--

DROP TABLE IF EXISTS user;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE user (
  user_id varchar(40) NOT NULL,
  username varchar(100) DEFAULT NULL,
  password varchar(100) DEFAULT NULL,
  admin tinyint(1) DEFAULT '0',
  name varchar(100) DEFAULT NULL,
  birth_day varchar(100) DEFAULT NULL,
  gender varchar(2) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  time_zone varchar(255) DEFAULT NULL,
  user_type_id varchar(40) DEFAULT NULL,
  associated_artist varchar(40) DEFAULT NULL,
  associated_producer varchar(40) DEFAULT NULL,
  current_role varchar(40) DEFAULT NULL,
  image_path text,
  time_create timestamp NULL DEFAULT NULL,
  time_edit timestamp NULL DEFAULT NULL,
  admin_create varchar(40) DEFAULT NULL,
  admin_edit varchar(40) DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  ip_edit varchar(50) DEFAULT NULL,
  reset_password_hash varchar(256) DEFAULT NULL,
  last_reset_password timestamp NULL DEFAULT NULL,
  blocked tinyint(1) DEFAULT '0',
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (user_id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table user_activity
--

DROP TABLE IF EXISTS user_activity;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE user_activity (
  user_activity_id varchar(40) NOT NULL,
  name varchar(255) DEFAULT NULL,
  user_id varchar(40) DEFAULT NULL,
  path text,
  method varchar(10) DEFAULT NULL,
  get_data longtext,
  post_data longtext,
  request_body longtext,
  time_create timestamp NULL DEFAULT NULL,
  ip_create varchar(50) DEFAULT NULL,
  PRIMARY KEY (user_activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table user_profile
--

DROP TABLE IF EXISTS user_profile;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE user_profile (
  user_profile_id varchar(40) NOT NULL,
  user_id varchar(40) DEFAULT NULL,
  profile_name varchar(100) DEFAULT NULL,
  profile_value text,
  time_edit timestamp NULL DEFAULT NULL,
  PRIMARY KEY (user_profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table user_type
--

DROP TABLE IF EXISTS user_type;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE user_type (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-31 17:02:42


";

$parser = new TableParser();
$parser->init();
$parser->parseAll($sqlDump);


print_r($parser->getTableInfo());