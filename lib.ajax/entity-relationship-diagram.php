<?php

require_once dirname(__DIR__) . "/inc.app/app.php";

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

// Setup the document
$image = new SVG(220, 820);
$doc = $image->getDocument();

// Create a small blue rectangle
$square = new SVGRect(20, 20, 200, 800);
$square->setStyle('fill', '#0000FF');
$doc->addChild($square);

// Add the proper header and echo the SVG
header('Content-Type: image/svg+xml');

$image->getDocument()->setWidth(100);
$image->getDocument()->setHeight(100);

echo $image;