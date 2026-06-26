"use client";

import { useCallback, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery, useMutation } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { money, dateTime, fullName, phone as fmtPhone } from "@/lib/format";
import type { BookingDetail, BookingPassenger } from "@/lib/types";
import { Button, Card, CardBody, CardHeader } from "@/components/ui";
import { FullScreenLoader, QueryState } from "@/components/data";
import { StatusBadge } from "@/components/status";
import { ConfirmModal } from "@/components/Modal";
import { Icon } from "@/components/icons";

export default function OrderDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { t, locale } = useI18n();
  const router = useRouter();

  const fetcher = useCallback(() => endpoints.orders.show(id), [id]);
  const { data: booking, loading, error, refetch } = useQuery<BookingDetail>(fetcher, [id]);

  if (loading) return <FullScreenLoader />;
  if (error || !booking) {
    return (
      <Card>
        <QueryState loading={false} error={error} onRetry={refetch}>
          <div />
        </QueryState>
      </Card>
    );
  }

  const trip = booking.trip;

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <Button variant="ghost" size="sm" onClick={() => router.push("/orders")} icon={<Icon.Chevron className="h-4 w-4 rotate-180" />}>
          {t("orders.title")}
        </Button>
        <StatusBadge status={booking.status} />
      </div>

      <div className="grid gap-4 lg:grid-cols-3">
        <Card className="lg:col-span-2">
          <CardHeader title={t("orders.route")} subtitle={trip ? dateTime(trip.start_time, locale) : undefined} />
          <CardBody>
            {trip ? (
              <div className="flex items-center gap-4">
                <Route from={trip.start_region} to={trip.end_region} fromSub={trip.start_district} toSub={trip.end_district} />
              </div>
            ) : (
              <p className="text-muted">—</p>
            )}
            <div className="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
              <Fact label={t("orders.seats")} value={booking.seats_booked} />
              <Fact label={t("orders.price")} value={money(booking.total_price)} />
              {trip ? <Fact label={t("orders.price") + " / " + t("orders.seats")} value={money(trip.price_per_seat)} /> : null}
            </div>
            {trip?.google_map_url ? (
              <a href={trip.google_map_url} target="_blank" rel="noreferrer" className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:underline">
                <Icon.Globe className="h-4 w-4" /> Google Maps
              </a>
            ) : null}
          </CardBody>
        </Card>

        <Card>
          <CardHeader title={t("user.name")} />
          <CardBody className="space-y-1">
            <p className="font-display text-lg font-bold text-ink">{fullName(booking.user)}</p>
            <p className="text-sm text-muted">{fmtPhone(booking.user?.phone)}</p>
          </CardBody>
        </Card>
      </div>

      <Card>
        <CardHeader title={t("orders.passengers")} subtitle={`${booking.passengers.length}`} />
        <PassengerList bookingId={id} passengers={booking.passengers} onChanged={refetch} />
      </Card>
    </div>
  );
}

function PassengerList({
  bookingId,
  passengers,
  onChanged,
}: {
  bookingId: string;
  passengers: BookingPassenger[];
  onChanged: () => void;
}) {
  const { t } = useI18n();
  const toast = useToast();
  const [target, setTarget] = useState<BookingPassenger | null>(null);
  const { run, pending } = useMutation((pid: number) => endpoints.orders.cancelPassenger(bookingId, pid));

  async function cancel() {
    if (!target) return;
    try {
      await run(target.id);
      toast.success(t("toast.saved"));
      setTarget(null);
      onChanged();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
      setTarget(null);
    }
  }

  if (passengers.length === 0) {
    return <CardBody><p className="text-sm text-muted">{t("common.empty")}</p></CardBody>;
  }

  return (
    <>
      <ul className="divide-y divide-line">
        {passengers.map((p) => {
          const cancelled = p.status === "cancelled";
          return (
            <li key={p.id} className="flex items-center justify-between gap-3 px-5 py-3.5">
              <div className="flex items-center gap-3">
                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 font-semibold text-brand-600">
                  {(p.name?.[0] ?? "?").toUpperCase()}
                </span>
                <div>
                  <p className="font-semibold text-ink">{p.name}</p>
                  <p className="text-xs text-muted">{fmtPhone(p.phone)}</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <StatusBadge status={p.status} />
                {!cancelled ? (
                  <Button variant="ghost" size="sm" className="text-danger hover:bg-red-50" onClick={() => setTarget(p)}>
                    {t("action.delete")}
                  </Button>
                ) : null}
              </div>
            </li>
          );
        })}
      </ul>

      <ConfirmModal
        open={target !== null}
        onClose={() => setTarget(null)}
        onConfirm={cancel}
        title={t("orders.passengers")}
        message={t("common.confirm_delete")}
        confirmLabel={t("action.confirm")}
        cancelLabel={t("action.cancel")}
        danger
        pending={pending}
      />
    </>
  );
}

function Route({ from, to, fromSub, toSub }: { from: string; to: string; fromSub?: string; toSub?: string }) {
  return (
    <div className="flex flex-1 items-center gap-3">
      <div className="flex-1">
        <p className="font-display text-lg font-bold text-ink">{from}</p>
        {fromSub ? <p className="text-xs text-muted">{fromSub}</p> : null}
      </div>
      <div className="flex flex-col items-center text-brand-500">
        <span className="h-2 w-2 rounded-full bg-current" />
        <span className="my-0.5 h-8 w-px bg-current/40" />
        <Icon.Chevron className="h-4 w-4 rotate-90" />
      </div>
      <div className="flex-1 text-right">
        <p className="font-display text-lg font-bold text-ink">{to}</p>
        {toSub ? <p className="text-xs text-muted">{toSub}</p> : null}
      </div>
    </div>
  );
}

function Fact({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="rounded-xl bg-canvas px-4 py-3">
      <p className="text-xs font-medium text-muted">{label}</p>
      <p className="mt-0.5 font-semibold text-ink">{value}</p>
    </div>
  );
}
