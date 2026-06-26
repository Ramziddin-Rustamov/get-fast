"use client";

import { useEffect, useRef, useState } from "react";
import { LOCALES, useI18n } from "@/lib/i18n";
import { useAuth } from "@/lib/auth";
import { Icon } from "./icons";
import { cn } from "@/lib/cn";

export function LanguageSwitcher() {
  const { locale, setLocale } = useI18n();
  const { user, setLanguage } = useAuth();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    return () => document.removeEventListener("mousedown", onClick);
  }, []);

  const current = LOCALES.find((l) => l.code === locale) ?? LOCALES[0];

  return (
    <div className="relative" ref={ref}>
      <button
        onClick={() => setOpen((o) => !o)}
        className="inline-flex items-center gap-1.5 rounded-xl border border-line bg-white px-2.5 py-2 text-sm font-semibold text-ink-soft transition hover:bg-canvas"
      >
        <Icon.Globe className="h-4 w-4 text-muted" />
        {current.short}
        <Icon.ChevronDown className={cn("h-4 w-4 text-muted transition", open && "rotate-180")} />
      </button>
      {open ? (
        <div className="animate-fade-in absolute right-0 z-30 mt-1.5 w-40 overflow-hidden rounded-xl border border-line bg-white py-1 shadow-pop">
          {LOCALES.map((l) => (
            <button
              key={l.code}
              onClick={() => {
                setLocale(l.code);
                setOpen(false);
                if (user) setLanguage(l.code).catch(() => {});
              }}
              className={cn(
                "flex w-full items-center justify-between px-3 py-2 text-sm transition hover:bg-canvas",
                l.code === locale ? "font-bold text-brand-700" : "text-ink-soft",
              )}
            >
              {l.label}
              {l.code === locale ? <Icon.Check className="h-4 w-4 text-brand-600" /> : null}
            </button>
          ))}
        </div>
      ) : null}
    </div>
  );
}
