<?php

use MagicObject\Util\PicoParsedown;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";

function getCommand($line)
{
    if(PicoStringUtil::endsWith($line, ":"))
    {
        return substr($line, 0, strlen($line) - 1);
    }
    return null;
}

function processBlock($block)
{
    $arr = explode("\r\n", $block);
    $lastCommand = null;
    $buffer = "";
    
    $parsedown = new PicoParsedown();

    // Enable the safe mode of Parsedown.
	$parsedown->setSafeMode(true);

    $fp = fopen(dirname(__DIR__) . "/tutorial.md", "w+");
    $article = 0;
    foreach($arr as $line)
    {

        if(!empty($line) && $lastCommand == "includes")
        {	
            $file = __DIR__ . "/includes/_".trim(ltrim($line, " - ")).".md";
            if(file_exists($file))
            {
                // Echo the HTML from the Markdown text submitted by the user. Note: Additional escaping functions should be implemented based on the context.
                $article++;
                $markdown = file_get_contents($file);
                $buffer .= "<article class=\"article\" id=\"part$article\">\r\n".$parsedown->text($markdown)."\r\n</article>\r\n\r\n";
                fwrite($fp, $markdown."\r\n");
            }
        }
        
        $command = getCommand($line);
        if($command !== null)
        {
            $lastCommand = $command;
        }
        
    }
    fclose($fp);
    return $buffer;
}

$index = file_get_contents(__DIR__ . "/index.html.md");
$index = PicoStringUtil::windowsCariageReturn($index);
$index .= "\r\n";
$arr = explode("\r\n", $index);
foreach($arr as $k=>$v)
{
    if(strpos($v, "---") === 0)
    {
        $arr[$k] = rtrim($v);
    }
}
$index = implode("\r\n", $arr);
$blocks = explode("---\r\n", $index);
foreach($blocks as $key=>$block)
{
    if($key % 2 != 0)
    {
        // process block
        $blocks[$key] = processBlock($block);
    }
}

file_put_contents(__DIR__ . "/index.html", implode("\r\n", $blocks));