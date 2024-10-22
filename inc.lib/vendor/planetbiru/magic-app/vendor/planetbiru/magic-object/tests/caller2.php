<?php

use MagicObject\Util\Database\PicoSqlParser;

require_once dirname(__DIR__) . "/vendor/autoload.php";

// Contoh penggunaan
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
  profile_value text comment 'profile value',
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

$parser = new PicoSqlParser();
$parser->init();
$tables = $parser->parseAll($sqlDump);

print_r($tables);
