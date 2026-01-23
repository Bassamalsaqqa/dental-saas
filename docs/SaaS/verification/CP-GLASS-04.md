# CP-GLASS-04 Verification

## Scope
Add clinic UUID/domain support and extend Control Plane global search to include them.

## Evidence

### Clinic UUID/domain schema + model + onboarding + search
Command:
`rg -n "uuid|domain" app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php app\\Models\\ClinicModel.php app\\Services\\OnboardingService.php app\\Controllers\\ControlPlane\\Search.php app\\Views\\layouts\\main_control_plane.php`

Output:
```
app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php:14:            'uuid' => [
app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php:20:            'domain' => [
app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php:26:        $this->db->query('CREATE UNIQUE INDEX clinics_uuid_unique ON clinics (uuid)');
app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php:27:        $this->db->query('CREATE UNIQUE INDEX clinics_domain_unique ON clinics (domain)');
app\\Database\\Migrations\\2026-01-23-220000_AddClinicUuidAndDomain.php:38:                ->update(['uuid' => $this->generateUuid()]);
app\\Models\\ClinicModel.php:12:    protected $allowedFields = ['name', 'slug', 'uuid', 'domain', 'status', 'created_at', 'updated_at'];
app\\Services\\OnboardingService.php:56:                'uuid' => $this->generateUuid(),
app\\Controllers\\ControlPlane\\Search.php:40:            ->orLike('uuid', $query)
app\\Controllers\\ControlPlane\\Search.php:41:            ->orLike('domain', $query)
app\\Views\\layouts\\main_control_plane.php:338:                    if (!detail && clinic.uuid) {
```
