<?php

declare(strict_types=1);

namespace AndreasHGK\EasyKits\ui;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\utils\LangUtils;
use Closure;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Effect;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;

class EditkitPotionSelectForm {

    public static function sendTo(Player $player, Kit $kit) : void {
        $ui = new SimpleForm(function (Player $player, $data) use ($kit) {
            if($data === null) {
                EditkitMainForm::sendTo($player, $kit);
                return;
            }

            EditkitPotionForm::sendTo($player, $kit, (int)$data);

            return;
        });
        $ui->setTitle(LangUtils::getMessage("editkit-title"));
        $ui->setContent(LangUtils::getMessage("editkit-potionselect-text"));

        $effects = Closure::bind(static function () {
            return Effect::$effects;
        }, null, Effect::class)();
        foreach($effects as $effect) {
            $ui->addButton(LangUtils::getMessage("editkit-potionselect-button", true, ["{POTION}" => new TranslationContainer($effect->getName())]), -1, "", (string)$effect->getId());
        }
        $player->sendForm($ui);
    }

}