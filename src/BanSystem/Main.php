<?php

namespace BanSystem;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase {

    private $banManager;

    public function onEnable() {
        $this->getLogger()->info("BanSystem has been enabled!");

        $this->saveDefaultConfig();
        $this->banManager = new BanManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable() {
        $this->getLogger()->info("BanSystem has been disabled!");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if ($cmd->getName() === "bansystem" && $sender->hasPermission("bansystem.use")) {
            $this->getBanGUI()->openBanGUI($sender);
            return true;
        }
        return false;
    }

    public function getBanManager(): BanManager {
        return $this->banManager;
    }

    public function getBanGUI(): BanGUI {
        return new BanGUI($this);
    }
}
