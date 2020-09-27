# codingame_api
Data download tool using Codingame API

APIs currently supported:
* Achievement_findByCodingamerId
* Challenge_findAllChallenges
* Challenge_findChallengeMinimalInfoByChallengePublicId
* ClashOfCode_getClashRankByCodinGamerId
* CodinGamer_findCodingamePointsStatsByHandle
* CodinGamer_findFollowerIds
* CodinGamer_findFollowingIds
* CodinGamer_findRankingPoints
* CodinGamer_findTotalAchievementProgress
* CodinGamer_getMyConsoleInformation
* Codingamer_loginSiteV2
* Leaderboards_findAllPuzzleLeaderboards
* Leaderboards_getCodinGamerChallengeRanking
* Leaderboards_getCodinGamerClashRanking
* Leaderboards_getCodinGamerGlobalRankingByHandle
* Leaderboards_getFilteredChallengeLeaderboard
* Leaderboards_getFilteredPuzzleLeaderboard
* Leaderboards_getGlobalLeaderboard
* Puzzle_findAllMinimalProgress
* Puzzle_findProgressByIds
* School_findById
* CodingamerPuzzleTopic_findTopicsByCodingamerId
* Puzzle_countSolvedPuzzlesByProgrammingLanguage
* Quest_countLootableQuests
* Quest_findQuestMap
* LastActivities_getLastActivities
* Career_getCodinGamerOptinLocation
* Certification_findTopCertifications
* CodinGamer_findCodinGamerPublicInformations
* CodinGamer_findFollowing
* CodinGamer_findFollowers
* Contribution_getAllPendingContributions
* Contribution_getAcceptedContributions
* Contribution_findContribution
* Contribution_findContributionModerators
* Puzzle_findProgressByPrettyId
* Topic_findTopicPageByTopicHandle
* ... and getting the user avatar PNG file by file id

Usage:
> php cg_api.php

or (redirecting report to output.txt)
> test.bat

Clear all generated files (*.json, *.csv, output.txt, avatar.png) with
> clear.bat

Edit which test cases to run near the end of the source file. All tests run in 3-4 minutes.

Licence: GNU General Public License v3 (I kindly ask for attribution.)