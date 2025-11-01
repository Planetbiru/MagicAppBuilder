<?php

class I18n
{
    private $translations;
    public function __construct($translations)
    {
        $this->translations = $translations;
    }

    public function t($key)
    {
        $text = isset($this->translations[$key]) ? $this->translations[$key] : $this->snakeCaseToTitleCase($key);

        $args = func_get_args();
        array_shift($args); // Remove the first argument ($key)

        foreach ($args as $i => $arg) {
            $text = str_replace('{' . $i . '}', $arg, $text);
        }
        return $text;
    }
    public function snakeCaseToTitleCase($text)
    {
        $words = explode('_', $text);
        $titleCaseText = '';
        foreach ($words as $key=>$word) {
            $words[$key] = ucfirst($word) . ' ';
        }
        $titleCaseText = implode(' ', $words);
        return $titleCaseText;
    }
}


$languageId = isset($_SERVER['HTTP_X_LANGUAGE_ID']) ? $_SERVER['HTTP_X_LANGUAGE_ID'] : 'en';
$arr = explode("_", $languageId);
$languageId = $arr[0];
$lang_file = dirname(__DIR__) . '/langs/i18n/' . $languageId . '.json';
if (!file_exists($lang_file)) {
    $lang_file = dirname(__DIR__) . '/langs/i18n/en.json'; // Fallback
}

if (file_exists($lang_file)) {
    $translations = json_decode(file_get_contents($lang_file), true);
} else {
    $translations = [];
}
$i18n = new I18n($translations);
