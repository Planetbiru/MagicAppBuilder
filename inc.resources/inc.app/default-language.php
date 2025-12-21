<?php

use MagicAppTemplate\AppLanguageImpl;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

// language directory path
$langDir = dirname(__DIR__) . "/inc.lang/";

// fetch available language folders
$availableLanguages = [];
if (is_dir($langDir)) {
    foreach (scandir($langDir) as $dir) {
        if ($dir === '.' || $dir === '..' || $dir === 'source') {
            continue;
        }
        if (is_dir($langDir . $dir)) {
            $availableLanguages[] = $dir;
        }
    }
}

// default from application config
$defaultLanguage = $appConfig->getOrDefaultLanguage();
if ($defaultLanguage == null || empty($defaultLanguage)) {
    $defaultLanguage = "en";
}

// get from Accept-Language header
$acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
$chosenLanguage = $defaultLanguage;

if (!empty($acceptLanguage)) {
    // example: "en-US,en;q=0.9,id;q=0.8"
    $lang = strtolower(substr($acceptLanguage, 0, 2));

    if (in_array($lang, $availableLanguages)) {
        $chosenLanguage = $lang;
    }
}

// initialize AppLanguageImpl
$appLanguage = new AppLanguageImpl(
    $appConfig->getApplication(),
    $chosenLanguage,
    function($var, $value) {
        $inputSource = dirname(__DIR__) . "/inc.lang/source/app.ini";
        $inputSource = FileUtil::fixFilePath($inputSource);

        // ensure source directory exists
        if (!file_exists(dirname($inputSource))) {
            mkdir(dirname($inputSource), 0755, true);
        }

        // load existing data if available
        $sourceData = null;
        if (file_exists($inputSource) && filesize($inputSource) > 3) {
            $sourceData = PicoIniUtil::parseIniFile($inputSource);
        }
        if ($sourceData == null || $sourceData === false) {
            $sourceData = [];
        }

        // merge new key and write back
        $output = array_merge(
            $sourceData,
            [PicoStringUtil::snakeize($var) => $value]
        );
        PicoIniUtil::writeIniFile($output, $inputSource);
    }
);
