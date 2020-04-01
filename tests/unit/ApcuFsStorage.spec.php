<?php

namespace LM\tests\unit;

use LM\GuzzleCache\Storage\ApcuFsStorage;
use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;

describe('ApcuFsStorage', function () {
    context('Implements Kevinrob\GuzzleCache\Storage\CacheStorageInterface', function () {
        $class = new \ReflectionClass('LM\GuzzleCache\Storage\ApcuFsStorage');
        assert($class->implementsInterface('Kevinrob\GuzzleCache\Storage\CacheStorageInterface'), 'ApcuFsStorage implements CacheStorage');
    });
});