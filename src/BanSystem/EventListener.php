<?php

namespace BanSystem;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;

class EventListener implements Listener {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) {
        // Check if the player is banned and handle accordingly
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($this->plugin->getBanManager()->isBanned($name)) {
            $event->setCancelled(true);
            $player->kick("You are banned from the server.");
        }
    }
}
