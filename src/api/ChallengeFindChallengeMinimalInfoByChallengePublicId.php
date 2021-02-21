<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class ChallengeFindChallengeMinimalInfoByChallengePublicId extends CodinGameApi
{
    public string $challengePublicId;

    public const SERVICE_URL = "Challenge/findChallengeMinimalInfoByChallengePublicId";

    public function __construct(string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '["' . $this->challengePublicId . '"]';
    }

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = $this->challengePublicId . " is a " . ($this->result["type"] ?? "unknown")
            . " type challenge, titled: " . ($this->result["title"] ?? "-") . PHP_EOL;
        return $s;
    }
}
