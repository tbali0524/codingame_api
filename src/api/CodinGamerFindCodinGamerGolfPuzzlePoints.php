<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

// OBSOLETE, this API no longer works
final class CodinGamerFindCodinGamerGolfPuzzlePoints extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/findCodinGamerGolfPuzzlePoints";
    public const GOLF_PUZZLE_IDS = [762986, 37513, 54473, 37514];

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',[' . implode(',', self::GOLF_PUZZLE_IDS) . ']]';
    }
}
