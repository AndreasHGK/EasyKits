<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\event\KitClaimEvent;
use AndreasHGK\EasyKits\utils\KitException;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\permission\Permissible;
use pocketmine\Player;
use pocketmine\Server;

class Kit
{

    //items
    /**
     * @var Item[]
     */
    protected $items = [];
    /**
     * @var Item[]
     */
    protected $armor = [];

    //settings
    /**
     * @var string
     */
    protected $name;
    /**
     * @var float
     */
    protected $price = 0;
    /**
     * @var int
     */
    protected $cooldown = 60;

    //flags
    /**
     * @var bool
     */
    protected $locked = true;
    /**
     * @var bool
     */
    protected $emptyOnClaim = true;
    /**
     * @var bool
     */
    protected $doOverride = false;
    /**
     * @var bool
     */
    protected $doOverrideArmor = false;
    /**
     * @var bool
     */
    protected $alwaysClaim = false;

    /**
     * claim a kit as a player
     * @param Player $player
     */
    public function claimFor(Player $player) : bool {
        if(!$this->hasPermission($player)) throw new KitException("Player is not permitted to claim this kit", 4);
        if($this->getCooldown() > 0){
            if(CooldownManager::hasKitCooldown($this, $player)){
                throw new KitException("Kit is on cooldown", 0);
            }
        }
        if($this->getPrice() > 0){
            $economy = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
            if($economy instanceof EconomyAPI){
                if($economy->myMoney($player) < $this->getPrice()){
                    throw new KitException("Player has insufficient funds", 1);
                }
            }else{
                throw new KitException("Economy not found", 2);
            }
        }

        $armorSlots = $this->getArmor();
        $playerArmorInv = $player->getArmorInventory();
        $playerArmor = $playerArmorInv->getContents(true);
        $playerInv = $player->getInventory();
        $playerSlots = $playerInv->getContents(false);
        $invSlots = $this->getItems();
        $invCount = count($invSlots);
        if($this->doOverrideArmor()){
            foreach($armorSlots as $key => $armorSlot){
                if($playerArmor[$key]->getId() !== Item::AIR){
                    $invCount++;
                }
            }
        }
        if(!$this->alwaysClaim()){
            if($invCount > $playerInv->getSize()){
                throw new KitException("Player has insufficient space", 3);
            }
            if(!$this->emptyOnClaim() && !$this->doOverride() && $invCount > ($playerInv->getSize() - count($playerSlots))){
                throw new KitException("Player has insufficient space", 3);
            }
        }

        $event = new KitClaimEvent($this, $player);
        $event->call();

        if($event->isCancelled()) return false;

        $player = $event->getPlayer();
        $kit = $event->getKit();

        if($kit->getCooldown() > 0){
            CooldownManager::setKitCooldown($kit, $player);
        }
        if($kit->getPrice() > 0){
            $economy->reduceMoney($player, $kit->getPrice(), true);
        }
        if($kit->emptyOnClaim()){
            $playerInv->clearAll();
            $playerArmorInv->clearAll();
        }
        foreach($invSlots as $key => $invSlot){
            if($kit->doOverride()) $playerInv->setItem($key, $invSlot);
            else $playerInv->addItem($invSlot);
        }
        foreach($armorSlots as $key => $armorSlot){
            if($kit->doOverrideArmor()) $playerArmorInv->setItem($key, $armorSlot);
            elseif($playerArmorInv->getItem($key)->getId() !== Item::AIR) $playerInv->addItem($armorSlot);
            else $playerArmorInv->addItem($armorSlot);
        }
        return true;
    }

    /**
     * @param Permissible $permissible
     * @return bool
     */
    public function hasPermission(Permissible $permissible) : bool {
        return $permissible->hasPermission(EasyKits::PERM_ROOT."kit.".$this->getName()) || !$this->isLocked();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return Item[]
     */
    public function getArmor(): array
    {
        return $this->armor;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        if ($price < 0) throw new KitException("price can't be below 0");
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getCooldown(): int
    {
        return $this->cooldown;
    }

    /**
     * @param int $cooldown
     */
    public function setCooldown(int $cooldown): void
    {
        if ($cooldown < 0) throw new KitException("cooldown can't be below 0");
        $this->cooldown = $cooldown;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     */
    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    /**
     * @return bool
     */
    public function doOverride(): bool
    {
        return $this->doOverride;
    }

    /**
     * @param bool $doOverride
     */
    public function setDoOverride(bool $doOverride): void
    {
        $this->doOverride = $doOverride;
    }

    /**
     * @return bool
     */
    public function doOverrideArmor(): bool
    {
        return $this->doOverrideArmor;
    }

    /**
     * @param bool $doOverrideArmor
     */
    public function setDoOverrideArmor(bool $doOverrideArmor): void
    {
        $this->doOverrideArmor = $doOverrideArmor;
    }

    /**
     * @return bool
     */
    public function alwaysClaim(): bool
    {
        return $this->alwaysClaim;
    }

    /**
     * @param bool $alwaysClaim
     */
    public function setAlwaysClaim(bool $alwaysClaim): void
    {
        $this->alwaysClaim = $alwaysClaim;
    }

    /**
     * @return bool
     */
    public function emptyOnClaim(): bool
    {
        return $this->emptyOnClaim;
    }

    /**
     * @param bool $emptyOnClaim
     */
    public function setEmptyOnClaim(bool $emptyOnClaim): void
    {
        $this->emptyOnClaim = $emptyOnClaim;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Kit constructor.
     * @param string $name
     * @param float $price
     * @param int $cooldown
     * @param Item[] $items
     * @param Item[] $armor
     */
    public function __construct(string $name, float $price, int $cooldown, array $items, array $armor)
    {
        $this->name = $name;
        $this->price = $price;
        $this->cooldown = $cooldown;
        $this->items = $items;
        $this->armor = $armor;
    }

}
