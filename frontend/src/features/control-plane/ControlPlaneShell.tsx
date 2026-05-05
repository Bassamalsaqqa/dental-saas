import { NavLink, Outlet, useLocation } from "react-router-dom";
import {
  ShieldCheck,
  LayoutDashboard,
  Building2,
  Layers,
  GitPullRequestArrow,
  Receipt,
  Wrench,
  Settings,
  ArrowLeft,
} from "lucide-react";
import { LanguageSwitcher } from "@/components/layout/LanguageSwitcher";
import { UserMenu } from "@/components/layout/UserMenu";
import { cn } from "@/lib/utils";

const items = [
  { to: "/app/controlplane/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { to: "/app/controlplane/clinics", label: "Clinics", icon: Building2 },
  { to: "/app/controlplane/plans", label: "Plans", icon: Layers },
  { to: "/app/controlplane/plan-requests", label: "Plan Requests", icon: GitPullRequestArrow },
  { to: "/app/controlplane/invoices", label: "Invoices", icon: Receipt },
  { to: "/app/controlplane/operations", label: "Operations", icon: Wrench },
  { to: "/app/controlplane/settings", label: "Settings", icon: Settings },
];

export function ControlPlaneShell() {
  const { pathname } = useLocation();

  return (
    <div className="flex min-h-screen w-full bg-controlplane text-controlplane-foreground">
      <aside className="hidden w-60 flex-col border-e border-controlplane-border bg-controlplane md:flex">
        <div className="flex h-14 items-center gap-2 border-b border-controlplane-border px-4">
          <ShieldCheck className="h-5 w-5 text-primary" />
          <div className="leading-tight">
            <p className="text-sm font-semibold">Control Plane</p>
            <p className="text-[10px] uppercase tracking-wider opacity-60">Platform Admin</p>
          </div>
        </div>
        <nav className="flex-1 space-y-0.5 p-2">
          {items.map((item) => {
            const active = pathname === item.to;
            return (
              <NavLink
                key={item.to}
                to={item.to}
                className={cn(
                  "flex items-center gap-2 rounded-md px-2.5 py-2 text-sm transition-colors",
                  active
                    ? "bg-primary text-primary-foreground"
                    : "text-controlplane-foreground/80 hover:bg-controlplane-accent hover:text-controlplane-foreground"
                )}
              >
                <item.icon className="h-4 w-4" />
                <span>{item.label}</span>
              </NavLink>
            );
          })}
        </nav>
        <div className="border-t border-controlplane-border p-2">
          <NavLink
            to="/app/dashboard"
            className="flex items-center gap-2 rounded-md px-2.5 py-2 text-xs text-controlplane-foreground/70 hover:bg-controlplane-accent"
          >
            <ArrowLeft className="h-3.5 w-3.5 rtl-flip" />
            Back to tenant app
          </NavLink>
        </div>
      </aside>

      <div className="flex flex-1 flex-col">
        <header className="flex h-14 items-center justify-between border-b border-controlplane-border px-4">
          <div className="text-sm font-medium opacity-80">Platform Administration</div>
          <div className="flex items-center gap-2">
            <LanguageSwitcher />
            <UserMenu />
          </div>
        </header>
        <main className="flex-1 px-4 py-5 md:px-6 md:py-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
