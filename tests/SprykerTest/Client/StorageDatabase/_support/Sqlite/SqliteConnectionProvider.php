<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\StorageDatabase\Sqlite;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Spryker\Client\StorageDatabase\Connection\ConnectionProviderInterface;

/**
 * Hands the storage reader the connection the host lane is already on, instead of opening a second
 * one: on this lane the `spy_*_storage` tables live in the very SQLite database Zed writes to, so
 * there is nothing separate to connect to.
 *
 * The shipped {@see \Spryker\Client\StorageDatabase\Connection\ConnectionProvider} cannot be reused
 * here — it registers its own Propel datasource and calls `setDefaultDatasource()`, which would
 * repoint every Propel query in the process at the storage connection.
 */
class SqliteConnectionProvider implements ConnectionProviderInterface
{
    public function getConnection(): ConnectionInterface
    {
        return Propel::getConnection();
    }
}
