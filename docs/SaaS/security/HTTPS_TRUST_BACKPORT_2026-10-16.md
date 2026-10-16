# Security Backport Review: Forwarded HTTPS Header Spoofing (GHSA-7wmf-pw8j-mc78)

## 1. Upstream Traceability & Advisory Details

- **GitHub Advisory:** [GHSA-7wmf-pw8j-mc78](https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-7wmf-pw8j-mc78)
- **CVE Identifier:** CVE-2026-63220
- **Upstream Releases:** Patched in CodeIgniter v4.7.4 and v4.7.5 ([Release v4.7.5](https://github.com/codeigniter4/CodeIgniter4/releases/tag/v4.7.5))
- **CWE:** [CWE-348: Use of Less Trusted Source](https://cwe.mitre.org/data/definitions/348.html)
- **MDN Reference:** [X-Forwarded-Proto](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Forwarded-Proto)

### Vulnerability Summary
In in-tree CodeIgniter 4.6.3, `IncomingRequest::isSecure()` trusted client-supplied `X-Forwarded-Proto` and `Front-End-Https` headers unconditionally from any client IP address. An attacker connecting directly over plain HTTP could spoof `X-Forwarded-Proto: https` to trick the application into treating an insecure connection as secure, potentially bypassing transport security checks (e.g. `AppApiHttps`, `forceGlobalSecureRequests`).

---

## 2. Adaptation Strategy for CodeIgniter 4.6.3

The patch is an **adapted backport** from official CodeIgniter v4.7.5 tailored for the in-tree 4.6.3 request abstraction without taking on a full framework upgrade:

1. **Native HTTPS Semantics Preserved:**
   - In upstream CI 4.7.5, server access was migrated to `service('superglobals')->server('HTTPS')`.
   - CI 4.6.3 does not possess the `Superglobals` service.
   - We retain the original legacy nonempty check: `if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') { return true; }`.
   - Native HTTPS requests (`HTTPS=on`, `HTTPS=1`) continue to be immediately recognized without proxy inspection.

2. **Strict Socket Peer Validation via `isFromTrustedProxy()`:**
   - Forwarded headers (`X-Forwarded-Proto`, `Front-End-Https`) are inspected **only if** `isFromTrustedProxy()` returns `true`.
   - `isFromTrustedProxy()` reads `REMOTE_ADDR` strictly via `$this->getServer('REMOTE_ADDR')` alone without falling back to global `$_SERVER`. This respects cached/explicit request state and prevents bypassing request encapsulation.
   - `$remoteAddr` is verified against `Config\App::$proxyIPs`.

3. **Untouched `getIPAddress()`:**
   - Unrelated `getIPAddress()` behavior is left untouched to prevent behavioral drift in client IP resolution.
   - `isFromTrustedProxy()` deliberately does **not** call `getIPAddress()`, ensuring that spoofed `X-Forwarded-For` headers cannot be used to spoof proxy trust.

4. **CIDR & Subnet Matching via `checkIPAgainstProxy()`:**
   - Extracted verbatim from upstream v4.7.5.
   - Supports single IPv4/IPv6 addresses and CIDR subnets (including partial-byte masks such as `/25` and `/65`).
   - Enforces family restriction on `/0` subnets (`0.0.0.0/0` only matches IPv4; `::/0` only matches IPv6).
   - Equivalence for compressed and expanded IPv6 addresses via binary `inet_pton` comparisons.
   - Fails closed on malformed IPs, non-numeric masks, negative masks, double-slash masks, oversized masks (`> 32` for IPv4, `> 128` for IPv6), protocol family mismatches, and numeric-list array misconfigurations.

5. **Forwarded Values Handling:**
   - `X-Forwarded-Proto` requires exact `'https'`.
   - `Front-End-Https` fails closed on `''`, `'0'`, `'off'`, and `'OFF'`, accepting truthy values like `'on'` and `'1'`.

---

## 3. Actual Code Diff & Compatibility Analysis

### Modified Files:
- `system/HTTP/IncomingRequest.php`:
  - `isSecure()` updated with proxy gate and sanitized forwarded header handling.
  - Net diff: 15 lines.
- `system/HTTP/RequestTrait.php`:
  - Added `protected function isFromTrustedProxy(): bool` and `private function checkIPAgainstProxy(string $ip, string $proxyIP): bool`.
  - Net diff: 90 lines.
  - `getIPAddress()` left 100% untouched.

### API & ABI Compatibility:
- Method signatures and visibility are completely unchanged:
  - `IncomingRequest::isSecure(): bool` (public).
  - Helper methods added to trait as `protected` (`isFromTrustedProxy`) and `private` (`checkIPAgainstProxy`).
- No dependencies on new packages, services, or PHP extensions beyond standard `inet_pton` and `ctype_digit`.
- Zero database or configuration schema changes.
- Syntax lint verified with `php.exe -n -l`: 0 errors.

---

## 4. Verification & Test Execution Status

### Standalone Security Regression Suite:
- **Location:** `scripts/verify_is_secure_backport.php`
- **Execution Mechanism:** Minimal independent PSR-4 autoloader for required `system/HTTP` classes; Reflection without constructor; zero CodeIgniter boot; zero DB access; zero network calls.
- **Scope of 48 Passing Test Cases:**
  1. Native HTTPS handling with legacy nonempty semantics (`on`, `1`, `off`, `""`).
  2. Untrusted client spoofing `X-Forwarded-Proto` and `Front-End-Https` (fail closed).
  3. Absent proxy configuration (`proxyIPs = []` and `proxyIPs = null`).
  4. Exact IPv4 and IPv6 proxy matches and mismatches.
  5. IPv4 CIDR matches (`/16`, `/24`, `/32`) and partial-byte `/25` boundary tests (`.127` match, `.128` nonmatch).
  6. IPv6 CIDR matches (`/48`), partial-byte `/65` boundary tests (`7fff::1` match, `8000::1` nonmatch), and compressed/expanded equivalence.
  7. IPv4 `/0` vs IPv6 `/0` address family restriction.
  8. Missing or null cached `REMOTE_ADDR` and proof of zero fallback to `$_SERVER` (with `try/finally` restoration).
  9. Malformed input fail-closed checks: negative masks, double slashes, nondigit masks, oversized masks (`/33`, `/129`), protocol mismatches, malformed IP strings, and numeric-list array misconfigurations.
  10. Forwarded header values: `http`, `off`, `OFF`, `""`, `0` rejected; `on`, `1` accepted.
  11. Resistance to `X-Forwarded-For` spoofing targeting proxy IPs.

- **Status:** **48 / 48 tests PASSING**.

### Existing Suite Status:
- Existing PHPUnit test suites were **NOT executed** prior to Codex review as instructed ("Existing tests may be unit PHPUnit but do not execute until Codex review. No Actions/broadtests. Scheduling stays OFF.").

---

## 5. Deployment Risk & Production Compatibility Notice

> [!WARNING]
> **Deployment Risk: Hostinger Edge HTTPS / Proxy Settings & Redirect Loops**
>
> If this patch is deployed to an environment behind a reverse proxy, CDN, or edge load balancer (such as Cloudflare or Hostinger's edge proxies) that terminates TLS and forwards requests to PHP over plain HTTP:
> 1. If `Config\App::$proxyIPs` does **NOT** contain the IP/CIDR of the edge proxy, `IncomingRequest::isSecure()` will return `false`.
> 2. If the application has HTTPS enforcement active (`AppApiHttps` filter returning `426 Upgrade Required`, `forceGlobalSecureRequests`, or `.htaccess` / framework redirect rules), users will experience **infinite redirect loops** or **API 426 rejection**.
> 3. Therefore, operations must independently certify that either:
>    - The web server sets native HTTPS (e.g. Apache `SetEnv HTTPS on` or Nginx `fastcgi_param HTTPS on;`), OR
>    - `Config\App::$proxyIPs` is properly configured with the upstream edge/reverse proxy IP/CIDR.

> [!IMPORTANT]
> **Advisory Scope & Non-Releasability Notice:**
> This narrow backport addresses **only** GHSA-7wmf-pw8j-mc78 / CVE-2026-63220. It does **not** close other CodeIgniter framework advisories affecting versions prior to 4.7.x. Consequently, this standalone patch cannot be treated as a comprehensive framework security clearance and is **not releasable alone**.
