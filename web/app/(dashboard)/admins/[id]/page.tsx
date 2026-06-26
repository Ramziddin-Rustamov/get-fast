"use client";

import { useCallback } from "react";
import { useParams, useRouter } from "next/navigation";
import { endpoints } from "@/lib/endpoints";
import { useQuery } from "@/lib/hooks";
import { useI18n } from "@/lib/i18n";
import type { UserDetail } from "@/lib/types";
import { Button, Card } from "@/components/ui";
import { FullScreenLoader, QueryState } from "@/components/data";
import { UserDetailHeader } from "@/components/UserDetailHeader";
import { Icon } from "@/components/icons";

export default function AdminDetailPage() {
  const { id } = useParams<{ id: string }>();
  const { t } = useI18n();
  const router = useRouter();

  const fetcher = useCallback(() => endpoints.admins.show(id), [id]);
  const { data: user, loading, error, refetch } = useQuery<UserDetail>(fetcher, [id]);

  if (loading) return <FullScreenLoader />;
  if (error || !user) {
    return (
      <Card>
        <QueryState loading={false} error={error} onRetry={refetch}>
          <div />
        </QueryState>
      </Card>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center">
        <Button
          variant="ghost"
          size="sm"
          onClick={() => router.push("/admins")}
          icon={<Icon.Chevron className="h-4 w-4 rotate-180" />}
        >
          {t("nav.admins")}
        </Button>
      </div>

      <UserDetailHeader
        user={user}
        resource={endpoints.admins}
        baseRoute="/admins"
        showStatus={false}
        showActions={false}
      />
    </div>
  );
}
