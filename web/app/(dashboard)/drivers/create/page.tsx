"use client";

import { endpoints } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserForm } from "@/components/UserForm";

export default function CreateDriverPage() {
  const { t } = useI18n();
  return <UserForm resource={endpoints.drivers} title={t("drivers.new")} baseRoute="/drivers" />;
}
