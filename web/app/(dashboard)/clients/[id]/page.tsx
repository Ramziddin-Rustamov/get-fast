"use client";

import { useCallback, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery, useMutation } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { money, dateTime, fullName } from "@/lib/format";
import type { BookingListItem, Paginated, UserDetail } from "@/lib/types";
import { Button, Card } from "@/components/ui";
import { FullScreenLoader, Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { Tabs } from "@/components/Tabs";
import { StatusBadge } from "@/components/status";
import { UserDetailHeader } from "@/components/UserDetailHeader";
import { UserTransactionsTab } from "@/components/UserTransactionsTab";
import { Icon } from "@/components/icons";

type TabKey = "bookings" | "transactions";

export default function ClientDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { t } = useI18n();
  const router = useRouter();
  const [tab, setTab] = useState<TabKey>("bookings");

  const fetcher = useCallback(() => endpoints.clients.show(id), [id]);
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
        <Button variant="ghost" size="sm" onClick={() => router.push("/clients")} icon={<Icon.Chevron className="h-4 w-4 rotate-180" />}>
          {t("nav.clients")}
        </Button>
      </div>

      <UserDetailHeader
        user={user}
        resource={endpoints.clients}
        baseRoute="/clients"
        showStatus={false}
        showVerified
        onChanged={refetch}
        extraActions={!user.is_verified ? <VerifyButton id={id} onDone={refetch} /> : null}
      />

      <Card>
        <Tabs
          active={tab}
          onChange={(k) => setTab(k as TabKey)}
          tabs={[
            { key: "bookings", label: t("clients.bookings") },
            { key: "transactions", label: t("nav.transactions") },
          ]}
        />
        {tab === "bookings" ? <BookingsTab id={id} /> : null}
        {tab === "transactions" ? <UserTransactionsTab id={id} source="clients" /> : null}
      </Card>
    </div>
  );
}

function VerifyButton({ id, onDone }: { id: string; onDone: () => void }) {
  const { t } = useI18n();
  const toast = useToast();
  const { run, pending } = useMutation(() => endpoints.clients.verify(id));
  return (
    <Button
      variant="secondary"
      size="sm"
      loading={pending}
      icon={<Icon.Check className="h-4 w-4" />}
      onClick={async () => {
        try {
          await run();
          toast.success(t("toast.saved"));
          onDone();
        } catch (e) {
          toast.error(e instanceof Error ? e.message : t("common.error"));
        }
      }}
    >
      {t("action.mark_verified")}
    </Button>
  );
}

function BookingsTab({ id }: { id: string }) {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.clients.bookings(id, page), [id, page]);
  const { data, loading, error, refetch } = useQuery<Paginated<BookingListItem>>(fetcher, [id, page]);
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
          {rows.map((b) => (
            <TR key={b.id} onClick={() => (window.location.href = `/orders/${b.id}`)}>
              <TD>
                {b.trip ? (
                  <>
                    <span className="font-semibold">{b.trip.start_region}</span>
                    <span className="text-muted"> → {b.trip.end_region}</span>
                  </>
                ) : (
                  <span className="text-muted">{fullName(b.user)}</span>
                )}
              </TD>
              <TD>{b.seats_booked}</TD>
              <TD className="text-right font-semibold">{money(b.total_price)}</TD>
              <TD>
                <StatusBadge status={b.status} />
              </TD>
              <TD className="whitespace-nowrap text-muted">{dateTime(b.created_at, locale)}</TD>
            </TR>
          ))}
        </TBody>
      </Table>
      <Pagination current={data?.meta.current_page ?? 1} last={data?.meta.last_page ?? 1} total={data?.meta.total} onChange={setPage} />
    </QueryState>
  );
}
