<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ContributionGetAllPendingContributions extends CodinGameApi
{
    public string $userId;
    public string $filter;

    public const SERVICE_URL = "Contribution/getAllPendingContributions";
    public const LEADERBOARD_TYPES = ["ALL", "PUZZLE", "CLASHOFCODE"];

    public function __construct(string $_userId = MySelf::USER_ID, string $_filter = "ALL")
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->filter = $_filter;
        $this->columnNames = ["id", "title", "type", "status",  "nickname", "codingamerId", "publicHandle", "upVotes"];
        $this->requestJSON = '[1,"' . $this->filter . '",' . $this->userId . ']';
        $this->authNeeded = true;
    }
}
