<?php

// codingame_api
// checks PHP minimum version requirement and loads all classes

declare(strict_types=1);

const MIN_PHP_VERSION = "7.4.0";
if (version_compare(phpversion(), MIN_PHP_VERSION, "<")) {
    echo "ERROR: Minimum required PHP version is " . MIN_PHP_VERSION . "; you are on " . phpversion() . PHP_EOL;
    exit(1);
}

const PRIVATE_CREDENTIALS_FILE = __DIR__ . "/misc_secret.php";
if (file_exists(PRIVATE_CREDENTIALS_FILE)) {
    require_once PRIVATE_CREDENTIALS_FILE;
} else {
    require_once "misc.php";   // defines login credentials with EMAIL and PW constants
}

require_once "myself.php";
require_once "codingameapi.php";

const API_NAMES = array(
    "AchievementFindByCodingamerId",
    "CareerGetCodinGamerOptinLocation",
    "CertificationFindTopCertifications",
    "ChallengeFindAllChallenges",
    "ChallengeFindChallengeMinimalInfoByChallengePublicId",
    "ClashOfCodeGetClashRankByCodinGamerId",
    "CodinGamerFindCodingamePointsStatsByHandle",
    "CodinGamerFindCodinGamerPublicInformations",
    "CodinGamerFindFollowerIds",
    "CodinGamerFindFollowers",
    "CodinGamerFindFollowing",
    "CodinGamerFindFollowingIds",
    "CodinGamerFindRankingPoints",
    "CodinGamerFindTotalAchievementProgress",
    "CodinGamerGetMyConsoleInformation",
    "CodingamerLoginSiteV2",
    "CodingamerPuzzleTopicFindTopicsByCodingamerId",
    "ContributionFindContribution",
    "ContributionFindContributionModerators",
    "ContributionGetAcceptedContributions",
    "ContributionGetAllPendingContributions",
    "LastActivitiesGetLastActivities",
    "LeaderboardsFindAllPuzzleLeaderboards",
    "LeaderboardsGetCodinGamerChallengeRanking",
    "LeaderboardsGetCodinGamerClashRanking",
    "LeaderboardsGetCodinGamerGlobalRankingByHandle",
    "LeaderboardsGetFilteredChallengeLeaderboard",
    "LeaderboardsGetFilteredPuzzleLeaderboard",
    "LeaderboardsGetGlobalLeaderboard",
    "PuzzleCountSolvedPuzzlesByProgrammingLanguage",
    "PuzzleFindAllMinimalProgress",
    "PuzzleFindProgressByIds",
    "PuzzleFindProgressByPrettyId",
    "QuestCountLootableQuests",
    "QuestFindQuestMap",
    "SchoolFindById",
    "SolutionFindBestSolutions",
    "SolutionFindMySolutions",
    "TopicFindTopicPageByTopicHandle",
    // obsolete:
    "CodinGamerFindCodinGamerGolfPuzzlePoints",
    "CodinGamerFindCPByCodinGamerAndPredefinedTestId",
    // special:
    "Avatar",
);
foreach (API_NAMES as $api_name) {
    require_once "api/" . $api_name . ".php";
}

require_once "codingamedownload.php";
