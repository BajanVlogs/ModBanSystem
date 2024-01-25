<?php

namespace ModBanSystem;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BanCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("modbansystem", "ModBanSystem command", "/modbansystem", []);
        $this->plugin = $plugin;
    }

    public
