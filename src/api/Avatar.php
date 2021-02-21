<?php

/**
 * Avatar class:
 * method getAvatar() retrieves the avatar picture of a user via a HTTP GET request and saves it to a local PNG file.
 */

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class Avatar extends CodinGameApi
{
    public string $id;
    public $responsePNG = null;

    public const SERVICE_URL = "https://static.codingame.com/servlet/fileservlet";
    public const CONTENT_TYPE = "image/png";

    public function __construct(string $_id = MySelf::AVATAR_ID)
    {
        $this->serviceURL = self::SERVICE_URL;
        $this->id = $_id;
    }

    public function getAvatar(string $fileName): void
    {
        $query = '?id=' . $this->id . '&format=profile_avatar';
        if (is_null($this->serviceURL)) {
            die("ERROR: missing service URL");
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->serviceURL . $query);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $this->responsePNG = curl_exec($curl);
        if (($this->responsePNG === false) or (curl_errno($curl) != 0)) {
            die("ERROR: Connection Failure: " . curl_error($curl));
        }
        curl_close($curl);
        $f = fopen($fileName, "wb")
            or die("ERROR: Cannot create json file.");
        fwrite($f, $this->responsePNG);
        fclose($f);
    }
}
