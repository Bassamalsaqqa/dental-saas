# P5-CON-02 Verification

## Scope
- Restore contract odontogram parity with the main odontogram (hover/select/tint/numbering) on the contract create/edit screen.
- Sync contract conditions table and treatment items with tooth updates.
- Print/show only non-empty optional contract fields.

## Commands & Evidence

### PHP lint: create view
Command:
php -l app\Views\contracts\create.php

Output:
No syntax errors detected in app\Views\contracts\create.php

### PHP lint: show view
Command:
php -l app\Views\contracts\show.php

Output:
No syntax errors detected in app\Views\contracts\show.php

### PHP lint: print view
Command:
php -l app\Views\contracts\print.php

Output:
No syntax errors detected in app\Views\contracts\print.php

## Notes
- Guardrail script execution still requires Bash access on this host (E_ACCESSDENIED). Manual inspection performed for DOM sinks in the updated views.
