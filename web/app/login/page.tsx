"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/lib/auth";
import { useI18n } from "@/lib/i18n";
import { ApiError } from "@/lib/api";
import { Button, Field, Input } from "@/components/ui";
import { LanguageSwitcher } from "@/components/LanguageSwitcher";
import { Icon } from "@/components/icons";

export default function LoginPage() {
  const { user, loading, login } = useAuth();
  const { t } = useI18n();
  const router = useRouter();

  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Already signed in -> go to dashboard.
  useEffect(() => {
    if (!loading && user) router.replace("/dashboard");
  }, [loading, user, router]);

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      await login(phone.trim(), password);
      router.replace("/dashboard");
    } catch (err) {
      if (err instanceof ApiError && err.message === "NOT_ADMIN") {
        setError(t("login.error_not_admin"));
      } else if (err instanceof ApiError && (err.status === 401 || err.status === 422)) {
        setError(t("login.error_invalid"));
      } else {
        setError(err instanceof Error ? err.message : t("common.error"));
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      {/* Brand panel */}
      <div className="relative hidden overflow-hidden brand-gradient lg:flex">
        <div className="absolute inset-0 opacity-20 [background-image:radial-gradient(white_1px,transparent_1px)] [background-size:22px_22px]" />
        <div className="relative z-10 flex flex-col justify-between p-12 text-white">
          <div className="flex items-center gap-3">
            <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 font-display text-2xl font-extrabold backdrop-blur">
              Q
            </span>
            <span className="font-display text-2xl font-extrabold">Qadam</span>
          </div>
          <div>
            <h2 className="font-display text-4xl font-extrabold leading-tight">
              Boshqaruv paneli
            </h2>
            <p className="mt-3 max-w-md text-white/85">
              Haydovchilar, mijozlar, buyurtmalar va moliyaviy operatsiyalarni bitta joydan
              boshqaring.
            </p>
          </div>
          <p className="text-sm text-white/70">© Qadam — ride-sharing platform</p>
        </div>
      </div>

      {/* Form panel */}
      <div className="flex flex-col">
        <div className="flex items-center justify-end p-4">
          <LanguageSwitcher />
        </div>
        <div className="flex flex-1 items-center justify-center px-6 pb-16">
          <div className="w-full max-w-sm">
            <div className="mb-8 lg:hidden">
              <span className="inline-flex h-11 w-11 items-center justify-center rounded-2xl brand-gradient font-display text-2xl font-extrabold text-white">
                Q
              </span>
            </div>
            <h1 className="font-display text-2xl font-extrabold text-ink">{t("login.title")}</h1>
            <p className="mt-1 text-sm text-muted">{t("login.subtitle")}</p>

            <form onSubmit={onSubmit} className="mt-7 space-y-4">
              <Field label={t("login.phone")} required>
                <Input
                  type="tel"
                  inputMode="tel"
                  autoComplete="username"
                  placeholder="+998 90 123 45 67"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  required
                />
              </Field>
              <Field label={t("login.password")} required>
                <Input
                  type="password"
                  autoComplete="current-password"
                  placeholder="••••••••"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </Field>

              {error ? (
                <div className="flex items-start gap-2 rounded-xl bg-red-50 px-3.5 py-2.5 text-sm font-medium text-red-700">
                  <Icon.X className="mt-0.5 h-4 w-4 shrink-0" />
                  <span>{error}</span>
                </div>
              ) : null}

              <Button type="submit" className="w-full" loading={submitting}>
                {submitting ? t("login.signing_in") : t("login.submit")}
              </Button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
