<?php

require __DIR__.'/../../vendor/autoload.php';
date_default_timezone_set('UTC');

function assert_failure($file, $line, $assertion, $message = '')
{
    fwrite(STDERR, "The assertion $assertion in $file on line $line has failed: $message\n\n");
    exit(1);
}

assert_options(ASSERT_CALLBACK, 'assert_failure');

use LM\GuzzleCache\Storage\ApcuFsStorage;

global $cacheHitCount;
global $cacheMissCount;
global $expiryCount;
$cacheHitCount = 0;
$cacheMissCount = 0;
$expiryCount = 0;

function onHit ($key) {
    echo "\t\t > HIT $key\n";
    global $cacheHitCount;
    $cacheHitCount++;
}

function onMiss ($key) {
    echo "\t\t > MISS $key\n";
    global $cacheMissCount;
    $cacheMissCount++;
}

$ldSDKKey = getenv('LD_TOKEN');

$cacheDir = 'cache/';
$namespace = 'launch_darkly';
$ttl = 5;
$cacheStorage = new ApcuFsStorage($cacheDir, $namespace, $ttl, 'onHit', 'onMiss');
$LDClient = new LaunchDarkly\LDClient($ldSDKKey, ["cache" => $cacheStorage]);
$LDUser = new LaunchDarkly\LDUser("test@githubactions.com");

$hasApc = extension_loaded('apc') && ini_get('apc.enabled');
echo "\n   uses ". ($hasApc ? "Apcu" : "Filesystem") . "...\n";
assert($cacheStorage->usingApcu == $hasApc, "Is using Apcu if available");
assert($cacheStorage->usingFilesystem == !$hasApc, "Is using Filesystem if Apcu is not available");
echo "✔️  OK\n";

// NOTE: every cache hit is alaways counted double. For some reason on cache hit LD tries an additional request (maybe revalidation attempt)

echo "\n   Get enabled-flag\n";
assert($LDClient->variation('enabled-flag', $LDUser) == true, "Must see an enabled flag as enabled");
assert($cacheMissCount == 1, "Missed cache 1 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 0, "Hit cache 0 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get enabled-flag (cache)\n";
assert($LDClient->variation('enabled-flag', $LDUser) == true, "Must see an enabled flag as enabled (cache)");
assert($cacheMissCount == 1, "Missed cache 1 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 2, "Hit cache 2 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get disabled-flag\n";
assert($LDClient->variation('disabled-flag', $LDUser) == false, "Must see a disabled flag as disabled");
assert($cacheMissCount == 2, "Missed cache 2 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 2, "Hit cache 2 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get disabled-flag (cache)\n";
assert($LDClient->variation('disabled-flag', $LDUser) == false, "Must see a disabled flag as disabled (cache)");
assert($cacheMissCount == 2, "Missed cache 2 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 4, "Hit cache 4 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

// sleep for a 10 seconds to let cache expire
echo "\n   ... sleeping 10 seconds to invalidate cache ...\n";
sleep (10);

// remake calls
echo "\n   Get enabled-flag\n";
assert($LDClient->variation('enabled-flag', $LDUser) == true, "Must see an enabled flag as enabled");
assert($cacheMissCount == 3, "Missed cache 3 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 4, "Hit cache 4 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get enabled-flag (cache)\n";
assert($LDClient->variation('enabled-flag', $LDUser) == true, "Must see an enabled flag as enabled (cache)");
assert($cacheMissCount == 3, "Missed cache 3 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 6, "Hit cache 6 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get disabled-flag\n";
assert($LDClient->variation('disabled-flag', $LDUser) == false, "Must see a disabled flag as disabled");
assert($cacheMissCount == 4, "Missed cache 4 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 6, "Hit cache 6 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";

echo "\n   Get disabled-flag (cache)\n";
assert($LDClient->variation('disabled-flag', $LDUser) == false, "Must see a disabled flag as disabled (cache)");
assert($cacheMissCount == 4, "Missed cache 4 time(s) (cacheMissCount: $cacheMissCount)");
assert($cacheHitCount == 8, "Hit cache 8 time(s) (cacheHitCount: $cacheHitCount)");
echo "✔️  OK\n";
