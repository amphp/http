<?php declare(strict_types=1);

use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Rfc7230;

require __DIR__ . "/../vendor/autoload.php";

$headers = Rfc7230::formatHeaders([
    "server" => [
        "GitHub.com",
    ],
    "location" => [
        "https://github.com/",
    ],
    "set-cookie" => [
        new ResponseCookie("session", bin2hex(random_bytes(16))),
        new ResponseCookie("user", "amphp"),
    ]
]);

var_dump($headers);
