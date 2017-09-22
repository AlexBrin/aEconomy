<?php
/**
 * Событие экономики
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

namespace AlexBrin\events;

use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

abstract class EconomyEvent extends PlayerEvent {

    const ECONOMY_ACTION_ADD = 1;
    const ECONOMY_ACTION_SET = 2;
    const ECONOMY_ACTION_REDUCE = 3;
    const ECONOMY_ACTION_GIVE = 4;
    const ECONOMY_ACTION_SHOW = 5;
    // ;)
    //const ECONOMY_ACTION_REQUEST;
    //const ECONOMY_ACTION_REQUEST_TRUE;
    //const ECONOMY_ACTION_REQUEST_FALSE;

    /**
     * @var CommandSender $commandSender
     */
    protected $commandSender;

    /**
     * @var Player $player
     */
    protected $player;

    /**
     * @var float $amount
     */
    protected $amount;

    /**
     * @var int $action
     */
    protected $action;

    /**
     * EconomyEvent constructor.
     * @param CommandSender $sender
     * @param Player $player
     * @param float $amount
     * @param int $action
     */
    public function __construct(CommandSender $sender, Player $player, float $amount, int $action) {
        $this->commandSender = $sender;
        $this->player = $player;
        $this->amount = $amount;
        $this->action = $action;
    }

    /**
     * @return CommandSender
     */
    public function getCommandSender(): CommandSender {
        return $this->commandSender;
    }

    /**
     * @return CommandSender
     */
    public function getSender(): CommandSender {
        return $this->getCommandSender();
    }

    /**
     * @return float
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAction(): int {
        return $this->action;
    }

}