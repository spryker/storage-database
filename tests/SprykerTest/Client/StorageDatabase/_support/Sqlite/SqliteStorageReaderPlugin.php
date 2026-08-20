<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\StorageDatabase\Sqlite;

use Spryker\Client\StorageDatabase\Storage\Reader\StorageReaderInterface;
use Spryker\Client\StorageDatabase\StorageTableNameResolver\StorageTableNameResolver;
use Spryker\Client\StorageDatabaseExtension\Dependency\Plugin\StorageReaderPluginInterface;

/**
 * Registers {@see \SprykerTest\Client\StorageDatabase\Sqlite\SqliteStorageReader} as the storage
 * reader for the host lane.
 *
 * Deliberately not an `AbstractPlugin`: the kernel resolves a plugin's factory from its namespace,
 * and this one lives outside `Spryker\Client\StorageDatabase`. It builds its collaborators
 * directly, which is also what lets a test hand in its own reader.
 */
class SqliteStorageReaderPlugin implements StorageReaderPluginInterface
{
    protected StorageReaderInterface $storageReader;

    public function __construct(?StorageReaderInterface $storageReader = null)
    {
        $this->storageReader = $storageReader ?? new SqliteStorageReader(
            new SqliteConnectionProvider(),
            new StorageTableNameResolver(new SqliteStorageDatabaseConfig()),
        );
    }

    public function get(string $resourceKey): string
    {
        return $this->storageReader->get($resourceKey);
    }

    /**
     * @param array<string> $resourceKeys
     *
     * @return array<string, string>
     */
    public function getMulti(array $resourceKeys): array
    {
        return $this->storageReader->getMulti($resourceKeys);
    }
}
