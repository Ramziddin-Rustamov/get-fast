import type { AuthUser, Locale } from "./types";

/** Format an integer UZS amount, e.g. 1500000 -> "1 500 000 so'm". */
export function money(amount: number | null | undefined, withSuffix = true): string {
  if (amount === null || amount === undefined || Number.isNaN(amount)) return "—";
  const formatted = new Intl.NumberFormat("ru-RU").format(Math.round(amount));
  return withSuffix ? `${formatted} so'm` : formatted;
}

const DATE_LOCALES: Record<Locale, string> = {
  uz: "uz-UZ",
  ru: "ru-RU",
  en: "en-GB",
};

/** Format an ISO/SQL datetime string for display. */
export function dateTime(value: string | null | undefined, locale: Locale = "uz"): string {
  if (!value) return "—";
  const d = parseDate(value);
  if (!d) return value;
  return new Intl.DateTimeFormat(DATE_LOCALES[locale] ?? "en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(d);
}

export function dateOnly(value: string | null | undefined, locale: Locale = "uz"): string {
  if (!value) return "—";
  const d = parseDate(value);
  if (!d) return value;
  return new Intl.DateTimeFormat(DATE_LOCALES[locale] ?? "en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(d);
}

function parseDate(value: string): Date | null {
  // Accept both ISO ("2024-01-01T10:00:00Z") and SQL ("2024-01-01 10:00:00").
  const normalized = value.includes("T") ? value : value.replace(" ", "T");
  const d = new Date(normalized);
  return Number.isNaN(d.getTime()) ? null : d;
}

export function fullName(
  user: Pick<AuthUser, "first_name" | "last_name"> | null | undefined,
): string {
  if (!user) return "—";
  return [user.first_name, user.last_name].filter(Boolean).join(" ").trim() || "—";
}

export function initials(
  user: Pick<AuthUser, "first_name" | "last_name"> | null | undefined,
): string {
  if (!user) return "?";
  const a = user.first_name?.[0] ?? "";
  const b = user.last_name?.[0] ?? "";
  return (a + b).toUpperCase() || a.toUpperCase() || "?";
}

/** Format a phone like +998 90 123 45 67 (best-effort). */
export function phone(value: string | null | undefined): string {
  if (!value) return "—";
  const digits = value.replace(/\D/g, "");
  if (digits.length === 12 && digits.startsWith("998")) {
    return `+${digits.slice(0, 3)} ${digits.slice(3, 5)} ${digits.slice(5, 8)} ${digits.slice(
      8,
      10,
    )} ${digits.slice(10, 12)}`;
  }
  return value;
}
