<?php
/**
 * Обработчик событий
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

namespace AlexBrin;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class EventHandler implements Listener {

    /**
     * @var aEconomy $plugin
     */
    private $plugin;

    public function __construct(aEconomy &$plugin) {
        $this->plugin = $plugin;
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer()->getName();

        if($this->getPlugin()->getMoney($player) === null)
            $this->getPlugin()->setMoney(
                new ConsoleCommandSender(),
                $player,
                $this->getPlugin()->getParam('defaultAmount', 1000));
    }

    public function getPlugin(): aEconomy {
        return $this->plugin;
    }

}