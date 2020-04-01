<?php

require __DIR__.'/../../vendor/autoload.php';
date_default_timezone_set('UTC');

set_exception_handler(function() {
    exit(1);
});

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