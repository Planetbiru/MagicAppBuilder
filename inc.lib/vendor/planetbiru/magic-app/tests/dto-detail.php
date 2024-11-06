<?php

use MagicApp\AppDto\ResponseDto\DetailDataDto;
use MagicApp\AppDto\ResponseDto\DetailDto;
use MagicApp\AppDto\ResponseDto\MetadataDetailDto;
use MagicApp\AppDto\ResponseDto\ValueDto;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy="SNAKE_CASE")
 */
class Apa extends MagicObject
{
    
}

$detailDto = new DetailDto("001", "Success", new DetailDataDto());
$detailDto->addPrimaryKeyName("apaId", "string");
$detailDto->setMetadata(new MetadataDetailDto(['apaId'=>'1234'], true, 0, '', ''));

$detailDto->addData('apaId', new ValueDto('1234'), 'string', 'ID', false, false, new ValueDto('1234'));
$detailDto->addData('name', new ValueDto('Coba'), 'string', 'Name', false, false, new ValueDto('Coba'));
echo $detailDto."\r\n";