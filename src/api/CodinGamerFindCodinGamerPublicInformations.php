<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class CodinGamerFindCodinGamerPublicInformations extends CodinGameApi
{
    public string $userId;

    public const SERVICE_URL = "CodinGamer/findCodinGamerPublicInformations";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' (pseudo: '"
            . ($this->result["pseudo"] ?? "?") . "') has level "
            . ($this->result["level"] ?? "?") . " and is from "
            . ($this->result["city"] ?? "?") . ", "
            . ($this->result["countryId"] ?? "??") . PHP_EOL;
        return $s;
    }
}
