"use client";

import { useCallback, useState } from "react";
import { useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { dateTime } from "@/lib/format";
import type { Paginated, SupportMessage } from "@/lib/types";
import { PageHeader, Card } from "@/components/ui";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { Icon } from "@/components/icons";

export default function SupportPage() {
  const { t, locale } = useI18n();
  const router = useRouter();
  const [page, setPage] = useState(1);
  const fetcher = useCallback(() => endpoints.support.list(page), [page]);
  const { data, loading, error, refetch } = useQuery<Paginated<SupportMessage>>(fetcher, [page]);
  const rows = data?.data ?? [];

  return (
    <div>
      <PageHeader title={t("support.title")} />
      <Card>
        <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Support className="h-6 w-6" />}>
          <Table>
            <THead>
              <TH>{t("support.from")}</TH>
              <TH>{t("support.message")}</TH>
              <TH>{t("user.status")}</TH>
              <TH>{t("user.joined")}</TH>
            </THead>
            <TBody>
              {rows.map((m) => (
                <TR key={m.id} onClick={() => router.push(`/support/${m.id}`)}>
                  <TD>
                    <div className="font-semibold">{m.name}</div>
                    <div className="text-xs text-muted">{m.email}</div>
                  </TD>
                  <TD className="max-w-[360px] truncate text-muted">{m.message}</TD>
                  <TD>
                    <StatusBadge status={m.status} />
                  </TD>
                  <TD className="whitespace-nowrap text-muted">{dateTime(m.created_at, locale)}</TD>
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
