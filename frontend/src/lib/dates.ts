export function formatDate(d: Date | string, locale: string = "en"): string {
  const date = typeof d === "string" ? new Date(d) : d;
  return date.toLocaleDateString(locale === "ar" ? "ar-EG" : "en-GB", {
    year: "numeric",
    month: "short",
    day: "2-digit",
  });
}
