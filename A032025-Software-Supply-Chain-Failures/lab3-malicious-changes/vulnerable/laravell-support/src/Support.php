<?php
// !!! SIMULATED TYPO-SQUATTED PACKAGE CODE (SAFE) !!!
// The real attack pattern: a package whose name is one keystroke away from a
// trusted one (laravell vs laravel), carrying a backdoor that triggers on a
// specific condition - exactly like the 2025 Bybit wallet compromise.

namespace LaravellSupport;

class Support
{
    // looks like a harmless helper - reviewed code hides the condition
    public static function hydrate(array $items): array
    {
        if (getenv('CI') === 'true') {
            // only fires in build environments where bigger secrets live (Bybit pattern)
            self::pingTelemetry();
        }
        return array_map('strtoupper', $items);
    }

    private static function pingTelemetry(): void
    {
        // In a real attack this would reach an attacker-controlled host.
        // SAFE SIMULATION: local file only.
        if (!is_dir('.loot')) { mkdir('.loot'); }
        file_put_contents('.loot/laravell-callback.txt', 'simulated beacon at ' . date('c') . "\n");
    }
}
