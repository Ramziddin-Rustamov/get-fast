"use client";

import { Badge, type BadgeTone } from "./ui";
import { useI18n } from "@/lib/i18n";

const TONE_MAP: Record<string, BadgeTone> = {
  // driver / verification
  none: "neutral",
  pending: "warning",
  approved: "success",
  rejected: "danger",
  blocked: "danger",
  // trips
  active: "brand",
  completed: "success",
  cancelled: "danger",
  expired: "neutral",
  full: "info",
  // bookings
  confirmed: "success",
  // support
  answered: "success",
  closed: "neutral",
  // withdraw / generic
  success: "success",
  failed: "danger",
  // transactions
  incoming: "success",
  outgoing: "danger",
  credit: "success",
  debit: "danger",
};

/** Renders a localized colored badge for any domain status string. */
export function StatusBadge({ status }: { status: string | null | undefined }) {
  const { t } = useI18n();
  if (!status) return <Badge tone="neutral">—</Badge>;
  const tone = TONE_MAP[status] ?? "neutral";
  // Falls back to the raw status if no translation key exists.
  const label = t(`status.${status}`);
  return <Badge tone={tone}>{label === `status.${status}` ? status : label}</Badge>;
}

export function VerifiedBadge({ verified }: { verified: boolean }) {
  const { t } = useI18n();
  return (
    <Badge tone={verified ? "success" : "neutral"}>
      {verified ? t("user.verified") : t("user.unverified")}
    </Badge>
  );
}
