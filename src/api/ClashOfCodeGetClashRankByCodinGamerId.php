<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ClashOfCodeGetClashRankByCodinGamerId extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "ClashOfCode/getClashRankByCodinGamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Clash of Code ranking of player '" . $this->userId . "' is "
            . ($this->result["rank"] ?? "?") . " from total players of "
            . ($this->result["totalPlayers"] ?? "?") . PHP_EOL;
        return $s;
    }
}
