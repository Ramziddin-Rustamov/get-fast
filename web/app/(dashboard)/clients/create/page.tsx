"use client";

import { endpoints } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserForm } from "@/components/UserForm";

export default function CreateClientPage() {
  const { t } = useI18n();
  return <UserForm resource={endpoints.clients} title={t("clients.new")} baseRoute="/clients" />;
}
