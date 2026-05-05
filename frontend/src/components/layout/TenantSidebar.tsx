import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  useSidebar,
} from "@/components/ui/sidebar";
import { NavLink, useLocation } from "react-router-dom";
import {
  Activity,
  Boxes,
  Calendar,
  ClipboardList,
  FileText,
  LayoutDashboard,
  Settings,
  Stethoscope,
  Users,
  UserCog,
  Wallet,
  Pill,
  Receipt,
  ArrowRightLeft,
  AlertTriangle,
  ListChecks,
  ScrollText,
  PackageSearch,
  Building2,
  type LucideIcon,
} from "lucide-react";
import { useT } from "@/app/bootstrap/useT";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/ui/collapsible";
import { ChevronDown } from "lucide-react";
import { useState } from "react";

interface LeafItem {
  type: "leaf";
  to: string;
  labelKey: string;
  icon: LucideIcon;
}
interface GroupItem {
  type: "group";
  labelKey: string;
  icon: LucideIcon;
  items: { to: string; labelKey: string }[];
}
type Item = LeafItem | GroupItem;

const items: Item[] = [
  { type: "leaf", to: "/app/dashboard", labelKey: "nav.dashboard", icon: LayoutDashboard },
  { type: "leaf", to: "/app/patients", labelKey: "nav.patients", icon: Users },
  { type: "leaf", to: "/app/appointments", labelKey: "nav.appointments", icon: Calendar },
  {
    type: "group",
    labelKey: "nav.clinical",
    icon: Stethoscope,
    items: [
      { to: "/app/clinical/examinations", labelKey: "nav.examinations" },
      { to: "/app/clinical/odontogram", labelKey: "nav.odontogram" },
      { to: "/app/clinical/treatments", labelKey: "nav.treatments" },
      { to: "/app/clinical/contracts", labelKey: "nav.contracts" },
      { to: "/app/clinical/prescriptions", labelKey: "nav.prescriptions" },
    ],
  },
  {
    type: "group",
    labelKey: "nav.finance",
    icon: Wallet,
    items: [
      { to: "/app/finance/transactions", labelKey: "nav.transactions" },
      { to: "/app/finance/reports", labelKey: "nav.reports" },
      { to: "/app/finance/supplier-bills", labelKey: "nav.supplier_bills" },
      { to: "/app/finance/provider-payments", labelKey: "nav.provider_payments" },
      { to: "/app/finance/treasury-transfers", labelKey: "nav.treasury_transfers" },
    ],
  },
  {
    type: "group",
    labelKey: "nav.inventory",
    icon: Boxes,
    items: [
      { to: "/app/inventory/items", labelKey: "nav.items" },
      { to: "/app/inventory/usage", labelKey: "nav.usage" },
      { to: "/app/inventory/movements", labelKey: "nav.movements" },
      { to: "/app/inventory/low-stock", labelKey: "nav.low_stock" },
      { to: "/app/inventory/reorder", labelKey: "nav.reorder" },
    ],
  },
  { type: "leaf", to: "/app/providers", labelKey: "nav.providers", icon: UserCog },
  { type: "leaf", to: "/app/users-roles", labelKey: "nav.users_roles", icon: Users },
  { type: "leaf", to: "/app/settings", labelKey: "nav.settings", icon: Settings },
  { type: "leaf", to: "/app/activity-log", labelKey: "nav.activity_log", icon: Activity },
];

// Used to silence unused import warnings (icons are referenced semantically)
void [
  ClipboardList,
  FileText,
  Pill,
  Receipt,
  ArrowRightLeft,
  AlertTriangle,
  ListChecks,
  ScrollText,
  PackageSearch,
];

export function TenantSidebar() {
  const t = useT();
  const { state } = useSidebar();
  const collapsed = state === "collapsed";
  const { pathname } = useLocation();

  return (
    <Sidebar collapsible="icon" className="border-e">
      <SidebarHeader className="border-b">
        <div className="flex items-center gap-2 px-2 py-1.5">
          <div className="flex h-7 w-7 items-center justify-center rounded-md bg-primary text-primary-foreground">
            <Building2 className="h-4 w-4" />
          </div>
          {!collapsed && (
            <span className="text-sm font-semibold tracking-tight">DentaCare</span>
          )}
        </div>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupContent>
            <SidebarMenu>
              {items.map((item) =>
                item.type === "leaf" ? (
                  <SidebarMenuItem key={item.to}>
                    <SidebarMenuButton asChild isActive={pathname === item.to} tooltip={t(item.labelKey)}>
                      <NavLink to={item.to} className="flex items-center gap-2">
                        <item.icon className="h-4 w-4" />
                        <span>{t(item.labelKey)}</span>
                      </NavLink>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                ) : (
                  <SidebarGroupCollapsible key={item.labelKey} item={item} pathname={pathname} t={t} collapsed={collapsed} />
                )
              )}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
    </Sidebar>
  );
}

function SidebarGroupCollapsible({
  item,
  pathname,
  t,
  collapsed,
}: {
  item: GroupItem;
  pathname: string;
  t: (k: string) => string;
  collapsed: boolean;
}) {
  const isActive = item.items.some((i) => pathname.startsWith(i.to));
  const [open, setOpen] = useState(isActive);

  if (collapsed) {
    return (
      <SidebarMenuItem>
        <SidebarMenuButton tooltip={t(item.labelKey)}>
          <item.icon className="h-4 w-4" />
        </SidebarMenuButton>
      </SidebarMenuItem>
    );
  }

  return (
    <Collapsible open={open} onOpenChange={setOpen}>
      <SidebarMenuItem>
        <CollapsibleTrigger asChild>
          <SidebarMenuButton isActive={isActive}>
            <item.icon className="h-4 w-4" />
            <span className="flex-1 text-start">{t(item.labelKey)}</span>
            <ChevronDown className={`h-3.5 w-3.5 transition-transform ${open ? "rotate-180" : ""}`} />
          </SidebarMenuButton>
        </CollapsibleTrigger>
        <CollapsibleContent>
          <SidebarMenuSub>
            {item.items.map((sub) => (
              <SidebarMenuSubItem key={sub.to}>
                <SidebarMenuSubButton asChild isActive={pathname === sub.to}>
                  <NavLink to={sub.to}>{t(sub.labelKey)}</NavLink>
                </SidebarMenuSubButton>
              </SidebarMenuSubItem>
            ))}
          </SidebarMenuSub>
        </CollapsibleContent>
      </SidebarMenuItem>
    </Collapsible>
  );
}

// Re-export for convenience
export { SidebarGroupLabel };
