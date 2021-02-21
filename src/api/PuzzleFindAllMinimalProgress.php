<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class PuzzleFindAllMinimalProgress extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "Puzzle/findAllMinimalProgress";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->authNeeded = true;
        $this->columnNames = ["id", "level", "creationTime", "solvedCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
