<?php

/*
 * Configuration Pest partagée par toute la suite.
 * Les tests de base de données ouvrent leur propre SQLite en mémoire : aucun
 * serveur n'est requis pour lancer `composer test`.
 */

use Calvino\Core\Request;

// config() lit BASE_PATH/config/*.php ; un dossier sans config fait rendre les défauts.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/fixtures');
}

/**
 * Request lit les superglobales à la construction : on les pose, puis on la crée.
 */
function makeRequest(string $method, string $uri, array $server = [], array $post = []): Request
{
    $_SERVER = array_merge(['REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri], $server);
    $query = parse_url($uri, PHP_URL_QUERY);
    $_GET = [];
    if ($query) {
        parse_str($query, $_GET);
    }
    $_POST = $post;
    $_FILES = [];

    return new Request();
}

/**
 * Ouvre une base SQLite en mémoire, crée le schéma des fixtures et la branche sur Model.
 */
function freshDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, role TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
    $pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT)');
    $pdo->exec('CREATE TABLE tokens (id TEXT PRIMARY KEY, label TEXT)');
    \Calvino\Core\Model::setConnection($pdo);

    return $pdo;
}
