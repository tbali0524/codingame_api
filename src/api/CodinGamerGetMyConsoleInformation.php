<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodinGamerGetMyConsoleInformation extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/getMyConsoleInformation";

    public function __construct(string $_userId = MySelf::USER_ID, bool $getPuzzles = true)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        if ($getPuzzles) {
            $this->keyToGetRows = "puzzles";
            $this->columnNames = ["puzzlePublicId", "labelTitle", "ranking", "totalPlayers"];
        } else {
            $this->keyToGetRows = "challenges";
            $this->columnNames = ["publicId", "title", "ranking", "total"];
        }
        $this->fieldFixedKey = "group";
        $this->fieldFixedValue = $this->keyToGetRows;
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
