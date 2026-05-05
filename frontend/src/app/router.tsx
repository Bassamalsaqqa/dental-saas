import { Navigate, Route, Routes } from "react-router-dom";
import LoginPage from "@/features/auth/LoginPage";
import ClinicSelectPage from "@/features/clinic/ClinicSelectPage";
import { AppShell } from "@/components/layout/AppShell";
import { ControlPlaneShell } from "@/features/control-plane/ControlPlaneShell";
import { TenantGuard, ControlPlaneGuard } from "@/app/guards";
import DashboardPage from "@/features/dashboard/DashboardPage";
import PatientsPage from "@/features/patients/PatientsPage";
import PatientDetailPage from "@/features/patients/PatientDetailPage";
import ControlPlanePage from "@/features/control-plane/ControlPlanePage";
import { PlaceholderPage } from "@/components/layout/PlaceholderPage";
import NotFound from "@/pages/NotFound";

const tenantPlaceholders: { path: string; title: string }[] = [
  { path: "appointments", title: "Appointments" },
  { path: "clinical/examinations", title: "Examinations" },
  { path: "clinical/odontogram", title: "Odontogram" },
  { path: "clinical/treatments", title: "Treatments" },
  { path: "clinical/contracts", title: "Contracts" },
  { path: "clinical/prescriptions", title: "Prescriptions" },
  { path: "finance/transactions", title: "Transactions" },
  { path: "finance/reports", title: "Financial Reports" },
  { path: "finance/supplier-bills", title: "Supplier Bills" },
  { path: "finance/provider-payments", title: "Provider Payments" },
  { path: "finance/treasury-transfers", title: "Treasury Transfers" },
  { path: "inventory/items", title: "Inventory Items" },
  { path: "inventory/usage", title: "Inventory Usage" },
  { path: "inventory/movements", title: "Inventory Movements" },
  { path: "inventory/low-stock", title: "Low Stock" },
  { path: "inventory/reorder", title: "Reorder List" },
  { path: "providers", title: "Providers" },
  { path: "users-roles", title: "Users & Roles" },
  { path: "settings", title: "Settings" },
  { path: "activity-log", title: "Activity Log" },
];

const cpPlaceholders: { path: string; title: string }[] = [
  { path: "dashboard", title: "Control Plane Dashboard" },
  { path: "clinics", title: "Clinics" },
  { path: "plans", title: "Plans" },
  { path: "plan-requests", title: "Plan Requests" },
  { path: "invoices", title: "Invoices" },
  { path: "operations", title: "Operations" },
  { path: "settings", title: "Platform Settings" },
];

export function AppRouter() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/app/dashboard" replace />} />
      <Route path="/app/login" element={<LoginPage />} />
      <Route path="/app/clinic-select" element={<ClinicSelectPage />} />

      <Route
        path="/app"
        element={
          <TenantGuard>
            <AppShell />
          </TenantGuard>
        }
      >
        <Route index element={<Navigate to="dashboard" replace />} />
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="patients" element={<PatientsPage />} />
        <Route path="patients/:id" element={<PatientDetailPage />} />
        {tenantPlaceholders.map((p) => (
          <Route
            key={p.path}
            path={p.path}
            element={<PlaceholderPage title={p.title} />}
          />
        ))}
      </Route>

      <Route
        path="/app/controlplane"
        element={
          <ControlPlaneGuard>
            <ControlPlaneShell />
          </ControlPlaneGuard>
        }
      >
        <Route index element={<Navigate to="dashboard" replace />} />
        {cpPlaceholders.map((p) => (
          <Route key={p.path} path={p.path} element={<ControlPlanePage title={p.title} />} />
        ))}
      </Route>

      <Route path="*" element={<NotFound />} />
    </Routes>
  );
}
