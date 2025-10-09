<?php
function ensure_default_admin(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $count = (int)$stmt->fetchColumn();
    if ($count > 0) {
        return;
    }

    $password = password_hash('12345678', PASSWORD_ALGO);
    $stmt = $pdo->prepare('INSERT INTO users(username, password_hash, role, can_manage_animals, can_manage_settings, can_manage_adoptions) VALUES (:username, :hash, :role, 1, 1, 1)');
    $stmt->execute([
        'username' => 'admin',
        'hash' => $password,
        'role' => 'admin'
    ]);
}

function authenticate(PDO $pdo, string $username, string $password): bool
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user'] = $user;
    return true;
}

function logout(): void
{
    unset($_SESSION['user']);
}
