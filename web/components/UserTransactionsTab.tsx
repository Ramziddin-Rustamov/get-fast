"use client";

import { useCallback, useState } from "react";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money, dateTime } from "@/lib/format";
import type { BalanceTransaction, Paginated } from "@/lib/types";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "./data";
import { StatusBadge } from "./status";
import { Icon } from "./icons";

/** Balance-transactions tab, shared by driver and client detail pages. */
export function UserTransactionsTab({
  id,
  source = "drivers",
}: {
  id: string;
  source?: "drivers" | "clients";
}) {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const resource = source === "drivers" ? endpoints.drivers : endpoints.clients;
  const fetcher = useCallback(() => resource.transactions(id, page), [resource, id, page]);
  const { data, loading, error, refetch } = useQuery<Paginated<BalanceTransaction>>(fetcher, [
    id,
    page,
    source,
  ]);
  const rows = data?.data ?? [];

  return (
    <QueryState
      loading={loading}
      error={error}
      isEmpty={rows.length === 0}
      onRetry={refetch}
      emptyIcon={<Icon.Transactions className="h-6 w-6" />}
    >
      <Table>
        <THead>
          <TH>{t("common.amount")}</TH>
          <TH>{t("transactions.balance_after")}</TH>
          <TH>{t("common.note")}</TH>
          <TH>{t("user.status")}</TH>
          <TH>{t("user.joined")}</TH>
        </THead>
        <TBody>
          {rows.map((tx) => (
            <TR key={tx.id}>
              <TD className={tx.type === "credit" ? "font-bold text-emerald-600" : "font-bold text-red-600"}>
                {tx.type === "credit" ? "+" : "−"}
                {money(tx.amount, false)}
              </TD>
              <TD className="text-muted">{money(tx.balance_after, false)}</TD>
              <TD className="max-w-[240px] truncate text-muted">{tx.reason ?? "—"}</TD>
              <TD>
                <StatusBadge status={tx.status} />
              </TD>
              <TD className="whitespace-nowrap text-muted">{dateTime(tx.created_at, locale)}</TD>
            </TR>
          ))}
        </TBody>
      </Table>
      <Pagination current={data?.meta.current_page ?? 1} last={data?.meta.last_page ?? 1} total={data?.meta.total} onChange={setPage} />
    </QueryState>
  );
}
