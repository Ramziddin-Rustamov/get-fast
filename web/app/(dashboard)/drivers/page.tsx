"use client";

import { useCallback } from "react";
import { endpoints, type UserListParams } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserListView } from "@/components/UserListView";
import { Icon } from "@/components/icons";

export default function DriversPage() {
  const { t } = useI18n();
  const list = useCallback((p: UserListParams) => endpoints.drivers.list(p), []);

  return (
    <UserListView
      title={t("drivers.title")}
      newLabel={t("drivers.new")}
      baseRoute="/drivers"
      searchPlaceholder={t("drivers.search_placeholder")}
      filter="status"
      list={list}
      emptyIcon={<Icon.Drivers className="h-6 w-6" />}
    />
  );
}
