<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\listener;

use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\KitManager;
use AndreasHGK\EasyKits\utils\KitException;
use AndreasHGK\EasyKits\utils\LangUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\StringTag;

class InteractClaimListener implements Listener{

    public function onInteract(PlayerInteractEvent $ev) : void {
        $item = $ev->getItem();
        if($item->getNamedTag()->hasTag("ekit", StringTag::class)){
            $kitname = $item->getNamedTag()->getTagValue("ekit", StringTag::class);
            if(KitManager::exists($kitname)){
                $ev->setCancelled();
                $player = $ev->getPlayer();
                $kit = KitManager::get($kitname);
                $kit->setPrice(0);
                $kit->setCooldown(0);
                if(!DataManager::getKey(DataManager::CONFIG, "chestKit-locked")){
                    $kit->setLocked(false);
                }

                try{
                    if($kit->claimFor($player)) $player->sendMessage(LangUtils::getMessage("chestclaim-success", true, ["{NAME}" => $kit->getName()]));
                    $player->getInventory()->remove($item);
                }catch(KitException $e){
                    switch ($e->getCode()){
                        case 0:
                            $time = CooldownManager::getKitCooldown($kit, $player);
                            $timeString = "";
                            $timeArray = [];
                            if($time >= 86400){
                                $unit = floor($time/86400);
                                $time -= $unit*86400;
                                $timeArray[] = $unit." days";
                            }
                            if($time >= 3600){
                                $unit = floor($time/3600);
                                $time -= $unit*3600;
                                $timeArray[] = $unit." hours";
                            }
                            if($time >= 60){
                                $unit = floor($time/60);
                                $time -= $unit*60;
                                $timeArray[] = $unit." minutes";
                            }
                            if($time >= 1){
                                $timeArray[] = $time." seconds";
                            }
                            foreach($timeArray as $key => $value){
                                if($key === 0){
                                    $timeString .= $value;
                                }elseif ($key === count($timeArray) - 1){
                                    $timeString .= " and ".$value;
                                }else{
                                    $timeString .= ", ".$value;
                                }
                            }
                            $player->sendMessage(LangUtils::getMessage("kit-cooldown-active", true, ["{TIME}" => $timeString]));
                            break;
                        case 1:
                            $player->sendMessage(LangUtils::getMessage("kit-insufficient-funds"));
                            break;
                        case 2:
                            $player->sendMessage(LangUtils::getMessage("no-economy"));
                            break;
                        case 3:
                            $player->sendMessage(LangUtils::getMessage("kit-insufficient-space"));
                            break;
                        case 4:
                            $player->sendMessage(LangUtils::getMessage("kit-no-permission"));
                            break;
                        default:
                            $player->sendMessage(LangUtils::getMessage("unknown-exception"));
                            break;
                    }
                }
            }
        }
    }

}