import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@/components/ui/sheet";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import type { ReactNode } from "react";

interface Props {
  open: boolean;
  onOpenChange: (v: boolean) => void;
  title: string;
  description?: string;
  children?: ReactNode;
  side?: "left" | "right";
}

/** Drawer that mirrors side based on RTL. */
export function Drawer({ open, onOpenChange, title, description, children, side }: Props) {
  const { direction } = useBootstrap();
  const resolved = side ?? "right";
  const flipped = direction === "rtl"
    ? resolved === "right" ? "left" : "right"
    : resolved;

  return (
    <Sheet open={open} onOpenChange={onOpenChange}>
      <SheetContent side={flipped} className="w-full sm:max-w-md">
        <SheetHeader>
          <SheetTitle>{title}</SheetTitle>
          {description && <SheetDescription>{description}</SheetDescription>}
        </SheetHeader>
        <div className="mt-4">{children}</div>
      </SheetContent>
    </Sheet>
  );
}
