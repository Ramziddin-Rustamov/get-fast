"use client";

import { useCallback } from "react";
import { endpoints, type UserListParams } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserListView } from "@/components/UserListView";
import { Icon } from "@/components/icons";

export default function AdminsPage() {
  const { t } = useI18n();
  const list = useCallback((p: UserListParams) => endpoints.admins.list(p), []);

  return (
    <UserListView
      title={t("admins.title")}
      newLabel={t("admins.new")}
      baseRoute="/admins"
      searchPlaceholder={t("drivers.search_placeholder")}
      filter="none"
      list={list}
      emptyIcon={<Icon.Admins className="h-6 w-6" />}
    />
  );
}
