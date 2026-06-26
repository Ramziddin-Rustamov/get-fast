"use client";

import { useCallback, useState } from "react";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { money, dateTime } from "@/lib/format";
import type { CompanyTransaction, Paginated } from "@/lib/types";
import { PageHeader, Card } from "@/components/ui";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { Badge } from "@/components/ui";
import { Icon } from "@/components/icons";

export default function TransactionsPage() {
  const { t, locale } = useI18n();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.dashboard.companyTransactions(page), [page]);
  const { data, loading, error, refetch } = useQuery<Paginated<CompanyTransaction>>(fetcher, [page]);
  const rows = data?.data ?? [];

  return (
    <div>
      <PageHeader title={t("transactions.title")} />
      <Card>
        <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Transactions className="h-6 w-6" />}>
          <Table>
            <THead>
              <TH>{t("user.role")}</TH>
              <TH className="text-right">{t("common.amount")}</TH>
              <TH className="text-right">{t("transactions.balance_after")}</TH>
              <TH>{t("common.note")}</TH>
              <TH>{t("user.joined")}</TH>
            </THead>
            <TBody>
              {rows.map((tx) => (
                <TR key={tx.id}>
                  <TD>
                    <Badge tone={tx.type === "incoming" ? "success" : "danger"}>
                      {tx.type === "incoming" ? t("transactions.incoming") : t("transactions.outgoing")}
                    </Badge>
                  </TD>
                  <TD className={tx.type === "incoming" ? "text-right font-bold text-emerald-600" : "text-right font-bold text-red-600"}>
                    {tx.type === "incoming" ? "+" : "−"}
                    {money(tx.amount, false)}
                  </TD>
                  <TD className="text-right text-muted">{money(tx.balance_after, false)}</TD>
                  <TD className="max-w-[280px] truncate text-muted">{tx.reason ?? "—"}</TD>
                  <TD className="whitespace-nowrap text-muted">{dateTime(tx.created_at, locale)}</TD>
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
