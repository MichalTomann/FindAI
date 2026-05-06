<?php

// Přihlašovací údaje k databázi
define('DB_HOST', 'host.docker.internal');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'explorai');

// Připojení k databázi
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Kontrola připojení
if (!$conn) {
    die('Chyba připojení k databázi: ' . mysqli_connect_error());
}

// Nastavení kódování
mysqli_set_charset($conn, 'utf8mb4');