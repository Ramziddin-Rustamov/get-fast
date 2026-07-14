"use client";

import type { ReactNode } from "react";
import { cn } from "@/lib/cn";

export interface TabDef {
  key: string;
  label: ReactNode;
}

export function Tabs({
  tabs,
  active,
  onChange,
}: {
  tabs: TabDef[];
  active: string;
  onChange: (key: string) => void;
}) {
  return (
    <div className="flex gap-1 overflow-x-auto border-b border-line">
      {tabs.map((tab) => (
        <button
          key={tab.key}
          onClick={() => onChange(tab.key)}
          className={cn(
            "whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-semibold transition",
            active === tab.key
              ? "border-brand-500 text-brand-700"
              : "border-transparent text-muted hover:text-ink",
          )}
        >
          {tab.label}
        </button>
      ))}
    </div>
  );
}
