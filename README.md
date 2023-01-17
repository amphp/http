# amphp/http

AMPHP is a collection of event-driven libraries for PHP designed with fibers and concurrency in mind.
`amphp/http` is a collection of basic HTTP primitives which can be shared by servers and clients.

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require amphp/http
```

This package requires PHP 8.1 or later.

## Usage

This package provides basic primitives needed for HTTP clients and servers.

- [Status codes](#status-codes)
- [Cookies](#cookies)
- [Headers](#headers)

### Status Codes

HTTP status codes are made human-readable via `Amp\Http\HttpStatus`.
It includes a constant for each IANA registered status code.
Additionally, a default reason is available via `HttpStatus::getReason($code)`.

### Cookies

HTTP cookies are specified by [RFC 6265](https://tools.ietf.org/html/rfc6265).
This package implements parsers for the `set-cookie` and `cookie` headers.
It further has a developer friendly API for creating such headers.

> **Note:**
> This library doesn't set standards regarding the cookie encoding.
> As such, the limitations of RFC 6265 apply to names and values.
> If you need to set arbitrary values for certain cookies, it's recommended to use an encoding mechanism like URL encoding or Base64.

#### Set-Cookie

The `set-cookie` header is used to create cookies.
Servers send this header in responses and clients parse the headers if a response contains such headers.
Every header contains exactly one header.
Hence, the responsible class is called `ResponseCookie`.

> **Note:**
> More information about `set-cookie` can be obtained from the [MDN reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie) or other sources.

`ResponseCookie::fromHeader()` accepts a header value and attempts to parse it.
If the parsing succeeds, a `ResponseCookie` is returned.
If not, `null` is returned.
No exceptions are thrown, because received cookies are always user input and untrusted and malformed headers should be discarded according to the RFC.

```php
$attributes = CookieAttributes::default()->withSecure();
$cookie = new ResponseCookie("session", \bin2hex(\random_bytes(16)), $attributes);

var_dump($cookie->getName());
var_dump($cookie->getValue());
var_dump($cookie->isHttpOnly());
var_dump("set-cookie: " . $cookie);
```

```plain
string(7) "session"
string(32) "7b6f532a60bc0786fdfc42307649d634"
bool(true)
string(70) "set-cookie: session=7b6f532a60bc0786fdfc42307649d634; Secure; HttpOnly"
```

#### Cookie

The `cookie` header is used to send cookies from a client to a server.
Clients send this header in requests and servers parse the header if a request contains such a header.
Clients must not send more than one such header.
Hence, the responsible class is called `RequestCookie`.

> **Note:**
> More information about `cookie` can be obtained from the [MDN reference](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cookie) or other sources.

`RequestCookie::fromHeader()` accepts a header value and attempts to parse it.
If the parsing succeeds, an array of `RequestCookie` instances is returned.
If not, an empty array is returned.
No exceptions are thrown, because received cookies are always user input and untrusted and malformed headers should be discarded according to the RFC.

```php
$responseCookie = new ResponseCookie("session", \bin2hex(\random_bytes(16)), $attributes);

$cookie = ResponseCookie::fromHeader($responseCookie);
$cookie = new RequestCookie("session", $cookie->getValue());

var_dump($cookie->getName());
var_dump($cookie->getValue());
var_dump("cookie: " . $cookie);
```

```plain
string(7) "session"
string(32) "7b6f532a60bc0786fdfc42307649d634"
string(48) "cookie: session=7b6f532a60bc0786fdfc42307649d634"
```

### Headers

This package provides an HTTP header parser based on [RFC 7230](https://tools.ietf.org/html/rfc7230).
It also provides a corresponding header formatter.

#### Parsing Headers

`Amp\Http\Rfc7230::parseHeaders()` parses raw headers into an array mapping header names to arrays of header values.
Every header line must end with `\r\n`, also the last one.

```php
<?php

use Amp\Http\Rfc7230;

require __DIR__ . "/../vendor/autoload.php";

$rawHeaders = "Server: GitHub.com\r\n"
    . "Date: Tue, 31 Oct 2006 08:00:29 GMT\r\n"
    . "Connection: close\r\n"
    . "Content-Length: 0\r\n";

$headers = Rfc7230::parseHeaders($rawHeaders);

var_dump($headers);
```

```plain
array(4) {
  ["server"]=>
  array(1) {
    [0]=>
    string(10) "GitHub.com"
  }
  ["date"]=>
  array(1) {
    [0]=>
    string(29) "Tue, 31 Oct 2006 08:00:29 GMT"
  }
  ["connection"]=>
  array(1) {
    [0]=>
    string(5) "close"
  }
  ["content-length"]=>
  array(1) {
    [0]=>
    string(1) "0"
  }
}
```

#### Formatting Headers

`Amp\Http\Rfc7230::formatHeaders()` takes an array with the same format as `parseHeaders()` returns.
It protects against header injections and other non-compliant header names and values.

```php
<?php

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
        new ResponseCookie("session", \bin2hex(\random_bytes(16))),
        new ResponseCookie("user", "amphp"),
    ]
]);

var_dump($headers);
```

```plain
string(149) "server: GitHub.com
location: https://github.com/
set-cookie: session=09f1906ab952c9ae14e2c07bb714392f; HttpOnly
set-cookie: user=amphp; HttpOnly
"
```

## Versioning

`amphp/http` follows the [semver](http://semver.org/) semantic versioning specification like all other `amphp` packages.

> **Note:** BC breaks that are strictly required for RFC compliance are not considered BC breaks.
> These include cases like wrong quote handling for cookies, where the RFC isn't too clear.
>
> A lax parser will however not be changed unless it is necessary for security reasons.

## Security

If you discover any security related issues, please email [`contact@amphp.org`](mailto:contact@amphp.org) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
