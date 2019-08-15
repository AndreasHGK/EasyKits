<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits;

use AndreasHGK\EasyKits\event\InteractItemClaimEvent;
use AndreasHGK\EasyKits\event\KitClaimEvent;
use AndreasHGK\EasyKits\manager\CooldownManager;
use AndreasHGK\EasyKits\manager\DataManager;
use AndreasHGK\EasyKits\manager\EconomyManager;
use AndreasHGK\EasyKits\utils\KitException;
use AndreasHGK\EasyKits\utils\LangUtils;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

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

    /**
     * @var EffectInstance[]|array
     */
    protected $effects = [];

    /**
     * @var array|string[]
     */
    protected $commands = [];

    /**
     * @var Item
     */
    protected $interactItem = null;

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
     * @var bool
     */
    protected $chestKit = false;

    public function claim(Player $player) : bool {
        if($this->isChestKit()) return $this->claimChestKitFor($player);
        else return $this->claimFor($player);
    }

    public function claimChestKitFor(Player $player) : bool {
        if(!$this->hasPermission($player) && $this->isLocked()) throw new KitException("Player is not permitted to claim this kit", 4);
        if($this->getCooldown() > 0){
            if(CooldownManager::hasKitCooldown($this, $player)){
                throw new KitException("Kit is on cooldown", 0);
            }
        }
        if($this->getPrice() > 0){
            if(EconomyManager::isEconomyLoaded()){
                if(EconomyManager::getMoney($player) < $this->getPrice()){
                    throw new KitException("Player has insufficient funds", 1);
                }
            }else{
                throw new KitException("Economy not found", 2);
            }
        }

        if(count($player->getInventory()->getContents(false)) >= $player->getInventory()->getSize()){
            throw new KitException("Player has insufficient space", 3);
        }

        $kit = clone $this;
        if($player->hasPermission(EasyKits::PERM_ROOT."free.".$kit->name)) $kit->price = 0;
        if($player->hasPermission(EasyKits::PERM_ROOT."instant.".$kit->name)) $kit->cooldown = 0;

        $event = new InteractItemClaimEvent($kit, $player);
        $event->call();

        if($event->isCancelled()) return false;


        $player = $event->getPlayer();
        $kit = $event->getKit();

        if($kit->getCooldown() > 0){
            CooldownManager::setKitCooldown($kit, $player);
        }
        if($kit->getPrice() > 0){
            EconomyManager::reduceMoney($player, $kit->getPrice(), true);
        }
        $player->getInventory()->addItem($kit->getInteractItem());
        return true;
    }

    /**
     * claim a kit as a player
     * @param Player $player
     * @return bool
     * @throws KitException
     */
    public function claimFor(Player $player) : bool {
        if(!$this->hasPermission($player) && $this->isLocked()) throw new KitException("Player is not permitted to claim this kit", 4);
        if($this->getCooldown() > 0){
            if(CooldownManager::hasKitCooldown($this, $player)){
                throw new KitException("Kit is on cooldown", 0);
            }
        }
        if($this->getPrice() > 0){
            if(EconomyManager::isEconomyLoaded()){
                if(EconomyManager::getMoney($player) < $this->getPrice()){
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
        $kit = clone $this;
        if($player->hasPermission(EasyKits::PERM_ROOT."free.".$kit->name)) $kit->price = 0;
        if($player->hasPermission(EasyKits::PERM_ROOT."instant.".$kit->name)) $kit->cooldown = 0;
        $event = new KitClaimEvent($kit, $player);
        $event->call();

        if($event->isCancelled()) return false;

        $player = $event->getPlayer();
        $kit = $event->getKit();

        if($kit->getCooldown() > 0){
            CooldownManager::setKitCooldown($kit, $player);
        }
        if($kit->getPrice() > 0){
            EconomyManager::reduceMoney($player, $kit->getPrice(), true);
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
        foreach($kit->getEffects() as $effect){
            $player->addEffect($effect);
        }
        foreach($kit->getCommands() as $command){
            Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
        }
        return true;
    }

    public function getInteractItem() : ?Item {
        return $this->interactItem;
    }

    public function hasInteractItem() : bool {
        return $this->getItems() !== null;
    }

    public function setInteractItem(Item $item) : void {
        if(!$item->getNamedTag()->hasTag("ekit") || $item->getNamedTag()->getTagValue("ekit", StringTag::class) !== $this->getName()){
            $item->setNamedTagEntry(new StringTag("ekit", $this->name));
        }
        $this->interactItem = $item;
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
     * @return EffectInstance[]|array
     */
    public function getEffects() : array {
        return $this->effects;
    }

    /**
     * @param array|EffectInstance[] $effects
     */
    public function setEffects(array $effects) : void {
        $this->effects = $effects;
    }

    /**
     * @return array|string[]
     */
    public function getCommands() : array {
        return $this->commands;
    }

    /**
     * @param array|string[] $commands
     */
    public function setCommands(array $commands) : void {
        $this->commands = $commands;
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

    /**
     * @return bool
     */
    public function isChestKit() : bool {
        return $this->chestKit;
    }

    /**
     * @param bool $bool
     */
    public function setChestKit(bool $bool) : void {
        $this->chestKit = $bool;
        if($bool){
            $item = ItemFactory::get(DataManager::getKey(DataManager::CONFIG, "chestKit-itemid"));
            $item->setCustomName(LangUtils::getMessage("chestkit-name", true, ["{NAME}" => $this->getName()]));
            $item->setLore(LangUtils::getMessage("chestkit-lore"));
            $this->setInteractItem($item);
        }elseif(isset($this->chestKit)){
            $this->interactItem = null;
        }
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

        PermissionManager::getInstance()->addPermission(new Permission(EasyKits::PERM_ROOT."kit.".$name, "permission to claim kit ".$name, DataManager::getKey(DataManager::CONFIG, "op-has-all-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
        PermissionManager::getInstance()->addPermission(new Permission(EasyKits::PERM_ROOT."free.".$name, "permission to claim kit ".$name." for free", DataManager::getKey(DataManager::CONFIG, "op-has-free-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
        PermissionManager::getInstance()->addPermission(new Permission(EasyKits::PERM_ROOT."instant.".$name, "permission to claim kit ".$name." without cooldown", DataManager::getKey(DataManager::CONFIG, "op-has-instant-kits") ? Permission::DEFAULT_OP : Permission::DEFAULT_FALSE));
    }

}
