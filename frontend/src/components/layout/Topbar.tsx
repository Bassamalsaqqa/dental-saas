import { SidebarTrigger } from "@/components/ui/sidebar";
import { LanguageSwitcher } from "./LanguageSwitcher";
import { ClinicSwitcherDisplay } from "./ClinicSwitcherDisplay";
import { UserMenu } from "./UserMenu";

export function Topbar() {
  return (
    <header className="sticky top-0 z-30 flex h-14 items-center justify-between gap-3 border-b bg-card/80 px-3 backdrop-blur">
      <div className="flex items-center gap-2">
        <SidebarTrigger />
        <ClinicSwitcherDisplay />
      </div>
      <div className="flex items-center gap-2">
        <LanguageSwitcher />
        <UserMenu />
      </div>
    </header>
  );
}
