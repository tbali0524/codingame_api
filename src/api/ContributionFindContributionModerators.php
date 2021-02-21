<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ContributionFindContributionModerators extends CodinGameApi
{
    public int $contributionId;

    public const SERVICE_URL = "Contribution/findContributionModerators";

    public function __construct(int $_contributionId = parent::DEFAULT_CONTRIBUTION_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->contributionId = $_contributionId;
        $this->columnNames = ["userId", "pseudo", "publicHandle"];
        $this->requestJSON = '[' . $this->contributionId . ',"validate"]';
    }
}
