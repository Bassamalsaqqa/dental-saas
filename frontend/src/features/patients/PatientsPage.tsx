import { useNavigate } from "react-router-dom";
import { PageHeader } from "@/components/layout/PageHeader";
import { DataTable, type DataTableColumn } from "@/components/data-table/DataTable";
import { TableToolbar } from "@/components/data-table/TableToolbar";
import { StatusBadge } from "@/components/feedback/StatusBadge";
import { PatientAvatar } from "@/components/Avatars";
import { Button } from "@/components/ui/button";
import { Plus } from "lucide-react";
import { useState } from "react";

interface MockPatient {
  id: string;
  name: string;
  phone: string;
  last_visit: string;
  status: "active" | "inactive";
}

const mock: MockPatient[] = [
  { id: "p_1001", name: "Ahmed Mostafa", phone: "+20 100 000 1001", last_visit: "2026-04-12", status: "active" },
  { id: "p_1002", name: "Layla Ibrahim", phone: "+20 100 000 1002", last_visit: "2026-03-28", status: "active" },
  { id: "p_1003", name: "Omar Farouk", phone: "+20 100 000 1003", last_visit: "2025-12-04", status: "inactive" },
];

export default function PatientsPage() {
  const navigate = useNavigate();
  const [q, setQ] = useState("");

  const filtered = mock.filter((p) => p.name.toLowerCase().includes(q.toLowerCase()));

  const columns: DataTableColumn<MockPatient>[] = [
    {
      key: "name",
      header: "Patient",
      cell: (r) => (
        <div className="flex items-center gap-2">
          <PatientAvatar name={r.name} />
          <button className="font-medium hover:underline" onClick={() => navigate(`/app/patients/${r.id}`)}>
            {r.name}
          </button>
        </div>
      ),
    },
    { key: "phone", header: "Phone", cell: (r) => <span className="text-muted-foreground">{r.phone}</span> },
    { key: "last_visit", header: "Last visit", cell: (r) => r.last_visit },
    {
      key: "status",
      header: "Status",
      cell: (r) => <StatusBadge variant={r.status === "active" ? "active" : "inactive"}>{r.status}</StatusBadge>,
    },
  ];

  return (
    <div>
      <PageHeader
        title="Patients"
        description="Patients module will be wired in the next phase."
        actions={
          <Button size="sm" disabled>
            <Plus className="me-1 h-4 w-4" /> New patient
          </Button>
        }
      />
      <TableToolbar searchValue={q} onSearchChange={setQ} searchPlaceholder="Search patients..." />
      <DataTable
        columns={columns}
        data={filtered}
        rowKey={(r) => r.id}
        emptyTitle="No patients"
        emptyDescription="Mock data only — preview of the patients list."
      />
      <p className="mt-3 text-xs text-muted-foreground">
        ⚠ Mock data only. Real Patients module is not yet implemented.
      </p>
    </div>
  );
}
