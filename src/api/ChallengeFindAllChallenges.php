<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ChallengeFindAllChallenges extends CodinGameApi
{
    public const SERVICE_URL = "Challenge/findAllChallenges";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->columnNames = ["publicId", "title", "type", "date"];
    }
}
