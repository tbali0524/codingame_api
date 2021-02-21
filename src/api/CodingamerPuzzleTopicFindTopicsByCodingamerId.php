<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodingamerPuzzleTopicFindTopicsByCodingamerId extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodingamerPuzzleTopic/findTopicsByCodingamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["handle", "category", "label", "puzzleCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
