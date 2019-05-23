<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\Plugin;

use Spryker\Client\Kernel\AbstractPlugin;
use Spryker\Client\StorageExtension\Dependency\Plugin\StoragePluginInterface;

/**
 * @method \Spryker\Client\StorageDatabase\StorageDatabaseClientInterface getClient()
 */
class StorageDatabasePlugin extends AbstractPlugin implements StoragePluginInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->getClient()->get($key);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param array $keys
     *
     * @return array
     */
    public function getMulti(array $keys): array
    {
        return $this->getClient()->getMulti($keys);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return void
     */
    public function resetAccessStats(): void
    {
        $this->getClient()->resetAccessStats();
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return array
     */
    public function getAccessStats(): array
    {
        return $this->getClient()->getAccessStats();
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug): void
    {
        $this->getClient()->setDebug($debug);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param string $key
     * @param string $value
     * @param int|null $ttl
     *
     * @return void
     */
    public function set(string $key, string $value, ?int $ttl = null): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param array $items
     *
     * @return void
     */
    public function setMulti(array $items): void
    {
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param string $key
     *
     * @return int
     */
    public function delete(string $key): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param array $keys
     *
     * @return int
     */
    public function deleteMulti(array $keys): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return int
     */
    public function deleteAll(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return array
     */
    public function getStats(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return array
     */
    public function getAllKeys(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param string $pattern
     *
     * @return array
     */
    public function getKeys(string $pattern): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return int
     */
    public function getCountItems(): int
    {
        return 0;
    }
}
