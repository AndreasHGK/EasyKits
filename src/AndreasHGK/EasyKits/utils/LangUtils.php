<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\utils;

use AndreasHGK\EasyKits\DataManager;
use pocketmine\utils\TextFormat;

abstract class LangUtils {

    public static function getMessage(string $key, bool $colorize = true, array $replace = []) : string {
        $msg = DataManager::getKey(DataManager::LANG, $key);
        if($msg === false) return "";
        $msg = self::replaceVariables($msg, $replace);
        if($colorize) TextFormat::colorize($msg);
        return $msg;
    }

    public static function replaceVariables(string $text, array $variables) : string {
        foreach($variables as $variable => $replace){
            $text = str_replace($variable, $replace, $text);
        }
        return $text;
    }

}
