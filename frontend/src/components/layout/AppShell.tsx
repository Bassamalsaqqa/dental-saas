import { Outlet } from "react-router-dom";
import { SidebarProvider } from "@/components/ui/sidebar";
import { TenantSidebar } from "./TenantSidebar";
import { Topbar } from "./Topbar";

export function AppShell() {
  return (
    <SidebarProvider>
      <div className="flex min-h-screen w-full bg-background">
        <TenantSidebar />
        <div className="flex flex-1 flex-col">
          <Topbar />
          <main className="flex-1 px-4 py-5 md:px-6 md:py-6">
            <Outlet />
          </main>
        </div>
      </div>
    </SidebarProvider>
  );
}
