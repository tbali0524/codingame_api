<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodinGamerFindCodingamePointsStatsByHandle extends CodinGameApi
{
    public string $publicHandle;

    public const SERVICE_URL = "CodinGamer/findCodingamePointsStatsByHandle";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' has "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsXp"] ?? "?") . " XP, "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsTotal"] ?? "?") . " CP, rank = "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsRank"] ?? "?") . ". from total of "
            . ($this->result["codingamePointsRankingDto"]["numberCodingamers"] ?? "?") . " players." . PHP_EOL;
        $s .= "Distribution of total CP is "
            . "Contest: " . ($this->result["codingamePointsRankingDto"]["codingamePointsContests"] ?? "?")
            . ", Multi: " . ($this->result["codingamePointsRankingDto"]["codingamePointsMultiTraining"] ?? "?")
            . ", Optim: " . ($this->result["codingamePointsRankingDto"]["codingamePointsOptim"] ?? "?")
            . ", Code golf: " . ($this->result["codingamePointsRankingDto"]["codingamePointsCodegolf"] ?? "?")
            . ", Clash: " . ($this->result["codingamePointsRankingDto"]["codingamePointsClash"] ?? "?") . PHP_EOL;
        return $s;
    }
}
