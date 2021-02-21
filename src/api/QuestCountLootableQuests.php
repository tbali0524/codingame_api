<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class QuestCountLootableQuests extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "Quest/countLootableQuests";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
        $this->authNeeded = true;
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' has " . ($this->responseJSON ?? "?") . " lootable quests." . PHP_EOL;
        return $s;
    }
}
