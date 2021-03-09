<?php

/**
 * ====================================================================
 * codingame_api v1.1
 * (c) 2021 by Bálint Tóth (TBali)
 *
 * CodinGame data download & API tool
 *
 * repository for latest source: https://github.com/tbali0524/codingame_api
 * requires PHP v7.4 or higher
 * ====================================================================
 */

declare(strict_types=1);

require_once "classloader.php";

$g = new CG\CodinGameDownload();
echo "codingame_api v1.1 - CodinGame data download & API tool (c) 2021 by Balint Toth (TBali)", PHP_EOL;
// $g->testAll(); // generateLanguageLeaderboardCSV() not included
$g->testAPI();
// $g->testEmulated();
// $g->testAvatar();
// $g->generateAllPuzzlesCSV();
// $g->generateAllPuzzlesCSV("easy");
// $g->generateAllPuzzleLeaderboardCSV();
// $g->generateAllChallengeLeaderboardCSV();
// $g->generateLanguageLeaderboardCSV(100);
