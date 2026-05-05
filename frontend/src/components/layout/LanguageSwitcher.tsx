import { Languages } from "lucide-react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import type { Locale } from "@/types";

export function LanguageSwitcher() {
  const { locale, setLocale } = useBootstrap();
  const items: { value: Locale; label: string }[] = [
    { value: "en", label: "English" },
    { value: "ar", label: "العربية" },
  ];
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="gap-2">
          <Languages className="h-4 w-4" />
          <span className="text-xs font-medium uppercase">{locale}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {items.map((i) => (
          <DropdownMenuItem
            key={i.value}
            onClick={() => setLocale(i.value)}
            className={locale === i.value ? "bg-accent" : ""}
          >
            {i.label}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
