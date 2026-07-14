"use client";

import { useCallback, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useI18n } from "@/lib/i18n";
import { useQuery, useDebounced } from "@/lib/hooks";
import { money, fullName, phone as fmtPhone, dateOnly } from "@/lib/format";
import type { DriverStatus, Paginated, UserListItem } from "@/lib/types";
import type { UserListParams } from "@/lib/endpoints";
import { PageHeader, Button, Card, Avatar, Input } from "./ui";
import { Icon } from "./icons";
import { Table, THead, TH, TBody, TR, TD, Pagination, QueryState } from "./data";
import { StatusBadge, VerifiedBadge } from "./status";

type FilterKind = "status" | "verified" | "none";

const DRIVER_STATUSES: DriverStatus[] = ["none", "pending", "approved", "rejected", "blocked"];

export function UserListView({
  title,
  subtitle,
  newLabel,
  baseRoute,
  searchPlaceholder,
  filter = "none",
  list,
  emptyIcon,
}: {
  title: string;
  subtitle?: string;
  newLabel: string;
  baseRoute: string;
  searchPlaceholder: string;
  filter?: FilterKind;
  list: (params: UserListParams) => Promise<Paginated<UserListItem>>;
  emptyIcon?: React.ReactNode;
}) {
  const { t, locale } = useI18n();
  const router = useRouter();

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState<DriverStatus | "">("");
  const [verified, setVerified] = useState<"1" | "0" | "">("");
  const [page, setPage] = useState(1);
  const debouncedSearch = useDebounced(search);

  const fetcher = useCallback(
    () => list({ search: debouncedSearch, status, verified, page }),
    [list, debouncedSearch, status, verified, page],
  );
  const { data, loading, error, refetch } = useQuery<Paginated<UserListItem>>(fetcher, [
    debouncedSearch,
    status,
    verified,
    page,
  ]);

  const rows = data?.data ?? [];

  return (
    <div>
      <PageHeader
        title={title}
        subtitle={subtitle}
        actions={
          <Button icon={<Icon.Plus className="h-4 w-4" />} onClick={() => router.push(`${baseRoute}/create`)}>
            {newLabel}
          </Button>
        }
      />

      <Card>
        <div className="flex flex-col gap-3 border-b border-line p-4 sm:flex-row sm:items-center">
          <div className="relative flex-1">
            <Icon.Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <Input
              className="pl-9"
              placeholder={searchPlaceholder}
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
            />
          </div>

          {filter === "status" ? (
            <FilterChips
              value={status}
              onChange={(v) => {
                setStatus(v as DriverStatus | "");
                setPage(1);
              }}
              options={[
                { value: "", label: t("common.all") },
                ...DRIVER_STATUSES.map((s) => ({ value: s, label: t(`status.${s}`) })),
              ]}
            />
          ) : null}

          {filter === "verified" ? (
            <FilterChips
              value={verified}
              onChange={(v) => {
                setVerified(v as "1" | "0" | "");
                setPage(1);
              }}
              options={[
                { value: "", label: t("common.all") },
                { value: "1", label: t("user.verified") },
                { value: "0", label: t("user.unverified") },
              ]}
            />
          ) : null}
        </div>

        <QueryState
          loading={loading}
          error={error}
          isEmpty={rows.length === 0}
          onRetry={refetch}
          emptyIcon={emptyIcon}
        >
          <Table>
            <THead>
              <TH>{t("user.name")}</TH>
              <TH>{t("user.phone")}</TH>
              <TH className="text-right">{t("user.balance")}</TH>
              <TH>{t("user.status")}</TH>
              <TH>{t("user.joined")}</TH>
              <TH />
            </THead>
            <TBody>
              {rows.map((u) => (
                <TR key={u.id} onClick={() => router.push(`${baseRoute}/${u.id}`)}>
                  <TD>
                    <div className="flex items-center gap-3">
                      <Avatar name={fullName(u)} src={u.image} size={36} />
                      <span className="font-semibold">{fullName(u)}</span>
                    </div>
                  </TD>
                  <TD className="whitespace-nowrap text-muted">{fmtPhone(u.phone)}</TD>
                  <TD className="whitespace-nowrap text-right font-semibold">{money(u.balance)}</TD>
                  <TD>
                    {filter === "verified" ? (
                      <VerifiedBadge verified={u.is_verified} />
                    ) : (
                      <StatusBadge status={u.driving_verification_status} />
                    )}
                  </TD>
                  <TD className="whitespace-nowrap text-muted">{dateOnly(u.created_at, locale)}</TD>
                  <TD className="text-right">
                    <Link
                      href={`${baseRoute}/${u.id}`}
                      onClick={(e) => e.stopPropagation()}
                      className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted transition hover:bg-canvas hover:text-ink"
                    >
                      <Icon.Chevron className="h-4 w-4" />
                    </Link>
                  </TD>
                </TR>
              ))}
            </TBody>
          </Table>
          <Pagination
            current={data?.meta.current_page ?? 1}
            last={data?.meta.last_page ?? 1}
            total={data?.meta.total}
            onChange={setPage}
          />
        </QueryState>
      </Card>
    </div>
  );
}

function FilterChips({
  value,
  onChange,
  options,
}: {
  value: string;
  onChange: (v: string) => void;
  options: { value: string; label: string }[];
}) {
  return (
    <div className="flex flex-wrap gap-1.5">
      {options.map((o) => (
        <button
          key={o.value}
          onClick={() => onChange(o.value)}
          className={
            value === o.value
              ? "rounded-lg bg-ink px-3 py-1.5 text-xs font-bold text-white"
              : "rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink-soft transition hover:bg-canvas"
          }
        >
          {o.label}
        </button>
      ))}
    </div>
  );
}
