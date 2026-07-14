"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { NAV } from "./nav";
import { Icon } from "./icons";
import { useI18n } from "@/lib/i18n";
import { cn } from "@/lib/cn";

export function Brand() {
  return (
    <Link href="/dashboard" className="flex items-center gap-2.5">
      <span className="flex h-9 w-9 items-center justify-center rounded-xl brand-gradient font-display text-lg font-extrabold text-white shadow-card">
        Q
      </span>
      <span className="font-display text-xl font-extrabold text-ink">
        Qadam
        <span className="text-muted"> admin</span>
      </span>
    </Link>
  );
}

export function SidebarNav({ onNavigate }: { onNavigate?: () => void }) {
  const pathname = usePathname();
  const { t } = useI18n();

  return (
    <nav className="flex flex-col gap-5 px-3 py-4">
      {NAV.map((section, i) => (
        <div key={i}>
          {section.titleKey ? (
            <p className="px-3 pb-1.5 text-[11px] font-bold uppercase tracking-wider text-muted/80">
              {t(section.titleKey)}
            </p>
          ) : null}
          <ul className="space-y-0.5">
            {section.items.map((item) => {
              const ActiveIcon = Icon[item.icon];
              const active =
                pathname === item.href || pathname.startsWith(item.href + "/");
              return (
                <li key={item.href}>
                  <Link
                    href={item.href}
                    onClick={onNavigate}
                    className={cn(
                      "group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition",
                      active
                        ? "brand-gradient text-white shadow-card"
                        : "text-ink-soft hover:bg-canvas",
                    )}
                  >
                    <ActiveIcon
                      className={cn("h-5 w-5", active ? "text-white" : "text-muted")}
                    />
                    {t(item.labelKey)}
                  </Link>
                </li>
              );
            })}
          </ul>
        </div>
      ))}
    </nav>
  );
}
