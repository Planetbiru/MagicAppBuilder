<?php

use MagicApp\AppDto\ResponseDto\DataMap;
use MagicApp\AppDto\ResponseDto\ListDataDto;
use MagicApp\AppDto\ResponseDto\ListDataTitleDto;
use MagicApp\AppDto\ResponseDto\ListDto;
use MagicApp\AppDto\ResponseDto\MetadataDto;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy="SNAKE_CASE")
 */
class Apa extends MagicObject
{
    
}

/**
 * @JSON(property-naming-strategy="SNAKE_CASE", prettify=true)
 */
class ApaDto extends ListDto
{
    
}

$listDto = new ApaDto("001", "Success", new ListDataDto());

$listDto->appendTitle(new ListDataTitleDto("apaId", "ID", true, "DESC"));
$listDto->appendTitle(new ListDataTitleDto("name", "Nama", true));
$listDto->appendTitle(new ListDataTitleDto("gender", "Jenis Kelamin", false));
$listDto->appendTitle(new ListDataTitleDto("active", "Aktif", true));

$listDto->appendDataMap(new DataMap('active', [true=>'Ya', false=>'Tidak']));
$listDto->appendDataMap(new DataMap('gender', ['M'=>'Laki-Laki']));
$listDto->addPrimaryKeyName("apaId", "string");

$listDto->setPage([5, 30]);

$listDto->appendData(new Apa(['apaId'=>'1234', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1235', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1236', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1237', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1238', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1239', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1240', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1241', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1242', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->appendData(new Apa(['apaId'=>'1243', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));

echo $listDto."\r\n";