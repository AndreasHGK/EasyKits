<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use AndreasHGK\EasyKits\EasyKits;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ReflectionProperty;

class DataManager {

    public const VERSIONS = [
        "config" => 6,
        "commands" => 3,
        "lang" => 9,
    ];

    public const CONFIG = "config.yml";
    public const LANG = "lang.yml";
    public const KITS = "kits.yml";
    public const COMMANDS = "commands.yml";
    public const COOLDOWN = "cooldown.json";
    public const CATEGORIES = "categories.yml";

    /**
     * @var DataManager
     */
    public static $instance = null;

    /**
     * @var Config[]
     */
    public static $memory = [];

    /**
     * @param string $file
     * @param string $key
     * @param bool $default
     * @return mixed
     */
    public static function getKey(string $file, string $key, $default = false) {
        return self::get($file)->get($key, $default);
    }

    public static function get(string $file, bool $keepLoaded = true) : Config {
        if(self::isLoaded($file)) return self::$memory[$file];
        return self::load($file, $keepLoaded);
    }

    public static function load(string $file, bool $keepLoaded = true) : Config {
        $data = self::getFile($file);
        if($keepLoaded) {
            self::$memory[$file] = $data;
        }
        return $data;
    }

    public static function reload(string $file, bool $save = false) : bool {
        if(!self::isLoaded($file)) return false;
        if($save) self::get($file)->save();
        self::get($file)->reload();
        return true;
    }

    public static function unload(string $file) : bool {
        if(!self::isLoaded($file)) return false;
        self::save($file);
        unset(self::$memory[$file]);
        return true;
    }

    public static function isLoaded(string $file) : bool {
        return isset(self::$memory[$file]);
    }

    public static function save(string $file) : bool {
        if(!self::isLoaded($file)) return false;
        self::$memory[$file]->save();
        return true;
    }

    public static function getFile(string $file) : Config {
        return new Config(EasyKits::get()->getDataFolder() . $file);
    }

    public static function deleteFile(string $file) : void {
        unlink(EasyKits::get()->getDataFolder() . $file);
    }

    public static function exists(string $file) : bool {
        return file_exists(EasyKits::get()->getDataFolder() . $file);
    }

    public static function loadDefault() : void {
        if(EasyKits::get()->saveResource(self::CONFIG)) EasyKits::get()->getLogger()->debug("creating " . self::CONFIG);
        self::get(self::CONFIG);
        if(self::getKey(self::CONFIG, "version") !== self::VERSIONS["config"]) {
            EasyKits::get()->getLogger()->warning(self::CONFIG . " version incorrect. Please regenerate your config to avoid errors.");
        }
        if(EasyKits::get()->saveResource(self::LANG)) EasyKits::get()->getLogger()->debug("creating " . self::LANG);
        self::get(self::LANG);
        if(self::getKey(self::LANG, "version") !== self::VERSIONS["lang"]) {
            EasyKits::get()->getLogger()->warning(self::LANG . " version incorrect. Please regenerate your config to avoid errors.");
        }
        if(EasyKits::get()->saveResource(self::COMMANDS)) EasyKits::get()->getLogger()->debug("creating " . self::COMMANDS);
        self::get(self::COMMANDS);
        if(self::getKey(self::COMMANDS, "version") !== self::VERSIONS["commands"]) {
            EasyKits::get()->getLogger()->warning(self::COMMANDS . " version incorrect. Please regenerate your config to avoid errors.");
        }
        if(EasyKits::get()->saveResource(self::KITS)) EasyKits::get()->getLogger()->debug("creating " . self::KITS);
        if(EasyKits::get()->saveResource(self::CATEGORIES)) EasyKits::get()->getLogger()->debug("creating " . self::CATEGORIES);
        self::get(self::KITS);
        self::get(self::CATEGORIES);
        self::get(self::COOLDOWN);
    }

    public static function updateAllConfigs() : void {
        $cfgs = [
            self::CONFIG,
            self::COMMANDS,
            self::LANG,
        ];
        foreach($cfgs as $cfg) {
            self::updateConfig($cfg);
        }
    }

    public static function updateConfig(string $file) : void {
        $cfg = self::get($file)->getAll();

        $reflect = new ReflectionProperty(PluginBase::class, "file");
        $reflect->setAccessible(true);
        $Pfile = $reflect->getValue(EasyKits::get());

        $filename = rtrim(str_replace("\\", "/", $file), "/");
        if(file_exists($Pfile . "resources/" . $filename)) {
            $resource = new Config($Pfile . "resources/" . $filename);
            $cfgResource = $resource->getAll();
        }
        $count = 0;
        foreach($cfgResource as $key => $value) {
            if(!isset($cfg[$key])) {
                $count++;
                $cfg[$key] = $value;
            }
        }
        if($count > 0) {
            DataManager::get($file)->setAll($cfg);
            DataManager::save($file);
            EasyKits::get()->getLogger()->notice("Auto updated " . $count . " keys in " . $file);
        }
    }

    private function __construct() {
    }

}