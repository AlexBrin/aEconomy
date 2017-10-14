<?php
/**
 * Событие передачи денег
 * Код защищен авторским правом
 * © Alex Brin, 2017
 */

declare(strict_types=1);

namespace AlexBrin\events;

use pocketmine\event\Cancellable;

class EconomyGiveEvent extends EconomyEvent implements Cancellable {

}