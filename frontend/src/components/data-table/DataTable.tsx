import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { LoadingState } from "@/components/feedback/LoadingState";
import { EmptyState } from "@/components/feedback/EmptyState";
import { cn } from "@/lib/utils";
import type { ReactNode } from "react";

export interface DataTableColumn<T> {
  key: string;
  header: ReactNode;
  cell: (row: T) => ReactNode;
  className?: string;
  align?: "start" | "end" | "center";
}

interface Props<T> {
  columns: DataTableColumn<T>[];
  data: T[];
  loading?: boolean;
  emptyTitle?: string;
  emptyDescription?: string;
  rowKey: (row: T) => string;
  className?: string;
}

export function DataTable<T>({
  columns,
  data,
  loading,
  emptyTitle,
  emptyDescription,
  rowKey,
  className,
}: Props<T>) {
  if (loading) return <LoadingState />;
  if (!data.length) return <EmptyState title={emptyTitle} description={emptyDescription} />;

  return (
    <div className={cn("rounded-lg border bg-card", className)}>
      <Table>
        <TableHeader>
          <TableRow className="hover:bg-transparent">
            {columns.map((c) => (
              <TableHead
                key={c.key}
                className={cn(
                  "text-xs font-semibold uppercase tracking-wide text-muted-foreground",
                  c.align === "end" && "text-end",
                  c.align === "center" && "text-center",
                  c.className
                )}
              >
                {c.header}
              </TableHead>
            ))}
          </TableRow>
        </TableHeader>
        <TableBody>
          {data.map((row) => (
            <TableRow key={rowKey(row)}>
              {columns.map((c) => (
                <TableCell
                  key={c.key}
                  className={cn(
                    "text-sm",
                    c.align === "end" && "text-end",
                    c.align === "center" && "text-center",
                    c.className
                  )}
                >
                  {c.cell(row)}
                </TableCell>
              ))}
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <div className="flex items-center justify-between border-t px-4 py-2 text-xs text-muted-foreground">
        <span>{data.length} rows</span>
        <span className="opacity-60">Pagination wired in next phase</span>
      </div>
    </div>
  );
}
