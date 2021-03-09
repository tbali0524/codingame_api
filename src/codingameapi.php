<?php

/**
 * ====================================================================
 * codingame_api (c) 2021 by Bálint Tóth (TBali)
 *
 * Abstract base class for the specific API classes
 *
 * Methods:
 *   callApi()
 *   writeRequestJSON()
 *   writeResponseJSON()
 *   readFromJSON()
 *   writeFilteredCSV()
 *   writeFilteredTableCSV()
 *   getSummary()
 *
 * Inherited API classes shall overload the constructor and optionally getSummary()
 * ====================================================================
 */

declare(strict_types=1);

namespace CG;

abstract class CodinGameApi
{
    public ?string $serviceURL = null;
    public ?string $requestJSON = "[]";
    public ?string $responseJSON = null;
    public $result = null;              // full response json as multi-level array
                                        // note: QuestCountLootableQuests returns int
    public $filteredResult = null;      // single level table with columnNames
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
            $g = new \CG\api\CodingamerLoginSiteV2();
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

    // This method will be overloaded in some inherited classes, we provide a default implementation here.
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
