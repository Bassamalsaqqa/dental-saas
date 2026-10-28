# Product UX and theme campaign evidence

Development baseline29dce746; branch feature/studio-workflows-themes-20261028. No production operations. Clinical/financial/RBAC authority unchanged.

## Platform P0

17actual local guarded session/browser observations pass: ordinary manager controls absent/directPOSTdenied, genuine platformentry and explicitimpersonation,9EN/AR320/390/1366route observations, missingCSRFdenial/noGETexit/Bearerseparation, visibleexit with sessionregeneration, second-tab404and hiddenbanner, globaltenantsearch403. Original339syntheticbusinessrows and schemas preserved. See platform-p0.json.

Server-injected platform identity is derived by unchanged BaseController from PermissionService::isSuperAdmin each request. The view uses that canonical boolean and exactmatching active/impersonatedclinic IDs; no email/group/tenantrole inference. Existing POST/CSRF/sessionregeneration/audit endpoints remain authority. Shared security appearance is deliberately excluded from future clinic theme overrides and hidden on print. Legacy retained tenant layout reuses it; its old mobile-hidden control/directPOSTentry was replaced with the same gateway/exit semantics.

Private screenshot/runners remain outside source in sibling studio-ux-theme-20261028. Actual ARmobile security banner inspected. These are specific tests, not complete product/theme/security certification. A temporary assertion initially expected redirect after another-tabexit; actual unchanged TenantFilter intentionally returns404 at the same dashboardURL. The corrected expectation verifies actual404and absence of tenantbanner; original failed receipt preserved. No backend behavior was changed to satisfy tests.

Runtime tests: php tests/regression/StudioPlatformChromeStandalone.php; private p0-platform.cjs requires existing guarded loopback57321/3307 synthetic fixture and refuses otherDBidentity. No GitHubActions. Additional persona/role-revocation/accessibility scenarios remain campaign work.
