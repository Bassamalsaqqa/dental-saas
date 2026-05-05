import { cn } from "@/lib/utils";
import { formatMoney } from "@/lib/money";
import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";

interface Props {
  amount: number;
  currency?: string;
  tone?: "neutral" | "positive" | "negative";
  className?: string;
}

export function MoneyBadge({ amount, currency = "USD", tone = "neutral", className }: Props) {
  const { locale } = useBootstrap();
  const toneCls =
    tone === "positive"
      ? "text-success"
      : tone === "negative"
      ? "text-destructive"
      : "text-foreground";
  return (
    <span className={cn("font-mono text-sm tabular-nums", toneCls, className)}>
      {formatMoney(amount, currency, locale)}
    </span>
  );
}
