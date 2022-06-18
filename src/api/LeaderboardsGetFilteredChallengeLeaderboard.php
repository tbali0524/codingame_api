<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetFilteredChallengeLeaderboard extends CodinGameApi
{
    public string $publicHandle;
    public string $challengePublicId;

    public const SERVICE_URL = "Leaderboards/getFilteredChallengeLeaderboard";

    public function __construct(
        string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID,
        string $_publicHandle = MySelf::PUBLIC_HANDLE,
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->challengePublicId = $_challengePublicId;
        $this->keyToGetRows = "users";
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->columnNames =        ["rank", "leagueName", "programmingLanguage", "pseudo", "codingamer",   "codingamer", "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,   null,          null,                 null,     "level",        "countryId",  "userId",     "publicHandle"];
        // phpcs:enable
        $this->fieldFixedKey = "challengePublicId";
        $this->fieldFixedValue = $this->challengePublicId;
        $this->requestJSON = '["' . $this->challengePublicId . '","' . $this->publicHandle
            . '","global",{"active":false,"column":"","filter":""}]';
    }
}
