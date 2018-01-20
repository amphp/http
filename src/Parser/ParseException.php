<?php

namespace Amp\Http\Parser;

class ParseException extends \Exception {
    /**
     * Thrown on parse errors.
     *
     * @param string $reason Reason that can be used as HTTP response reason.
     */
    public function __construct(string $reason) {
        parent::__construct($reason);
    }
}
