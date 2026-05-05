import { useBootstrap } from "@/app/bootstrap/BootstrapProvider";
import { t } from "@/lib/i18n";

/** Translation hook bound to current locale. */
export function useT() {
  const { locale } = useBootstrap();
  return (key: string) => t(locale, key);
}
