import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { cn } from "@/lib/utils";

function initials(name: string) {
  return name
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((n) => n[0]?.toUpperCase())
    .join("");
}

interface Props {
  name: string;
  className?: string;
}

export function PatientAvatar({ name, className }: Props) {
  return (
    <Avatar className={cn("h-8 w-8", className)}>
      <AvatarFallback className="bg-info/10 text-info text-xs font-medium">
        {initials(name)}
      </AvatarFallback>
    </Avatar>
  );
}

export function ProviderAvatar({ name, className }: Props) {
  return (
    <Avatar className={cn("h-8 w-8", className)}>
      <AvatarFallback className="bg-primary/10 text-primary text-xs font-medium">
        {initials(name)}
      </AvatarFallback>
    </Avatar>
  );
}
