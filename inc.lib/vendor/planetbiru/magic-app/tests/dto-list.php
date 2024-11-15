<?php

use MagicApp\AppDto\ResponseDto\ButtonFormData;
use MagicApp\AppDto\ResponseDto\DataMap;
use MagicApp\AppDto\ResponseDto\ListDataDto;
use MagicApp\AppDto\ResponseDto\ListDataTitleDto;
use MagicApp\AppDto\ResponseDto\ListDto;
use MagicApp\AppDto\ResponseDto\MetadataDto;
use MagicApp\AppDto\ResponseDto\ToString;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy="KEBAB_CASE")
 */
class Apa extends MagicObject
{
    
}

/**
 * @JSON(property-naming-strategy="SNAKE_CASE", prettify="true")
 */
class ApaDto extends ListDto
{
    
}

$listDto = new ApaDto("001", "Success", new ListDataDto());

$listDto->addTitle(new ListDataTitleDto("apaId", "ID", true, "DESC"));
$listDto->addTitle(new ListDataTitleDto("name", "Nama", true));
$listDto->addTitle(new ListDataTitleDto("gender", "Jenis Kelamin", false));
$listDto->addTitle(new ListDataTitleDto("active", "Aktif", true));

$listDto->addDataMap(new DataMap('active', [true=>'Ya', false=>'Tidak']));
$listDto->addDataMap(new DataMap('gender', ['M'=>'Laki-Laki']));
$listDto->addPrimaryKeyName("apaId", "string");

$listDto->setPage([5, 30]);

$updateButton = new ButtonFormData('button', 'button', 'btn btn-primary', 'edit-data', null, null, 'Edit');
$updateButton->addAttribute('data-id', 1);
$updateButton->addAttribute('onclick', "window.location='?user-action=edit&id=1234'");

$listDto->addDataControl($updateButton);

$listDto->addData(new Apa(['apaId'=>'1234', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1234'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1235', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1235'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1236', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1236'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1237', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1237'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1238', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1238'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1239', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1239'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1240', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1240'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1241', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1241'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1242', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1242'], true, 0));
$listDto->addData(new Apa(['apaId'=>'1243', 'name'=>'Coba', 'gender'=>'M', 'active'=>"Ya"]), new MetadataDto(['apaId'=>'1243'], true, 0));

//echo $listDto."\r\n";

echo $listDto->propertyValue(ToString::SNAKE_CASE, true);