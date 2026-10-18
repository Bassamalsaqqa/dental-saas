# Framework security upgrade: CodeIgniter 4.7.5

Baseline: production release `97cd12c67b3f426997d5f80f769031fa35a9f0c0`.

The bundled `system/` runtime is upgraded from 4.6.3 to the official CodeIgniter 4.7.5 distribution. Provenance: tag `v4.7.5`, commit `36256090764badf4f9862378c55fcfa850405fe1`; official codeload ZIP SHA256 `ed6bbad62cb166d2f721171e3a84db0091b84e16d99708735ade4bb3c89820eb`. All 653 upstream system files match after CRLF/LF normalization. Files with only line-ending differences retain baseline bytes. The unused baseline `system/Boot_temp.php` is retained, unchanged, as a documented legacy exception; application boot uses `system/Boot.php`. Protected `.htaccess` files retain baseline bytes.

Compatibility changes are limited to `Config\Format::$jsonEncodeDepth = 512`, unchanged official `Hostnames` and `WorkerMode` defaults, and a PHP minimum of 8.2 in Composer metadata and HTTP/CLI entry guards. Composer lock package versions are unchanged. Adding the WorkerMode defaults does not activate FrankenPHP workers; existing PHP request handling remains in place. Application services, tenant filters, configuration, database schema, migrations, WhatsApp fresh-client protection, and scheduling flags are unchanged.

Focused isolated command (refuses an application `.env`, creates only owned temporary fixtures, no database calls):

```
php scripts/verify_framework_475.php
```

Checks cover application route loading/Arabic JSON compatibility, renderer reserved data keys, Parser re-substitution, proxy HTTPS trust, valid JPG/JPEG/PNG/PDF upload rules, trailing-dot/embedded-PHP rejection, empty-first multiple-upload size enforcement, and filename traversal sanitization. These verify the actual bundled runtime, not Composer's vendor copy. Broader authenticated tenant workflows are certified separately against synthetic staging data.

Security applicability: upstream 4.7.5 includes the intervening framework fixes for renderer data-key replacement, Parser re-substitution, upload handling, HTTPS proxy trust, query-builder batch escaping and CURL option/connection isolation. Do not treat this as proof that every application workflow is safe. In particular, arbitrary untrusted Parser conditionals require the upstream `restrictConditionals` opt-in; its compatibility default remains false and no application Parser callsites were found. The earlier WhatsApp fresh-client mitigation remains intact. Production currently runs 4.6.3 until an independently reviewed release is explicitly authorized.

No production changes, Composer package version updates, schema changes, migrations, customer-data rewrites, GitHub Actions, deployment or scheduling activation are part of this source correction. Runtime PHP 8.2+ and required extensions must be checked before any eventual deployment. Source deployment must preserve production `.env`, all `.htaccess` files, writable storage, uploads and historical data.