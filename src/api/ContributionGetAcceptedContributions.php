<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ContributionGetAcceptedContributions extends CodinGameApi
{
    public string $filter;

    public const SERVICE_URL = "Contribution/getAcceptedContributions";
    public const LEADERBOARD_TYPES = ["ALL", "PUZZLE", "CLASHOFCODE"];

    public function __construct(string $_filter = "ALL")
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->filter = $_filter;
        $this->columnNames = ["id", "title", "type", "status",  "nickname", "codingamerId", "publicHandle", "upVotes"];
        $this->requestJSON = '["' . $this->filter . '"]';
        $this->authNeeded = true;
    }
}
