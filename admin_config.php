<?php
declare(strict_types=1);

// Local demonstration credentials. Override these with ADMIN_USER and
// ADMIN_PASSWORD_HASH environment variables before deploying the site.
$adminUser = getenv('ADMIN_USER') ?: 'admin';
$adminPasswordHash = getenv('ADMIN_PASSWORD_HASH')
    ?: '$2y$10$UyfhLfhvyWxcHNkFI3fpweKVjpSnJWnanCsSsgh.bHh0qF9QAGXbC';
