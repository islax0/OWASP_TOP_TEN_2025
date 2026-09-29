<?php
/**
 * LAB 2 — Debug page left enabled (Vulnerable)
 *
 * Vulnerable code:
 *   phpinfo();  // no auth, no environment check
 *   → full PHP/env/server disclosure to anonymous users (CWE-489, CWE-215)
 *
 * Fix (see ../fixed/debug_secure.php):
 *   404 in prod; login-only minimal output when debugging.
 */
// ========== VULNERABLE: unauthenticated phpinfo in production ==========
phpinfo();
