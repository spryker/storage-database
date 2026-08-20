<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Client\StorageDatabase\Sqlite;

use PDOException;
use Propel\Runtime\Connection\StatementInterface;
use RuntimeException;
use Spryker\Client\StorageDatabase\Storage\Reader\AbstractStorageReader;

/**
 * Reads the `spy_*_storage` tables from a SQLite database, so host-lane integration suites can run
 * storage-backed resources against the same SQLite file the Zed layer already uses — no Redis, no
 * service container. The shipped module covers MySQL and PostgreSQL only; this lives in test
 * support because SQLite is a test-lane engine, not a supported storage backend.
 *
 * Two SQLite specifics drive the SQL, both verified rather than assumed:
 *
 * - `HAVING` without an aggregate is rejected ("HAVING clause on a non-aggregate query"), so the
 *   null filter goes into a `WHERE` over a subquery — the shape PostgreSQL already uses.
 * - `json_extract()` returns the *unquoted* scalar, unlike MySQL's `JSON_EXTRACT()`, so an alias
 *   hit needs no further unwrapping.
 */
class SqliteStorageReader extends AbstractStorageReader
{
    protected const string DEFAULT_PLACEHOLDER_KEY = ':key';

    protected const string DEFAULT_PLACEHOLDER_ALIAS_KEY = ':alias_key';

    protected const string SELECT_STATEMENT_PATTERN = '
      SELECT *
        FROM (
          SELECT %1$s AS resource_key, (CASE WHEN `key` = %1$s THEN data WHEN json_extract(alias_keys, %2$s) IS NOT NULL THEN json_extract(alias_keys, %2$s) END) AS resource_data
            FROM %3$s
        ) AS storage
        WHERE storage.resource_data IS NOT NULL
    ';

    /**
     * @throws \RuntimeException
     */
    protected function createSingleSelectStatementForResourceKey(string $resourceKey): StatementInterface
    {
        $tableName = $this->tableNameResolver->resolveByResourceKey($resourceKey);

        try {
            $statement = $this->createPreparedStatement($this->buildSelectQuerySql($tableName));
        } catch (PDOException $pdoException) {
            throw new RuntimeException(sprintf(
                'Storage read for resource key "%s" resolved to table "%s", which the SQLite template does not carry.'
                . ' If the resource name does not match its table, add a mapping to %s::getResourceNameToStorageTableMap().',
                $resourceKey,
                $tableName,
                SqliteStorageDatabaseConfig::class,
            ), 0, $pdoException);
        }

        $statement->bindValue(static::DEFAULT_PLACEHOLDER_KEY, $resourceKey);
        $statement->bindValue(static::DEFAULT_PLACEHOLDER_ALIAS_KEY, $this->formatKeyAsSearchPath($resourceKey));

        return $statement;
    }

    /**
     * @param array<string> $resourceKeys
     */
    protected function createMultiSelectStatementForResourceKeys(array $resourceKeys): StatementInterface
    {
        $queryDataPerTable = $this->prepareMultiTableQueryData($resourceKeys);

        return $this->bindValuesToStatement(
            $this->buildMultiTableSelectStatement($queryDataPerTable),
            $queryDataPerTable,
        );
    }

    /**
     * The placeholder index is derived from the position, not the array key: callers of
     * `StorageClient::getMulti()` are free to index their key list by anything, and several do —
     * `CustomerStorageReader` passes storage keys indexed by customer reference, which made the
     * string key reach the int-typed placeholder builder.
     *
     * @param array<string> $resourceKeys
     *
     * @return array<string, array<array<string, string>>>
     */
    protected function prepareMultiTableQueryData(array $resourceKeys): array
    {
        $multiTableQueryData = [];

        foreach (array_values($resourceKeys) as $index => $resourceKey) {
            $tableName = $this->tableNameResolver->resolveByResourceKey($resourceKey);
            $multiTableQueryData[$tableName][] = [
                $this->buildKeyPlaceholder($index) => $resourceKey,
                $this->buildAliasKeysPlaceholder($index) => $this->formatKeyAsSearchPath($resourceKey),
            ];
        }

        return $multiTableQueryData;
    }

    /**
     * @param array<string, array<array<string, string>>> $queryDataPerTable
     *
     * @throws \RuntimeException
     */
    protected function buildMultiTableSelectStatement(array $queryDataPerTable): StatementInterface
    {
        $selectFragments = [];

        foreach ($queryDataPerTable as $tableName => $tableQueryData) {
            foreach ($tableQueryData as $dataSet) {
                /**
                 * @var string $keyPlaceholder
                 * @var string $aliasKeyPlaceholder
                 */
                [$keyPlaceholder, $aliasKeyPlaceholder] = array_keys($dataSet);
                $selectFragments[] = $this->buildSelectQuerySql($tableName, $keyPlaceholder, $aliasKeyPlaceholder);
            }
        }

        try {
            return $this->createPreparedStatement(implode(' UNION ALL ', $selectFragments));
        } catch (PDOException $pdoException) {
            throw new RuntimeException(sprintf(
                'Storage read resolved to tables "%s", at least one of which the SQLite template does not carry.'
                . ' If the resource name does not match its table, add a mapping to %s::getResourceNameToStorageTableMap().',
                implode('", "', array_keys($queryDataPerTable)),
                SqliteStorageDatabaseConfig::class,
            ), 0, $pdoException);
        }
    }

    /**
     * @param array<string, array<array<string, string>>> $queryDataPerTable
     */
    protected function bindValuesToStatement(StatementInterface $statement, array $queryDataPerTable): StatementInterface
    {
        foreach ($queryDataPerTable as $queryData) {
            foreach (array_merge(...$queryData) as $placeholder => $value) {
                $statement->bindValue($placeholder, $value);
            }
        }

        return $statement;
    }

    protected function buildSelectQuerySql(
        string $tableName,
        string $keyPlaceholder = self::DEFAULT_PLACEHOLDER_KEY,
        string $aliasKeyPlaceholder = self::DEFAULT_PLACEHOLDER_ALIAS_KEY,
    ): string {
        return sprintf(static::SELECT_STATEMENT_PATTERN, $keyPlaceholder, $aliasKeyPlaceholder, $tableName);
    }

    protected function formatKeyAsSearchPath(string $key): string
    {
        return sprintf('$."%s"', $key);
    }

    protected function buildKeyPlaceholder(int $index = 0): string
    {
        return static::DEFAULT_PLACEHOLDER_KEY . $index;
    }

    protected function buildAliasKeysPlaceholder(int $index = 0): string
    {
        return static::DEFAULT_PLACEHOLDER_ALIAS_KEY . $index;
    }
}
