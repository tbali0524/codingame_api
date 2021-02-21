<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

class CodinGamerFindFollowingIds extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/findFollowingIds";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
}
