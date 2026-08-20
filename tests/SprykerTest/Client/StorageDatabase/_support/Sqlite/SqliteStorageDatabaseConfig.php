<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\StorageDatabase\Sqlite;

use Spryker\Client\StorageDatabase\StorageDatabaseConfig;
use Spryker\Shared\StorageDatabase\StorageDatabaseConfig as SharedStorageDatabaseConfig;

/**
 * Maps the storage resource names that do not match their table name to the table the schema
 * actually carries. Without them the reader derives `spy_<resource>_storage` and every read of such
 * a resource dies on a missing table — for `translation` that means any error response at all, since
 * the error provider translates its message before rendering.
 *
 * Both entries below are the ones the core config documents as its example; the host lane needs them
 * declared because it reads storage out of the database rather than Redis, where the resource name
 * is the whole key and no table is ever resolved.
 */
class SqliteStorageDatabaseConfig extends StorageDatabaseConfig
{
    /**
     * @return array<array<string>>
     */
    public function getResourceNameToStorageTableMap(): array
    {
        return [
            'translation' => [
                SharedStorageDatabaseConfig::KEY_STORAGE_TABLE_NAME => 'glossary',
            ],
            'product_search_config_extension' => [
                SharedStorageDatabaseConfig::KEY_STORAGE_TABLE_NAME => 'product_search_config',
            ],
            'product_abstract_tax_set' => [
                SharedStorageDatabaseConfig::KEY_STORAGE_TABLE_NAME => 'tax_product',
            ],
        ];
    }
}
