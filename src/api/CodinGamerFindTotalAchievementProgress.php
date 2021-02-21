<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodinGamerFindTotalAchievementProgress extends CodinGameApi
{
    public string $publicHandle;

    public const SERVICE_URL = "CodinGamer/findTotalAchievementProgress";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' has " . ($this->result["achievementCount"] ?? "?")
            . " achievements from total of " . ($this->result["achievementTotal"] ?? "?") . PHP_EOL;
        return $s;
    }
}
