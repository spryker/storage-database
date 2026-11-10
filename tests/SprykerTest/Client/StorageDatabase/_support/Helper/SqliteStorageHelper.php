<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\StorageDatabase\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Spryker\Client\Storage\StorageClient;
use Spryker\Client\Storage\StorageDependencyProvider;
use Spryker\Client\Storage\StorageFactory;
use Spryker\Client\StorageDatabase\Plugin\StorageDatabasePlugin;
use Spryker\Client\StorageDatabase\StorageDatabaseClient;
use Spryker\Client\StorageDatabase\StorageDatabaseDependencyProvider;
use SprykerTest\Client\StorageDatabase\Sqlite\SqliteStorageReaderPlugin;
use SprykerTest\Shared\Testify\Helper\DependencyHelperTrait;
use SprykerTest\Shared\Testify\Helper\StaticVariablesHelper;

/**
 * Points the Storage client at the database instead of Redis, so storage-backed resources can be
 * exercised on the docker-free host lane. Reads land on the `spy_*_storage` tables of the same
 * SQLite database the lane already runs on — the mode Spryker ships as `StorageDatabase`.
 *
 * Enable it after {@see \SprykerTest\Shared\Testify\Helper\SqliteDatabaseHelper} and alongside
 * {@see \SprykerTest\Shared\Testify\Helper\DependencyHelper}, which carries the bindings.
 *
 * This only changes where storage is *read* from. Rows still have to be published first — a test
 * that provisions an entity has to drain the publish queue before the API can see it.
 */
class SqliteStorageHelper extends Module
{
    use DependencyHelperTrait;
    use StaticVariablesHelper;

    public function _before(TestInterface $test): void
    {
        $this->cleanupStorageServices();
        $this->bindStoragePlugins();
    }

    public function _after(TestInterface $test): void
    {
        // The storage client and factory hold the storage service statically; unrestored, the SQLite reader serves every later suite.
        $this->resetStaticCaches();
        $this->staticDefinitions = [];
        (new StorageClient())->resetCache();
    }

    protected function cleanupStorageServices(): void
    {
        $this->cleanupStaticCache(StorageClient::class, 'service');
        $this->cleanupStaticCache(StorageFactory::class, 'storageService');
        $this->cleanupStaticCache(StorageDatabaseClient::class, 'storageDatabase');
        (new StorageClient())->resetCache();
    }

    protected function bindStoragePlugins(): void
    {
        $this->getDependencyHelper()->setDependency(
            StorageDependencyProvider::PLUGIN_STORAGE,
            new StorageDatabasePlugin(),
        );

        $this->getDependencyHelper()->setDependency(
            StorageDatabaseDependencyProvider::PLUGIN_STORAGE_READER_PROVIDER,
            new SqliteStorageReaderPlugin(),
        );
    }
}
