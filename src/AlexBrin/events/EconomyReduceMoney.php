<?php
/**
 * Событие вычитания денег
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

declare(strict_types=1);

namespace AlexBrin\events;

use pocketmine\event\Cancellable;

class EconomyReduceMoney extends EconomyEvent implements Cancellable {

}