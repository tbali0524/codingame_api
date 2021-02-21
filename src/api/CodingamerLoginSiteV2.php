<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodingamerLoginSiteV2 extends CodinGameApi
{
    public string $userId = MySelf::USER_ID;

    public const SERVICE_URL = "Codingamer/loginSiteV2";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->requestJSON = '["' . Myself::EMAIL . '","' . Myself::PASSWORD . '",true]';
    }
}
