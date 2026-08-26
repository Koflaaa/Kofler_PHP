<?php
declare(strict_types=1); // Erzwingt strikte Typprüfung

function renderHeader(string $titel): void
// Gibt den HTML-Kopf (Doctype, Meta-Tags, Titel, CSS) für alle Seiten aus
{
    echo '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">'; // Doctype, Sprache Deutsch, Zeichenkodierung UTF-8
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">'; // Responsive Darstellung auf mobilen Geräten
    echo '<title>' . htmlspecialchars($titel) . '</title>'; // Seitentitel im Browser-Tab, escaped gegen XSS
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
    </style></head><body>'; // Gemeinsames CSS für alle Seiten: Grundlayout, Formulare, Fehler-/Erfolgstexte, Tabellen, Navigation
    echo '<h1>' . htmlspecialchars($titel) . '</h1>'; // Überschrift auf der Seite selbst, ebenfalls escaped
}

function renderFooter(): void
// Gibt den schließenden HTML-Teil (body/html) für alle Seiten aus
{
    echo '</body></html>'; // Schließt body- und html-Tag
}
