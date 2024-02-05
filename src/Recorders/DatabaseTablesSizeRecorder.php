<?php declare(strict_types=1);

namespace EuSonLito\LaravelPulse\DatabaseTablesSize\Recorders;

use RuntimeException;
use Illuminate\Config\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Process;
use Laravel\Pulse\Events\SharedBeat;
use Laravel\Pulse\Pulse;

class DatabaseTablesSizeRecorder
{
    /**
     * @var class-string
     */
    public string $listen = SharedBeat::class;

    /**
     * @param \Laravel\Pulse\Pulse $pulse
     * @param \Illuminate\Config\Repository $config
     *
     * @return self
     */
    public function __construct(
        protected Pulse $pulse,
        protected Repository $config,
        protected DatabaseManager $manager,
    )
    {
    }

    /**
     * @param \Laravel\Pulse\Events\SharedBeat $event
     *
     * @return void
     */
    public function record(SharedBeat $event): void
    {
        if ($this->enabled()) {
            $this->set();
        }
    }

    /**
     * @return bool
     */
    protected function enabled(): bool
    {
        return $this->enabledConfig()
            && $this->enabledSchedule();
    }

    /**
     * @return bool
     */
    protected function enabledConfig(): bool
    {
        return $this->config('enabled');
    }

    /**
     * @return bool
     */
    protected function enabledSchedule(): bool
    {
        $timestamp = $this->pulse->values('database-tables-size', ['result'])->value('timestamp');
        $schedule = intval($this->config('schedule'));

        return $timestamp && $schedule && ($timestamp <= strtotime('-'.$schedule.' minutes'));
    }

    /**
     * @return void
     */
    protected function set(): void
    {
        $result = [];

        foreach (array_filter($this->config('connections')) as $name) {
            if ($values = $this->getConnection($name)) {
                $result[$name] = $values;
            }
        }

        if ($result) {
            $this->pulse->set('database-tables-size', 'result', json_encode($result));
        }
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function getConnection(string $name): array
    {
        $connection = $this->manager->connection($name);

        $result = match ($connection->getDriverName()) {
            'mysql' => $this->resultMysql($connection),
            'pgsql' => $this->resultPgsql($connection),
            'sqlite' => $this->resultSqlite($connection),
            default => $this->resultNotValid($connection),
        };

        return $this->map($result);
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    protected function resultMysql(Connection $connection): array
    {
        $tables = $connection->select('
            SELECT `table_name` AS `table_name`
            FROM `information_schema`.`tables`
            WHERE `table_schema` = :table_schema;
        ', [
            'table_schema' => $connection->getDatabaseName(),
        ]);

        $connection->statement('
            ANALYZE TABLE `'.implode('`, `', array_column($tables, 'table_name')).'`;
        ');

        return $connection->select('
            SELECT
                `table_name` AS `table_name`,
                ROUND((`data_length` + `index_length`) / 1024 / 1024, 2) AS `total_size`,
                ROUND(`data_length` / 1024 / 1024, 2) AS `table_size`,
                ROUND(`index_length` / 1024 / 1024, 2) AS `index_size`,
                `table_rows` AS `table_rows`
            FROM
                `information_schema`.`TABLES`
            WHERE
                `table_schema` = :table_schema
            ORDER BY
                (`data_length` + `index_length`) DESC;
        ', [
            'table_schema' => $connection->getDatabaseName(),
        ]);
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    protected function resultPgsql(Connection $connection): array
    {
        return $connection->select('
            SELECT
                "s"."relname" AS "table_name",
                pg_total_relation_size("s"."schemaname" || '.' || "s"."relname") / 1024 / 1024 AS "total_size",
                pg_relation_size("s"."schemaname" || '.' || "s"."relname") / 1024 / 1024 AS "table_size",
                (pg_total_relation_size("s"."schemaname" || '.' || "s"."relname") - pg_relation_size("s"."schemaname" || '.' || "s"."relname")) / 1024 / 1024 AS "index_size",
                "s"."n_live_tup" AS "table_rows"
            FROM
                "pg_stat_user_tables" "s"
            JOIN
                "pg_class" "c" ON "s"."relid" = "c"."oid"
            ORDER BY
                pg_total_relation_size("s"."schemaname" || '.' || "s"."relname") DESC;
        ');
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    protected function resultSqlite(Connection $connection): array
    {
        return $connection->select('
            SELECT
                "name" AS "table_name",
                (SELECT COUNT(*) FROM " || name || ") AS "table_rows",
                0 AS "total_size",
                0 AS "table_size",
                0 AS "index_size",
            FROM
                "sqlite_master"
            WHERE
                "type" = "table"
            ORDER BY
                "name";
        ');
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    protected function resultNotValid(Connection $connection): array
    {
        return [];
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function map(array $result): array
    {
        return array_map(static fn ($line) => [
            'table_name' => $line->table_name,
            'table_rows' => intval($line->table_rows),
            'total_size' => floatval($line->total_size),
            'table_size' => floatval($line->table_size),
            'index_size' => floatval($line->index_size),
        ], $result);
    }

    /**
     * @param string $key
     * @param mixed $default = null
     *
     * @return mixed
     */
    protected function config(string $key, mixed $default = null): mixed
    {
        static $config;

        $config ??= $this->config->get('pulse.recorders.'.__CLASS__, []);

        return $config[$key] ?? $default;
    }
}
