"use client";

import { endpoints } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserForm } from "@/components/UserForm";

export default function CreateAdminPage() {
  const { t } = useI18n();
  return <UserForm resource={endpoints.admins} title={t("admins.new")} baseRoute="/admins" />;
}
