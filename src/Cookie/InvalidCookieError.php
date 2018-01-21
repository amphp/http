<?php

namespace Amp\Http\Cookie;

final class InvalidCookieError extends \Error {
    public function __construct(string $message) {
        parent::__construct($message);
    }
}
