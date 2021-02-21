<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsGetCodinGamerChallengeRanking extends CodinGameApi
{
    public string $userId;
    public string $challengePublicId;

    public const SERVICE_URL = "Leaderboards/getCodinGamerChallengeRanking";

    public function __construct(
        string $_userId = MySelf::USER_ID,
        string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID,
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '[' . $this->userId . ',"' . $this->challengePublicId . '","global"]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . ($this->result["pseudo"] ?? "") . "' has a ranking of "
            . ($this->result["rank"] ?? "") . " in challenge " . $this->challengePublicId;
        $divisionCount = ($this->result["league"]["divisionCount"] ?? 0);
        $leagueName = $this->getLeagueName($this->result["league"] ?? []);
        if ($divisionCount != 0) {
            $s .= ", and is in the " . $leagueName . " league";
        }
        $s .= PHP_EOL;
        return $s;
    }
}
