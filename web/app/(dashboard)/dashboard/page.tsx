"use client";

import { useCallback } from "react";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money } from "@/lib/format";
import { PageHeader, Card, CardHeader, CardBody, Skeleton } from "@/components/ui";
import { StatCard } from "@/components/StatCard";
import { QueryState } from "@/components/data";
import { Icon } from "@/components/icons";
import type { DashboardStats } from "@/lib/types";

export default function DashboardPage() {
  const { t } = useI18n();
  const fetcher = useCallback(() => endpoints.dashboard.stats(), []);
  const { data, loading, error, refetch } = useQuery<DashboardStats>(fetcher, []);

  return (
    <div>
      <PageHeader title={t("dashboard.title")} subtitle={t("dashboard.subtitle")} />

      {error && !data ? (
        <Card>
          <QueryState loading={false} error={error} onRetry={refetch}>
            <div />
          </QueryState>
        </Card>
      ) : (
        <>
          {/* Money row */}
          <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <StatCard
              label={t("dashboard.company_balance")}
              value={money(data?.company_balance ?? 0)}
              icon={<Icon.Wallet className="h-5 w-5" />}
              tone="brand"
              loading={loading}
            />
            <StatCard
              label={t("dashboard.total_income")}
              value={money(data?.total_income ?? 0)}
              icon={<Icon.Transactions className="h-5 w-5" />}
              tone="sky"
              loading={loading}
            />
            <StatCard
              label={t("dashboard.today_income")}
              value={money(data?.today_income ?? 0)}
              icon={<Icon.Payments className="h-5 w-5" />}
              tone="violet"
              loading={loading}
            />
          </div>

          {/* Counts row */}
          <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
              label={t("dashboard.bookings")}
              value={data?.total_bookings ?? 0}
              icon={<Icon.Orders className="h-5 w-5" />}
              tone="slate"
              hint={`${data?.confirmed_bookings ?? 0} ${t("dashboard.confirmed")} · ${
                data?.completed_bookings ?? 0
              } ${t("dashboard.completed")}`}
              loading={loading}
            />
            <StatCard
              label={t("dashboard.drivers")}
              value={data?.total_drivers ?? 0}
              icon={<Icon.Drivers className="h-5 w-5" />}
              tone="amber"
              hint={`${data?.drivers_approved ?? 0} ${t("status.approved")}`}
              loading={loading}
            />
            <StatCard
              label={t("dashboard.clients")}
              value={data?.total_clients ?? 0}
              icon={<Icon.Clients className="h-5 w-5" />}
              tone="sky"
              loading={loading}
            />
            <StatCard
              label={t("dashboard.cards")}
              value={data?.total_cards ?? 0}
              icon={<Icon.Payments className="h-5 w-5" />}
              tone="rose"
              hint={`${data?.total_transactions ?? 0} ${t("dashboard.transactions")}`}
              loading={loading}
            />
          </div>

          {/* Breakdown */}
          <div className="mt-4 grid gap-4 lg:grid-cols-2">
            <Card>
              <CardHeader title={t("dashboard.drivers_breakdown")} />
              <CardBody>
                {loading ? (
                  <Skeleton className="h-32 w-full" />
                ) : (
                  <Breakdown
                    rows={[
                      { label: t("status.approved"), value: data?.drivers_approved ?? 0, tone: "bg-emerald-500" },
                      { label: t("status.pending"), value: data?.drivers_pending ?? 0, tone: "bg-amber-500" },
                      { label: t("status.rejected"), value: data?.drivers_rejected ?? 0, tone: "bg-red-500" },
                      { label: t("status.blocked"), value: data?.drivers_blocked ?? 0, tone: "bg-slate-500" },
                    ]}
                  />
                )}
              </CardBody>
            </Card>
            <Card>
              <CardHeader title={t("user.status")} />
              <CardBody>
                {loading ? (
                  <Skeleton className="h-32 w-full" />
                ) : (
                  <Breakdown
                    rows={[
                      { label: t("dashboard.active_users"), value: data?.active_users ?? 0, tone: "bg-brand-500" },
                      { label: t("dashboard.inactive_users"), value: data?.inactive_users ?? 0, tone: "bg-slate-400" },
                      { label: t("dashboard.cancelled"), value: data?.cancelled_bookings ?? 0, tone: "bg-red-500" },
                    ]}
                  />
                )}
              </CardBody>
            </Card>
          </div>
        </>
      )}
    </div>
  );
}

function Breakdown({ rows }: { rows: { label: string; value: number; tone: string }[] }) {
  const max = Math.max(1, ...rows.map((r) => r.value));
  return (
    <div className="space-y-4">
      {rows.map((r) => (
        <div key={r.label}>
          <div className="mb-1 flex items-center justify-between text-sm">
            <span className="font-medium text-ink-soft">{r.label}</span>
            <span className="font-bold text-ink">{r.value}</span>
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-slate-100">
            <div
              className={`h-full rounded-full ${r.tone}`}
              style={{ width: `${(r.value / max) * 100}%` }}
            />
          </div>
        </div>
      ))}
    </div>
  );
}
