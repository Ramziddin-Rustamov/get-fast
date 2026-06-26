"use client";

import { useCallback, useState } from "react";
import { Modal } from "./Modal";
import { Button, Field, Input, Select, Textarea } from "./ui";
import { Icon } from "./icons";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { useMutation } from "@/lib/hooks";
import { money } from "@/lib/format";
import type { Card, DriverStatus } from "@/lib/types";

/** The subset of a user resource the action modals need (drivers/clients/admins). */
export interface UserActionsResource {
  sendSms: (id: number | string, message: string) => Promise<void>;
  transfer: (id: number | string, payload: { card_id: number; amount: number }) => Promise<void>;
  addBalance: (id: number | string, payload: { amount: number; note?: string }) => Promise<void>;
  deductBalance: (id: number | string, payload: { amount: number; note?: string }) => Promise<void>;
  cards: (id: number | string) => Promise<Card[]>;
  updateStatus?: (id: number | string, status: DriverStatus) => Promise<void>;
}

const DRIVER_STATUSES: DriverStatus[] = ["none", "pending", "approved", "rejected", "blocked"];

/* --------------------------------- SMS -------------------------------- */

export function SmsModal({
  open,
  onClose,
  resource,
  id,
}: {
  open: boolean;
  onClose: () => void;
  resource: UserActionsResource;
  id: number | string;
}) {
  const { t } = useI18n();
  const toast = useToast();
  const [message, setMessage] = useState("");
  const { run, pending } = useMutation(resource.sendSms);

  async function submit() {
    try {
      await run(id, message.trim());
      toast.success(t("toast.sms_sent"));
      setMessage("");
      onClose();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={t("action.send_sms")}
      footer={
        <>
          <Button variant="ghost" onClick={onClose} disabled={pending}>
            {t("action.cancel")}
          </Button>
          <Button onClick={submit} loading={pending} disabled={!message.trim()}>
            {t("action.send")}
          </Button>
        </>
      }
    >
      <Field label={t("form.sms_message")} hint={`${message.length}/255 · ${t("form.sms_hint")}`}>
        <Textarea maxLength={255} value={message} onChange={(e) => setMessage(e.target.value)} />
      </Field>
    </Modal>
  );
}

/* ------------------------------ Transfer ------------------------------ */

export function TransferModal({
  open,
  onClose,
  resource,
  id,
  onDone,
}: {
  open: boolean;
  onClose: () => void;
  resource: UserActionsResource;
  id: number | string;
  onDone?: () => void;
}) {
  const { t } = useI18n();
  const toast = useToast();
  const [cards, setCards] = useState<Card[] | null>(null);
  const [cardId, setCardId] = useState("");
  const [amount, setAmount] = useState("");
  const { run, pending } = useMutation(resource.transfer);

  const loadCards = useCallback(() => {
    resource
      .cards(id)
      .then((c) => {
        setCards(c);
        if (c[0]) setCardId(String(c[0].id));
      })
      .catch(() => setCards([]));
  }, [resource, id]);

  async function submit() {
    try {
      await run(id, { card_id: Number(cardId), amount: Number(amount) });
      toast.success(t("toast.transferred"));
      setAmount("");
      onClose();
      onDone?.();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={t("action.transfer")}
      footer={
        <>
          <Button variant="ghost" onClick={onClose} disabled={pending}>
            {t("action.cancel")}
          </Button>
          <Button onClick={submit} loading={pending} disabled={!cardId || !amount}>
            {t("action.transfer")}
          </Button>
        </>
      }
    >
      <div className="space-y-4" onFocus={() => cards === null && loadCards()}>
        <Field label={t("form.select_card")}>
          <Select value={cardId} onChange={(e) => setCardId(e.target.value)}>
            {cards === null ? (
              <option>{t("common.loading")}</option>
            ) : cards.length === 0 ? (
              <option value="">{t("common.empty")}</option>
            ) : (
              cards.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.number} {c.label ? `· ${c.label}` : ""}
                </option>
              ))
            )}
          </Select>
        </Field>
        <Field label={t("form.transfer_amount")} hint={amount ? money(Number(amount)) : undefined}>
          <Input
            type="number"
            min={1000}
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            placeholder="0"
          />
        </Field>
      </div>
    </Modal>
  );
}

/* ---------------------------- Balance move ---------------------------- */

export function BalanceMoveModal({
  open,
  onClose,
  resource,
  id,
  mode,
  onDone,
}: {
  open: boolean;
  onClose: () => void;
  resource: UserActionsResource;
  id: number | string;
  mode: "pay" | "withdraw";
  onDone?: () => void;
}) {
  const { t } = useI18n();
  const toast = useToast();
  const [amount, setAmount] = useState("");
  const [note, setNote] = useState("");
  const action = mode === "pay" ? resource.addBalance : resource.deductBalance;
  const { run, pending } = useMutation(action);

  async function submit() {
    try {
      await run(id, { amount: Number(amount), note: note.trim() || undefined });
      toast.success(t("toast.saved"));
      setAmount("");
      setNote("");
      onClose();
      onDone?.();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={mode === "pay" ? t("action.add_balance") : t("action.deduct_balance")}
      footer={
        <>
          <Button variant="ghost" onClick={onClose} disabled={pending}>
            {t("action.cancel")}
          </Button>
          <Button
            variant={mode === "pay" ? "primary" : "danger"}
            onClick={submit}
            loading={pending}
            disabled={!amount}
          >
            {t("action.confirm")}
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <Field label={t("common.amount")} hint={amount ? money(Number(amount)) : undefined}>
          <Input
            type="number"
            min={1}
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            placeholder="0"
          />
        </Field>
        <Field label={`${t("common.note")} (${t("common.optional")})`}>
          <Input value={note} onChange={(e) => setNote(e.target.value)} />
        </Field>
      </div>
    </Modal>
  );
}

/* ----------------------------- Status ----------------------------- */

export function StatusModal({
  open,
  onClose,
  resource,
  id,
  current,
  onDone,
}: {
  open: boolean;
  onClose: () => void;
  resource: UserActionsResource;
  id: number | string;
  current: DriverStatus;
  onDone?: () => void;
}) {
  const { t } = useI18n();
  const toast = useToast();
  const [status, setStatus] = useState<DriverStatus>(current);
  const { run, pending } = useMutation((s: DriverStatus) => resource.updateStatus!(id, s));

  async function submit() {
    try {
      await run(status);
      toast.success(t("toast.status_updated"));
      onClose();
      onDone?.();
    } catch (e) {
      toast.error(e instanceof Error ? e.message : t("common.error"));
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={t("action.change_status")}
      footer={
        <>
          <Button variant="ghost" onClick={onClose} disabled={pending}>
            {t("action.cancel")}
          </Button>
          <Button onClick={submit} loading={pending} disabled={status === current}>
            {t("action.save")}
          </Button>
        </>
      }
    >
      <Field label={t("form.new_status")}>
        <Select value={status} onChange={(e) => setStatus(e.target.value as DriverStatus)}>
          {DRIVER_STATUSES.map((s) => (
            <option key={s} value={s}>
              {t(`status.${s}`)}
            </option>
          ))}
        </Select>
      </Field>
    </Modal>
  );
}

/* ------------------------- Action bar wrapper ------------------------- */

type ModalKind = "sms" | "transfer" | "pay" | "withdraw" | "status" | null;

/** Buttons + wired modals for a user detail page. */
export function UserActionBar({
  resource,
  id,
  status,
  onChanged,
  extra,
}: {
  resource: UserActionsResource;
  id: number | string;
  status?: DriverStatus;
  onChanged?: () => void;
  extra?: React.ReactNode;
}) {
  const { t } = useI18n();
  const [modal, setModal] = useState<ModalKind>(null);
  const close = () => setModal(null);

  return (
    <>
      <div className="flex flex-wrap items-center gap-2">
        {extra}
        {resource.updateStatus ? (
          <Button variant="outline" size="sm" onClick={() => setModal("status")}>
            {t("action.change_status")}
          </Button>
        ) : null}
        <Button
          variant="outline"
          size="sm"
          icon={<Icon.Phone className="h-4 w-4" />}
          onClick={() => setModal("sms")}
        >
          {t("action.send_sms")}
        </Button>
        <Button
          variant="outline"
          size="sm"
          icon={<Icon.Wallet className="h-4 w-4" />}
          onClick={() => setModal("pay")}
        >
          {t("action.add_balance")}
        </Button>
        <Button variant="outline" size="sm" onClick={() => setModal("withdraw")}>
          {t("action.deduct_balance")}
        </Button>
        <Button
          variant="primary"
          size="sm"
          icon={<Icon.Transactions className="h-4 w-4" />}
          onClick={() => setModal("transfer")}
        >
          {t("action.transfer")}
        </Button>
      </div>

      <SmsModal open={modal === "sms"} onClose={close} resource={resource} id={id} />
      <TransferModal
        open={modal === "transfer"}
        onClose={close}
        resource={resource}
        id={id}
        onDone={onChanged}
      />
      <BalanceMoveModal
        open={modal === "pay"}
        onClose={close}
        resource={resource}
        id={id}
        mode="pay"
        onDone={onChanged}
      />
      <BalanceMoveModal
        open={modal === "withdraw"}
        onClose={close}
        resource={resource}
        id={id}
        mode="withdraw"
        onDone={onChanged}
      />
      {resource.updateStatus && status !== undefined ? (
        <StatusModal
          open={modal === "status"}
          onClose={close}
          resource={resource}
          id={id}
          current={status}
          onDone={onChanged}
        />
      ) : null}
    </>
  );
}
