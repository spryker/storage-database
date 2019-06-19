<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\StorageTableNameResolver;

use Spryker\Client\StorageDatabase\Exception\InvalidRecourseToTableMappingConfigurationException;
use Spryker\Client\StorageDatabase\StorageDatabaseConfig;

class StorageTableNameResolver implements StorageTableNameResolverInterface
{
    protected const MESSAGE_INVALID_RESOURCE_TO_TABLE_CONFIGURATION_MAPPING_EXCEPTION = 'Invalid resource to table mapping configuration.';

    /**
     * @var \Spryker\Client\StorageDatabase\StorageDatabaseConfig
     */
    protected $config;

    /**
     * @var string[][]
     */
    protected $resourceNameToStorageTableMap;

    /**
     * @param \Spryker\Client\StorageDatabase\StorageDatabaseConfig $config
     */
    public function __construct(StorageDatabaseConfig $config)
    {
        $this->config = $config;
        $this->resourceNameToStorageTableMap = $config->getResourceNameToStorageTableMap();
    }

    /**
     * @param string $resourceKey
     *
     * @return string
     */
    public function resolveByResourceKey(string $resourceKey): string
    {
        $resourceName = $this->getResourceNameFromKey($resourceKey);

        return $this->getStorageTableNameByResourceName($resourceName);
    }

    /**
     * @param string $resourceKey
     *
     * @return string
     */
    protected function getResourceNameFromKey(string $resourceKey): string
    {
        [$resourceName] = explode(':', $resourceKey);

        return $resourceName;
    }

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getStorageTableNameByResourceName(string $resourceName): string
    {
        $storageTableName = $this->getStorageTableName($resourceName);
        $storageTablePrefix = $this->getStorageTablePrefix($resourceName);
        $storageTableSuffix = $this->getStorageTableSuffix($resourceName);
        $storageTableNamePartSeparator = $this->config->getStorageTableNamePartSeparator();

        return implode($storageTableNamePartSeparator, array_filter([
            $storageTablePrefix,
            $storageTableName,
            $storageTableSuffix,
        ]));
    }

    /**
     * @param string $resourceName
     *
     * @return bool
     */
    protected function isResourceNameNeedsMapping(string $resourceName): bool
    {
        return array_key_exists($resourceName, $this->resourceNameToStorageTableMap);
    }

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getStorageTablePrefix(string $resourceName): string
    {
        $storageTableNameParts = $this->getStorageTableNamePartsForResourceName($resourceName);

        return $storageTableNameParts[$this->config->getStorageTablePrefixConfigKey()] ?? $this->config->getDefaultStorageTablePrefix();
    }

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getStorageTableSuffix(string $resourceName): string
    {
        $storageTableNameParts = $this->getStorageTableNamePartsForResourceName($resourceName);

        return $storageTableNameParts[$this->config->getStorageTableSuffixConfigKey()] ?? $this->config->getDefaultStorageTableSuffix();
    }

    /**
     * @param string $resourceName
     *
     * @throws \Spryker\Client\StorageDatabase\Exception\InvalidRecourseToTableMappingConfigurationException
     *
     * @return string
     */
    protected function getStorageTableName(string $resourceName): string
    {
        if (!$this->isResourceNameNeedsMapping($resourceName)) {
            return $resourceName;
        }

        $storageTableNameParts = $this->resourceNameToStorageTableMap[$resourceName];
        $resourceStorageTableName = $storageTableNameParts[$this->config->getStorageTableNameConfigKey()] ?? null;

        if (!$resourceStorageTableName) {
            throw new InvalidRecourseToTableMappingConfigurationException(static::MESSAGE_INVALID_RESOURCE_TO_TABLE_CONFIGURATION_MAPPING_EXCEPTION);
        }

        return $resourceStorageTableName;
    }

    /**
     * @param string $resourceName
     *
     * @return string[]
     */
    protected function getStorageTableNamePartsForResourceName(string $resourceName): array
    {
        if (!$this->isResourceNameNeedsMapping($resourceName)) {
            return [];
        }

        return $this->resourceNameToStorageTableMap[$resourceName];
    }
}
