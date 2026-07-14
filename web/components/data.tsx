"use client";

import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { useI18n } from "@/lib/i18n";
import type { ApiError } from "@/lib/api";
import { Button, Card, EmptyState, Skeleton, Spinner } from "./ui";
import { Icon } from "./icons";

/* -------------------------------- Table ------------------------------- */

export function Table({ children }: { children: ReactNode }) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[640px] border-collapse text-sm">{children}</table>
    </div>
  );
}

export function THead({ children }: { children: ReactNode }) {
  return (
    <thead>
      <tr className="border-b border-line text-left text-xs font-semibold uppercase tracking-wide text-muted">
        {children}
      </tr>
    </thead>
  );
}

export function TH({ children, className }: { children?: ReactNode; className?: string }) {
  return <th className={cn("px-4 py-3 font-semibold", className)}>{children}</th>;
}

export function TBody({ children }: { children: ReactNode }) {
  return <tbody className="divide-y divide-line">{children}</tbody>;
}

export function TR({
  children,
  onClick,
  className,
}: {
  children: ReactNode;
  onClick?: () => void;
  className?: string;
}) {
  return (
    <tr
      onClick={onClick}
      className={cn(
        "transition hover:bg-canvas",
        onClick && "cursor-pointer",
        className,
      )}
    >
      {children}
    </tr>
  );
}

export function TD({ children, className }: { children?: ReactNode; className?: string }) {
  return <td className={cn("px-4 py-3 align-middle text-ink", className)}>{children}</td>;
}

/* ----------------------------- Pagination ----------------------------- */

export function Pagination({
  current,
  last,
  total,
  onChange,
}: {
  current: number;
  last: number;
  total?: number;
  onChange: (page: number) => void;
}) {
  const { t } = useI18n();
  if (last <= 1) {
    return total !== undefined ? (
      <div className="px-4 py-3 text-xs text-muted">{t("common.results", { count: total })}</div>
    ) : null;
  }
  return (
    <div className="flex items-center justify-between gap-3 px-4 py-3 text-sm">
      <span className="text-xs text-muted">
        {t("common.page_of", { current, total: last })}
        {total !== undefined ? ` · ${t("common.results", { count: total })}` : ""}
      </span>
      <div className="flex items-center gap-1.5">
        <Button
          size="sm"
          variant="outline"
          disabled={current <= 1}
          onClick={() => onChange(current - 1)}
        >
          <Icon.Chevron className="h-4 w-4 rotate-180" />
        </Button>
        <Button
          size="sm"
          variant="outline"
          disabled={current >= last}
          onClick={() => onChange(current + 1)}
        >
          <Icon.Chevron className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}

/* --------------------------- Query state UI --------------------------- */

/**
 * Standard wrapper around list/detail fetches: shows a skeleton while loading,
 * an error card with retry, an empty state, or the children.
 */
export function QueryState({
  loading,
  error,
  isEmpty,
  onRetry,
  emptyTitle,
  emptyIcon,
  children,
  skeletonRows = 6,
}: {
  loading: boolean;
  error: ApiError | null;
  isEmpty?: boolean;
  onRetry?: () => void;
  emptyTitle?: ReactNode;
  emptyIcon?: ReactNode;
  children: ReactNode;
  skeletonRows?: number;
}) {
  const { t } = useI18n();

  if (loading) {
    return (
      <div className="space-y-2 p-4">
        {Array.from({ length: skeletonRows }).map((_, i) => (
          <Skeleton key={i} className="h-12 w-full" />
        ))}
      </div>
    );
  }

  if (error) {
    return (
      <EmptyState
        title={t("common.error")}
        description={error.message}
        action={
          onRetry ? (
            <Button variant="outline" size="sm" onClick={onRetry}>
              {t("action.retry")}
            </Button>
          ) : null
        }
      />
    );
  }

  if (isEmpty) {
    return <EmptyState icon={emptyIcon} title={emptyTitle ?? t("common.empty")} />;
  }

  return <>{children}</>;
}

/** Full-screen centered loader for route-level suspense/guards. */
export function FullScreenLoader() {
  return (
    <div className="flex min-h-screen items-center justify-center">
      <Spinner className="h-8 w-8 text-brand-500" />
    </div>
  );
}

export { Card };
