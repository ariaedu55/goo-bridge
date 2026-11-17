<?php

declare(strict_types=1);

/**
 * Champ piège anti-bots (doit rester vide).
 * Invisible pour les humains ; les bots le remplissent souvent automatiquement.
 */
function honeypot_field_name(): string
{
    return 'website';
}

function honeypot_field(): string
{
    $name = htmlspecialchars(honeypot_field_name(), ENT_QUOTES, 'UTF-8');
    // Styles inline : garantit le masquage même si le CSS de page échoue / est surchargé
    $wrapStyle = 'position:absolute;left:-10000px;top:auto;width:1px;height:1px;'
        . 'overflow:hidden;clip:rect(0,0,0,0);clip-path:inset(50%);'
        . 'white-space:nowrap;border:0;padding:0;margin:0;'
        . 'opacity:0;pointer-events:none;';

    return '<div class="hp-field" style="' . $wrapStyle . '" aria-hidden="true" tabindex="-1">'
        . '<label for="hp_' . $name . '">Ne pas remplir</label>'
        . '<input type="text" id="hp_' . $name . '" name="' . $name . '" value=""'
        . ' tabindex="-1" autocomplete="off" inputmode="none">'
        . '</div>';
}

/**
 * true = bot probable (champ rempli).
 */
function honeypot_triggered(?array $post = null): bool
{
    $post ??= $_POST;
    $name = honeypot_field_name();
    $val = trim((string) ($post[$name] ?? ''));

    return $val !== '';
}
