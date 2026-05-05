import { cn } from "@/lib/utils";

type Variant = "active" | "inactive" | "pending" | "alert" | "info" | "planned";

const styles: Record<Variant, string> = {
  active: "bg-success/10 text-success border-success/20",
  inactive: "bg-muted text-muted-foreground border-border",
  pending: "bg-warning/10 text-warning border-warning/20",
  alert: "bg-destructive/10 text-destructive border-destructive/20",
  info: "bg-info/10 text-info border-info/20",
  planned: "bg-info/10 text-info border-info/20",
};

interface Props {
  variant?: Variant;
  children: React.ReactNode;
  className?: string;
}

export function StatusBadge({ variant = "inactive", children, className }: Props) {
  return (
    <span
      className={cn(
        "inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium",
        styles[variant],
        className
      )}
    >
      {children}
    </span>
  );
}
