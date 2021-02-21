<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class LastActivitiesGetLastActivities extends CodinGameApi
{
    public string $userId;
    public int $countActivities;

    public const SERVICE_URL = "LastActivities/getLastActivities";

    public function __construct(string $_userId = MySelf::USER_ID, int $_countActivities = 3)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->countActivities = $_countActivities;
        $this->requestJSON = '[' . $this->userId . ',' . $this->countActivities . ']';
        $this->authNeeded = true;
    }
}
