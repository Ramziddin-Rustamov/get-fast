"use client";

import { useCallback, useState } from "react";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money, dateTime, fullName, phone as fmtPhone } from "@/lib/format";
import type { Paginated, Payment } from "@/lib/types";
import { PageHeader, Card } from "@/components/ui";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { Icon } from "@/components/icons";

export default function PaymentsPage() {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.payments.list(page), [page]);
  const { data, loading, error, refetch } = useQuery<Paginated<Payment>>(fetcher, [page]);
  const rows = data?.data ?? [];

  return (
    <div>
      <PageHeader title={t("payments.title")} />
      <Card>
        <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Payments className="h-6 w-6" />}>
          <Table>
            <THead>
              <TH>{t("user.name")}</TH>
              <TH className="text-right">{t("common.amount")}</TH>
              <TH>{t("payments.method")}</TH>
              <TH>{t("payments.pay_id")}</TH>
              <TH>{t("user.status")}</TH>
              <TH>{t("user.joined")}</TH>
            </THead>
            <TBody>
              {rows.map((p) => (
                <TR key={p.id}>
                  <TD>
                    <div className="font-semibold">{fullName(p.user)}</div>
                    <div className="text-xs text-muted">{fmtPhone(p.user?.phone)}</div>
                  </TD>
                  <TD className="text-right font-bold">{money(p.amount)}</TD>
                  <TD className="text-muted">
                    {p.payment_method ?? "—"}
                    {p.card ? <span className="block text-xs">{p.card.number}</span> : null}
                  </TD>
                  <TD className="font-mono text-xs text-muted">{p.pay_id ?? "—"}</TD>
                  <TD>
                    <StatusBadge status={p.status} />
                  </TD>
                  <TD className="whitespace-nowrap text-muted">{dateTime(p.created_at, locale)}</TD>
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
