import { PageHeader } from "@/components/layout/PageHeader";
import { StatCard } from "@/components/feedback/StatCard";
import { Calendar, Users, Wallet, Activity } from "lucide-react";
import { Card } from "@/components/ui/card";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";

export default function DashboardPage() {
  const { active_clinic } = useBootstrap();
  return (
    <div>
      <PageHeader
        title="Dashboard"
        description={active_clinic ? `Overview for ${active_clinic.name}` : "Overview"}
      />
      <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        <StatCard label="Patients" value="—" hint="Mock metric" icon={<Users className="h-5 w-5" />} />
        <StatCard label="Today's appointments" value="—" hint="Mock metric" icon={<Calendar className="h-5 w-5" />} />
        <StatCard label="Open balance" value="—" hint="Mock metric" icon={<Wallet className="h-5 w-5" />} />
        <StatCard label="Activity" value="—" hint="Mock metric" icon={<Activity className="h-5 w-5" />} />
      </div>
      <Card className="mt-6 p-6">
        <h2 className="text-sm font-semibold">Welcome to DentaCare</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          This is a frontend shell prototype. Modules will be wired to the App API in upcoming phases.
        </p>
      </Card>
    </div>
  );
}
