<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class AchievementFindByCodingamerId extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "Achievement/findByCodingamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["id", "title", "categoryId", "groupId", "level", "points"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
