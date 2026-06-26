"use client";

import { useParams } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useI18n } from "@/lib/i18n";
import { UserForm } from "@/components/UserForm";

export default function EditAdminPage() {
  const { t } = useI18n();
  const { id } = useParams<{ id: string }>();
  return (
    <UserForm
      resource={endpoints.admins}
      id={id}
      title={`${t("action.edit")} · ${t("nav.admins")}`}
      baseRoute="/admins"
    />
  );
}
