<?php
declare(strict_types=1);

/** Escape output for HTML — use on EVERY value printed into a template. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function render(string $template, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require __DIR__ . '/../templates/layout.php';
}

function redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}
