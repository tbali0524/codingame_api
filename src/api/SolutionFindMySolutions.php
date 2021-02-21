<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class SolutionFindMySolutions extends CodinGameApi
{
    public string $userId;
    public int $soloPuzzleId;

    public const SERVICE_URL = "Solution/findMySolutions";

    public function __construct(
        string $_userId = MySelf::USER_ID,
        int $_soloPuzzleId = parent::DEFAULT_SOLO_PUZZLE_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->soloPuzzleId = $_soloPuzzleId;
        $this->columnNames = ["pseudo", "programmingLanguageId"];
        $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',null]';
        $this->authNeeded = true;
    }
}
