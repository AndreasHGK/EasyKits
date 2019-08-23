<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\manager;

use AndreasHGK\EasyKits\EasyKits;
use AndreasHGK\EasyKits\event\KitCreateEvent;
use AndreasHGK\EasyKits\event\KitDeleteEvent;
use AndreasHGK\EasyKits\event\KitEditEvent;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\utils\ItemUtils;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\permission\Permissible;
use pocketmine\utils\Config;

class KitManager {

    public const KIT_FORMAT = [
        "items" => [],
        "armor" => [],
        "price" => 0,
        "cooldown" => 0,
        "flags" => [
            "locked" => true,
            "doOverride" => false,
            "doOverrideArmor" => false,
            "alwaysClaim" => false,
            "emptyOnClaim" => false,
        ],
    ];

    /**
     * @var Kit[]
     */
    public static $kits = [];

    /**
     * @param Permissible $permissible
     * @return Kit[]
     */
    public static function getPermittedKitsFor(Permissible $permissible) : array {
        $kits = [];
        foreach(KitManager::getAll() as $kit){
            if($kit->hasPermission($permissible)){
                $kits[] = $kit;
            }
        }
        return $kits;
    }

    public static function update(Kit $old, Kit $new, bool $silent = false) : bool {
        $event = new KitEditEvent($old, $new);

        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        if($event->getOriginalKit()->getName() !== $event->getKit()->getName()){
            self::remove($old, true);
        }
        self::$kits[$event->getKit()->getName()] = $event->getKit();
        return true;
    }

    public static function add(Kit $kit, bool $silent = false) : bool {
        $event = new KitCreateEvent($kit);
        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        self::$kits[$event->getKit()->getName()] = $event->getKit();
        return true;
    }

    public static function remove(Kit $kit, bool $silent = false) : bool {
        $event = new KitDeleteEvent($kit);
        if(!$silent) $event->call();

        if($event->isCancelled()) return false;

        $kits = self::getKitFile();
        $kits->remove($event->getKit()->getName());
        DataManager::save(DataManager::KITS);
        self::unload($event->getKit()->getName());
        return true;
    }

    /**
     * @return Kit[]
     */
    public static function getAll() : array {
        return self::$kits;
    }

    public static function get(string $name) : ?Kit {
        return isset(self::$kits[$name]) ? clone self::$kits[$name] : null;
    }

    public static function loadAll() : void {
        $file = self::getKitFile()->getAll();
        foreach ($file as $name => $kit){
            self::load((string)$name);
        }
    }

    public static function reloadAll() : void {
        DataManager::reload(DataManager::KITS);
        self::unloadAll();
        self::loadAll();
    }

    public static function unloadAll() : void {
        self::$kits = [];
    }

    public static function unload(string $kit) : void {
        unset(self::$kits[$kit]);
    }

    public static function exists(string $file) : bool{
        return isset(self::$kits[$file]);
    }

    public static function saveAll() : void {
        foreach(self::getAll() as $name => $kit){
            self::save((string)$name);
        }
        DataManager::save(DataManager::KITS);
    }

    /**
     * @param string $name
     * @internal
     */
    public static function load(string $name) : void {
        $file = self::getKitFile()->getAll();
        $kitdata = $file[$name];
        try{

            $items = [];
            foreach ($kitdata["items"] as $slot => $itemData){
                $items[$slot] = ItemUtils::dataToItem($itemData);
            }

            $armor = [];
            foreach ($kitdata["armor"] as $slot => $itemData){
                $armor[$slot] = ItemUtils::dataToItem($itemData);;
            }
            $effects = [];
            foreach($kitdata["effects"] ?? [] as $id => $effect){
                $effects[$id] = new EffectInstance(Effect::getEffect($id), $effect["duration"] ?? null, $effect["amplifier"] ?? 0);
            }
            $commands = [];
            foreach($kitdata["commands"] ?? [] as $command){
                $commands[] = $command;
            }
            $kit = new Kit($name, $kitdata["price"], $kitdata["cooldown"], $items, $armor);
            $kit->setLocked($kitdata["flags"]["locked"]);
            $kit->setDoOverride($kitdata["flags"]["doOverride"]);
            $kit->setDoOverrideArmor($kitdata["flags"]["doOverrideArmor"]);
            $kit->setAlwaysClaim($kitdata["flags"]["alwaysClaim"]);
            $kit->setEmptyOnClaim($kitdata["flags"]["emptyOnClaim"]);
            $kit->setChestKit($kitdata["flags"]["chestKit"] ?? DataManager::getKey(DataManager::CONFIG, "default-flags")["chestKit"]);

            $kit->setEffects($effects);
            $kit->setCommands($commands);

            self::$kits[$name] = $kit;

        }catch (\Throwable $e){
            EasyKits::get()->getLogger()->error("failed to load kit '".$name."'");
            EasyKits::get()->getLogger()->debug($e->getMessage());
        }
    }

    /**
     * @param string $name
     * @internal
     */
    public static function save(string $name) : void {
        $file = self::getKitFile();
        $kit = self::get($name);
        $kitData = self::KIT_FORMAT;
        $kitData["price"] = $kit->getPrice();
        $kitData["cooldown"] = $kit->getCooldown();
        $kitData["flags"]["locked"] = $kit->isLocked();
        $kitData["flags"]["doOverride"] = $kit->doOverride();
        $kitData["flags"]["doOverrideArmor"] = $kit->doOverrideArmor();
        $kitData["flags"]["alwaysClaim"] = $kit->alwaysClaim();
        $kitData["flags"]["emptyOnClaim"] = $kit->emptyOnClaim();
        $kitData["flags"]["chestKit"] = $kit->isChestKit();
        foreach($kit->getItems() as $slot => $item){
            $kitData["items"][$slot] = ItemUtils::itemToData($item);
        }
        foreach($kit->getArmor() as $slot => $item){
            $kitData["armor"][$slot] = ItemUtils::itemToData($item);
        }
        foreach($kit->getEffects() as $effect){
            $kitData["effects"][$effect->getId()] = [
                "amplifier" => $effect->getAmplifier(),
                "duration" => $effect->getDuration(),
            ];
        }
        foreach ($kit->getCommands() as $command){
            $kitData["commands"][] = $command;
        }
        $file->set($kit->getName(), $kitData);
    }

    private static function getKitFile() : Config{
        return DataManager::get(DataManager::KITS);
    }

    private function __construct(){}


}