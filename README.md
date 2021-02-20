# codingame_api
Data download tool using CodinGame's API

APIs currently supported:
* Achievement/FindByCodingamerId
* Career/GetCodinGamerOptinLocation
* Certification/FindTopCertifications
* Challenge/FindAllChallenges
* Challenge/FindChallengeMinimalInfoByChallengePublicId
* ClashOfCode/GetClashRankByCodinGamerId
* CodinGamer/FindCodingamePointsStatsByHandle
* CodinGamer/FindCodinGamerPublicInformations
* CodinGamer/FindFollowerIds
* CodinGamer/FindFollowers
* CodinGamer/FindFollowing
* CodinGamer/FindFollowingIds
* CodinGamer/FindRankingPoints
* CodinGamer/FindTotalAchievementProgress
* CodinGamer/GetMyConsoleInformation
* Codingamer/LoginSiteV2
* CodingamerPuzzleTopic/FindTopicsByCodingamerId
* Contribution/FindContribution
* Contribution/FindContributionModerators
* Contribution/GetAcceptedContributions
* Contribution/GetAllPendingContributions
* Leaderboards/FindAllPuzzleLeaderboards
* Leaderboards/GetCodinGamerChallengeRanking
* Leaderboards/GetCodinGamerClashRanking
* Leaderboards/GetCodinGamerGlobalRankingByHandle
* Leaderboards/GetFilteredChallengeLeaderboard
* Leaderboards/GetFilteredPuzzleLeaderboard
* Leaderboards/GetGlobalLeaderboard
* LastActivities/GetLastActivities
* Puzzle/CountSolvedPuzzlesByProgrammingLanguage
* Puzzle/FindAllMinimalProgress
* Puzzle/FindProgressByIds
* Puzzle/FindProgressByPrettyId
* Quest/CountLootableQuests
* Quest/FindQuestMap
* School/FindById
* Solution/FindBestSolutions
* Solution/FindMySolutions
* Topic/FindTopicPageByTopicHandle
* ... and getting the user avatar PNG file by file id

Composite functions with multiple API calls:
* Getting all puzzles info
* Getting all puzzle leaderboards
* Getting all challenge leaderboards
* Getting achievement count and number of puzzles solved per language for top players on global leaderboard

Usage:
> php cg_api.php

Helper scripts (Windows only):

Redirect report to output.txt
> test.bat

Clear all generated files (request*.json, response*.json, result*.csv, output.txt, avatar.png, cookie.txt) with
> clear.bat

You can edit which test cases to run near the very end of the source file.
All tests run in ~5 minutes
Code is using single thread with blocking I/O, to avoid flooding the CG site.
