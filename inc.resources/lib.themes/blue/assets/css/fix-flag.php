<?php

$files = glob(__DIR__."/flag/cif-*.svg");

foreach($files as $file)
{
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $svgString = $lines[0]."</svg>";
    echo $svgString."\r\n\r\n";
    // Load SVG string into DOMDocument
    $dom = new DOMDocument();
    $dom->loadXML($svgString);

    // Get the viewBox attribute
    $viewBox = $dom->documentElement->getAttribute('viewBox');

    $dim = explode(" ", $viewBox);
    $max = max($dim[2], $dim[3]);
    $scale = 20 / $max;

    $width = $scale * $dim[2];
    $height = $scale * $dim[3];

    // Output the viewBox value
    echo "VIEWBOX = ".$viewBox."\r\n";
    echo "WIDTH = $width HEIGHT = $height\r\n";
    $viewBox2 = "0 0 $width $height";
    $lines[0] = str_replace(["width=\"$dim[2]\"", "height=\"$dim[3]\""], ["width=\"$width\"", "height=\"$height\""], $lines[0]);
    echo $lines[0]."\r\n";
    $newContent = implode("\n", $lines);
    file_put_contents(str_replace("cif-", "", $file), $newContent);
    unlink($file);
}