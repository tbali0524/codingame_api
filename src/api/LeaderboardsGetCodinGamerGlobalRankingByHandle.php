<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetCodinGamerGlobalRankingByHandle extends CodinGameApi
{
    public string $publicHandle;

    public const SERVICE_URL = "Leaderboards/getCodinGamerGlobalRankingByHandle";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '","GENERAL","global",null]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' (pseudo: "
            . ($this->result["pseudo"] ?? "?") . ") has "
            . ($this->result["score"] ?? "?") . " CP, rank = "
            . ($this->result["rank"] ?? "?") . ". from total of "
            . ($this->result["total"] ?? "?") . " players." . PHP_EOL;
        return $s;
    }
}
