<?php
/**
 * Основной класс
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

namespace AlexBrin;

use AlexBrin\cmd\MoneyCommand;
use AlexBrin\events\EconomyAddEvent;
use AlexBrin\events\EconomyEvent;
use AlexBrin\events\EconomyGiveEvent;
use AlexBrin\events\EconomyReduceMoney;
use AlexBrin\events\EconomySetEvent;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class aEconomy extends PluginBase {

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Config $players
     */
    private $players;

    private static $instance;

    public function onEnable() {
        $f = $this->getDataFolder();
        if(!is_dir($f))
            @mkdir($f);

        $this->saveResource('config.yml');
        $this->config = new Config($f.'config.yml', Config::YAML);
        $this->players = new Config($f.'players.json', Config::JSON);

        $this->getServer()->getCommandMap()->register(
            'money',
            new MoneyCommand(
                'money',
                'Economy',
                'aeconomy.use',
                '/money help',
                ['eco', 'mymoney', 'economy']
            )
        );

        self::$instance = &$this;
    }

    public function getMessage($node, $vars = []): string {
        $message = $this->config->getNested('messages.' . $node, '');

        $i = 0;
        foreach($vars as $var) {
            $message = str_replace("%var$i%", $var, $message);
            $i++;
        }

        return $message;
    }

    public function getParam($node, $default) {
        return $this->config->getNested('params.' . $node, $default);
    }

    public function getHelp(): string {
        return implode(
            "\n",
            $this->config->getNested('messages.help', [])
        );
    }

    /**
     * @param  string|Player $player
     * @return string
     */
    public function getPlayer($player): string {
        if($player instanceof Player)
            $player = $player->getName();

        return mb_strtolower($player);
    }

    private function save() {
        $this->players->save();
    }

    ##################################
    #               API
    ##################################


    public function getMoney($player) {
        $player = $this->getPlayer($player);

        return $this->players->get($player);
    }

    /**
     * @param CommandSender $sender
     * @param string|Player $player
     * @param float $amount
     * @return bool
     */
    public function addMoney(CommandSender $sender, $player, float $amount): bool {
        $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player);

        $ev = new EconomyAddEvent($sender, $player, $amount, EconomyEvent::ECONOMY_ACTION_ADD);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) + $ev->getAmount()
        );
        $this->save();

        $sender->sendMessage(
            $this->getMessage(
                'add.out',
                [$ev->getAmount()]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'add.in',
                    [
                        $ev->getAmount(),
                        $this->getMoney($player)
                    ]
                )
            );
        return true;
    }

    /**
     * @param CommandSender $sender
     * @param string|Player $player
     * @param float $amount
     * @return bool
     */
    public function setMoney(CommandSender $sender, $player, float $amount): bool {
        $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player) ?? $player;

        $ev = new EconomySetEvent($sender, $player, $amount, EconomyEvent::ECONOMY_ACTION_SET);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $ev->getAmount()
        );
        $this->save();

        $sender->sendMessage(
            $this->getMessage(
                'set.out',
                [$ev->getAmount(), $player->getName()]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'set.in',
                    [$ev->getAmount()]
                )
            );
        return true;
    }

    /**
     * @param CommandSender $sender
     * @param $player
     * @param float $amount
     * @return bool
     */
    public function reduceMoney(CommandSender $sender, $player, float $amount): bool {
        if(!$player instanceof Player && !$player instanceof OfflinePlayer)
            $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player) ?? $player;

        $ev = new EconomyReduceMoney($sender, $player, $amount, EconomyEvent::ECONOMY_ACTION_REDUCE);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) - $ev->getAmount()
        );

        $sender->sendMessage(
            $this->getMessage(
                'reduce.out',
                [$ev->getAmount(), $player->getName()]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'reduce.in',
                    [$ev->getAmount()]
                )
            );

        return true;
    }

    /**
     * @param CommandSender $sender
     * @param $player
     * @param float $amount
     * @return bool
     */
    public function giveMoney(CommandSender $sender, $player, float $amount): bool {
        $player = $this->getServer()->getPlayer($player) ?? $this->getServer()->getOfflinePlayer($player) ?? $player;

        $ev = new EconomyGiveEvent($sender, $player, $amount, EconomyEvent::ECONOMY_ACTION_GIVE);
        if($ev->isCancelled())
            return false;

        $this->players->set(
            $this->getPlayer($sender),
            $this->getMoney($sender) - $ev->getAmount()
        );
        $this->players->set(
            $this->getPlayer($player),
            $this->getMoney($player) + $ev->getAmount()
        );

        $sender->sendMessage(
            $this->getMessage(
                'give.out',
                [$ev->getAmount(), $player->getName()]
            )
        );

        if($player instanceof Player)
            $player->sendMessage(
                $this->getMessage(
                    'give.in',
                    [$ev->getAmount(), $player->getName()]
                )
            );

        return true;
    }


    /**
     * @return aEconomy
     */
    public static function getInstance(): aEconomy {
        return self::$instance;
    }

}