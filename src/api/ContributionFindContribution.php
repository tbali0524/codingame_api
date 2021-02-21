<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ContributionFindContribution extends CodinGameApi
{
    public string $contributionPublicHandle;

    public const SERVICE_URL = "Contribution/findContribution";

    public function __construct(string $_contributionPublicHandle = parent::DEFAULT_CONTRIBUTION_PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->contributionPublicHandle = $_contributionPublicHandle;
        $this->requestJSON = '["' . $this->contributionPublicHandle . '",true]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Contribution '" . $this->contributionPublicHandle . "' ('"
            . ($this->result["title"] ?? "?") . "') by '"
            . ($this->result["nickname"] ?? "?") . "' is a '"
            . ($this->result["type"] ?? "?") . "' and received "
            . ($this->result["score"] ?? "??") . " score." . PHP_EOL;
        return $s;
    }
}
