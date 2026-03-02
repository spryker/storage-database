<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\Storage;

use Spryker\Client\StorageDatabase\Dependency\Service\StorageDatabaseToUtilEncodingInterface;
use Spryker\Client\StorageDatabaseExtension\Dependency\Plugin\StorageReaderPluginInterface;

class StorageDatabase implements StorageDatabaseInterface
{
    /**
     * @var string
     */
    protected const KEY_PLACEHOLDER = ':key';

    /**
     * @var string
     */
    protected const KV_PREFIX = 'kv:';

    /**
     * @var string
     */
    protected const ACCESS_STATS_KEY_COUNT = 'count';

    /**
     * @var string
     */
    protected const ACCESS_STATS_KEY_KEYS = 'keys';

    /**
     * @var string
     */
    protected const ACCESS_STATS_KEY_READ = 'read';

    /**
     * @var \Spryker\Client\StorageDatabaseExtension\Dependency\Plugin\StorageReaderPluginInterface
     */
    protected $storageReaderPlugin;

    /**
     * @var array
     */
    protected $accessStats;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var \Spryker\Client\StorageDatabase\Dependency\Service\StorageDatabaseToUtilEncodingInterface
     */
    protected $utilEncodingService;

    public function __construct(StorageDatabaseToUtilEncodingInterface $utilEncodingService, StorageReaderPluginInterface $storageReaderPlugin)
    {
        $this->utilEncodingService = $utilEncodingService;
        $this->storageReaderPlugin = $storageReaderPlugin;
        $this->resetAccessStats();
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        $result = $this->storageReaderPlugin->get($key);
        $this->addReadAccessStats($key);

        $decodedResult = $this->decodeResult($result);

        return $decodedResult ?: null;
    }

    /**
     * @param array<string> $keys
     *
     * @return array
     */
    public function getMulti(array $keys): array
    {
        if (count($keys) === 0) {
            return [];
        }

        $results = $this->storageReaderPlugin->getMulti($keys);
        $results = $this->combineKeysWithValues($keys, $results);
        $this->addMultiReadAccessStats($keys);

        return $results;
    }

    /**
     * @param array<string> $keys
     * @param array<string, mixed> $data
     *
     * @return array
     */
    protected function combineKeysWithValues(array $keys, array $data): array
    {
        $results = [];

        foreach ($keys as $key) {
            $prefixedKey = $this->getPrefixedKeyName($key);
            $results[$prefixedKey] = $data[$key] ?? null;
        }

        return $results;
    }

    public function resetAccessStats(): void
    {
        $this->accessStats = [
            static::ACCESS_STATS_KEY_COUNT => [
                static::ACCESS_STATS_KEY_READ => 0,
            ],
            static::ACCESS_STATS_KEY_KEYS => [
                static::ACCESS_STATS_KEY_READ => [],
            ],
        ];
    }

    public function getAccessStats(): array
    {
        return $this->accessStats;
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    public function set(string $key, string $value, ?int $ttl = null): void
    {
    }

    public function setMulti(array $items): void
    {
    }

    public function delete(string $key): int
    {
        return 0;
    }

    public function deleteMulti(array $keys): int
    {
        return 0;
    }

    public function deleteAll(): int
    {
        return 0;
    }

    public function getStats(): array
    {
        return [];
    }

    public function getAllKeys(): array
    {
        return [];
    }

    public function getKeys(string $pattern): array
    {
        return [];
    }

    public function getCountItems(): int
    {
        return 0;
    }

    /**
     * @param array<string> $keys
     *
     * @return array<string>
     */
    protected function getPrefixedKeys(array $keys): array
    {
        $prefixedKeys = [];

        foreach ($keys as $key) {
            $prefixedKeys[] = $this->getPrefixedKeyName($key);
        }

        return $prefixedKeys;
    }

    protected function getPrefixedKeyName(string $key): string
    {
        return sprintf('%s%s', static::KV_PREFIX, $key);
    }

    protected function addReadAccessStats(string $key): void
    {
        if ($this->debug) {
            $this->accessStats[static::ACCESS_STATS_KEY_COUNT][static::ACCESS_STATS_KEY_READ]++;
            $this->accessStats[static::ACCESS_STATS_KEY_KEYS][static::ACCESS_STATS_KEY_READ][] = $key;
        }
    }

    /**
     * @param array<string> $keys
     *
     * @return void
     */
    protected function addMultiReadAccessStats(array $keys): void
    {
        if ($this->debug) {
            $this->accessStats[static::ACCESS_STATS_KEY_COUNT][static::ACCESS_STATS_KEY_READ] += count($keys);
            $this->accessStats[static::ACCESS_STATS_KEY_KEYS][static::ACCESS_STATS_KEY_READ] = array_unique(
                array_merge($this->accessStats[static::ACCESS_STATS_KEY_KEYS][static::ACCESS_STATS_KEY_READ], $keys),
            );
        }
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    protected function decodeResult(string $data)
    {
        $decodedResult = $this->utilEncodingService->decodeJson($data, true);

        if (json_last_error() === JSON_ERROR_SYNTAX) {
            return $data;
        }

        return $decodedResult;
    }
}
