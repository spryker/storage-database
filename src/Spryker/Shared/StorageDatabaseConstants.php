<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
interface StorageDatabaseConstants
{
    public const STORAGE_DATABASE_CONNECTION = 'STORAGE_DATABASE_CONFIGURATION';
    public const STORAGE_DB_ENGINE = 'STORAGE_DB_ENGINE';
    public const STORAGE_DB_HOST = 'STORAGE_DB_HOST';
    public const STORAGE_DB_PORT = 'STORAGE_DB_PORT';
    public const STORAGE_DB_DATABASE = 'STORAGE_DB_DATABASE';
    public const STORAGE_DB_USERNAME = 'STORAGE_DB_USERNAME';
    public const STORAGE_DB_PASSWORD = 'STORAGE_DB_PASSWORD';
}
