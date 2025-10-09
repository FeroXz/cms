<?php

function get_available_themes(): array
{
    return [
        'aurora' => [
            'label' => 'Aurora Nacht',
            'stylesheet' => null,
            'body_class' => 'theme-aurora',
        ],
        'serpent' => [
            'label' => 'Serpent Flux',
            'stylesheet' => 'theme-serpent.css',
            'body_class' => 'theme-serpent',
        ],
    ];
}

function get_theme_config(string $key): array
{
    $themes = get_available_themes();
    return $themes[$key] ?? $themes['aurora'];
}

