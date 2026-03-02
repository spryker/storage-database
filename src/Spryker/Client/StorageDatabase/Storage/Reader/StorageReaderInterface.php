<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\StorageDatabase\Storage\Reader;

interface StorageReaderInterface
{
    public function get(string $resourceKey): string;

    /**
     * @param array<string> $resourceKeys
     *
     * @return array
     */
    public function getMulti(array $resourceKeys): array;
}
