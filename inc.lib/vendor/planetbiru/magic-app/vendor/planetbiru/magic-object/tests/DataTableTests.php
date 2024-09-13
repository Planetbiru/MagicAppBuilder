<?php

use MagicObject\DataTable;
use MagicObject\Util\ClassUtil\PicoObjectParser;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * House
 * 
 * @Attributes(id="house" width="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Language")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class House extends DataTable
{
    /**
     * ID
     *
     * @Label(content="ID")
     * @Column(name="id")
     * @var string
     */
    protected $id;
    
    /**
     * Address
     *
     * @Label(content="Address")
     * @Column(name="address")
     * @var string
     */
    protected $address;
    
    /**
     * Color
     *
     * @Label(content="Color")
     * @Column(name="color")
     * @var string
     */
    protected $color;

    /**
     * Time Create
     *
     * @Label(content="Time Create")
     * @Column(name="timeCreate")
     * @var DateTime
     */
    protected $timeCreate;
    
}

class BahasaIndonesia extends stdClass
{
    public $id = "ID";
    
    public $address = "Alamat";
    
    public $color = "Warna";

    public $timeCreate = "Waktu Buat";
}

$data = PicoObjectParser::parseYamlRecursive(
"id: 1
address: Jalan Inspeksi no 9
color: blue
"
);

$language = new BahasaIndonesia();

$rumah = new House($data);
$rumah->addLanguage('id', $language);
$rumah->selectLanguage('id');

$rumah->replaceClass('table', 'coba');
$rumah->addClass('coba');
$rumah->addClass('coba');
$rumah->addClass('coba');
$rumah->addClass('coba');

$apa = $rumah."";
echo $apa;