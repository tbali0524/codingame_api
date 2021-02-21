<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class PuzzleCountSolvedPuzzlesByProgrammingLanguage extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "Puzzle/countSolvedPuzzlesByProgrammingLanguage";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["programmingLanguageId", "languageName", "puzzleCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
