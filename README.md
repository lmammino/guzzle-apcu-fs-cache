# guzzle-apcu-fs-cache

A zero config cache storage for guzzle-cache-middleware that tries to cache on Apcu and fallbacks to filesystem.

![Tests](https://github.com/lmammino/guzzle-apcu-fs-cache/workflows/Tests/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/lmammino/guzzle-apcu-fs-cache/v/stable)](https://packagist.org/packages/lmammino/guzzle-apcu-fs-cache)
[![Total Downloads](https://poser.pugx.org/lmammino/guzzle-apcu-fs-cache/downloads)](https://packagist.org/packages/lmammino/guzzle-apcu-fs-cache)
[![Latest Unstable Version](https://poser.pugx.org/lmammino/guzzle-apcu-fs-cache/v/unstable)](https://packagist.org/packages/lmammino/guzzle-apcu-fs-cache)
[![License](https://poser.pugx.org/lmammino/guzzle-apcu-fs-cache/license)](https://packagist.org/packages/lmammino/guzzle-apcu-fs-cache)
[![composer.lock](https://poser.pugx.org/lmammino/guzzle-apcu-fs-cache/composerlock)](https://packagist.org/packages/lmammino/guzzle-apcu-fs-cache)


## Install

Requires PHP 5.5+.

Using composer:

```bash
composer require lmammino/guzzle-apcu-fs-cache
```


## Usage

This is an example of how you can use this storage engine with [Guzzle](https://guzzlephp.org) and [GuzzleCacheMiddleware](https://github.com/Kevinrob/guzzle-cache-middleware):

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use LM\GuzzleCache\Storage\ApcuFsStorage;

// creates the storage (with default options)
$cacheStorage = new ApcuFsStorage();

// creates the cache middleware
$cacheMiddleware = new CacheMiddleware(
    new PrivateCacheStrategy($cacheStorage)
);

// creates a Guzzle client middleware stack
$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push($cacheMiddleware, 'cache');
$client = new Client(['handler' => $stack]);

// make a request
$res = $client->request('GET', 'https://loige.co/');
// print response headers
$headers = $res->getHeaders();
foreach ($headers as  $k => $h) {
    foreach ($h as $v) {
        echo $k . ": " . $v . "\n";
    }
}
// print response body
echo "\n\n" . $res->getBody()->__toString() . "\n\n";
```

### Use with Launch Darkly PHP SDK

If you want to use this cache storage with the [Launch Darkly PHP SDK](https://docs.launchdarkly.com/sdk/server-side/php), here's how you can do it:

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use LaunchDarkly\LDClient;
use LaunchDarkly\LDUser;
use LM\GuzzleCache\Storage\ApcuFsStorage;

$cacheStorage = new ApcuFsStorage();
$LDClient = new LDClient($ldSDKKey, ["cache" => $cacheStorage]);
$LDUser = new LDUser("test@example.com");

// use the client
var_dump($LDClient->variation('some-flag', $LDUser));
```


## Configuration

The storage layer can be configured at construction time.

These are the following parameters accepted by the constructor:

 - (string) `$dir`: The directory where the cache should be saved if using the filesystem (Default: the temp directory).
 - (string) `$namespace`: A namespace for the cache storage, useful when using multiple instances and cache should not be mixed between them. It will create a subfolder on the filesystme and a prefix on APC. (Default: `'default'`).
 - (integer) `$ttl` The duration of a cache entry in seconds (Default: `60`).
 - (callable) `$onHit` An optional function that gets called when there's a cache hit.
 - (callable) `$onMiss` An optional function that gets called when there's a cache miss.

If you want to inspect whether your current instance is using APC or the filesystem you can use the following public properties:

 - `$storage->usingApcu` (returns `true` or `false`)
 - `$storage->usingFilesystem` (returns `true` or `false`)


## Rationale and Notes

This was originally created to cache requests made by the Launch Darkly client in a more controlled and configurable way.

**Note**: this middleware will always respect the TTL you provide and will ignore any HTTP cache header returned as response. This is by design. Use this middleware only if you want to enforce cache or for micro-caching scenarios (e.g. very expensive and frequent API calls).


## Contributing

Everyone is very welcome to contribute to this project.
You can contribute just by submitting bugs or suggesting improvements by
[opening an issue on GitHub](https://github.com/lmammino/guzzle-apcu-fs-cache/issues).


## License

Licensed under [MIT License](LICENSE). © Luciano Mammino.

