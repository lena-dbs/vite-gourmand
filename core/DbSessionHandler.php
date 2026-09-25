<?php
declare(strict_types=1);

// Sessions stockées en base : elles survivent au redémarrage des machines fly.io
// (disque éphémère) et sont partagées entre plusieurs machines.
class DbSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        try {
            return $this->fetch($id);
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S02') {
                throw $e;
            }
            $this->createTable();
            return $this->fetch($id);
        }
    }

    public function write(string $id, string $data): bool
    {
        $stmt = Database::getInstance()->prepare(
            'REPLACE INTO php_session (id, data, updated_at) VALUES (:id, :data, :t)'
        );
        return $stmt->execute([':id' => $id, ':data' => $data, ':t' => time()]);
    }

    public function destroy(string $id): bool
    {
        return Database::getInstance()->prepare('DELETE FROM php_session WHERE id = :id')
            ->execute([':id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = Database::getInstance()->prepare('DELETE FROM php_session WHERE updated_at < :t');
        $stmt->execute([':t' => time() - $max_lifetime]);
        return $stmt->rowCount();
    }

    public function validateId(string $id): bool
    {
        try {
            return $this->exists($id);
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S02') {
                throw $e;
            }
            $this->createTable();
            return false;
        }
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return Database::getInstance()->prepare('UPDATE php_session SET updated_at = :t WHERE id = :id')
            ->execute([':t' => time(), ':id' => $id]);
    }

    private function exists(string $id): bool
    {
        $stmt = Database::getInstance()->prepare('SELECT 1 FROM php_session WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    private function fetch(string $id): string
    {
        $stmt = Database::getInstance()->prepare('SELECT data FROM php_session WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetchColumn();
        return $data === false ? '' : (string)$data;
    }

    private function createTable(): void
    {
        Database::getInstance()->exec(
            'CREATE TABLE IF NOT EXISTS `php_session` (
                `id`         VARCHAR(128) NOT NULL,
                `data`       MEDIUMBLOB   NOT NULL,
                `updated_at` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_php_session_updated_at` (`updated_at`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4'
        );
    }
}
