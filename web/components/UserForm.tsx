"use client";

import { useCallback, useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useI18n } from "@/lib/i18n";
import { useToast } from "@/lib/toast";
import { endpoints, type UserPayload } from "@/lib/endpoints";
import { ApiError } from "@/lib/api";
import type { LocationRef, UserDetail } from "@/lib/types";
import { Button, Card, CardBody, Field, Input, PageHeader, Select, Spinner } from "./ui";

interface UserResourceForm {
  show: (id: number | string) => Promise<UserDetail>;
  create: (payload: UserPayload) => Promise<UserDetail>;
  update: (id: number | string, payload: Partial<UserPayload>) => Promise<UserDetail>;
}

type FormState = {
  first_name: string;
  last_name: string;
  father_name: string;
  phone: string;
  email: string;
  password: string;
  region_id: string;
  district_id: string;
  quarter_id: string;
  home: string;
};

const EMPTY: FormState = {
  first_name: "",
  last_name: "",
  father_name: "",
  phone: "",
  email: "",
  password: "",
  region_id: "",
  district_id: "",
  quarter_id: "",
  home: "",
};

export function UserForm({
  resource,
  id,
  title,
  baseRoute,
}: {
  resource: UserResourceForm;
  id?: string;
  title: string;
  baseRoute: string;
}) {
  const { t, ln } = useI18n();
  const toast = useToast();
  const router = useRouter();
  const isEdit = Boolean(id);

  const [form, setForm] = useState<FormState>(EMPTY);
  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const [regions, setRegions] = useState<LocationRef[]>([]);
  const [districts, setDistricts] = useState<LocationRef[]>([]);
  const [quarters, setQuarters] = useState<LocationRef[]>([]);

  const set = (key: keyof FormState, value: string) =>
    setForm((f) => ({ ...f, [key]: value }));

  // Load regions once.
  useEffect(() => {
    endpoints.locations.regions().then(setRegions).catch(() => {});
  }, []);

  // Load the user on edit.
  useEffect(() => {
    if (!id) return;
    resource
      .show(id)
      .then((u) => {
        setForm({
          first_name: u.first_name ?? "",
          last_name: u.last_name ?? "",
          father_name: u.father_name ?? "",
          phone: u.phone ?? "",
          email: u.email ?? "",
          password: "",
          region_id: u.region?.id ? String(u.region.id) : "",
          district_id: u.district?.id ? String(u.district.id) : "",
          quarter_id: u.quarter?.id ? String(u.quarter.id) : "",
          home: u.home ?? "",
        });
      })
      .catch((e) => toast.error(e instanceof Error ? e.message : t("common.error")))
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  // Cascade: districts depend on region.
  const loadDistricts = useCallback((regionId: string) => {
    if (!regionId) return setDistricts([]);
    endpoints.locations.districts(regionId).then(setDistricts).catch(() => setDistricts([]));
  }, []);
  const loadQuarters = useCallback((districtId: string) => {
    if (!districtId) return setQuarters([]);
    endpoints.locations.quarters(districtId).then(setQuarters).catch(() => setQuarters([]));
  }, []);

  useEffect(() => {
    if (form.region_id) loadDistricts(form.region_id);
  }, [form.region_id, loadDistricts]);
  useEffect(() => {
    if (form.district_id) loadQuarters(form.district_id);
  }, [form.district_id, loadQuarters]);

  function toPayload(): UserPayload {
    return {
      first_name: form.first_name.trim(),
      last_name: form.last_name.trim() || undefined,
      father_name: form.father_name.trim() || undefined,
      phone: form.phone.trim(),
      email: form.email.trim() || undefined,
      password: form.password || undefined,
      region_id: form.region_id ? Number(form.region_id) : undefined,
      district_id: form.district_id ? Number(form.district_id) : undefined,
      quarter_id: form.quarter_id ? Number(form.quarter_id) : undefined,
      home: form.home.trim() || undefined,
    };
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setSaving(true);
    setErrors({});
    try {
      const saved = isEdit
        ? await resource.update(id!, toPayload())
        : await resource.create(toPayload());
      toast.success(t("toast.saved"));
      router.push(`${baseRoute}/${saved.id ?? id}`);
    } catch (e) {
      if (e instanceof ApiError && e.errors) {
        const flat: Record<string, string> = {};
        for (const [k, v] of Object.entries(e.errors)) flat[k] = v[0];
        setErrors(flat);
        toast.error(e.message);
      } else {
        toast.error(e instanceof Error ? e.message : t("common.error"));
      }
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="flex justify-center py-20">
        <Spinner className="h-8 w-8 text-brand-500" />
      </div>
    );
  }

  return (
    <div>
      <PageHeader
        title={title}
        actions={
          <Button variant="ghost" onClick={() => router.back()}>
            {t("action.back")}
          </Button>
        }
      />
      <form onSubmit={submit}>
        <Card>
          <CardBody className="grid gap-5 sm:grid-cols-2">
            <Field label={t("user.first_name")} error={errors.first_name} required>
              <Input value={form.first_name} onChange={(e) => set("first_name", e.target.value)} required />
            </Field>
            <Field label={t("user.last_name")} error={errors.last_name}>
              <Input value={form.last_name} onChange={(e) => set("last_name", e.target.value)} />
            </Field>
            <Field label={t("user.father_name")} error={errors.father_name}>
              <Input value={form.father_name} onChange={(e) => set("father_name", e.target.value)} />
            </Field>
            <Field label={t("user.phone")} error={errors.phone} required>
              <Input
                type="tel"
                value={form.phone}
                onChange={(e) => set("phone", e.target.value)}
                placeholder="+998..."
                required
              />
            </Field>
            <Field label={t("user.email")} error={errors.email}>
              <Input type="email" value={form.email} onChange={(e) => set("email", e.target.value)} />
            </Field>
            <Field
              label={t("user.password")}
              error={errors.password}
              hint={isEdit ? t("common.optional") : t("user.password_hint")}
              required={!isEdit}
            >
              <Input
                type="password"
                value={form.password}
                onChange={(e) => set("password", e.target.value)}
                required={!isEdit}
                autoComplete="new-password"
              />
            </Field>

            <Field label={t("user.region")} error={errors.region_id}>
              <Select
                value={form.region_id}
                onChange={(e) => {
                  set("region_id", e.target.value);
                  set("district_id", "");
                  set("quarter_id", "");
                }}
              >
                <option value="">—</option>
                {regions.map((r) => (
                  <option key={r.id} value={r.id}>
                    {ln(r)}
                  </option>
                ))}
              </Select>
            </Field>
            <Field label={t("user.district")} error={errors.district_id}>
              <Select
                value={form.district_id}
                disabled={!form.region_id}
                onChange={(e) => {
                  set("district_id", e.target.value);
                  set("quarter_id", "");
                }}
              >
                <option value="">—</option>
                {districts.map((d) => (
                  <option key={d.id} value={d.id}>
                    {ln(d)}
                  </option>
                ))}
              </Select>
            </Field>
            <Field label={t("user.quarter")} error={errors.quarter_id}>
              <Select
                value={form.quarter_id}
                disabled={!form.district_id}
                onChange={(e) => set("quarter_id", e.target.value)}
              >
                <option value="">—</option>
                {quarters.map((q) => (
                  <option key={q.id} value={q.id}>
                    {ln(q)}
                  </option>
                ))}
              </Select>
            </Field>
            <Field label={t("user.home")} error={errors.home}>
              <Input value={form.home} onChange={(e) => set("home", e.target.value)} />
            </Field>
          </CardBody>
          <div className="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
            <Button type="button" variant="ghost" onClick={() => router.back()} disabled={saving}>
              {t("action.cancel")}
            </Button>
            <Button type="submit" loading={saving}>
              {saving ? t("common.saving") : t("action.save")}
            </Button>
          </div>
        </Card>
      </form>
    </div>
  );
}
