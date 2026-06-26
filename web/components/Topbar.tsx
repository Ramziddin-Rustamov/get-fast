"use client";

import { useEffect, useRef, useState } from "react";
import { useAuth } from "@/lib/auth";
import { useI18n } from "@/lib/i18n";
import { fullName } from "@/lib/format";
import { Avatar } from "./ui";
import { Icon } from "./icons";
import { LanguageSwitcher } from "./LanguageSwitcher";
import { cn } from "@/lib/cn";

export function Topbar({ onMenu }: { onMenu: () => void }) {
  const { user, logout } = useAuth();
  const { t } = useI18n();
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", onClick);
    return () => document.removeEventListener("mousedown", onClick);
  }, []);

  return (
    <header className="sticky top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-line bg-white/80 px-4 backdrop-blur-md lg:px-6">
      <button
        onClick={onMenu}
        className="rounded-xl border border-line p-2 text-ink-soft transition hover:bg-canvas lg:hidden"
        aria-label="Menu"
      >
        <Icon.Menu className="h-5 w-5" />
      </button>

      <div className="ml-auto flex items-center gap-2.5">
        <LanguageSwitcher />
        <div className="relative" ref={ref}>
          <button
            onClick={() => setOpen((o) => !o)}
            className="flex items-center gap-2.5 rounded-xl border border-line bg-white py-1.5 pl-1.5 pr-2.5 transition hover:bg-canvas"
          >
            <Avatar name={fullName(user)} src={user?.image} size={32} />
            <span className="hidden text-sm font-semibold text-ink sm:block">
              {fullName(user)}
            </span>
            <Icon.ChevronDown
              className={cn("h-4 w-4 text-muted transition", open && "rotate-180")}
            />
          </button>
          {open ? (
            <div className="animate-fade-in absolute right-0 z-30 mt-1.5 w-52 overflow-hidden rounded-xl border border-line bg-white py-1 shadow-pop">
              <div className="border-b border-line px-3 py-2.5">
                <p className="truncate text-sm font-bold text-ink">{fullName(user)}</p>
                <p className="truncate text-xs text-muted">{user?.phone}</p>
              </div>
              <button
                onClick={logout}
                className="flex w-full items-center gap-2 px-3 py-2.5 text-sm font-semibold text-danger transition hover:bg-red-50"
              >
                <Icon.Logout className="h-4 w-4" />
                {t("action.logout")}
              </button>
            </div>
          ) : null}
        </div>
      </div>
    </header>
  );
}
