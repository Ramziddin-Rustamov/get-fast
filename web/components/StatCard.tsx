"use client";

import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Skeleton } from "./ui";

type Tone = "brand" | "sky" | "amber" | "violet" | "rose" | "slate";

const TONES: Record<Tone, { ring: string; icon: string }> = {
  brand: { ring: "from-brand-500/15 to-brand-500/0", icon: "bg-brand-50 text-brand-600" },
  sky: { ring: "from-sky-500/15 to-sky-500/0", icon: "bg-sky-50 text-sky-600" },
  amber: { ring: "from-amber-500/15 to-amber-500/0", icon: "bg-amber-50 text-amber-600" },
  violet: { ring: "from-violet-500/15 to-violet-500/0", icon: "bg-violet-50 text-violet-600" },
  rose: { ring: "from-rose-500/15 to-rose-500/0", icon: "bg-rose-50 text-rose-600" },
  slate: { ring: "from-slate-500/15 to-slate-500/0", icon: "bg-slate-100 text-slate-600" },
};

export function StatCard({
  label,
  value,
  icon,
  tone = "brand",
  hint,
  loading,
}: {
  label: ReactNode;
  value: ReactNode;
  icon?: ReactNode;
  tone?: Tone;
  hint?: ReactNode;
  loading?: boolean;
}) {
  const t = TONES[tone];
  return (
    <div className="relative overflow-hidden rounded-card border border-line bg-surface p-5 shadow-card">
      <div
        className={cn("pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-gradient-to-br blur-2xl", t.ring)}
      />
      <div className="relative flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p className="truncate text-sm font-medium text-muted">{label}</p>
          {loading ? (
            <Skeleton className="mt-2 h-7 w-24" />
          ) : (
            <p className="mt-1 font-display text-2xl font-extrabold text-ink">{value}</p>
          )}
          {hint ? <p className="mt-1 text-xs text-muted">{hint}</p> : null}
        </div>
        {icon ? (
          <span className={cn("flex h-11 w-11 shrink-0 items-center justify-center rounded-xl", t.icon)}>
            {icon}
          </span>
        ) : null}
      </div>
    </div>
  );
}
