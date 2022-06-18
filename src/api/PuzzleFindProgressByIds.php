<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class PuzzleFindProgressByIds extends CodinGameApi
{
    public array $puzzleIdArray;
    public string $userId;

    public const SERVICE_URL = "Puzzle/findProgressByIds";

    public function __construct(
        array $_puzzleIdArray = [parent::DEFAULT_SOLO_PUZZLE_ID],
        string $_userId = MySelf::USER_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->puzzleIdArray = $_puzzleIdArray;
        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->columnNames =        ["prettyId", "title", "level", "contributor", "creationTime", "id", "solvedCount", "globalTotal", "leagueName", "position", "total"];
        $this->columnNamesDepth2 =  [null,          null, null,     "pseudo",       null,       null,   null,           null,           null,       null,       null,];
        // phpcs:enable
        $this->requestJSON = '[[' . implode(',', $this->puzzleIdArray) . '],' . $this->userId . ',2]';
    }
}
