<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodinGamerFindFollowers extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/findFollowers";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["userId", "pseudo", "countryId", "city", "level", "points", "rank"];
        $this->requestJSON = '[' . $this->userId . ',' . $this->userId . ', null]';
        $this->authNeeded = true;
    }
}
