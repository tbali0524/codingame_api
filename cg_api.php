<?php
// --------------------------------------------------------------------
// CodinGame data downloader & API tool
// (c) 2020 by Balint Toth
// v1.02
// --------------------------------------------------------------------

require_once('misc.php');   // defines login credentials with EMAIL and PW constants

abstract class MySelf
{
    const Pseudo = "TBali";
    const UserId = "3305510";
    const PublicHandle = "08e6e13d9f7cad047d86ec4d10c777500155033";
    const Email = EMAIL;
    const Password = PW;
    const SchoolId = 467;
    const Avatar = "26750785092441";
    const Cover = "27032383437051";
} // class MySelf

// --------------------------------------------------------------------
abstract class CodinGameApi
{
    public $serviceURL = NULL;                  // string
    public $requestJSON = "[]";                 // string
    public $responseJSON = NULL;                // string
    public $result = NULL;                      // array
    public $authNeeded = FALSE;                 // bool
    public $loggedIn = FALSE;                   // bool

    // extract filtered data to CSV
    public $keyToGetRows = NULL;                // string
    public $columnNames = NULL;                 // array of string
    public $fieldFixedKey = NULL;               // string
    public $fieldFixedValue = NULL;             // string

    const LeagueName = ["Legend", "Gold", "Silver", "Bronze", "Wood", "Wood", "Wood", "Wood", "Wood", "Wood"];
    const LeagueNameNone = "None";
    const ContentType = "application/json;charset=UTF-8";
    const CookieJarFileName ="cookie.txt";

    const DefaultPuzzlePublicId = "tower-dereference";
    const DefaultChallengePublicId = "a-code-of-ice-and-fire";
    const DefaultSoloPuzzleId = 539;  // Let's go to the Cinema
    const MaxDepthJSON = 100;  

    public function callApi($session = NULL)
    {
        if (is_null($this->serviceURL))
            die("ERROR: missing service URL");
        if (is_null($session))
            $curl = curl_init();
        else
            $curl = $session;
        if ($this->authNeeded and !$this->loggedIn)
        {
            $g = new Codingamer_loginSiteV2;
            $g->callApi($curl);
            $this->loggedIn = TRUE;
        }
        curl_setopt($curl, CURLOPT_URL, $this->serviceURL);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->requestJSON);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_COOKIESESSION, FALSE);
        curl_setopt($curl, CURLOPT_COOKIEJAR, self::CookieJarFileName);
        curl_setopt($curl, CURLOPT_COOKIEFILE, self::CookieJarFileName);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . self::ContentType,
         ));
        $this->responseJSON = curl_exec($curl);
        if (($this->responseJSON === FALSE) or (curl_errno($curl) != 0))
            die("ERROR: Connection Failure: " . curl_error($curl));
        if (is_null($session))
            curl_close($curl);
        $this->result = json_decode($this->responseJSON, TRUE);
        if (json_last_error() != JSON_ERROR_NONE)
            die("ERROR: Response is not in valid JSON format.");
    } // function callApi

    public function writeRequestJSON(string $fileName): void
    {
        if (is_null($this->requestJSON))
            return;
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        fwrite($f, $this->requestJSON);
        fclose($f);
    } // function writeRequestJSON

    public function writeResponseJSON(string $fileName, bool $isPretty = TRUE): void
    {
        if (is_null($this->responseJSON))
            return;
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        if ($isPretty)
        {
            if (is_null($this->result))
                $this->result = json_decode($this->responseJSON, TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                die("ERROR: Response is not in valid JSON format.");
            $output = json_encode($this->result, JSON_PRETTY_PRINT);
        }
        else
            $output = $this->responseJSON;
        fwrite($f, $output);
        fclose($f);
    } // function writeResponseJSON

    public function readFromJSON(string $fileName): void
    {
        $this->responseJSON = file_get_contents($fileName);
        if ($this->responseJSON === FALSE)
            die("ERROR: Cannot open json file.");
        $this->result = json_decode($this->responseJSON, TRUE);
        if (json_last_error() != JSON_ERROR_NONE)
            die("ERROR: file is not in valid JSON format.");
    } // function readFromJSON

    public function getLeagueName(array $inputRow = []): string
    {
        $divisionIndex = $inputRow["divisionIndex"] ?? 0;
        $divisionCount = $inputRow["divisionCount"] ?? 0;
        $divisionOffset = $inputRow["divisionOffset"] ?? 0;
        $leagueId = $divisionCount + $divisionOffset - $divisionIndex - 1;
        return self::LeagueName[$leagueId] ?? self::LeagueNameNone; 
    } // function getLeagueName
    
    public function getRow(array $inputRow): array
    {
        $row = array();
        if (is_null($this->columnNames))
            return $row;
        foreach ($this->columnNames as $key)
        {
            if ($key == "leagueName")
                $row[] = $this->getLeagueName($inputRow["league"] ?? []);
            else
                $row[] = $inputRow[$key] ?? "";
        }
        return $row;
    } // function getRow

    public function writeFilteredCSV($f, bool $headerRow = TRUE, int $startUid = 0): int
    {
        if (is_null($this->result))
            return 0;
        if (is_null($this->columnNames))
            return 0;
        if ($headerRow)
        {
            $row = array();
            $row[] = 'uid';
            if (!is_null($this->fieldFixedKey))
                $row[]= $this->fieldFixedKey;
            foreach ($this->columnNames as $item)
                $row[]= $item;
            fputcsv($f, $row);
        }
        if (is_null($this->keyToGetRows))
            $rows = $this->result;
        else
            $rows = $this->result[$this->keyToGetRows] ?? [];
        $uid = $startUid;
        foreach ($rows as $inputRow)
        {
            $row = array();
            $row[] = $uid;
            if (!is_null($this->fieldFixedValue))
                $row[]= $this->fieldFixedValue;
            $row2 = $this->getRow($inputRow);
            foreach ($row2 as $item)
                $row[]= $item;
            fputcsv($f, $row);
            $uid++;
        }
        return $uid - $startUid;
    } // function writeFilteredCSV

    private function echoItem($key, $value, $depth = 0)
    {
        $pad = str_repeat(' ', $depth * 4);
        if (is_array($value))
        {
            echo $pad . $key . " : (list)\n";
            if ($depth < self::MaxDepthJSON)
                foreach ($value as $key2 => $value2)
                    $this->echoItem($key2, $value2, $depth + 1);
            return;
        }
        echo $pad . $key . " = ";
        if ($value === TRUE)
            echo "true";
        elseif ($value === FALSE)
            echo "false";
        else
            echo $value;
        echo "\n";
    } // function echoItem

    public function echoResult()
    {
        if (is_null($this->result))
            return;
        foreach ($this->result as $key => $value)
            $this->echoItem($key, $value);
    } // function echoResult

    public function getSummary(): string
    {
        if (is_null($this->keyToGetRows))
            $count = count($this->result);
        else
            $count = count($this->result[$this->keyToGetRows] ?? []);
        return "Response list has " . $count . " records.\n";
    } // function getSummary()

} // class CodinGameApi

// --------------------------------------------------------------------
class Achievement_findByCodingamerId extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/Achievement/findByCodingamerId";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->columnNames = ["id", "title", "categoryId", "groupId", "level", "points"];
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class Achievement_findByCodingamerId

// --------------------------------------------------------------------
class Challenge_findAllChallenges extends CodinGameApi
{
    const ServiceURL = "https://www.codingame.com/services/Challenge/findAllChallenges";

    public function __construct()
    {
        $this->serviceURL = self::ServiceURL;
        $this->columnNames = ["publicId", "title", "type", "date"];
    } // function __construct

} // class Challenge_findAllChallenges

// --------------------------------------------------------------------
class Challenge_findChallengeMinimalInfoByChallengePublicId extends CodinGameApi
{
    public $challengePublicId;

    const ServiceURL = "https://www.codingame.com/services/Challenge/findChallengeMinimalInfoByChallengePublicId";

    public function __construct(string $_challengePublicId = CodinGameApi::DefaultChallengePublicId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '["' . $this->challengePublicId . '"]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = $this->challengePublicId . " is a " . ($this->result["type"] ?? "unknown"). " type challenge, titled: " . ($this->result["title"] ?? "-") . "\n";
        return $s;
    } // function getSummary

} // class Challenge_findChallengeMinimalInfoByChallengePublicId

// --------------------------------------------------------------------
class ClashOfCode_getClashRankByCodinGamerId extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/ClashOfCode/getClashRankByCodinGamerId";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Clash of Code ranking of player '" . $this->userId . "' is " . ($this->result["rank"] ?? "?"). " from total players of " . ($this->result["totalPlayers"] ?? "?") . "\n";
        return $s;
    } // function getSummary

} // class ClashOfCode_getClashRankByCodinGamerId

// --------------------------------------------------------------------
class CodinGamer_findCodingamePointsStatsByHandle extends CodinGameApi
{
    public $publicHandle;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findCodingamePointsStatsByHandle";

    public function __construct(String $_publicHandle = MySelf::PublicHandle)
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Player '" . $this->publicHandle . "' has "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsXp"] ?? "?") . " XP, " 
            . ($this->result["codingamePointsRankingDto"]["codingamePointsTotal"] ?? "?") . " CP, rank = "
            . ($this->result["codingamePointsRankingDto"]["codingamePointsRank"] ?? "?") . ". from total of "
            . ($this->result["codingamePointsRankingDto"]["numberCodingamers"] ?? "?") . " players.\n";
        return $s;
    } // function getSummary

} // class CodinGamer_findCodingamePointsStatsByHandle

// --------------------------------------------------------------------
class CodinGamer_findCodinGamerGolfPuzzlePoints extends CodinGameApi
{
    public $userId;
    const GolfPuzzleIds = [762986, 37513, 54473, 37514];

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findCodinGamerGolfPuzzlePoints";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',['. implode(',', self::GolfPuzzleIds). ']]';
    } // function __construct

} // class CodinGamer_findCodinGamerGolfPuzzlePoints

// --------------------------------------------------------------------
class CodinGamer_findCPByCodinGamerAndPredefinedTestId extends CodinGameApi
{
    public $userId;
    const DefaultTestIds = [823636,817286,782435, 835210,52158,260665,427580,68589,502798,674483,298135,53134,40288,37511,199893,64142,695739,46738,34944,25745,6634,6188,818833,60823];

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findCPByCodinGamerAndPredefinedTestId";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',['. implode(',', self::DefaultTestIds) . ']]';
    } // function __construct

} // class CodinGamer_findCPByCodinGamerAndPredefinedTestId

// --------------------------------------------------------------------
class CodinGamer_findFollowerIds extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findFollowerIds";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class CodinGamer_findFollowerIds

// --------------------------------------------------------------------
class CodinGamer_findFollowingIds extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findFollowingIds";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class CodinGamer_findFollowingIds

// --------------------------------------------------------------------
class CodinGamer_findRankingPoints extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findRankingPoints";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class CodinGamer_findRankingPoints

// --------------------------------------------------------------------
class CodinGamer_findTotalAchievementProgress extends CodinGameApi
{
    public $publicHandle;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/findTotalAchievementProgress";

    public function __construct(String $_publicHandle = MySelf::PublicHandle)
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '"]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Player '" . $this->publicHandle . "' has " . ($this->result["achievementCount"] ?? "?") . " achievements from total of " . ($this->result["achievementTotal"] ?? "?") . "\n";
        return $s;
    } // function getSummary

} // class CodinGamer_findTotalAchievementProgress

// --------------------------------------------------------------------
class CodinGamer_getMyConsoleInformation extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/CodinGamer/getMyConsoleInformation";

    public function __construct(string $_userId = MySelf::UserId, bool $getPuzzles = TRUE)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        if ($getPuzzles)
        {
            $this->keyToGetRows = "puzzles";
            $this->columnNames = ["puzzlePublicId", "labelTitle", "ranking", "totalPlayers"];
        }
        else
        {
            $this->keyToGetRows = "challenges";
            $this->columnNames = ["publicId", "title", "ranking", "total"];
        }
        $this->fieldFixedKey = "group"; 
        $this->fieldFixedValue = $this->keyToGetRows; 
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class CodinGamer_getMyConsoleInformation

// --------------------------------------------------------------------
class Codingamer_loginSiteV2 extends CodinGameApi
{
    const ServiceURL = "https://www.codingame.com/services/Codingamer/loginSiteV2";

    public function __construct()
    {
        $this->serviceURL = self::ServiceURL;
        $this->requestJSON = '["' . Myself::Email . '","' . Myself::Password . '",true]';
    } // function __construct

} // class Codingamer_loginSiteV2

// --------------------------------------------------------------------
class Leaderboards_findAllPuzzleLeaderboards extends CodinGameApi
{
    const ServiceURL = "https://www.codingame.com/services/Leaderboards/findAllPuzzleLeaderboards";

    public function __construct()
    {
        $this->serviceURL = self::ServiceURL;
        $this->columnNames = ["publicId", "title", "level", "creationTime", "puzzleId"];
    } // function __construct

} // class Leaderboards_findAllPuzzleLeaderboards

// --------------------------------------------------------------------
class Leaderboards_getCodinGamerChallengeRanking extends CodinGameApi
{
    public $userId;
    public $challengePublicId;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getCodinGamerChallengeRanking";

    public function __construct(string $_challengePublicId = CodinGameApi::DefaultChallengePublicId, string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->challengePublicId = $_challengePublicId;
        $this->requestJSON = '[' . $this->userId . ',"' . $this->challengePublicId . '","global"]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Player '" . ($this->result["pseudo"] ?? ""). "' has a ranking of " . ($this->result["rank"] ?? "") . " in challenge "
            . $this->challengePublicId;
        $divisionCount = ($this->result["league"]["divisionCount"] ?? 0);
        $leagueName = $this->getLeagueName($this->result["league"] ?? []);
        if ($divisionCount != 0)
            $s .= ", and is in the " . $leagueName . " league"; 
        $s .= "\n";
        return $s;
    } // function getSummary

} // class Leaderboards_getCodinGamerChallengeRanking

// --------------------------------------------------------------------
class Leaderboards_getCodinGamerClashRanking extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getCodinGamerClashRanking";

    public function __construct(string $_challengePublicId = CodinGameApi::DefaultChallengePublicId, string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->requestJSON = '[' . $this->userId . ',"global",null]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Player '" . $this->userId . "' did " . ($this->result["clashesCount"] ?? "?") . " Clash of Codes, and has a ranking of "
            . ($this->result["rank"] ?? "?") . " from total players of " . ($this->result["total"] ?? "?") . "\n";
        return $s;
    } // function getSummary

} // class Leaderboards_getCodinGamerClashRanking

// --------------------------------------------------------------------
class Leaderboards_getCodinGamerGlobalRankingByHandle extends CodinGameApi
{
    public $publicHandle;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getCodinGamerGlobalRankingByHandle";

    public function __construct(string $_publicHandle = MySelf::PublicHandle)
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->requestJSON = '["' . $this->publicHandle . '","GENERAL","global",null]';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "Player '" . $this->publicHandle . "' (pseudo: "
            . ($this->result["pseudo"] ?? "?") . ") has "
            . ($this->result["score"] ?? "?") . " CP, rank = "
            . ($this->result["rank"] ?? "?") . ". from total of "
            . ($this->result["total"] ?? "?") . " players.\n";
        return $s;
    } // function getSummary

} // class Leaderboards_getCodinGamerGlobalRankingByHandle

// --------------------------------------------------------------------
class Leaderboards_getFilteredChallengeLeaderboard extends Leaderboards_getFilteredPuzzleLeaderboard
{
    public $publicHandle;
    public $challengePublicId;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getFilteredChallengeLeaderboard";

    public function __construct(string $_challengePublicId = CodinGameApi::DefaultChallengePublicId, string $_publicHandle = MySelf::PublicHandle)
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->challengePublicId = $_challengePublicId;
        $this->keyToGetRows = "users";
        $this->columnNames = ["rank", "leagueName", "programmingLanguage", "pseudo"];
        $this->fieldFixedKey = "challengePublicId"; 
        $this->fieldFixedValue = $this->challengePublicId; 
        $this->requestJSON = '["' . $this->challengePublicId . '","' . $this->publicHandle . '","global",{"active":false,"column":"","filter":""}]';
    } // function __construct

} // class Leaderboards_getFilteredChallengeLeaderboard

// --------------------------------------------------------------------
class Leaderboards_getFilteredPuzzleLeaderboard extends CodinGameApi
{
    public $publicHandle;
    public $puzzlePublicId;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getFilteredPuzzleLeaderboard";

    public function __construct(string $_puzzlePublicId = CodinGameApi::DefaultPuzzlePublicId, string $_publicHandle = MySelf::PublicHandle)
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->puzzlePublicId = $_puzzlePublicId;
        $this->keyToGetRows = "users";
        $this->columnNames = ["rank", "leagueName", "programmingLanguage", "pseudo"];
        $this->fieldFixedKey = "puzzlePublicId"; 
        $this->fieldFixedValue = $this->puzzlePublicId; 
        $this->requestJSON = '["' . $this->puzzlePublicId . '","' . $this->publicHandle . '","global",{"active":false,"column":"","filter":""}]';
    } // function __construct

} // class Leaderboards_getFilteredPuzzleLeaderboard

// --------------------------------------------------------------------
class Leaderboards_getGlobalLeaderboard extends CodinGameApi
{
    public $publicHandle;
    public $pageNum;
    public $leaderboardType;

    const ServiceURL = "https://www.codingame.com/services/Leaderboards/getGlobalLeaderboard";
    const leaderBoardTypes = ["GENERAL", "CONTESTS", "BOT_PROGRAMMING", "OPTIM", "CODEGOLF"];

    public function __construct(int $_pageNum = 1, string $_publicHandle = MySelf::PublicHandle, string $_leaderboardType = "GENERAL")
    {
        $this->serviceURL = self::ServiceURL;
        $this->publicHandle = $_publicHandle;
        $this->pageNum = $_pageNum;
        $this->leaderboardType = $_leaderboardType;
        $this->keyToGetRows = "users";
        $this->columnNames = ["pseudo", "rank", "score",  "xp"];
        $this->requestJSON = '[' . $this->pageNum . ',"' . $this->leaderboardType . '",{keyword: "", active: false, column: "", filter: ""},"' . $this->publicHandle . '",true,"global"]';
    } // function __construct

} // class Leaderboards_getGlobalLeaderboard

// --------------------------------------------------------------------
class Puzzle_findAllMinimalProgress extends CodinGameApi
{
    public $userId;

    const ServiceURL = "https://www.codingame.com/services/Puzzle/findAllMinimalProgress";

    public function __construct(string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->authNeeded = TRUE;
        $this->columnNames = ["id", "level", "creationTime", "solvedCount"];
        $this->requestJSON = '[' . $this->userId . ']';
    } // function __construct

} // class Puzzle_findAllMinimalProgress

// --------------------------------------------------------------------
class Puzzle_findProgressByIds extends CodinGameApi
{
    public $userId;
    public $puzzleIdArray;

    const ServiceURL = "https://www.codingame.com/services/Puzzle/findProgressByIds";

    public function __construct(array $_puzzleIdArray = [CodinGameApi::DefaultSoloPuzzleId], string $_userId = MySelf::UserId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->userId = $_userId;
        $this->puzzleIdArray = $_puzzleIdArray;
        $this->columnNames = ["prettyId", "title", "level", "creationTime", "id", "solvedCount", "globalTotal", "leagueName", "position", "total"];
        $this->requestJSON = '[[' . implode(',', $this->puzzleIdArray) . '],' . $this->userId . ',2]';
    } // function __construct

} // class Puzzle_findProgressByIds

// --------------------------------------------------------------------
class School_findById extends CodinGameApi
{
    public $schoolId;

    const ServiceURL = "https://www.codingame.com/services/School/findById";

    public function __construct(string $_schoolId = MySelf::SchoolId)
    {
        $this->serviceURL = self::ServiceURL;
        $this->schoolId = $_schoolId;
        $this->requestJSON = '[' . $this->schoolId . ']';
    } // function __construct

    public function getSummary(): string
    {
        if (is_null($this->result))
            return "";
        $s = "School '" . $this->schoolId . "' is "
            . ($this->result["name"] ?? "?") . ", located in "
            . ($this->result["city"] ?? "?") . ", "
            . ($this->result["countryId"] ?? "?") . "\n";
        return $s;
    } // function getSummary

} // class School_findById

// --------------------------------------------------------------------
class CG_Avatar extends CodinGameApi
{
    public $id;
    public $responsePNG = NULL;

    const ServiceURL = "https://static.codingame.com/servlet/fileservlet";
    const ContentType = "image/png";

    public function __construct(string $_id = MySelf::Avatar)
    {
        $this->serviceURL = self::ServiceURL;
        $this->id = $_id;
    } // function __construct

    public function getAvatar(string $fileName): void
    {
        $query = '?id=' . $this->id . '&format=profile_avatar';
        if (is_null($this->serviceURL))
            die("ERROR: missing service URL");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->serviceURL . $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $this->responsePNG = curl_exec($curl);
        if (($this->responsePNG === FALSE) or (curl_errno($curl) != 0))
            die("ERROR: Connection Failure: " . curl_error($curl));
        curl_close($curl);
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        fwrite($f, $this->responsePNG);
        fclose($f);
    } // function getAvatar

} // class CG_Avatar

// --------------------------------------------------------------------
class CG
{
    const InputFileNameJSON = "input.json";
    const FileNamePrefixRequestJSON = "request_";
    const FileNamePrefixJSON = "response_";
    const FileNamePostfixJSON = ".json";
    const FileNamePrefixCSV = "result_";
    const FileNamePostfixCSV = ".csv";
    const AvatarFileName = "avatar.png";

    const PuzzlePublicIds = array(
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

        // codegolf
        "paranoid-codesize",
        "thor-codesize",
        "temperatures-codesize",
        "chuck-norris-codesize",
    );

    const ChallengePublicIds = array(
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
        20, // tron-battle, "Tron Battle"

        // WORLDCUP
        "detective-pikaptcha", 
        "a-star-craft", 
        "the-accountant", 
        "code-vs-zombies", 
        "code-of-the-rings", 
        "there-is-no-spoon", 
        "dont-panic", 
        "vox-codei", 
        35, // shadows-of-the-knight, "Shadows of the Knight"
        33, // the-last-crusade, "The Last Crusade"
        32, // skynet-final, "Skynet Finale"
        29, // skynet-revolution, "Skynet Revolution"
        25, // kirks-quest, "Kirk's Quest"
        23, // thor, "Power of Thor"
        21, // mars-lander-fuel, "Mars Lander"
        17, // doctor-who, "Doctor Who"
        15, // bender, "Bender"
        10, // codingame-july-2013, "CodinGame July 2013"
        8,  // genome_sequencing, "Genome Sequencing"
        7,  // codingame-march-2013, "CodinGame March 2013", "CGX Formatter"
        3,  // chuck-norris, "Chuck Norris"
        2,  // codingame-october-2012, "CodinGame October 2012"

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

    const PuzzleIds = array(
        "tutorial"  => [43],
        "easy"      => [4, 5, 6, 7, 8, 9, 10, 40, 108, 121, 133, 154, 171, 182, 188, 203, 210, 229, 235, 238, 319, 341, 343, 345, 351, 355, 358, 360, 373, 393, 395, 
            396, 403, 408, 419, 428, 429, 433, 437, 441, 442, 443, 451, 454, 455, 459, 465, 469, 501, 505, 508, 512, 515, 516, 517, 519, 520, 521, 525, 528, 535, 542, 
            546, 552, 558, 562, 576, 581, 586, 587],
        "medium"    => [1, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 41, 47, 50, 54, 111, 86, 106, 116, 112, 76, 77, 78, 87, 95, 97, 103, 104, 120, 123, 128, 131, 132, 
            142, 147, 150, 157, 158, 159, 161, 169, 170, 172, 173, 174, 187, 190, 193, 198, 199, 202, 207, 220, 223, 227, 228, 230, 233, 234, 239, 243, 244, 245, 246, 
            299, 322, 326, 331, 332, 336, 337, 339, 344, 349, 350, 352, 354, 361, 363, 364, 366, 367, 370, 372, 374, 375, 377, 384, 386, 387, 388, 394, 397, 400, 401, 
            402, 406, 413, 415, 421, 422, 423, 424, 425, 426, 427, 434, 435, 436, 438, 440, 444, 445, 446, 448, 452, 456, 457, 462, 466, 470, 472, 475, 478, 479, 480, 
            482, 484, 485, 487, 488, 490, 492, 499, 502, 503, 510, 511, 518, 522, 526, 529, 531, 532, 533, 537, 538, 539, 543, 544, 545, 548, 551, 561, 565, 566, 567,
            569, 570, 578, 582, 584, 585],
        "hard"      => [22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 35, 44, 48, 55, 113, 96, 89, 109, 85, 100, 84, 119, 122, 125, 127, 130, 134, 135, 136, 138, 
            141, 143, 145, 146, 153, 162, 167, 176, 179, 180, 181, 183, 184, 185, 192, 195, 196, 197, 200, 217, 219, 222, 224, 225, 231, 232, 236, 240, 241, 248, 249, 
            250, 251, 252, 254, 255, 293, 294, 307, 308, 312, 313, 314, 315, 318, 320, 325, 327, 330, 340, 342, 346, 347, 356, 365, 369, 371, 378, 398, 399, 404, 405, 
            407, 412, 417, 418, 431, 432, 453, 458, 463, 476, 477, 486, 506, 507, 523, 547, 554, 559, 590, 591],
        "expert"    => [36, 37, 38, 39, 42, 46, 49, 79, 126, 129, 137, 139, 140, 149, 151, 152, 160, 175, 177, 178, 186, 189, 191, 194, 201, 211, 226, 237, 242, 253, 
            309, 310, 311, 321, 323, 328, 348, 357, 368, 381, 385, 411, 414, 527, 555],
        "multi"     => [63, 64, 68, 66, 65, 67, 69, 148, 156, 168, 221, 247, 298, 324, 329, 359, 376, 380, 382, 383, 410, 420, 450, 460, 468, 471, 473, 474, 481, 483, 
            491, 500, 530, 549, 550, 553, 560, 564, 572, 573, 577, 580, 583, 592], 
        "optim" => [56, 60, 70, 71, 439, 461, 524, 563, 575],
        "codegolf"  => [57, 58, 73, 464],
    );

    public function testAPI(int $idxAPI = self::DefaultIdxAPI, bool $readFromFile = FALSE): void
    {
        if (!isset(self::APInames[$idxAPI]))
            return;
        $apiName = self::APInames[$idxAPI];
        $idxPadded = str_pad($idxAPI, 2, "0", STR_PAD_LEFT);
        echo str_repeat("=", 60) . "\n";
        echo " TEST #" . $idxPadded . " : " . $apiName . "\n";
        echo str_repeat("=", strlen($apiName) + 13) . "\n";
        $g = new $apiName;
        $fileName = self::FileNamePrefixRequestJSON . $apiName . self::FileNamePostfixJSON;
        echo "--- writing API request body to file: " . $fileName . "\n";
        $g->writeRequestJSON($fileName);
        if ($readFromFile)
        {
            echo "--- emulating API response by reading from file: " . self::InputFileNameJSON . "\n";
            $g->readFromJSON(self::InputFileNameJSON);
        }
        else
        {
            echo "--- calling API: " . $g->serviceURL . "\n";
            $g->callApi();
        }
        $fileName = self::FileNamePrefixJSON . $apiName . self::FileNamePostfixJSON;
        echo "--- writing API response to file: " . $fileName . "\n";
        $g->writeResponseJSON($fileName);
        if (!is_null($g->columnNames))
        {
            $fileName = self::FileNamePrefixCSV . $apiName . self::FileNamePostfixCSV;
            echo "--- writing CSV export to file: " . $fileName . "\n";
            $f = fopen($fileName, "w")
                or die("ERROR: Cannot create csv file.");
            $g->writeFilteredCSV($f);
            fclose($f);
        }
        echo $g->getSummary();
        echo "--- END OF TEST #" . $idxPadded . " ---\n\n";
    } // function testAPI

    public function testEmulated(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo " TESTING emulated API call:\n";
        echo str_repeat("=", 28) . "\n";
        $apiName = self::APInames[self::DefaultIdxAPI];
        $g = new $apiName;
        $g->callApi();
        $fileName = self::InputFileNameJSON;
        $g->writeResponseJSON(self::InputFileNameJSON);
        $this->testAPI(self::DefaultIdxAPI, TRUE);
    } // function testEmulated

    public function generateAllPuzzlesCSV(?string $level = NULL): void
    {
        if (!is_null($level) and !isset(self::PuzzleIds[$level]))
            return;
        echo str_repeat("=", 60) . "\n";
        echo " Getting all puzzles info:\n";
        echo str_repeat("=", 27) . "\n";
        echo "--- calling API: Puzzle_findProgressByIds\n";
        if (is_null($level))
        {
            $name = "ALL_PUZZLES";
            $idList = array();
            foreach (self::PuzzleIds as $idArray)
                foreach ($idArray as $id)
                    $idList[] = $id;
        }
        else
        {
            $name = "ALL_PUZZLES_" . $level;
            echo "--- filter: level = " . $level;
            $idList = self::PuzzleIds[$level];
        }
        $g = new Puzzle_findProgressByIds($idList);
        $g->callApi();
        $fileName = self::FileNamePrefixJSON . $name . self::FileNamePostfixJSON;
        echo "--- writing API response to file: " . $fileName . "\n";
        $g->writeResponseJSON($fileName);
        $fileName = self::FileNamePrefixCSV . $name . self::FileNamePostfixCSV;
        echo "--- writing CSV export to file: " . $fileName . "\n";
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $g->writeFilteredCSV($f);
        fclose($f);
        echo "--- END ---\n\n";
    } // function generateAllPuzzlesCSV

    public function generateAllPuzzleLeaderboardCSV(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo " Getting all puzzle leaderboards:\n";
        echo str_repeat("=", 34) . "\n";
        echo "--- calling API: Leaderboards_getFilteredPuzzleLeaderboard multiple times\n";
        $fileName = self::FileNamePrefixCSV . "ALL_PUZZLES_LEADERBOARDS". self::FileNamePostfixCSV;
        echo "--- writing CSV export to file: " . $fileName . "\n";
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $isFirst = TRUE;
        $nextUid = 0;
        foreach(self::PuzzlePublicIds as $puzzlePublicId)
        {
            $g = new Leaderboards_getFilteredPuzzleLeaderboard($puzzlePublicId);
            $g->callApi();
            $countUid = $g->writeFilteredCSV($f, $isFirst, $nextUid);
            $nextUid += $countUid; 
            $isFirst = FALSE;
        }
        fclose($f);
        echo "--- END ---\n\n";
    } // function generateAllPuzzleLeaderboardCSV

    public function generateAllChallengeLeaderboardCSV(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo " Getting all challenge leaderboards:\n";
        echo str_repeat("=", 37) . "\n";
        echo "--- calling API: Leaderboards_getFilteredChallengeLeaderboard multiple times\n";
        $fileName = self::FileNamePrefixCSV . "ALL_CHALLENGES_LEADERBOARDS". self::FileNamePostfixCSV;
        echo "--- writing CSV export to file: " . $fileName . "\n";
        $f = fopen($fileName, "w")
            or die("ERROR: Cannot create csv file.");
        $isFirst = TRUE;
        $nextUid = 0;
        foreach(self::ChallengePublicIds as $challengePublicId)
        {
            $g = new Leaderboards_getFilteredChallengeLeaderboard($challengePublicId);
            $g->callApi();
            $countUid = $g->writeFilteredCSV($f, $isFirst, $nextUid);
            $nextUid += $countUid; 
            $isFirst = FALSE;
        }
        fclose($f);
        echo "--- END ---\n\n";
    } // function generateAllChallengeLeaderboardCSV

    public function testAvatar(): void
    {
        echo str_repeat("=", 60) . "\n";
        echo " TESTING getting avatar PNG file:\n";
        echo str_repeat("=", 32) . "\n";
        $g = new CG_Avatar;
        echo "--- calling GET: " . $g->serviceURL . "\n";
        $g->getAvatar(self::AvatarFileName);
        echo "--- writing PNG response to file: " . self::AvatarFileName . "\n";
        echo "--- END ---\n\n";
    } // function testAvatar

    public function testAll(): void
    {
    	$startTime = microtime(TRUE);
        echo str_repeat("=", 60) . "\n";
        echo "REGRESSION TESTING\n\n";
        $this->testEmulated();
        foreach (self::APInames as $idxAPI => $name)
            $this->testAPI($idxAPI);
        $this->generateAllPuzzlesCSV();
        $this->generateAllPuzzleLeaderboardCSV();
        $this->generateAllChallengeLeaderboardCSV();
        $this->testAvatar();
        $thinkTime = microtime(TRUE) - $startTime;
        echo "Running all tests took " . number_format($thinkTime, 0, '.', '') . " sec\n";
        echo "--- ALL TESTS ENDED ---\n";
    } // function testAll

    const DefaultIdxAPI = 16;
    const APInames = array(
        /*  0 */ "Achievement_findByCodingamerId",
        /*  1 */ "Challenge_findAllChallenges", 
        /*  2 */ "Challenge_findChallengeMinimalInfoByChallengePublicId", 
        /*  3 */ "ClashOfCode_getClashRankByCodinGamerId",
        /*  4 */ "CodinGamer_findCodingamePointsStatsByHandle", 
        /*  5 */ "CodinGamer_findCodinGamerGolfPuzzlePoints", 
        /*  6 */ "CodinGamer_findCPByCodinGamerAndPredefinedTestId", 
        /*  7 */ "CodinGamer_findFollowerIds", 
        /*  8 */ "CodinGamer_findFollowingIds", 
        /*  9 */ "CodinGamer_findRankingPoints", 
        /* 10 */ "CodinGamer_findTotalAchievementProgress", 
        /* 11 */ "CodinGamer_getMyConsoleInformation", 
        /* 12 */ "Codingamer_loginSiteV2", 
        /* 13 */ "Leaderboards_findAllPuzzleLeaderboards", 
        /* 14 */ "Leaderboards_getCodinGamerChallengeRanking", 
        /* 15 */ "Leaderboards_getCodinGamerClashRanking", 
        /* 16 */ "Leaderboards_getCodinGamerGlobalRankingByHandle", 
        /* 17 */ "Leaderboards_getFilteredChallengeLeaderboard", 
        /* 18 */ "Leaderboards_getFilteredPuzzleLeaderboard", 
        /* 19 */ "Leaderboards_getGlobalLeaderboard", 
        /* 20 */ "Puzzle_findAllMinimalProgress",
        /* 21 */ "Puzzle_findProgressByIds", 
        /* 22 */ "School_findById", 
    );

} // class CG

// --------------------------------------------------------------------
// main program
$g = new CG;
echo "CodinGame data downloader & API tool, (c) 2020 by Balint Toth (TBali)\n";
$g->testAll();
// $g->testAPI();
// $g->testEmulated();
// $g->generateAllPuzzlesCSV("easy");
// $g->generateAllPuzzleLeaderboardCSV();
// $g->generateAllChallengeLeaderboardCSV();
// $g->testAvatar();
?>