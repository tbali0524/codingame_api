# codingame_api
Data download tool using Codingame API

APIs currently supported:
* Achievement_findByCodingamerId
* Challenge_findAllChallenges
* Challenge_findChallengeMinimalInfoByChallengePublicId
* ClashOfCode_getClashRankByCodinGamerId
* CodinGamer_findCodingamePointsStatsByHandle
* CodinGamer_findCodinGamerGolfPuzzlePoints
* CodinGamer_findCPByCodinGamerAndPredefinedTestId
* CodinGamer_findFollowerIdx
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
* + getting the user avatar PNG file by file id

Usage:
> php cg_api.php

or
> test.bat

Edit which test cases to run near the end of the source file. All tests run in 2-3 minutes.
