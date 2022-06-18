<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetFilteredPuzzleLeaderboard extends CodinGameApi
{
    public string $publicHandle;
    public string $puzzlePublicId;

    public const SERVICE_URL = "Leaderboards/getFilteredPuzzleLeaderboard";

    public function __construct(
        string $_puzzlePublicId = parent::DEFAULT_PUZZLE_PUBLIC_ID,
        string $_publicHandle = MySelf::PUBLIC_HANDLE
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->puzzlePublicId = $_puzzlePublicId;
        $this->keyToGetRows = "users";
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->columnNames =        ["rank", "leagueName", "programmingLanguage", "pseudo", "codingamer",   "codingamer",   "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,   null,          null,                 null,     "level",        "countryId",    "userId",     "publicHandle"];
        // phpcs:enable
        $this->fieldFixedKey = "puzzlePublicId";
        $this->fieldFixedValue = $this->puzzlePublicId;
        $this->requestJSON = '["' . $this->puzzlePublicId . '","' . $this->publicHandle
            . '","global",{"active":false,"column":"","filter":""}]';
    }
}
