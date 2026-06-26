"use client";

import { useCallback, useState } from "react";
import { useParams } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money, dateTime } from "@/lib/format";
import type { Paginated, Trip, UserDetail, Vehicle } from "@/lib/types";
import { Button, Card } from "@/components/ui";
import { FullScreenLoader, Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { Tabs } from "@/components/Tabs";
import { StatusBadge } from "@/components/status";
import { UserDetailHeader } from "@/components/UserDetailHeader";
import { UserTransactionsTab } from "@/components/UserTransactionsTab";
import { Icon } from "@/components/icons";
import { useRouter } from "next/navigation";

type TabKey = "vehicles" | "trips" | "transactions" | "documents";

export default function DriverDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { t } = useI18n();
  const router = useRouter();
  const [tab, setTab] = useState<TabKey>("vehicles");

  const fetcher = useCallback(() => endpoints.drivers.show(id), [id]);
  const { data: user, loading, error, refetch } = useQuery<UserDetail>(fetcher, [id]);

  if (loading) return <FullScreenLoader />;
  if (error || !user) {
    return (
      <Card>
        <QueryState loading={false} error={error} onRetry={refetch}>
          <div />
        </QueryState>
      </Card>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center">
        <Button variant="ghost" size="sm" onClick={() => router.push("/drivers")} icon={<Icon.Chevron className="h-4 w-4 rotate-180" />}>
          {t("nav.drivers")}
        </Button>
      </div>

      <UserDetailHeader user={user} resource={endpoints.drivers} baseRoute="/drivers" showStatus onChanged={refetch} />

      <Card>
        <Tabs
          active={tab}
          onChange={(k) => setTab(k as TabKey)}
          tabs={[
            { key: "vehicles", label: t("drivers.vehicles") },
            { key: "trips", label: t("drivers.trips") },
            { key: "transactions", label: t("nav.transactions") },
            { key: "documents", label: t("drivers.documents") },
          ]}
        />
        {tab === "vehicles" ? <VehiclesTab id={id} /> : null}
        {tab === "trips" ? <TripsTab id={id} /> : null}
        {tab === "transactions" ? <UserTransactionsTab id={id} source="drivers" /> : null}
        {tab === "documents" ? <DocumentsTab id={id} /> : null}
      </Card>
    </div>
  );
}

function VehiclesTab({ id }: { id: string }) {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.drivers.vehicles(id, page), [id, page]);
  const { data, loading, error, refetch } = useQuery<Paginated<Vehicle>>(fetcher, [id, page]);
  const rows = data?.data ?? [];

  return (
    <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Car className="h-6 w-6" />}>
      <Table>
        <THead>
          <TH>{t("drivers.vehicles")}</TH>
          <TH>{t("orders.seats")}</TH>
          <TH>{t("user.joined")}</TH>
        </THead>
        <TBody>
          {rows.map((v) => (
            <TR key={v.id}>
              <TD>
                <div className="flex items-center gap-3">
                  <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <Icon.Car className="h-5 w-5" />
                  </span>
                  <div>
                    <p className="font-semibold">{v.model}</p>
                    <p className="text-xs text-muted">
                      {v.car_number}
                      {v.color ? ` · ${v.color.title_uz ?? v.color.code}` : ""}
                    </p>
                  </div>
                </div>
              </TD>
              <TD>{v.seats}</TD>
              <TD className="text-muted">{dateTime(v.created_at, locale)}</TD>
            </TR>
          ))}
        </TBody>
      </Table>
      <Pagination current={data?.meta.current_page ?? 1} last={data?.meta.last_page ?? 1} total={data?.meta.total} onChange={setPage} />
    </QueryState>
  );
}

function TripsTab({ id }: { id: string }) {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.drivers.trips(id, page), [id, page]);
  const { data, loading, error, refetch } = useQuery<Paginated<Trip>>(fetcher, [id, page]);
  const rows = data?.data ?? [];

  return (
    <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Orders className="h-6 w-6" />}>
      <Table>
        <THead>
          <TH>{t("orders.route")}</TH>
          <TH>{t("orders.seats")}</TH>
          <TH className="text-right">{t("orders.price")}</TH>
          <TH>{t("user.status")}</TH>
          <TH>{t("user.joined")}</TH>
        </THead>
        <TBody>
          {rows.map((trip) => (
            <TR key={trip.id}>
              <TD>
                <span className="font-semibold">{trip.start_region}</span>
                <span className="text-muted"> → {trip.end_region}</span>
              </TD>
              <TD>
                {trip.available_seats}/{trip.total_seats}
              </TD>
              <TD className="text-right font-semibold">{money(trip.price_per_seat)}</TD>
              <TD>
                <StatusBadge status={trip.status} />
              </TD>
              <TD className="whitespace-nowrap text-muted">{dateTime(trip.start_time, locale)}</TD>
            </TR>
          ))}
        </TBody>
      </Table>
      <Pagination current={data?.meta.current_page ?? 1} last={data?.meta.last_page ?? 1} total={data?.meta.total} onChange={setPage} />
    </QueryState>
  );
}

function DocumentsTab({ id }: { id: string }) {
  const { t } = useI18n();
  const fetcher = useCallback(() => endpoints.drivers.documents(id), [id]);
  const { data, loading, error, refetch } = useQuery(fetcher, [id]);
  const images = data?.images ?? [];

  return (
    <QueryState loading={loading} error={error} isEmpty={images.length === 0} onRetry={refetch} emptyIcon={<Icon.Doc className="h-6 w-6" />}>
      <div className="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 lg:grid-cols-4">
        {images.map((img) => (
          <a
            key={img.id}
            href={img.url}
            target="_blank"
            rel="noreferrer"
            className="group overflow-hidden rounded-xl border border-line bg-canvas"
          >
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={img.url} alt={img.type} className="aspect-video w-full object-cover transition group-hover:scale-105" />
            <p className="px-3 py-2 text-xs font-semibold text-muted">{img.type || t("drivers.documents")}</p>
          </a>
        ))}
      </div>
    </QueryState>
  );
}
