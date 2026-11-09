<?php
declare(strict_types=1);

// Hosting adapter: run the original PHP pages with an isolated PostgreSQL sandbox.
function demoConnection(): PDO {
    $url = parse_url(getenv('DATABASE_URL') ?: '');
    if (!$url || empty($url['host']) || empty($url['path'])) throw new RuntimeException('Demo database is not configured.');
    $ssl = in_array($url['host'], ['127.0.0.1','localhost'], true) ? 'disable' : 'require';
    return new PDO('pgsql:host=' . $url['host'] . ';port=' . ($url['port'] ?? 5432) . ';dbname=' . ltrim($url['path'], '/') . ';sslmode=' . $ssl, rawurldecode($url['user'] ?? ''), rawurldecode($url['pass'] ?? ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => true]);
}

final class DemoSessions implements SessionHandlerInterface {
    private PDO $db;
    public function __construct() {
        $this->db = demoConnection();
        $this->db->exec('CREATE SCHEMA IF NOT EXISTS seis_hosting');
        $this->db->exec('CREATE TABLE IF NOT EXISTS seis_hosting.sessions (id text PRIMARY KEY, data text NOT NULL, expires_at timestamptz NOT NULL)');
        $this->db->exec('CREATE TABLE IF NOT EXISTS seis_hosting.sandboxes (name text PRIMARY KEY, expires_at timestamptz NOT NULL)');
    }
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $id): string|false {
        $query = $this->db->prepare('SELECT data FROM seis_hosting.sessions WHERE id = ? AND expires_at > now()');
        $query->execute([$id]);
        return $query->fetchColumn() ?: '';
    }
    public function write(string $id, string $data): bool {
        return $this->db->prepare("INSERT INTO seis_hosting.sessions VALUES (?, ?, now() + interval '6 hours') ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, expires_at = EXCLUDED.expires_at")->execute([$id, $data]);
    }
    public function destroy(string $id): bool { return $this->db->prepare('DELETE FROM seis_hosting.sessions WHERE id = ?')->execute([$id]); }
    public function gc(int $max_lifetime): int|false { return $this->db->exec('DELETE FROM seis_hosting.sessions WHERE expires_at < now()'); }
}

function demoDatabase(): PDO {
    static $db;
    if ($db) return $db;
    $_SESSION['sandbox_id'] ??= bin2hex(random_bytes(16));
    $schema = 'seis_demo_' . $_SESSION['sandbox_id'];
    if (!preg_match('/^seis_demo_[a-f0-9]{32}$/D', $schema)) throw new RuntimeException('Invalid demo session.');
    $db = demoConnection();
    $db->beginTransaction();
    try {
        foreach ($db->query('SELECT name FROM seis_hosting.sandboxes WHERE expires_at < now() LIMIT 10')->fetchAll() as $expired) {
            if (preg_match('/^seis_demo_[a-f0-9]{32}$/D', $expired['name'])) {
                $db->exec('DROP SCHEMA IF EXISTS "' . $expired['name'] . '" CASCADE');
                $db->prepare('DELETE FROM seis_hosting.sandboxes WHERE name = ?')->execute([$expired['name']]);
            }
        }
        $lock = $db->prepare('SELECT pg_advisory_xact_lock(hashtext(?))');
        $lock->execute([$schema]);
        $exists = $db->prepare('SELECT 1 FROM information_schema.schemata WHERE schema_name = ?');
        $exists->execute([$schema]);
        $fresh = !$exists->fetchColumn();
        $db->exec('CREATE SCHEMA IF NOT EXISTS "' . $schema . '"');
        $db->exec('SET search_path TO "' . $schema . '"');
        if ($fresh) {
            $db->exec(file_get_contents(__DIR__ . '/schema.sql'));
            $db->prepare("INSERT INTO users (username,password,full_name,email,role) VALUES ('demo',?,'Portfolio Visitor','visitor@example.test','admin')")->execute([password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT)]);
        }
        $db->prepare("INSERT INTO seis_hosting.sandboxes VALUES (?, now() + interval '6 hours') ON CONFLICT (name) DO UPDATE SET expires_at = EXCLUDED.expires_at")->execute([$schema]);
        $db->commit();
    } catch (Throwable $error) { $db->rollBack(); throw $error; }
    return $db;
}
