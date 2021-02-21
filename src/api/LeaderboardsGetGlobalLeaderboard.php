<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetGlobalLeaderboard extends CodinGameApi
{
    public int $pageNum;
    public string $publicHandle;
    public string $leaderboardType;

    public const SERVICE_URL = "Leaderboards/getGlobalLeaderboard";
    public const LEADERBOARD_TYPES = ["GENERAL", "CONTESTS", "BOT_PROGRAMMING", "OPTIM", "CODEGOLF"];

    public function __construct(
        int $_pageNum = 1,
        string $_publicHandle = MySelf::PUBLIC_HANDLE,
        string $_leaderboardType = "GENERAL"
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->pageNum = $_pageNum;
        $this->leaderboardType = $_leaderboardType;
        $this->keyToGetRows = "users";
        $this->columnNames =        ["pseudo",  "rank", "score",  "xp", "codingamer", "codingamer",   "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,      null,   null,     null, "level",      "countryId",    "userId",     "publicHandle"];
        $this->requestJSON = '[' . $this->pageNum . ',"' . $this->leaderboardType
            . '",{keyword: "", active: false, column: "", filter: ""},"' . $this->publicHandle . '",true,"global"]';
    }
}
