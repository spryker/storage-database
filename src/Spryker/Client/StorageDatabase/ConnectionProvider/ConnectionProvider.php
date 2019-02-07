<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\ConnectionProvider;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Propel;
use Propel\Runtime\ServiceContainer\ServiceContainerInterface;
use Propel\Runtime\ServiceContainer\StandardServiceContainer;
use Spryker\Client\StorageDatabase\Exception\ConnectionFailedException;
use Spryker\Client\StorageDatabase\Exception\InvalidConnectionConfigurationException;
use Spryker\Client\StorageDatabase\StorageDatabaseConfig;
use Throwable;

class ConnectionProvider implements ConnectionProviderInterface
{
    protected const CONNECTION_NAME = 'storage connection';

    protected const MESSAGE_INVALID_CONNECTION_CONFIGURATION_EXCEPTION = 'Connection configuration is invalid';

    /**
     * @var \Propel\Runtime\Connection\ConnectionWrapper|null
     */
    protected static $connection;

    /**
     * @var \Spryker\Client\StorageDatabase\StorageDatabaseConfig
     */
    protected $config;

    /**
     * @param \Spryker\Client\StorageDatabase\StorageDatabaseConfig $config
     */
    public function __construct(StorageDatabaseConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        if (!static::$connection) {
            $this->establishConnection();
        }

        return static::$connection;
    }

    /**
     * @return void
     */
    protected function establishConnection(): void
    {
        $manager = new ConnectionManagerSingle();
        $manager->setConfiguration($this->getPropelConfig());
        $manager->setName(static::CONNECTION_NAME);

        $serviceContainer = $this->getServiceContainer();
        $serviceContainer->setAdapterClass(static::CONNECTION_NAME, $this->config->getDatabaseEngine());
        $serviceContainer->setConnectionManager(static::CONNECTION_NAME, $manager);
        $serviceContainer->setDefaultDatasource(static::CONNECTION_NAME);

        $this->setupConnection();
    }

    /**
     * @return \Propel\Runtime\ServiceContainer\StandardServiceContainer
     */
    protected function getServiceContainer(): StandardServiceContainer
    {
        /** @var \Propel\Runtime\ServiceContainer\StandardServiceContainer $serviceContainer */
        $serviceContainer = Propel::getServiceContainer();

        return $serviceContainer;
    }

    /**
     * @throws \Spryker\Client\StorageDatabase\Exception\ConnectionFailedException
     *
     * @return void
     */
    private function setupConnection(): void
    {
        try {
            static::$connection = Propel::getConnection(static::CONNECTION_NAME, ServiceContainerInterface::CONNECTION_READ);

            static::$connection->useDebug(
                $this->config->isConnectInDebugMode()
            );
        } catch (Throwable $e) {
            throw new ConnectionFailedException($e->getMessage());
        }
    }

    /**
     * @throws \Spryker\Client\StorageDatabase\Exception\InvalidConnectionConfigurationException
     *
     * @return array
     */
    private function getPropelConfig(): array
    {
        $config = $this->config->getConnectionConfigForCurrentEngine();

        if (empty($config) || !is_array($config)) {
            throw new InvalidConnectionConfigurationException(static::MESSAGE_INVALID_CONNECTION_CONFIGURATION_EXCEPTION);
        }

        return $config;
    }
}
