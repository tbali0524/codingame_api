<?php

/*
--------------------------------------------------------------------
 CodinGame data downloader & API tool
  (c) 2021 by Bálint Tóth (TBali)
   v1.07
  latest source can be found at:
    https://github.com/tbali0524/codingame_api
  required PHP version: 7.4 or higher
--------------------------------------------------------------------
*/

declare(strict_types=1);

namespace CG;

require_once('misc.php');   // defines login credentials with EMAIL and PW constants

// --------------------------------------------------------------------
// used for default request values and login credentials
abstract class MySelf
{
    public const PSEUDO = "TBali";
    public const USER_ID = "3305510";
    public const PUBLIC_HANDLE = "08e6e13d9f7cad047d86ec4d10c777500155033";
    public const EMAIL = EMAIL;
    public const PASSWORD = PW;
    public const SCHOOL_ID = 467;
    public const AVATAR_ID = "26750785092441";
    public const COVER_ID = "27032383437051";
}
// class MySelf

// --------------------------------------------------------------------
// GENERIC API HANDLER
// --------------------------------------------------------------------
abstract class CodinGameApi
{
    public ?string $serviceURL = null;
    public ?string $requestJSON = "[]";
    public ?string $responseJSON = null;
    public ?array $result = null;               // full response json as multi-level array
    public ?array $filteredResult = null;       // single level table with columnNames
    public bool $authNeeded = false;
    public bool $loggedIn = false;

    // extract filtered data to CSV
    public ?string $keyToGetRows = null;
    public ?array $columnNames = null;          // array of string
    public ?array $columnNamesDepth2 = null;    // array of string
    public ?string $fieldFixedKey = null;
    public ?string $fieldFixedValue = null;

    public const BASE_URL = "https://www.codingame.com/services/";
    public const LEAGUE_NAME = ["Legend", "Gold", "Silver", "Bronze", "Wood", "Wood", "Wood", "Wood", "Wood", "Wood"];
    public const LEAGUE_NAME_NONE = "None";
    public const CONTENT_TYPE = "application/json;charset=UTF-8";
    public const COOKIEJAR_FILENAME = "cookie.txt";

    public const DEFAULT_PUZZLE_PUBLIC_ID = "tower-dereference";
    public const DEFAULT_CHALLENGE_PUBLIC_ID = "a-code-of-ice-and-fire";
    public const DEFAULT_TOPIC_HANDLE = "combinatorics";
    public const DEFAULT_PUZZLE_PRETTY_ID = "hello-world";
    public const DEFAULT_SOLO_PUZZLE_ID = 539;
    public const DEFAULT_CONTRIBUTION_ID = 4528;
    public const DEFAULT_CONTRIBUTION_PUBLIC_HANDLE = "452848ff9a694483d6a668e0927484f877e7";
    public const MAX_DEPTH_JSON = 100;

    public function callApi($session = null)
    {
        if (is_null($this->serviceURL)) {
            die("ERROR: missing service URL");
        }
        if (is_null($session)) {
            $curl = curl_init();
        } else {
            $curl = $session;
        }
        if ($this->authNeeded and !$this->loggedIn) {
            $g = new \CG\CodingamerLoginSiteV2();
            $g->callApi($curl);
            $this->loggedIn = true;
        }
        curl_setopt($curl, CURLOPT_URL, $this->serviceURL);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->requestJSON);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, false);
        curl_setopt($curl, CURLOPT_COOKIEJAR, self::COOKIEJAR_FILENAME);
        curl_setopt($curl, CURLOPT_COOKIEFILE, self::COOKIEJAR_FILENAME);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . self::CONTENT_TYPE,
        ));
        $this->responseJSON = curl_exec($curl);
        if (($this->responseJSON === false) or (curl_errno($curl) != 0)) {
            die("ERROR: Connection Failure: " . curl_error($curl));
        }
        if (is_null($session)) {
            curl_close($curl);
        }
        $this->result = json_decode($this->responseJSON, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            die("ERROR: Response is not in valid JSON format.");
        }
    }
    // function callApi

    public function writeRequestJSON(string $fileName): void
    {
        if (is_null($this->requestJSON)) {
            return;
        }
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        fwrite($f, $this->requestJSON);
        fclose($f);
    }
    // function writeRequestJSON

    public function writeResponseJSON(string $fileName, bool $isPretty = true): void
    {
        if (is_null($this->responseJSON)) {
            return;
        }
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        if ($isPretty) {
            if (is_null($this->result)) {
                $this->result = json_decode($this->responseJSON, true);
            }
            if (json_last_error() != JSON_ERROR_NONE) {
                die("ERROR: Response is not in valid JSON format.");
            }
            $output = json_encode($this->result, JSON_PRETTY_PRINT);
        } else {
            $output = $this->responseJSON;
        }
        fwrite($f, $output);
        fclose($f);
    }
    // function writeResponseJSON

    public function readFromJSON(string $fileName): void
    {
        $this->responseJSON = file_get_contents($fileName);
        if ($this->responseJSON === false) {
            die("ERROR: Cannot open json file.");
        }
        $this->result = json_decode($this->responseJSON, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            die("ERROR: file is not in valid JSON format.");
        }
    }
    // function readFromJSON

    public function getLeagueName(array $inputRow = []): string
    {
        $divisionIndex = $inputRow["divisionIndex"] ?? 0;
        $divisionCount = $inputRow["divisionCount"] ?? 0;
        $divisionOffset = $inputRow["divisionOffset"] ?? 0;
        $leagueId = $divisionCount + $divisionOffset - $divisionIndex - 1;
        return self::LEAGUE_NAME[$leagueId] ?? self::LEAGUE_NAME_NONE;
    }
    // function getLeagueName

    public function getRow($inputRow): array
    {
        $row = array();
        if (is_null($this->columnNames)) {
            return $row;
        }
        foreach ($this->columnNames as $idx => $key) {
            if ($key == "leagueName") {
                $row[] = $this->getLeagueName($inputRow["league"] ?? []);
            } elseif (is_null($this->columnNamesDepth2[$idx] ?? null)) {
                $row[] = $inputRow[$key] ?? "";
            } else {
                $row[] = $inputRow[$key][$this->columnNamesDepth2[$idx]] ?? "";
            }
        }
        return $row;
    }
    // function getRow

    // returns number of rows written (excluding header)
    public function writeFilteredCSV($f, bool $headerRow = true, int $startUid = 0): int
    {
        if (is_null($this->result)) {
            return 0;
        }
        if (is_null($this->columnNames)) {
            return 0;
        }
        if ($headerRow) {
            $row = array();
            $row[] = 'uid';
            if (!is_null($this->fieldFixedKey)) {
                $row[] = $this->fieldFixedKey;
            }
            foreach ($this->columnNames as $idx => $item) {
                if (is_null($this->columnNamesDepth2[$idx] ?? null)) {
                    $row[] = $item;
                } else {
                    $row[] = $this->columnNamesDepth2[$idx];
                }
            }
            fputcsv($f, $row);
        }
        if (is_null($this->keyToGetRows)) {
            $rows = $this->result;
        } else {
            $rows = $this->result[$this->keyToGetRows] ?? [];
        }
        $uid = $startUid;
        foreach ($rows as $inputRow) {
            $row = array();
            $row[] = $uid;
            if (!is_null($this->fieldFixedValue)) {
                $row[] = $this->fieldFixedValue;
            }
            $row2 = $this->getRow($inputRow);
            foreach ($row2 as $item) {
                $row[] = $item;
            }
            fputcsv($f, $row);
            $uid++;
        }
        return $uid - $startUid;
    }
    // function writeFilteredCSV

    public function extractFilteredTable(): void
    {
        if (is_null($this->result)) {
            return;
        }
        if (is_null($this->columnNames)) {
            return;
        }
        $this->filteredResult = array();
        if (is_null($this->keyToGetRows)) {
            $rows = $this->result;
        } else {
            $rows = $this->result[$this->keyToGetRows] ?? [];
        }
        foreach ($rows as $inputRow) {
            $row = array();
            foreach ($this->columnNames as $idx => $key) {
                if ($key == "leagueName") {
                    $row[$key] = $this->getLeagueName($inputRow["league"] ?? []);
                } elseif (is_null($this->columnNamesDepth2[$idx] ?? null)) {
                    $row[$key] = $inputRow[$key] ?? "";
                } else {
                    $row[$this->columnNamesDepth2[$idx]] = $inputRow[$key][$this->columnNamesDepth2[$idx]] ?? "";
                }
            }
            $this->filteredResult[] = $row;
        }
    }
    // function extractFilteredTable

    public function writeFilteredTableCSV($f, bool $headerRow = true): void
    {
        if (is_null($this->filteredResult)) {
            return;
        }
        if (count($this->filteredResult) == 0) {
            return;
        }
        if ($headerRow) {
            $row = [];
            foreach ($this->filteredResult[0] as $key => $value) {
                $row[] = $key;
            }
            fputcsv($f, $row);
        }
        foreach ($this->filteredResult as $inputRow) {
            $row = [];
            foreach ($this->filteredResult[0] as $key => $value) {
                $row[] = $inputRow[$key] ?? "";
            }
            fputcsv($f, $row);
        }
    }
    // function writeFilteredTableCSV

    private function echoItem($key, $value, $depth = 0)
    {
        $pad = str_repeat(' ', $depth * 4);
        if (is_array($value)) {
            echo $pad . $key . " : (list)", PHP_EOL;
            if ($depth < self::MAX_DEPTH_JSON) {
                foreach ($value as $key2 => $value2) {
                    $this->echoItem($key2, $value2, $depth + 1);
                }
            }
            return;
        }
        echo $pad . $key . " = ";
        if ($value === true) {
            echo "true";
        } elseif ($value === false) {
            echo "false";
        } else {
            echo $value;
        }
        echo PHP_EOL;
    }
    // function echoItem

    public function echoResult()
    {
        if (is_null($this->result)) {
            return;
        }
        foreach ($this->result as $key => $value) {
            $this->echoItem($key, $value);
        }
    }
    // function echoResult

    public function getSummary(): string
    {
        if (is_null($this->keyToGetRows)) {
            $count = count($this->result);
        } else {
            $count = count($this->result[$this->keyToGetRows] ?? []);
        }
        return "Response list has " . $count . " records."  . PHP_EOL;
    }
    // function getSummary()
}
// class CodinGameApi

// --------------------------------------------------------------------
// SPECIFIC APIs
// --------------------------------------------------------------------
class AchievementFindByCodingamerId extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Achievement/findByCodingamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["id", "title", "categoryId", "groupId", "level", "points"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class AchievementFindByCodingamerId

// --------------------------------------------------------------------
class CareerGetCodinGamerOptinLocation extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "career/getCodinGamerOptinLocation";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' is from "
            . ($this->result["countryName"] ?? "?") . " ["
            . ($this->result["countryIsoCode"] ?? "??") . "], "
            . ($this->result["subdivision1Name"] ?? "?") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class CareerGetCodinGamerOptinLocation

// --------------------------------------------------------------------
class CertificationFindTopCertifications extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Certification/findTopCertifications";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["category", "level"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CertificationFindTopCertifications

// --------------------------------------------------------------------
class ChallengeFindAllChallenges extends CodinGameApi
{
    public const SERVICE_URL = "Challenge/findAllChallenges";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->columnNames = ["publicId", "title", "type", "date"];
    }
    // function __construct
}
// class ChallengeFindAllChallenges

// --------------------------------------------------------------------
class ChallengeFindChallengeMinimalInfoByChallengePublicId extends CodinGameApi
{
    public $challengePublicId;

    public const SERVICE_URL = "Challenge/findChallengeMinimalInfoByChallengePublicId";

    public function __construct(string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '["' . $this->challengePublicId . '"]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = $this->challengePublicId . " is a " . ($this->result["type"] ?? "unknown")
            . " type challenge, titled: " . ($this->result["title"] ?? "-") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class ChallengeFindChallengeMinimalInfoByChallengePublicId

// --------------------------------------------------------------------
class ClashOfCodeGetClashRankByCodinGamerId extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "ClashOfCode/getClashRankByCodinGamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Clash of Code ranking of player '" . $this->userId . "' is "
            . ($this->result["rank"] ?? "?") . " from total players of "
            . ($this->result["totalPlayers"] ?? "?") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class ClashOfCodeGetClashRankByCodinGamerId

// --------------------------------------------------------------------
class CodinGamerFindCodingamePointsStatsByHandle extends CodinGameApi
{
    public $publicHandle;

    public const SERVICE_URL = "CodinGamer/findCodingamePointsStatsByHandle";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' has "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsXp"] ?? "?") . " XP, "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsTotal"] ?? "?") . " CP, rank = "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsRank"] ?? "?") . ". from total of "
            . ($this->result["codingamePointsRankingDto"]["numberCodingamers"] ?? "?") . " players." . PHP_EOL;
        $s .= "Distribution of total CP is "
            . "Contest: " . ($this->result["codingamePointsRankingDto"]["codingamePointsContests"] ?? "?")
            . ", Multi: " . ($this->result["codingamePointsRankingDto"]["codingamePointsMultiTraining"] ?? "?")
            . ", Optim: " . ($this->result["codingamePointsRankingDto"]["codingamePointsOptim"] ?? "?")
            . ", Code golf: " . ($this->result["codingamePointsRankingDto"]["codingamePointsCodegolf"] ?? "?")
            . ", Clash: " . ($this->result["codingamePointsRankingDto"]["codingamePointsClash"] ?? "?") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class CodinGamerFindCodingamePointsStatsByHandle

// --------------------------------------------------------------------
// OBSOLETE
class CodinGamerFindCodinGamerGolfPuzzlePoints extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findCodinGamerGolfPuzzlePoints";
    public const GOLF_PUZZLE_IDS = [762986, 37513, 54473, 37514];

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',[' . implode(',', self::GOLF_PUZZLE_IDS) . ']]';
    }
    // function __construct
}
// class CodinGamerFindCodinGamerGolfPuzzlePoints

// --------------------------------------------------------------------
class CodinGamerFindCodinGamerPublicInformations extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findCodinGamerPublicInformations";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct

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
    // function getSummary
}
// class CodinGamerFindCodinGamerPublicInformations

// --------------------------------------------------------------------
// OBSOLETE
class CodinGamerFindCPByCodinGamerAndPredefinedTestId extends CodinGameApi
{
    public $userId;

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
    // function __construct
}
// class CodinGamerFindCPByCodinGamerAndPredefinedTestId

// --------------------------------------------------------------------
class CodinGamerFindFollowerIds extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findFollowerIds";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CodinGamerFindFollowerIds

// --------------------------------------------------------------------
class CodinGamerFindFollowers extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findFollowers";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["userId", "pseudo", "countryId", "city", "level", "points", "rank"];
        $this->requestJSON = '[' . $this->userId . ',' . $this->userId . ', null]';
        $this->authNeeded = true;
    }
    // function __construct
}
// class CodinGamerFindFollowers

// --------------------------------------------------------------------
class CodinGamerFindFollowing extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findFollowing";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["userId", "pseudo", "countryId", "city", "level", "points", "rank"];
        $this->requestJSON = '[' . $this->userId . ',' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct
}
// class CodinGamerFindFollowing

// --------------------------------------------------------------------
class CodinGamerFindFollowingIds extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findFollowingIds";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CodinGamerFindFollowingIds

// --------------------------------------------------------------------
class CodinGamerFindRankingPoints extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/findRankingPoints";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CodinGamerFindRankingPoints

// --------------------------------------------------------------------
class CodinGamerFindTotalAchievementProgress extends CodinGameApi
{
    public $publicHandle;

    public const SERVICE_URL = "CodinGamer/findTotalAchievementProgress";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' has " . ($this->result["achievementCount"] ?? "?")
            . " achievements from total of " . ($this->result["achievementTotal"] ?? "?") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class CodinGamerFindTotalAchievementProgress

// --------------------------------------------------------------------
class CodinGamerGetMyConsoleInformation extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodinGamer/getMyConsoleInformation";

    public function __construct(string $_userId = MySelf::USER_ID, bool $getPuzzles = true)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        if ($getPuzzles) {
            $this->keyToGetRows = "puzzles";
            $this->columnNames = ["puzzlePublicId", "labelTitle", "ranking", "totalPlayers"];
        } else {
            $this->keyToGetRows = "challenges";
            $this->columnNames = ["publicId", "title", "ranking", "total"];
        }
        $this->fieldFixedKey = "group";
        $this->fieldFixedValue = $this->keyToGetRows;
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CodinGamerGetMyConsoleInformation

// --------------------------------------------------------------------
class CodingamerLoginSiteV2 extends CodinGameApi
{
    public const SERVICE_URL = "Codingamer/loginSiteV2";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->requestJSON = '["' . Myself::EMAIL . '","' . Myself::PASSWORD . '",true]';
    }
    // function __construct
}
// class CodingamerLoginSiteV2

// --------------------------------------------------------------------
class CodingamerPuzzleTopicFindTopicsByCodingamerId extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "CodingamerPuzzleTopic/findTopicsByCodingamerId";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["handle", "category", "label", "puzzleCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class CodingamerPuzzleTopicFindTopicsByCodingamerId

// --------------------------------------------------------------------
class ContributionFindContribution extends CodinGameApi
{
    public $contributionPublicHandle;

    public const SERVICE_URL = "Contribution/findContribution";

    public function __construct(string $_contributionPublicHandle = parent::DEFAULT_CONTRIBUTION_PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->contributionPublicHandle = $_contributionPublicHandle;
        $this->requestJSON = '["' . $this->contributionPublicHandle . '",true]';
    }
    // function __construct

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
// class ContributionFindContribution

// --------------------------------------------------------------------
class ContributionFindContributionModerators extends CodinGameApi
{
    public $contributionId;

    public const SERVICE_URL = "Contribution/findContributionModerators";

    public function __construct(int $_contributionId = parent::DEFAULT_CONTRIBUTION_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->contributionId = $_contributionId;
        $this->columnNames = ["userId", "pseudo", "publicHandle"];
        $this->requestJSON = '[' . $this->contributionId . ',"validate"]';
    }
    // function __construct
}
// class ContributionFindContributionModerators

// --------------------------------------------------------------------
class ContributionGetAcceptedContributions extends CodinGameApi
{
    public $filter;

    public const SERVICE_URL = "Contribution/getAcceptedContributions";
    public const LEADERBOARD_TYPES = ["ALL", "PUZZLE", "CLASHOFCODE"];

    public function __construct(string $_filter = "ALL")
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->filter = $_filter;
        $this->columnNames = ["id", "title", "type", "status",  "nickname", "codingamerId", "publicHandle", "upVotes"];
        $this->requestJSON = '["' . $this->filter . '"]';
        $this->authNeeded = true;
    }
    // function __construct
}
// class ContributionGetAcceptedContributions

// --------------------------------------------------------------------
class ContributionGetAllPendingContributions extends CodinGameApi
{
    public $userId;
    public $filter;

    public const SERVICE_URL = "Contribution/getAllPendingContributions";
    public const LEADERBOARD_TYPES = ["ALL", "PUZZLE", "CLASHOFCODE"];

    public function __construct(string $_userId = MySelf::USER_ID, string $_filter = "ALL")
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->filter = $_filter;
        $this->columnNames = ["id", "title", "type", "status",  "nickname", "codingamerId", "publicHandle", "upVotes"];
        $this->requestJSON = '[1,"' . $this->filter . '",' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct
}
// class ContributionGetAllPendingContributions

// --------------------------------------------------------------------
class LastActivitiesGetLastActivities extends CodinGameApi
{
    public $userId;
    public $countActivities;

    public const SERVICE_URL = "LastActivities/getLastActivities";

    public function __construct(string $_userId = MySelf::USER_ID, int $_countActivities = 3)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->countActivities = $_countActivities;
        $this->requestJSON = '[' . $this->userId . ',' . $this->countActivities . ']';
        $this->authNeeded = true;
    }
    // function __construct
}
// class LastActivitiesGetLastActivities

// --------------------------------------------------------------------
class LeaderboardsFindAllPuzzleLeaderboards extends CodinGameApi
{
    public const SERVICE_URL = "Leaderboards/findAllPuzzleLeaderboards";

    public function __construct()
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->columnNames = ["publicId", "title", "level", "creationTime", "puzzleId"];
    }
    // function __construct
}
// class LeaderboardsFindAllPuzzleLeaderboards

// --------------------------------------------------------------------
class LeaderboardsGetCodinGamerChallengeRanking extends CodinGameApi
{
    public $userId;
    public $challengePublicId;

    public const SERVICE_URL = "Leaderboards/getCodinGamerChallengeRanking";

    public function __construct(
        string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID,
        string $_userId = MySelf::USER_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '[' . $this->userId . ',"' . $this->challengePublicId . '","global"]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . ($this->result["pseudo"] ?? "") . "' has a ranking of "
            . ($this->result["rank"] ?? "") . " in challenge " . $this->challengePublicId;
        $divisionCount = ($this->result["league"]["divisionCount"] ?? 0);
        $leagueName = $this->getLeagueName($this->result["league"] ?? []);
        if ($divisionCount != 0) {
            $s .= ", and is in the " . $leagueName . " league";
        }
        $s .= PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class LeaderboardsGetCodinGamerChallengeRanking

// --------------------------------------------------------------------
class LeaderboardsGetCodinGamerClashRanking extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Leaderboards/getCodinGamerClashRanking";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',"global",null]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' did " . ($this->result["clashesCount"] ?? "?")
            . " Clash of Codes, and has a ranking of " . ($this->result["rank"] ?? "?")
            . " from total players of " . ($this->result["total"] ?? "?") . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class LeaderboardsGetCodinGamerClashRanking

// --------------------------------------------------------------------
class LeaderboardsGetCodinGamerGlobalRankingByHandle extends CodinGameApi
{
    public $publicHandle;

    public const SERVICE_URL = "Leaderboards/getCodinGamerGlobalRankingByHandle";

    public function __construct(string $_publicHandle = MySelf::PUBLIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '","GENERAL","global",null]';
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->publicHandle . "' (pseudo: "
            . ($this->result["pseudo"] ?? "?") . ") has "
            . ($this->result["score"] ?? "?") . " CP, rank = "
            . ($this->result["rank"] ?? "?") . ". from total of "
            . ($this->result["total"] ?? "?") . " players." . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class LeaderboardsGetCodinGamerGlobalRankingByHandle

// --------------------------------------------------------------------
class LeaderboardsGetFilteredChallengeLeaderboard extends LeaderboardsGetFilteredPuzzleLeaderboard
{
    public $publicHandle;
    public $challengePublicId;

    public const SERVICE_URL = "Leaderboards/getFilteredChallengeLeaderboard";

    public function __construct(
        string $_challengePublicId = parent::DEFAULT_CHALLENGE_PUBLIC_ID,
        string $_publicHandle = MySelf::PUBLIC_HANDLE
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->challengePublicId = $_challengePublicId;
        $this->keyToGetRows = "users";
        $this->columnNames =        ["rank", "leagueName", "programmingLanguage", "pseudo", "codingamer",   "codingamer", "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,   null,          null,                 null,     "level",        "countryId",  "userId",     "publicHandle"];
        $this->fieldFixedKey = "challengePublicId";
        $this->fieldFixedValue = $this->challengePublicId;
        $this->requestJSON = '["' . $this->challengePublicId . '","' . $this->publicHandle
            . '","global",{"active":false,"column":"","filter":""}]';
    }
    // function __construct
}
// class LeaderboardsGetFilteredChallengeLeaderboard

// --------------------------------------------------------------------
class LeaderboardsGetFilteredPuzzleLeaderboard extends CodinGameApi
{
    public $publicHandle;
    public $puzzlePublicId;

    public const SERVICE_URL = "Leaderboards/getFilteredPuzzleLeaderboard";

    public function __construct(
        string $_puzzlePublicId = parent::DEFAULT_PUZZLE_PUBLIC_ID,
        string $_publicHandle = MySelf::PUBLIC_HANDLE
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->puzzlePublicId = $_puzzlePublicId;
        $this->keyToGetRows = "users";
        $this->columnNames =        ["rank", "leagueName", "programmingLanguage", "pseudo", "codingamer",   "codingamer",   "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,   null,          null,                 null,     "level",        "countryId",    "userId",     "publicHandle"];
        $this->fieldFixedKey = "puzzlePublicId";
        $this->fieldFixedValue = $this->puzzlePublicId;
        $this->requestJSON = '["' . $this->puzzlePublicId . '","' . $this->publicHandle
            . '","global",{"active":false,"column":"","filter":""}]';
    }
    // function __construct
}
// class LeaderboardsGetFilteredPuzzleLeaderboard

// --------------------------------------------------------------------
class LeaderboardsGetGlobalLeaderboard extends CodinGameApi
{
    public $publicHandle;
    public $pageNum;
    public $leaderboardType;

    public const SERVICE_URL = "Leaderboards/getGlobalLeaderboard";
    public const LEADERBOARD_TYPES = ["GENERAL", "CONTESTS", "BOT_PROGRAMMING", "OPTIM", "CODEGOLF"];

    public function __construct(
        int $_pageNum = 1,
        string $_publicHandle = MySelf::PUBLIC_HANDLE,
        string $_leaderboardType = "GENERAL"
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->publicHandle = $_publicHandle;
        $this->pageNum = $_pageNum;
        $this->leaderboardType = $_leaderboardType;
        $this->keyToGetRows = "users";
        $this->columnNames =        ["pseudo",  "rank", "score",  "xp", "codingamer", "codingamer",   "codingamer", "codingamer"];
        $this->columnNamesDepth2 =  [null,      null,   null,     null, "level",      "countryId",    "userId",     "publicHandle"];
        $this->requestJSON = '[' . $this->pageNum . ',"' . $this->leaderboardType
            . '",{keyword: "", active: false, column: "", filter: ""},"' . $this->publicHandle . '",true,"global"]';
    }
    // function __construct
}
// class LeaderboardsGetGlobalLeaderboard

// --------------------------------------------------------------------
class PuzzleCountSolvedPuzzlesByProgrammingLanguage extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Puzzle/countSolvedPuzzlesByProgrammingLanguage";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->columnNames = ["programmingLanguageId", "languageName", "puzzleCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class PuzzleCountSolvedPuzzlesByProgrammingLanguage

// --------------------------------------------------------------------
class PuzzleFindAllMinimalProgress extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Puzzle/findAllMinimalProgress";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->authNeeded = true;
        $this->columnNames = ["id", "level", "creationTime", "solvedCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    }
    // function __construct
}
// class PuzzleFindAllMinimalProgress

// --------------------------------------------------------------------
class PuzzleFindProgressByIds extends CodinGameApi
{
    public $userId;
    public $puzzleIdArray;

    public const SERVICE_URL = "Puzzle/findProgressByIds";

    public function __construct(
        array $_puzzleIdArray = [parent::DEFAULT_SOLO_PUZZLE_ID],
        string $_userId = MySelf::USER_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->puzzleIdArray = $_puzzleIdArray;
        $this->columnNames =        ["prettyId", "title", "level", "contributor", "creationTime", "id", "solvedCount", "globalTotal", "leagueName", "position", "total"];
        $this->columnNamesDepth2 =  [null,          null, null,     "pseudo",       null,       null,   null,           null,           null,       null,       null,];
        $this->requestJSON = '[[' . implode(',', $this->puzzleIdArray) . '],' . $this->userId . ',2]';
    }
    // function __construct
}
// class PuzzleFindProgressByIds

// --------------------------------------------------------------------
class PuzzleFindProgressByPrettyId extends CodinGameApi
{
    public $userId;
    public $puzzlePrettyId;

    public const SERVICE_URL = "Puzzle/findProgressByPrettyId";

    public function __construct(
        string $_puzzlePrettyId = parent::DEFAULT_PUZZLE_PRETTY_ID,
        string $_userId = MySelf::USER_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->puzzlePrettyId = $_puzzlePrettyId;
        $this->requestJSON = '["' . $this->puzzlePrettyId . '",' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Puzzle '" . $this->puzzlePrettyId . "' (id: "
            . ($this->result["id"] ?? "?") . ") is '"
            . ($this->result["level"] ?? "?") . "' level puzzle, ";
        $p = $this->result["contributor"]["pseudo"] ?? null;
        if (!is_null($p)) {
            $s .= "contributed by '" . $p . "', ";
        }
        $s .= "solved by "
            . ($this->result["solvedCount"] ?? "?") . " players from "
            . ($this->result["attemptCount"] ?? "?") . " attempts." . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class PuzzleFindProgressByPrettyId

// --------------------------------------------------------------------
class QuestCountLootableQuests extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Quest/countLootableQuests";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result)) {
            return "";
        }
        $s = "Player '" . $this->userId . "' has " . ($this->responseJSON ?? "?") . " lootable quests." . PHP_EOL;
        return $s;
    }
    // function getSummary
}
// class QuestCountLootableQuests

// --------------------------------------------------------------------
class QuestFindQuestMap extends CodinGameApi
{
    public $userId;

    public const SERVICE_URL = "Quest/findQuestMap";

    public function __construct(string $_userId = MySelf::USER_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
        $this->authNeeded = true;
    }
    // function __construct
}
// class QuestFindQuestMap

// --------------------------------------------------------------------
class SchoolFindById extends CodinGameApi
{
    public $schoolId;

    public const SERVICE_URL = "School/findById";

    public function __construct(int $_schoolId = MySelf::SCHOOL_ID)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->schoolId = $_schoolId;
        $this->requestJSON = '[' . $this->schoolId . ']';
    }
    // function __construct

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
    // function getSummary
}
// class SchoolFindById

// --------------------------------------------------------------------
class SolutionFindBestSolutions extends CodinGameApi
{
    public $userId;
    public $soloPuzzleId;
    public $programmingLanguageId;

    public const SERVICE_URL = "Solution/findBestSolutions";

    public function __construct(
        string $_userId = MySelf::USER_ID,
        int $_soloPuzzleId = parent::DEFAULT_SOLO_PUZZLE_ID,
        ?string $_programmingLanguageId = null
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->soloPuzzleId = $_soloPuzzleId;
        $this->programmingLanguageId = $_programmingLanguageId;
        $this->columnNames = ["pseudo", "programmingLanguageId", "codingamerId"];
        if (is_null($this->programmingLanguageId)) {
            $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',null,false]';
        } else {
            $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',"'
                . $this->programmingLanguageId . '", false]';
        }
        $this->authNeeded = true;
    }
    // function __construct
}
// class SolutionFindBestSolutions

// --------------------------------------------------------------------
class SolutionFindMySolutions extends CodinGameApi
{
    public $userId;
    public $soloPuzzleId;

    public const SERVICE_URL = "Solution/findMySolutions";

    public function __construct(
        string $_userId = MySelf::USER_ID,
        int $_soloPuzzleId = parent::DEFAULT_SOLO_PUZZLE_ID
    ) {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->userId = $_userId;
        $this->soloPuzzleId = $_soloPuzzleId;
        $this->columnNames = ["pseudo", "programmingLanguageId"];
        $this->requestJSON = '[' . $this->userId . ',' . $this->soloPuzzleId .  ',null]';
        $this->authNeeded = true;
    }
    // function __construct
}
// class SolutionFindMySolutions

// --------------------------------------------------------------------
class TopicFindTopicPageByTopicHandle extends CodinGameApi
{
    public $topicHandle;

    public const SERVICE_URL = "Topic/findTopicPageByTopicHandle";

    public function __construct(string $_topicHandle = parent::DEFAULT_TOPIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->topicHandle = $_topicHandle;
        $this->requestJSON = '["' . $this->topicHandle . '"]';
    }
    // function __construct
}
// class TopicFindTopicPageByTopicHandle

// --------------------------------------------------------------------
class CodinGameAvatar extends CodinGameApi
{
    public $id;
    public $responsePNG = null;

    public const SERVICE_URL = "https://static.codingame.com/servlet/fileservlet";
    public const CONTENT_TYPE = "image/png";

    public function __construct(string $_id = MySelf::AVATAR_ID)
    {
        $this->serviceURL = self::SERVICE_URL;
        $this->id = $_id;
    }
    // function __construct

    public function getAvatar(string $fileName): void
    {
        $query = '?id=' . $this->id . '&format=profile_avatar';
        if (is_null($this->serviceURL)) {
            die("ERROR: missing service URL");
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->serviceURL . $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $this->responsePNG = curl_exec($curl);
        if (($this->responsePNG === false) or (curl_errno($curl) != 0)) {
            die("ERROR: Connection Failure: " . curl_error($curl));
        }
        curl_close($curl);
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        fwrite($f, $this->responsePNG);
        fclose($f);
    }
    // function getAvatar
}
// class CodinGameAvatar

// --------------------------------------------------------------------
// Data extraction wrapper class
// --------------------------------------------------------------------
class CG
{
    public $countCalls = 0;

    public const NAMESPACE = "CG\\";
    public const INPUT_FILENAME_JSON = "input.json";
    public const FILENAME_PREFIX_REQUEST_JSON = "request_";
    public const FILENAME_PREFIX_JSON = "response_";
    public const FILENAME_POSTFIX_JSON = ".json";
    public const FILENAME_PREFIX_CSV = "result_";
    public const FILENAME_POSTFIX_CSV = ".csv";
    public const AVATAR_FILENAME = "avatar.png";

    public const LANGUAGE_IDS = array(
        "Bash", "C", "C#", "C++", "Clojure", "D", "Dart", "F#", "Go",
        "Groovy", "Haskell", "Java", "Javascript", "Kotlin", "Lua", "ObjectiveC", "OCaml", "Pascal",
        "Perl", "PHP", "Python3", "Ruby", "Rust", "Scala", "Swift", "TypeScript", "VB.NET"
    );
    public const PUZZLE_PUBLIC_IDS = array(
        // multi
        "tron-battle",
        "game-of-drone",
        "poker-chip-race",
        "platinum-rift",
        "platinum-rift2",
        "back-to-the-code",
        "great-escape",
        "coders-strike-back",
        "smash-the-code",
        "codebusters",
        "hypersonic",
        "fantastic-bits",
        "ghost-in-the-cell",
        "coders-of-the-caribbean",
        "wondev-woman",
        "mean-max",
        "code4life",
        "tic-tac-toe",
        "botters-of-the-galaxy",
        "code-royale",
        "code-of-kutulu",
        "legends-of-code-magic",
        "xmas-rush",
        "code-a-la-mode",
        "crystal-rush",
        "ocean-of-code",
        "spring-challenge-2020",

        // multi-community
        "vindinium",
        "langton-s-ant",
        "checkers",
        "yavalath",
        "cultist-wars",
        "bit-runner-2048",
        "bandas",
        "a-code-of-ice-and-fire",
        "oware-abapa",
        "breakthrough",
        "paper-soccer",
        "onitama",
        "tower-dereference",
        "twixt-pp",
        "coders-of-the-realm---1v1",
        "coders-of-the-realm",
        "tulips-and-daisies",
        "yinsh",
        "othello-1",
        "atari-go",
        "dots-and-boxes",
        "atari-go-9x9",
        "penguins",
        "blocking",
        "chess",
        "tryangle-catch",

        // optim
        "mars-lander-fuel",
        "code-of-the-rings-output",
        "code-vs-zombies-score",
        "codingame-optim",
        "a-star-craft",

        // optim-community
        "bender---episode-4",
        "cgfunge-prime",
        "number-shifting",
        "bulls-and-cows-2",
        "search-race",
        "samegame",
        "2048",

        // codegolf
        "paranoid-codesize",
        "thor-codesize",
        "temperatures-codesize",
        "chuck-norris-codesize",
    );

    public const CHALLENGE_PUBLIC_IDS = array(
        // BATTLE
        "spring-challenge-2020",
        "ocean-of-code",
        "unleash-the-geek-amadeus",
        "a-code-of-ice-and-fire",
        "code-a-la-mode",
        "xmas-rush",
        "legends-of-code-and-magic-marathon",
        "legends-of-code-and-magic",
        "code-of-kutulu",
        "code-royale",
        "botters-of-the-galaxy",
        "mean-max",
        "wondev-woman",
        "code4life",
        "coders-of-the-caribbean",
        "ghost-in-the-cell",
        "fantastic-bits",
        "hypersonic",
        "codebusters",
        "smash-the-code",
        "coders-strike-back",
        "back-to-the-code",
        "the-great-escape",
        "platinum-rift-2",
        "platinum-rift",
        "winamax",  // "Poker Chip Race"
        "parrot",   // "Game of Drones"
        "20", // tron-battle, "Tron Battle"

        // WORLDCUP
        "detective-pikaptcha",
        "a-star-craft",
        "the-accountant",
        "code-vs-zombies",
        "code-of-the-rings",
        "there-is-no-spoon",
        "dont-panic",
        "vox-codei",
        "35", // shadows-of-the-knight, "Shadows of the Knight"
        "33", // the-last-crusade, "The Last Crusade"
        "32", // skynet-final, "Skynet Finale"
        "29", // skynet-revolution, "Skynet Revolution"
        "25", // kirks-quest, "Kirk's Quest"
        "23", // thor, "Power of Thor"
        "21", // mars-lander-fuel, "Mars Lander"
        "17", // doctor-who, "Doctor Who"
        "15", // bender, "Bender"
        "10", // codingame-july-2013, "CodinGame July 2013"
        "8",  // genome_sequencing, "Genome Sequencing"
        "7",  // codingame-march-2013, "CodinGame March 2013", "CGX Formatter"
        "3",  // chuck-norris, "Chuck Norris"
        "2",  // codingame-october-2012, "CodinGame October 2012"

        // PRIVATE
        "nokia-openday",
        "hackathlon",
        "amadeus-challenge",
        "thales-hackathon-2018",
        "societe-generale",
        "enedis-2019-55oal421",
        "ea-2019-battlegrounds",
        "sf2442",

        // PRIVATE - UNAVAILABLE
        /*
        "the-great-dispatch",
        "klee",
        "utg2019-demo-c370c",
        "unleash-the-geek-2019-86477221",
        "facebook-2019-xk7",
        */
    );

    public const PUZZLE_IDS = array(
        "tutorial"  => [43],
        "easy"      => [
            4, 5, 6, 7, 8, 9, 10, 40, 108, 121, 133, 154, 171, 182, 188, 203,
            210, 229, 235, 238, 319, 341, 343, 345, 351, 355, 358, 360, 373,
            393, 395, 396, 403, 408, 419, 428, 429, 433, 437, 441, 442, 443,
            451, 454, 455, 459, 465, 469, 501, 505, 508, 512, 515, 516, 517,
            519, 520, 521, 525, 528, 535, 542, 546, 552, 558, 562, 576, 581,
            586, 587, 611, 612, 614, 615, 623, 627, 630, 639, 643, 644, 647,
            648, 652, 653, 655, 656, 659, 661, 668, 673, 678, 688, 690, 691,
            697, 705, 706
        ],
        "medium"    => [
            1, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 41, 47, 50, 54,
            111, 86, 106, 116, 112, 76, 77, 78, 87, 95, 97, 103, 104, 120,
            123, 128, 131, 132, 142, 147, 150, 157, 158, 159, 161, 169, 170,
            172, 173, 174, 187, 190, 193, 198, 199, 202, 207, 220, 223, 227,
            228, 230, 233, 234, 239, 243, 244, 245, 246, 299, 322, 326, 331,
            332, 336, 337, 339, 344, 349, 350, 352, 354, 361, 363, 364, 366,
            367, 370, 372, 374, 375, 377, 384, 386, 387, 388, 394, 397, 400,
            401, 402, 406, 413, 415, 421, 422, 423, 424, 425, 426, 427, 434,
            435, 436, 438, 440, 444, 445, 446, 448, 452, 456, 457, 462, 466,
            470, 472, 475, 478, 479, 480, 482, 484, 485, 487, 488, 490, 492,
            499, 502, 503, 510, 511, 518, 522, 526, 529, 531, 532, 533, 537,
            538, 539, 543, 544, 545, 548, 551, 561, 565, 566, 567, 569, 570,
            578, 582, 584, 585, 591, 602, 616, 618, 619, 620, 621, 625, 629,
            633, 638, 641, 645, 646, 649, 662, 670, 672, 677, 689, 692, 693,
            696, 703, 707
        ],
        "hard"      => [
            22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 35, 44, 48, 55,
            113, 96, 89, 109, 85, 100, 84, 119, 122, 125, 127, 130, 134, 135,
            136, 138, 141, 143, 145, 146, 153, 162, 167, 176, 179, 180, 181,
            183, 184, 185, 192, 195, 196, 197, 200, 217, 219, 222, 224, 225,
            231, 232, 236, 240, 241, 248, 249, 250, 251, 252, 254, 255, 293,
            294, 307, 308, 312, 313, 314, 315, 318, 320, 325, 327, 330, 340,
            342, 346, 347, 356, 365, 369, 371, 378, 398, 399, 404, 405, 407,
            412, 417, 418, 431, 432, 453, 458, 463, 476, 477, 486, 506, 507,
            523, 547, 554, 559, 590, 609, 622, 624, 632, 640, 642, 651, 657,
            660, 674, 685, 687, 694, 695, 698, 702
        ],
        "expert"    => [
            36, 37, 38, 39, 42, 46, 49, 79, 126, 129, 137, 139, 140, 149, 151,
            152, 160, 175, 177, 178, 186, 189, 191, 194, 201, 211, 226, 237,
            242, 253, 309, 310, 311, 321, 323, 328, 348, 357, 368, 381, 385,
            411, 414, 527, 555, 650, 663, 700
        ],
        "multi"     => [
            63, 64, 68, 66, 65, 67, 69, 148, 156, 168, 221, 247, 298, 324,
            329, 359, 376, 380, 382, 383, 410, 420, 450, 460, 468, 471, 473,
            474, 481, 483, 491, 500, 530, 549, 550, 553, 560, 564, 572, 573,
            577, 580, 583, 592, 610, 613, 617, 628, 631, 654, 667, 684, 699,
            701, 704
        ],
        "optim" => [56, 60, 70, 71, 439, 461, 524, 563, 575, 593, 626, 658],
        "codegolf"  => [57, 58, 73, 464],
    );

    public function testAPI(int $idxAPI = self::DEFAULT_IDX_API, bool $readFromFile = false): void
    {
        if (!isset(self::API_NAMES[$idxAPI])) {
            return;
        }
        $apiName = self::API_NAMES[$idxAPI];
        $idxPadded = str_pad(strval($idxAPI), 2, "0", STR_PAD_LEFT);
        echo str_repeat("=", 60) . PHP_EOL;
        echo " TEST #" . $idxPadded . " : " . $apiName . PHP_EOL;
        echo str_repeat("=", strlen($apiName) + 13) . PHP_EOL;
        $namespaceApiName = self::NAMESPACE . $apiName;
        $g = new $namespaceApiName();
        $fileName = self::FILENAME_PREFIX_REQUEST_JSON . $apiName . self::FILENAME_POSTFIX_JSON;
        if ($apiName != "CodingamerLoginSiteV2") {
            echo "--- writing API request body to file: " . $fileName . PHP_EOL;
            $g->writeRequestJSON($fileName);
        }
        if ($readFromFile) {
            echo "--- emulating API response by reading from file: " . self::INPUT_FILENAME_JSON . PHP_EOL;
            $g->readFromJSON(self::INPUT_FILENAME_JSON);
        } else {
            echo "--- calling API: " . $g->serviceURL . PHP_EOL;
            $this->countCalls++;
            $g->callApi();
        }
        $fileName = self::FILENAME_PREFIX_JSON . $apiName . self::FILENAME_POSTFIX_JSON;
        echo "--- writing API response to file: " . $fileName . PHP_EOL;
        $g->writeResponseJSON($fileName);
        if (!is_null($g->columnNames)) {
            $fileName = self::FILENAME_PREFIX_CSV . $apiName . self::FILENAME_POSTFIX_CSV;
            echo "--- writing CSV export to file: " . $fileName . PHP_EOL;
            $f = fopen($fileName, "w")
                or die("ERROR: Cannot create csv file.");
            $g->writeFilteredCSV($f);
            fclose($f);
        }
        echo $g->getSummary();
        echo "--- END OF TEST #" . $idxPadded . " ---" . PHP_EOL . PHP_EOL;
    }
    // function testAPI

    public function testEmulated(): void
    {
        echo str_repeat("=", 60) . PHP_EOL;
        echo " TESTING emulated API call:" . PHP_EOL;
        echo str_repeat("=", 28) . PHP_EOL;
        $apiName = self::API_NAMES[self::DEFAULT_IDX_API];
        $namespaceApiName = self::NAMESPACE . $apiName;
        $g = new $namespaceApiName();
        $this->countCalls++;
        $g->callApi();
        $fileName = self::INPUT_FILENAME_JSON;
        $g->writeResponseJSON(self::INPUT_FILENAME_JSON);
        $this->testAPI(self::DEFAULT_IDX_API, true);
    }
    // function testEmulated

    public function generateAllPuzzlesCSV(?string $level = null): void
    {
        if (!is_null($level) and !isset(self::PUZZLE_IDS[$level])) {
            return;
        }
        echo str_repeat("=", 60) . PHP_EOL;
        echo " Getting all puzzles info:" . PHP_EOL;
        echo str_repeat("=", 27) . PHP_EOL;
        echo "--- calling API: Puzzle/FindProgressByIds" . PHP_EOL;
        if (is_null($level)) {
            $name = "ALL_PUZZLES";
            $idList = array();
            foreach (self::PUZZLE_IDS as $idArray) {
                foreach ($idArray as $id) {
                    $idList[] = $id;
                }
            }
        } else {
            $name = "ALL_PUZZLES_" . $level;
            echo "--- filter: level = " . $level;
            $idList = self::PUZZLE_IDS[$level];
        }
        $g = new \CG\PuzzleFindProgressByIds($idList);
        $this->countCalls++;
        $g->callApi();
        $fileName = self::FILENAME_PREFIX_JSON . $name . self::FILENAME_POSTFIX_JSON;
        echo "--- writing API response to file: " . $fileName . PHP_EOL;
        $g->writeResponseJSON($fileName);
        $fileName = self::FILENAME_PREFIX_CSV . $name . self::FILENAME_POSTFIX_CSV;
        echo "--- writing CSV export to file: " . $fileName . PHP_EOL;
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $g->writeFilteredCSV($f);
        fclose($f);
        echo "--- END ---" . PHP_EOL . PHP_EOL;
    }
    // function generateAllPuzzlesCSV

    public function generateAllPuzzleLeaderboardCSV(): void
    {
        echo str_repeat("=", 60) . PHP_EOL;
        echo " Getting all puzzle leaderboards:" . PHP_EOL;
        echo str_repeat("=", 34) . PHP_EOL;
        echo "--- calling API: Leaderboards/GetFilteredPuzzleLeaderboard (multiple times)" . PHP_EOL;
        $fileName = self::FILENAME_PREFIX_CSV . "ALL_PUZZLES_LEADERBOARDS" . self::FILENAME_POSTFIX_CSV;
        echo "--- writing CSV export to file: " . $fileName . PHP_EOL;
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $isFirst = true;
        $nextUid = 0;
        foreach (self::PUZZLE_PUBLIC_IDS as $puzzlePublicId) {
            $g = new \CG\LeaderboardsGetFilteredPuzzleLeaderboard($puzzlePublicId);
            $this->countCalls++;
            $g->callApi();
            $countUid = $g->writeFilteredCSV($f, $isFirst, $nextUid);
            $nextUid += $countUid;
            $isFirst = false;
        }
        fclose($f);
        echo "--- END ---" . PHP_EOL . PHP_EOL;
    }
    // function generateAllPuzzleLeaderboardCSV

    public function generateAllChallengeLeaderboardCSV(): void
    {
        echo str_repeat("=", 60) . PHP_EOL;
        echo " Getting all challenge leaderboards:" . PHP_EOL;
        echo str_repeat("=", 37) . PHP_EOL;
        echo "--- calling API: Leaderboards/GetFilteredChallengeLeaderboard (multiple times)" . PHP_EOL;
        $fileName = self::FILENAME_PREFIX_CSV . "ALL_CHALLENGES_LEADERBOARDS" . self::FILENAME_POSTFIX_CSV;
        echo "--- writing CSV export to file: " . $fileName . PHP_EOL;
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $isFirst = true;
        $nextUid = 0;
        foreach (self::CHALLENGE_PUBLIC_IDS as $challengePublicId) {
            $g = new \CG\LeaderboardsGetFilteredChallengeLeaderboard($challengePublicId);
            $this->countCalls++;
            $g->callApi();
            $countUid = $g->writeFilteredCSV($f, $isFirst, $nextUid);
            $nextUid += $countUid;
            $isFirst = false;
        }
        fclose($f);
        echo "--- END ---" . PHP_EOL . PHP_EOL;
    }
    // function generateAllChallengeLeaderboardCSV

    public function generateLanguageLeaderboardCSV(int $toPlayer = 100, int $fromPlayer = 1): void
    {
        $startTime = microtime(true);
        $startCounter = $this->countCalls;
        echo str_repeat("=", 60) . PHP_EOL;
        echo " Getting achievement count and puzzles solved per language for top players on global leaderboard:"
            . PHP_EOL;
        echo str_repeat("=", 76) . PHP_EOL;
        echo "--- calling API: Leaderboards/GetGlobalLeaderboard (multiple times)" . PHP_EOL;
        echo "--- calling API: CodinGamer/FindTotalAchievementProgress (multiple times)" . PHP_EOL;
        echo "--- calling API: Puzzle/CountSolvedPuzzlesByProgrammingLanguage (multiple times)" . PHP_EOL;
        $fileName = self::FILENAME_PREFIX_CSV . "LANGUAGE_LEADERBOARDS" . self::FILENAME_POSTFIX_CSV;
        echo "--- writing CSV export to file: " . $fileName . PHP_EOL;
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $fromPageNum = max(1, ceil($fromPlayer / 100));
        $maxPageNum = max(1, ceil($toPlayer / 100));
        for ($pageNum = $fromPageNum; $pageNum <= $maxPageNum; $pageNum++) {
            if ($maxPageNum - $fromPageNum > 1) {
                error_log(strval($pageNum));
            }
            $g = new \CG\LeaderboardsGetGlobalLeaderboard($pageNum);
            $this->countCalls++;
            $g->callApi();
            $g->extractFilteredTable();
            foreach ($g->filteredResult as $idx => $row) {
                $userId = strval($row["userId"]);
                $publicHandle = strval($row["publicHandle"]);
                $apiAchievement = new \CG\CodinGamerFindTotalAchievementProgress($publicHandle);
                $this->countCalls++;
                $apiAchievement->callApi();
                $achievementCount = $apiAchievement->result["achievementCount"] ?? 0;
                $g->filteredResult[$idx]["achievementCount"] = $achievementCount;
                $apiLanguage = new \CG\PuzzleCountSolvedPuzzlesByProgrammingLanguage($userId);
                $this->countCalls++;
                $apiLanguage->callApi();
                $puzzleCounts = array();
                foreach (self::LANGUAGE_IDS as $languageId) {
                    $puzzleCounts[$languageId] = 0;
                }
                foreach ($apiLanguage->result as $row) {
                    if (!isset($row["programmingLanguageId"])) {
                        continue;
                    }
                    $languageId = $row["programmingLanguageId"];
                    $puzzleCount = $row["puzzleCount"] ?? 0;
                    $puzzleCounts[$languageId] = $puzzleCount;
                }
                foreach (self::LANGUAGE_IDS as $languageId) {
                    $g->filteredResult[$idx][$languageId] = $puzzleCounts[$languageId];
                }
            }
            $g->writeFilteredTableCSV($f, $pageNum == 1);
        }
        fclose($f);
        $totalCounter = $this->countCalls - $startCounter;
        $thinkTime = microtime(true) - $startTime;
        echo "Number of API calls: " . $totalCounter . PHP_EOL;
        echo "Time spent: " . number_format($thinkTime, 0, '.', '') . " sec" . PHP_EOL;
        echo "--- END ---" . PHP_EOL . PHP_EOL;
    }
    // function generateLanguageLeaderboardCSV

    public function testAvatar(): void
    {
        echo str_repeat("=", 60) . PHP_EOL;
        echo " TESTING getting avatar PNG file:" . PHP_EOL;
        echo str_repeat("=", 32) . PHP_EOL;
        $g = new \CG\CodinGameAvatar();
        echo "--- calling GET: " . $g->serviceURL . PHP_EOL;
        $this->countCalls++;
        $g->getAvatar(self::AVATAR_FILENAME);
        echo "--- writing PNG response to file: " . self::AVATAR_FILENAME . PHP_EOL;
        echo "--- END ---" . PHP_EOL . PHP_EOL;
    }
    // function testAvatar

    public function testAll(): void
    {
        $startTime = microtime(true);
        $this->countCalls = 0;
        echo str_repeat("=", 60) . PHP_EOL;
        echo "RUNNING ALL TESTS" . PHP_EOL . PHP_EOL;
        $this->testEmulated();
        foreach (self::API_NAMES as $idxAPI => $name) {
            $this->testAPI($idxAPI);
        }
        $this->generateAllPuzzlesCSV();
        $this->generateAllPuzzleLeaderboardCSV();
        $this->generateAllChallengeLeaderboardCSV();
        $this->testAvatar();
        $thinkTime = microtime(true) - $startTime;
        echo "Total number of API calls: " . $this->countCalls . PHP_EOL;
        echo "Running all tests took " . number_format($thinkTime, 0, '.', '') . " sec" . PHP_EOL;
        echo "--- ALL TESTS ENDED ---" . PHP_EOL;
    }
    // function testAll

    public const DEFAULT_IDX_API = 28;
    // call TestAPI() with the integer key from this array
    public const API_NAMES = array(
        /*  0 */
        "AchievementFindByCodingamerId",
        /*  1 */ "CareerGetCodinGamerOptinLocation",
        /*  2 */ "CertificationFindTopCertifications",
        /*  3 */ "ChallengeFindAllChallenges",
        /*  4 */ "ChallengeFindChallengeMinimalInfoByChallengePublicId",
        /*  5 */ "ClashOfCodeGetClashRankByCodinGamerId",
        /*  6 */ "CodinGamerFindCodingamePointsStatsByHandle",
        /*  7 */ "CodinGamerFindCodinGamerPublicInformations",
        /*  8 */ "CodinGamerFindFollowerIds",
        /*  9 */ "CodinGamerFindFollowers",
        /* 10 */ "CodinGamerFindFollowing",
        /* 11 */ "CodinGamerFindFollowingIds",
        /* 12 */ "CodinGamerFindRankingPoints",
        /* 13 */ "CodinGamerFindTotalAchievementProgress",
        /* 14 */ "CodinGamerGetMyConsoleInformation",
        /* 15 */ "CodingamerLoginSiteV2",
        /* 16 */ "CodingamerPuzzleTopicFindTopicsByCodingamerId",
        /* 17 */ "ContributionFindContribution",
        /* 18 */ "ContributionFindContributionModerators",
        /* 19 */ "ContributionGetAcceptedContributions",
        /* 20 */ "ContributionGetAllPendingContributions",
        /* 21 */ "LastActivitiesGetLastActivities",
        /* 22 */ "LeaderboardsFindAllPuzzleLeaderboards",
        /* 23 */ "LeaderboardsGetCodinGamerChallengeRanking",
        /* 24 */ "LeaderboardsGetCodinGamerClashRanking",
        /* 25 */ "LeaderboardsGetCodinGamerGlobalRankingByHandle",
        /* 26 */ "LeaderboardsGetFilteredChallengeLeaderboard",
        /* 27 */ "LeaderboardsGetFilteredPuzzleLeaderboard",
        /* 28 */ "LeaderboardsGetGlobalLeaderboard",
        /* 29 */ "PuzzleCountSolvedPuzzlesByProgrammingLanguage",
        /* 30 */ "PuzzleFindAllMinimalProgress",
        /* 31 */ "PuzzleFindProgressByIds",
        /* 32 */ "PuzzleFindProgressByPrettyId",
        /* 33 */ "QuestCountLootableQuests",
        /* 34 */ "QuestFindQuestMap",
        /* 35 */ "SchoolFindById",
        /* 36 */ "SolutionFindBestSolutions",
        /* 37 */ "SolutionFindMySolutions",
        /* 38 */ "TopicFindTopicPageByTopicHandle",
        // obsolete:
        //   "CodinGamerFindCodinGamerGolfPuzzlePoints",
        //   "CodinGamerFindCPByCodinGamerAndPredefinedTestId",
    );
}
// class CG

// --------------------------------------------------------------------
// main program
$g = new CG();
echo "CodinGame data download & API tool (c) 2021 by Bálint Tóth (TBali)" . PHP_EOL;
// $g->testAll(); // generateLanguageLeaderboardCSV() not included
$g->testAPI();
// $g->testEmulated();
// $g->testAvatar();
// $g->generateAllPuzzlesCSV();
// $g->generateAllPuzzlesCSV("easy");
// $g->generateAllPuzzleLeaderboardCSV();
// $g->generateAllChallengeLeaderboardCSV();
// $g->generateLanguageLeaderboardCSV(100);
