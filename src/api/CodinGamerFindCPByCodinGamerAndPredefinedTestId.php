<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

// OBSOLETE, this API no longer works
final class CodinGamerFindCPByCodinGamerAndPredefinedTestId extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/findCPByCodinGamerAndPredefinedTestId";
    public const DEFAULT_TEST_IDS = [
        823636, 817286, 782435, 835210, 52158, 260665, 427580, 68589, 502798, 674483,
        298135, 53134, 40288, 37511, 199893, 64142, 695739, 46738, 34944, 25745, 6634, 6188, 818833, 60823
    ];

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',[' . implode(',', self::DEFAULT_TEST_IDS) . ']]';
    }
}
