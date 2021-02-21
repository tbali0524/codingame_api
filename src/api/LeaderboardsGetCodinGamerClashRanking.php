<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetCodinGamerClashRanking extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "Leaderboards/getCodinGamerClashRanking";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',"global",null]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' did " . ($this->result["clashesCount"] ?? "?")
            . " Clash of Codes, and has a ranking of " . ($this->result["rank"] ?? "?")
            . " from total players of " . ($this->result["total"] ?? "?") . PHP_EOL;
        return $s;
    }
}
