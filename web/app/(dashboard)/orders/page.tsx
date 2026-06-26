"use client";

import { useCallback, useState } from "react";
import { useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money, dateTime, fullName, phone as fmtPhone } from "@/lib/format";
import type { BookingListItem, Paginated } from "@/lib/types";
import { PageHeader, Card, Select } from "@/components/ui";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { Icon } from "@/components/icons";

export default function OrdersPage() {
  const { t, locale } = useI18n();
  const router = useRouter();
  const [status, setStatus] = useState("");
  const [date, setDate] = useState("");
  const [page, setPage] = useState(1);

  const fetcher = useCallback(
    () => endpoints.orders.list({ status: status || undefined, date: date || undefined, page }),
    [status, date, page],
  );
  const { data, loading, error, refetch } = useQuery<Paginated<BookingListItem>>(fetcher, [status, date, page]);
  const rows = data?.data ?? [];

  return (
    <div>
      <PageHeader title={t("orders.title")} />
      <Card>
        <div className="flex flex-col gap-3 border-b border-line p-4 sm:flex-row">
          <Select
            value={status}
            onChange={(e) => {
              setStatus(e.target.value);
              setPage(1);
            }}
            className="sm:max-w-48"
          >
            <option value="">{t("common.all")}</option>
            <option value="confirmed">{t("status.confirmed")}</option>
            <option value="completed">{t("status.completed")}</option>
            <option value="cancelled">{t("status.cancelled")}</option>
          </Select>
          <Select
            value={date}
            onChange={(e) => {
              setDate(e.target.value);
              setPage(1);
            }}
            className="sm:max-w-48"
          >
            <option value="">{t("orders.date_filter")}: {t("common.all")}</option>
            <option value="today">{t("orders.filter.today")}</option>
            <option value="week">{t("orders.filter.week")}</option>
            <option value="last_week">{t("orders.filter.last_week")}</option>
          </Select>
        </div>

        <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Orders className="h-6 w-6" />}>
          <Table>
            <THead>
              <TH>{t("orders.route")}</TH>
              <TH>{t("user.name")}</TH>
              <TH>{t("orders.seats")}</TH>
              <TH className="text-right">{t("orders.price")}</TH>
              <TH>{t("user.status")}</TH>
              <TH>{t("user.joined")}</TH>
            </THead>
            <TBody>
              {rows.map((b) => (
                <TR key={b.id} onClick={() => router.push(`/orders/${b.id}`)}>
                  <TD>
                    {b.trip ? (
                      <>
                        <span className="font-semibold">{b.trip.start_region}</span>
                        <span className="text-muted"> → {b.trip.end_region}</span>
                      </>
                    ) : (
                      "—"
                    )}
                  </TD>
                  <TD>
                    <div className="font-semibold">{fullName(b.user)}</div>
                    <div className="text-xs text-muted">{fmtPhone(b.user?.phone)}</div>
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
      </Card>
    </div>
  );
}
