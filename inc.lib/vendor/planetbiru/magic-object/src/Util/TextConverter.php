<?php
namespace MagicObject\Util;

/**
 * Class TextConverter
 * 
 * Converts text from one character encoding to another, specifically from ISO-8859-2 to UTF-8.
 * This class handles character mappings for special characters and provides functionality
 * for decoding HTML entities.
 *
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class TextConverter
{
    /**
     * @var string $encodingFrom The source encoding (default is ISO-8859-2).
     */
    private $encodingFrom = 'ISO-8859-2';

    /**
     * @var string $encodingTo The target encoding (default is UTF-8).
     */
    private $encodingTo = 'UTF-8';

    /**
     * @var array $mapChrChr Mapping of character codes to corresponding character codes.
     */
    private $mapChrChr = [
        0x8A => 0xA9,
        0x8C => 0xA6,
        0x8D => 0xAB,
        0x8E => 0xAE,
        0x8F => 0xAC,
        0x9C => 0xB6,
        0x9D => 0xBB,
        0xA1 => 0xB7,
        0xA5 => 0xA1,
        0xBC => 0xA5,
        0x9F => 0xBC,
        0xB9 => 0xB1,
        0x9A => 0xB9,
        0xBE => 0xB5,
        0x9E => 0xBE
    ];

    /**
     * @var array $mapChrString Mapping of character codes to HTML entities.
     */
    private $mapChrString = [
        0x80 => '&euro;',
        0x82 => '&sbquo;',
        0x84 => '&bdquo;',
        0x85 => '&hellip;',
        0x86 => '&dagger;',
        0x87 => '&Dagger;',
        0x89 => '&permil;',
        0x8B => '&lsaquo;',
        0x91 => '&lsquo;',
        0x92 => '&rsquo;',
        0x93 => '&ldquo;',
        0x94 => '&rdquo;',
        0x95 => '&bull;',
        0x96 => '&ndash;',
        0x97 => '&mdash;',
        0x99 => '&trade;',
        0x9B => '&rsquo;',
        0xA6 => '&brvbar;',
        0xA9 => '&copy;',
        0xAB => '&laquo;',
        0xAE => '&reg;',
        0xB1 => '&plusmn;',
        0xB5 => '&micro;',
        0xB6 => '&para;',
        0xB7 => '&middot;',
        0xBB => '&raquo;'
    ];

    /**
     * TextConverter constructor.
     *
     * Initializes the encoding settings.
     *
     * @param string|null $encodingFrom The source encoding (default is ISO-8859-2).
     * @param string|null $encodingTo The target encoding (default is UTF-8).
     */
    public function __construct($encodingFrom = null, $encodingTo = null)
    {
        if(isset($encodingFrom))
        {
            $this->encodingFrom = $encodingFrom;
        }
        if(isset($encodingTo))
        {
            $this->encodingTo = $encodingTo;
        }
    }

    /**
     * Converts the given text from the source encoding to the target encoding.
     *
     * @param string $text The input text to be converted.
     * @return string The converted text.
     */
    public function execute($text)
    {
        $map = $this->prepareMap();

        return html_entity_decode(
            mb_convert_encoding(strtr($text, $map), $this->encodingTo, $this->encodingFrom),
            ENT_QUOTES,
            $this->encodingTo
        );
    }

    /**
     * Prepares a mapping of characters based on the defined character mappings.
     *
     * @return array The prepared character mapping array.
     */
    private function prepareMap()
    {
        $maps[] = $this->arrayMapAssoc(function ($k, $v) {
            return [chr($k), chr($v)];
        }, $this->mapChrChr);

        $maps[] = $this->arrayMapAssoc(function ($k, $v) {
            return [chr($k), $v];
        }, $this->mapChrString);

        return array_merge([], ...$maps);
    }

    /**
     * Maps an associative array using a callback function.
     *
     * @param callable $function The callback function to apply to each element.
     * @param array $array The array to be mapped.
     * @return array The resulting array after mapping.
     */
    private function arrayMapAssoc($function, $array)
    {
        return array_column(
            array_map(
                $function,
                array_keys($array),
                $array
            ),
            1,
            0
        );
    }
}