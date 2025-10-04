<?php
// /app/Core/csrf.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or return an existing CSRF token for the session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token against the session.
 */
function csrf_check(?string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}
