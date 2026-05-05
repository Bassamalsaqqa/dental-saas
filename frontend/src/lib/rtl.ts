import type { Direction } from "@/types";

export function applyDirection(dir: Direction) {
  if (typeof document === "undefined") return;
  document.documentElement.dir = dir;
  document.documentElement.lang = dir === "rtl" ? "ar" : "en";
}
