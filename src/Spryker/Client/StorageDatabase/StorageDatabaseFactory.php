<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\StorageDatabase\Connection\ConnectionProvider;
use Spryker\Client\StorageDatabase\Connection\ConnectionProviderInterface;
use Spryker\Client\StorageDatabase\Dependency\Service\StorageDatabaseToUtilEncodingInterface;
use Spryker\Client\StorageDatabase\Storage\Reader\MySqlStorageReader;
use Spryker\Client\StorageDatabase\Storage\Reader\PostgreSqlStorageReader;
use Spryker\Client\StorageDatabase\Storage\Reader\StorageReaderInterface;
use Spryker\Client\StorageDatabase\Storage\StorageDatabase;
use Spryker\Client\StorageDatabase\Storage\StorageDatabaseInterface;
use Spryker\Client\StorageDatabase\StorageTableNameResolver\StorageTableNameResolver;
use Spryker\Client\StorageDatabase\StorageTableNameResolver\StorageTableNameResolverInterface;
use Spryker\Client\StorageDatabaseExtension\Dependency\Plugin\StorageReaderPluginInterface;

/**
 * @method \Spryker\Client\StorageDatabase\StorageDatabaseConfig getConfig()
 */
class StorageDatabaseFactory extends AbstractFactory
{
    public function createConnectionProvider(): ConnectionProviderInterface
    {
        return new ConnectionProvider(
            $this->getConfig(),
        );
    }

    public function createStorageDatabase(): StorageDatabaseInterface
    {
        return new StorageDatabase(
            $this->getUtilEncodingService(),
            $this->getStorageReaderPlugin(),
        );
    }

    public function createStorageTableNameResolver(): StorageTableNameResolverInterface
    {
        return new StorageTableNameResolver(
            $this->getConfig(),
        );
    }

    public function createPostgreSqlStorageReader(): StorageReaderInterface
    {
        return new PostgreSqlStorageReader(
            $this->createConnectionProvider(),
            $this->createStorageTableNameResolver(),
        );
    }

    public function createMySqlStorageReader(): StorageReaderInterface
    {
        return new MySqlStorageReader(
            $this->createConnectionProvider(),
            $this->createStorageTableNameResolver(),
        );
    }

    public function getUtilEncodingService(): StorageDatabaseToUtilEncodingInterface
    {
        return $this->getProvidedDependency(StorageDatabaseDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    public function getStorageReaderPlugin(): StorageReaderPluginInterface
    {
        return $this->getProvidedDependency(StorageDatabaseDependencyProvider::PLUGIN_STORAGE_READER_PROVIDER);
    }
}
