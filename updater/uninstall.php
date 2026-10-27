<?php
/*
 * Non-destructive uninstall: preserve player settings, avatar history and backups.
 * This avoids permanently deleting user data when the CodyChat addon is disabled.
 * Tables can be removed manually only after the chat owner has exported them.
 */
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    $candidate = dirname(__DIR__, 2) . '/system/database.php';
    if (is_file($candidate)) require_once $candidate;
}
/* Intentionally do not DROP tables or delete generated avatar files. */
