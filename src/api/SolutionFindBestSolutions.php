<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class SolutionFindBestSolutions extends CodinGameApi
{
    public string $userId;
    public int $soloPuzzleId;
    public ?string $programmingLanguageId;

    public const SERVICE_URL = "Solution/findBestSolutions";

    public function __construct(
        string $_userId = MySelf::USER_ID,
        int $_soloPuzzleId = parent::DEFAULT_SOLO_PUZZLE_ID,
        ?string $_programmingLanguageId = null
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->soloPuzzleId = $_soloPuzzleId;
        $this->programmingLanguageId = $_programmingLanguageId;
        $this->columnNames = ["pseudo", "programmingLanguageId", "codingamerId"];
        if (is_null($this->programmingLanguageId)) {
            $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',null,false]';
        } else {
            $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',"'
                . $this->programmingLanguageId . '", false]';
        }
        $this->authNeeded = true;
    }
}
