<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\utils;

use AndreasHGK\EasyKits\manager\DataManager;
use pocketmine\utils\TextFormat;

abstract class LangUtils {

    /**
     * Get a message from the lang.yml file
     *
     * @param string $key
     * @param bool $colorize
     * @param array $replace
     * @return string[]|string
     */
    public static function getMessage(string $key, bool $colorize = true, array $replace = []) {
        $msg = DataManager::getKey(DataManager::LANG, $key, null);
        if($msg === null) return $key;
        if(is_array($msg)) {
            $return = [];
            foreach($msg as $key => $msgE) {
                if($msgE === false) return "";
                $msgE = self::replaceVariables($msgE, $replace);
                if($colorize) TextFormat::colorize($msgE);
                $return[$key] = $msgE;
            }
        } else {
            if($msg === false) return "";
            $msg = self::replaceVariables($msg, $replace);
            if($colorize) TextFormat::colorize($msg);
            $return = $msg;
        }
        return $return;
    }

    public static function replaceVariables(string $text, array $variables) : string {
        foreach($variables as $variable => $replace) {
            $text = str_replace($variable, (string)$replace, $text);
        }
        return $text;
    }

}
