"use client";

import { useCallback, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery, useMutation } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { dateTime } from "@/lib/format";
import type { SupportMessage } from "@/lib/types";
import { Button, Card, CardBody, CardHeader } from "@/components/ui";
import { FullScreenLoader, QueryState } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { ConfirmModal } from "@/components/Modal";
import { Icon } from "@/components/icons";

export default function SupportDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { t, locale } = useI18n();
  const toast = useToast();
  const router = useRouter();
  const [confirmDelete, setConfirmDelete] = useState(false);

  const fetcher = useCallback(() => endpoints.support.show(id), [id]);
  const { data: msg, loading, error, refetch } = useQuery<SupportMessage>(fetcher, [id]);

  const answer = useMutation(() => endpoints.support.answer(id));
  const remove = useMutation(() => endpoints.support.remove(id));

  async function markAnswered() {
    try {
      await answer.run();
      toast.success(t("toast.saved"));
      refetch();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
    }
  }

  async function doDelete() {
    try {
      await remove.run();
      toast.success(t("toast.deleted"));
      router.push("/support");
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
      setConfirmDelete(false);
    }
  }

  if (loading) return <FullScreenLoader />;
  if (error || !msg) {
    return (
      <Card>
        <QueryState loading={false} error={error} onRetry={refetch}>
          <div />
        </QueryState>
      </Card>
    );
  }

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <div className="flex items-center justify-between">
        <Button variant="ghost" size="sm" onClick={() => router.push("/support")} icon={<Icon.Chevron className="h-4 w-4 rotate-180" />}>
          {t("support.title")}
        </Button>
        <StatusBadge status={msg.status} />
      </div>

      <Card>
        <CardHeader
          title={msg.name}
          subtitle={`${msg.email} · ${dateTime(msg.created_at, locale)}`}
        />
        <CardBody>
          <p className="whitespace-pre-wrap text-sm leading-relaxed text-ink-soft">{msg.message}</p>
        </CardBody>
        <div className="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
          <Button
            variant="ghost"
            className="text-danger hover:bg-red-50"
            icon={<Icon.Trash className="h-4 w-4" />}
            onClick={() => setConfirmDelete(true)}
          >
            {t("action.delete")}
          </Button>
          {msg.status !== "answered" ? (
            <Button icon={<Icon.Check className="h-4 w-4" />} loading={answer.pending} onClick={markAnswered}>
              {t("action.mark_answered")}
            </Button>
          ) : null}
        </div>
      </Card>

      <ConfirmModal
        open={confirmDelete}
        onClose={() => setConfirmDelete(false)}
        onConfirm={doDelete}
        title={t("action.delete")}
        message={t("common.confirm_delete")}
        confirmLabel={t("action.delete")}
        cancelLabel={t("action.cancel")}
        danger
        pending={remove.pending}
      />
    </div>
  );
}
