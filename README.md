# codingame_api
Data download tool using Codingame API

APIs currently supported:
* Achievement_findByCodingamerId
* Career_getCodinGamerOptinLocation
* Certification_findTopCertifications
* Challenge_findAllChallenges
* Challenge_findChallengeMinimalInfoByChallengePublicId
* ClashOfCode_getClashRankByCodinGamerId
* CodinGamer_findCodingamePointsStatsByHandle
* CodinGamer_findCodinGamerPublicInformations
* CodinGamer_findFollowers
* CodinGamer_findFollowing
* CodinGamer_findFollowerIds
* CodinGamer_findFollowingIds
* CodinGamer_findRankingPoints
* CodinGamer_findTotalAchievementProgress
* CodinGamer_getMyConsoleInformation
* Codingamer_loginSiteV2
* CodingamerPuzzleTopic_findTopicsByCodingamerId
* Contribution_findContribution
* Contribution_findContributionModerators
* Contribution_getAcceptedContributions
* Contribution_getAllPendingContributions
* Leaderboards_findAllPuzzleLeaderboards
* Leaderboards_getCodinGamerChallengeRanking
* Leaderboards_getCodinGamerClashRanking
* Leaderboards_getCodinGamerGlobalRankingByHandle
* Leaderboards_getFilteredChallengeLeaderboard
* Leaderboards_getFilteredPuzzleLeaderboard
* Leaderboards_getGlobalLeaderboard
* LastActivities_getLastActivities
* Puzzle_countSolvedPuzzlesByProgrammingLanguage
* Puzzle_findAllMinimalProgress
* Puzzle_findProgressByIds
* Puzzle_findProgressByPrettyId
* Quest_countLootableQuests
* Quest_findQuestMap
* School_findById
* Solution_findBestSolutions
* Solution_findMySolutions
* Topic_findTopicPageByTopicHandle
* ... and getting the user avatar PNG file by file id

Composite functions with multiple API calls:
* Getting all puzzles info
* Getting all puzzle leaderboards
* Getting all challenge leaderboards
* Getting achievement count and puzzles solved per language for top 1000 players on global leaderboard 

Usage:
> php cg_api.php

or in Windows you can redirecting report to output.txt by invoking
> test.bat

Clear all generated files (*.json, *.csv, output.txt, avatar.png, cookie.txt) with
> clear.bat

You can edit which test cases to run near the very end of the source file. 
All tests run in ~5 minutes.

Licence: GNU General Public License v3
(If reused, I kindly ask for basic attribution.)