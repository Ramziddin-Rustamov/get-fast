"use client";

import { useCallback, useState } from "react";
import { endpoints } from "@/lib/endpoints";
import { useQuery, useMutation } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { money, dateTime, fullName, phone as fmtPhone } from "@/lib/format";
import type { Paginated, WithdrawRequest } from "@/lib/types";
import { PageHeader, Card, Button } from "@/components/ui";
import { Pagination, QueryState, TBody, TD, TH, THead, TR, Table } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { ConfirmModal } from "@/components/Modal";
import { Icon } from "@/components/icons";

export default function WithdrawalsPage() {
  const { t, locale } = useI18n();
  const toast = useToast();
  const [status, setStatus] = useState("");
  const [page, setPage] = useState(1);

  const fetcher = useCallback(
    () => endpoints.withdrawals.list({ status: status || undefined, page }),
    [status, page],
  );
  const { data, loading, error, refetch } = useQuery<Paginated<WithdrawRequest>>(fetcher, [status, page]);
  const rows = data?.data ?? [];

  const [action, setAction] = useState<{ id: number; kind: "approve" | "reject" } | null>(null);
  const approve = useMutation((id: number) => endpoints.withdrawals.approve(id));
  const reject = useMutation((id: number) => endpoints.withdrawals.reject(id));
  const pending = approve.pending || reject.pending;

  async function confirm() {
    if (!action) return;
    try {
      if (action.kind === "approve") await approve.run(action.id);
      else await reject.run(action.id);
      toast.success(action.kind === "approve" ? t("toast.approved") : t("toast.rejected"));
      setAction(null);
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
      setAction(null);
    }
  }

  const filters = ["", "pending", "approved", "rejected"];

  return (
    <div>
      <PageHeader title={t("withdrawals.title")} />
      <Card>
        <div className="flex flex-wrap gap-1.5 border-b border-line p-4">
          {filters.map((f) => (
            <button
              key={f || "all"}
              onClick={() => {
                setStatus(f);
                setPage(1);
              }}
              className={
                status === f
                  ? "rounded-lg bg-ink px-3 py-1.5 text-xs font-bold text-white"
                  : "rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink-soft transition hover:bg-canvas"
              }
            >
              {f ? t(`status.${f}`) : t("common.all")}
            </button>
          ))}
        </div>

        <QueryState loading={loading} error={error} isEmpty={rows.length === 0} onRetry={refetch} emptyIcon={<Icon.Withdraw className="h-6 w-6" />}>
          <Table>
            <THead>
              <TH>{t("user.name")}</TH>
              <TH className="text-right">{t("common.amount")}</TH>
              <TH>{t("withdrawals.card_holder")}</TH>
              <TH>{t("user.status")}</TH>
              <TH>{t("user.joined")}</TH>
              <TH />
            </THead>
            <TBody>
              {rows.map((w) => (
                <TR key={w.id}>
                  <TD>
                    <div className="font-semibold">{fullName(w.user)}</div>
                    <div className="text-xs text-muted">{fmtPhone(w.user?.phone)}</div>
                  </TD>
                  <TD className="text-right font-bold">{money(w.amount)}</TD>
                  <TD className="text-muted">{w.card_holder ?? "—"}</TD>
                  <TD>
                    <StatusBadge status={w.status} />
                  </TD>
                  <TD className="whitespace-nowrap text-muted">{dateTime(w.created_at, locale)}</TD>
                  <TD className="text-right">
                    {w.status === "pending" ? (
                      <div className="flex justify-end gap-1.5">
                        <Button size="sm" variant="ghost" className="text-danger hover:bg-red-50" onClick={() => setAction({ id: w.id, kind: "reject" })}>
                          {t("action.reject")}
                        </Button>
                        <Button size="sm" onClick={() => setAction({ id: w.id, kind: "approve" })}>
                          {t("action.approve")}
                        </Button>
                      </div>
                    ) : (
                      <span className="text-xs text-muted">—</span>
                    )}
                  </TD>
                </TR>
              ))}
            </TBody>
          </Table>
          <Pagination current={data?.meta.current_page ?? 1} last={data?.meta.last_page ?? 1} total={data?.meta.total} onChange={setPage} />
        </QueryState>
      </Card>

      <ConfirmModal
        open={action !== null}
        onClose={() => setAction(null)}
        onConfirm={confirm}
        title={action?.kind === "approve" ? t("action.approve") : t("action.reject")}
        message={action?.kind === "approve" ? t("withdrawals.approve_confirm") : t("withdrawals.reject_confirm")}
        confirmLabel={t("action.confirm")}
        cancelLabel={t("action.cancel")}
        danger={action?.kind === "reject"}
        pending={pending}
      />
    </div>
  );
}
