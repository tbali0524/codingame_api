<?php

/**
 * ====================================================================
 * codingame_api (c) 2021 by Bálint Tóth (TBali)
 *
 * Data downloader wrapper class using the specific API classes
 *
 * Methods:
 *   testAll()
 *     Note: generateLanguageLeaderboardCSV() not included
 *   testAPI()
 *   testEmulated()
 *   testAvatar()
 *   generateAllPuzzlesCSV()
 *   generateAllPuzzleLeaderboardCSV()
 *   generateAllChallengeLeaderboardCSV()
 *   generateLanguageLeaderboardCSV()
 * ====================================================================
 */

declare(strict_types=1);

namespace CG;

class CodinGameDownload
{
    public $countCalls = 0;

    public const NAMESPACE = "\\CG\\api\\";
    public const INPUT_FILENAME_JSON = "responses/input.json";
    public const FILENAME_PREFIX_REQUEST_JSON = "requests/request_";
    public const FILENAME_PREFIX_JSON = "responses/response_";
    public const FILENAME_POSTFIX_JSON = ".json";
    public const FILENAME_PREFIX_CSV = "results/result_";
    public const FILENAME_POSTFIX_CSV = ".csv";
    public const AVATAR_FILENAME = "results/avatar.png";

    public const DEFAULT_IDX_API = 28;
     // call TestAPI() with the integer key from this array
    public const API_NAMES = array(
        /*  0 */ "AchievementFindByCodingamerId",
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
        $g = new \CG\api\PuzzleFindProgressByIds($idList);
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
            $g = new \CG\api\LeaderboardsGetFilteredPuzzleLeaderboard($puzzlePublicId);
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
            $g = new \CG\api\LeaderboardsGetFilteredChallengeLeaderboard($challengePublicId);
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
            $g = new \CG\api\LeaderboardsGetGlobalLeaderboard($pageNum);
            $this->countCalls++;
            $g->callApi();
            $g->extractFilteredTable();
            foreach ($g->filteredResult as $idx => $row) {
                $userId = strval($row["userId"]);
                $publicHandle = strval($row["publicHandle"]);
                $apiAchievement = new \CG\api\CodinGamerFindTotalAchievementProgress($publicHandle);
                $this->countCalls++;
                $apiAchievement->callApi();
                $achievementCount = $apiAchievement->result["achievementCount"] ?? 0;
                $g->filteredResult[$idx]["achievementCount"] = $achievementCount;
                $apiLanguage = new \CG\api\PuzzleCountSolvedPuzzlesByProgrammingLanguage($userId);
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
        $g = new \CG\api\Avatar();
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
}
