"use client";

import { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { fullName, phone as fmtPhone, money, dateOnly } from "@/lib/format";
import type { UserDetail } from "@/lib/types";
import { Avatar, Button, Card } from "./ui";
import { Icon } from "./icons";
import { StatusBadge, VerifiedBadge } from "./status";
import { ConfirmModal } from "./Modal";
import { UserActionBar, type UserActionsResource } from "./user-actions";

export function UserDetailHeader({
  user,
  resource,
  baseRoute,
  showStatus = true,
  showVerified = false,
  showActions = true,
  onChanged,
  extraActions,
}: {
  user: UserDetail;
  resource: UserActionsResource & { remove: (id: number | string) => Promise<void> };
  baseRoute: string;
  showStatus?: boolean;
  showVerified?: boolean;
  showActions?: boolean;
  onChanged?: () => void;
  extraActions?: React.ReactNode;
}) {
  const { t, locale, ln } = useI18n();
  const toast = useToast();
  const router = useRouter();
  const [confirmDelete, setConfirmDelete] = useState(false);
  const [deleting, setDeleting] = useState(false);

  async function doDelete() {
    setDeleting(true);
    try {
      await resource.remove(user.id);
      toast.success(t("toast.deleted"));
      router.push(baseRoute);
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
      setDeleting(false);
      setConfirmDelete(false);
    }
  }

  const location = [ln(user.region), ln(user.district), ln(user.quarter)]
    .filter((s) => s && s !== "—")
    .join(", ");

  return (
    <Card className="overflow-hidden">
      <div className="brand-gradient h-20" />
      <div className="px-5 pb-5">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div className="flex items-end gap-4">
            <div className="-mt-10 rounded-full ring-4 ring-white">
              <Avatar name={fullName(user)} src={user.image} size={84} />
            </div>
            <div className="pb-1">
              <h1 className="font-display text-xl font-extrabold text-ink">{fullName(user)}</h1>
              <div className="mt-1 flex flex-wrap items-center gap-2 text-sm text-muted">
                <span className="inline-flex items-center gap-1">
                  <Icon.Phone className="h-3.5 w-3.5" />
                  {fmtPhone(user.phone)}
                </span>
                {user.email ? <span>· {user.email}</span> : null}
              </div>
              <div className="mt-2 flex flex-wrap items-center gap-2">
                {showStatus ? <StatusBadge status={user.driving_verification_status} /> : null}
                {showVerified ? <VerifiedBadge verified={user.is_verified} /> : null}
                <span className="text-xs text-muted">
                  {t("user.joined")}: {dateOnly(user.created_at, locale)}
                </span>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <Link href={`${baseRoute}/${user.id}/edit`}>
              <Button variant="outline" size="sm" icon={<Icon.Edit className="h-4 w-4" />}>
                {t("action.edit")}
              </Button>
            </Link>
            <Button
              variant="ghost"
              size="sm"
              className="text-danger hover:bg-red-50"
              icon={<Icon.Trash className="h-4 w-4" />}
              onClick={() => setConfirmDelete(true)}
            >
              {t("action.delete")}
            </Button>
          </div>
        </div>

        {/* Quick facts */}
        <div className="mt-5 grid gap-3 sm:grid-cols-3">
          <Fact label={t("user.balance")} value={money(user.balance)} strong />
          <Fact label={t("user.home")} value={[location, user.home].filter(Boolean).join(" · ") || "—"} />
          <Fact label={t("user.role")} value={user.role} />
        </div>

        {/* Actions */}
        {showActions ? (
          <div className="mt-5">
            <UserActionBar
              resource={resource}
              id={user.id}
              status={showStatus ? user.driving_verification_status : undefined}
              onChanged={onChanged}
              extra={extraActions}
            />
          </div>
        ) : extraActions ? (
          <div className="mt-5 flex flex-wrap items-center gap-2">{extraActions}</div>
        ) : null}
      </div>

      <ConfirmModal
        open={confirmDelete}
        onClose={() => setConfirmDelete(false)}
        onConfirm={doDelete}
        title={t("action.delete")}
        message={t("common.confirm_delete")}
        confirmLabel={t("action.delete")}
        cancelLabel={t("action.cancel")}
        danger
        pending={deleting}
      />
    </Card>
  );
}

function Fact({ label, value, strong }: { label: string; value: React.ReactNode; strong?: boolean }) {
  return (
    <div className="rounded-xl bg-canvas px-4 py-3">
      <p className="text-xs font-medium text-muted">{label}</p>
      <p className={strong ? "mt-0.5 font-display text-lg font-bold text-ink" : "mt-0.5 text-sm font-semibold text-ink"}>
        {value}
      </p>
    </div>
  );
}
