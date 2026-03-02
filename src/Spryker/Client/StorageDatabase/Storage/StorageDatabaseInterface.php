<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\Storage;

interface StorageDatabaseInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    public function getMulti(array $keys): array;

    public function resetAccessStats(): void;

    public function getAccessStats(): array;

    public function setDebug(bool $debug): void;

    public function set(string $key, string $value, ?int $ttl = null): void;

    public function setMulti(array $items): void;

    public function delete(string $key): int;

    public function deleteMulti(array $keys): int;

    public function deleteAll(): int;

    public function getStats(): array;

    public function getAllKeys(): array;

    public function getKeys(string $pattern): array;

    public function getCountItems(): int;
}
