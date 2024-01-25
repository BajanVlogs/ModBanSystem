<?php

namespace BanSystem;

use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\form\SimpleForm; // Add the use statement for SimpleForm

class BanGUI {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;

        // Ensure the config file is copied from resources to the data folder
        $this->saveDefaultConfig();
    }

    public function openBanGUI(Player $player) {
        // Load form data from config
        $config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
        $formTitle = $config->get("gui_title", "Ban System");
        $formContent = $config->get("gui_content", "Choose an action:");
        $options = $config->get("gui_options", ["Warning", "Temporary Ban", "Permanent Ban"]);

        // Create a SimpleForm (you can use other form types too)
        $form = new SimpleForm(function (Player $player, $data) use ($options) {
            if ($data !== null) {
                $selectedOption = $options[$data];
                $this->handleOption($player, $selectedOption);
            }
        });

        $form->setTitle($formTitle);
        $form->setContent($formContent);

        // Add options to the form
        foreach ($options as $option) {
            $form->addButton($option);
        }

        // Send the form to the player
        $player->sendForm($form);
    }

    private function handleOption(Player $player, string $selectedOption) {
        // Handle the selected option
        $player->sendMessage("You selected: $selectedOption");

        // Implement actions based on the selected option
        switch ($selectedOption) {
            case "Warning":
                // Handle warning action
                break;
            case "Temporary Ban":
                // Handle temporary ban action
                break;
            case "Permanent Ban":
                // Handle permanent ban action
                break;
            // Add more cases as needed
        }
    }

    // Function to copy the default config from resources to the data folder
    private function saveDefaultConfig() {
        $config = $this->plugin->getResource("config.yml");
        $this->plugin->saveResource("config.yml");
        if ($config !== null) {
            fclose($config);
        }
    }
}
