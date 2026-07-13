<?php
declare(strict_types=1);

function renderHeader(string $titel): void
{
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . htmlspecialchars($titel) . '</title>';
    echo '<style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 0 1rem; color: #222; }
        form { display: grid; gap: .6rem; margin-top: 1rem; }
        input, button { padding: .5rem; font-size: 1rem; }
        button { cursor: pointer; }
        .fehler { color: #b00020; }
        .erfolg { color: #0a7a2f; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: .4rem .6rem; text-align: left; }
        nav a { margin-right: 1rem; }
    </style></head><body>';
    echo '<h1>' . htmlspecialchars($titel) . '</h1>';
}

function renderFooter(): void
{
    echo '</body></html>';
}
