<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class SchoolFindById extends CodinGameApi
{
    public int $schoolId;

    public const SERVICE_URL = "School/findById";

    public function __construct(int $_schoolId = MySelf::SCHOOL_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->schoolId = $_schoolId;
        $this->requestJSON = '[' . $this->schoolId . ']';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "School '" . $this->schoolId . "' is "
            . ($this->result["name"] ?? "?") . ", located in "
            . ($this->result["city"] ?? "?") . ", "
            . ($this->result["countryId"] ?? "?") . PHP_EOL;
        return $s;
    }
}
