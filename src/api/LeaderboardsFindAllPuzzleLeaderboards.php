<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LeaderboardsFindAllPuzzleLeaderboards extends CodinGameApi
{
    public const SERVICE_URL = "Leaderboards/findAllPuzzleLeaderboards";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->columnNames = ["publicId", "title", "level", "creationTime", "puzzleId"];
    }
}
