"use client";

import { useCallback } from "react";
import { endpoints, type UserListParams } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserListView } from "@/components/UserListView";
import { Icon } from "@/components/icons";

export default function ClientsPage() {
  const { t } = useI18n();
  const list = useCallback((p: UserListParams) => endpoints.clients.list(p), []);

  return (
    <UserListView
      title={t("clients.title")}
      newLabel={t("clients.new")}
      baseRoute="/clients"
      searchPlaceholder={t("clients.search_placeholder")}
      filter="verified"
      list={list}
      emptyIcon={<Icon.Clients className="h-6 w-6" />}
    />
  );
}
