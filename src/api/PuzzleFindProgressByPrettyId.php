<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class PuzzleFindProgressByPrettyId extends CodinGameApi
{
    public string $puzzlePrettyId;
    public string $userId;

    public const SERVICE_URL = "Puzzle/findProgressByPrettyId";

    public function __construct(
        string $_puzzlePrettyId = parent::DEFAULT_PUZZLE_PRETTY_ID,
        string $_userId = MySelf::USER_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->puzzlePrettyId = $_puzzlePrettyId;
        $this->requestJSON = '["' . $this->puzzlePrettyId . '",' . $this->userId . ']';
        $this->authNeeded = true;
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Puzzle '" . $this->puzzlePrettyId . "' (id: "
            . ($this->result["id"] ?? "?") . ") is '"
            . ($this->result["level"] ?? "?") . "' level puzzle, ";
        $p = $this->result["contributor"]["pseudo"] ?? null;
        if (!is_null($p)) {
            $s .= "contributed by '" . $p . "', ";
        }
        $s .= "solved by "
            . ($this->result["solvedCount"] ?? "?") . " players from "
            . ($this->result["attemptCount"] ?? "?") . " attempts." . PHP_EOL;
        return $s;
    }
}
