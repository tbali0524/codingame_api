# codingame_api
CodinGame data download & API tool\
(c) 2021 by Balint Toth (TBali)\
v1.1

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
> test.bat

or
> test.sh

You can edit which test cases to run in `src/cg_api_test.php`
Some APIs require authentication. To call these, you must set your CodinGame credentials in `src/misc.php`
All tests run in ~5 minutes.
(Code is using only a single thread and blocking I/O, to avoid flooding the CG site.)

Generated files:
* `output.txt` : overall report textfile
* `requests/*.json` : API request body contents
* `responses/*.json` : API response body contents
* `results/*.csv` : API response extracted as tabular data
* `results/avatar.png` : avatar picture
* `cookie.txt` : temporary file

Helper script to delete all the generated files:
> clear_output.bat

or
> clear_output.sh

Requirements: `PHP v7.4` or later.

Licensed under MIT license.
