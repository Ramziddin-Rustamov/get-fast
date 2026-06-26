# Qadam — Admin Panel (Next.js)

A modern, mobile-friendly rebuild of the **Qadam / ketamiz** ride-sharing admin panel.
It is a single-page admin that talks **directly to the Laravel API** with a JWT bearer token.

- **Stack:** Next.js 16 (App Router) · React 19 · Tailwind CSS v4 · TypeScript. **Zero extra
  runtime dependencies** — data fetching, toasts, modals, icons and i18n are all hand-rolled.
- **Languages:** Uzbek (default), Russian, English — switchable live, persisted per browser and
  synced to the user via `/auth/update-user-language`.
- **Brand:** the existing teal → sky (`#16d39a → #0ea5e9`) identity, refreshed.

## Setup

```bash
cd web
cp .env.local .env.local   # already present — points NEXT_PUBLIC_API_BASE_URL at the API
npm install                # only needed once
npm run dev                # http://localhost:3000
```

`.env.local`:
```
NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

> ⚠️ **Backend required.** The admin JSON API does **not exist yet** — the current Laravel admin
> is Blade + sessions. Implement the endpoints in [`API_CONTRACT.md`](./API_CONTRACT.md) under
> `/api/v1/admin/*` (JWT `auth:api` + an `admin` role check). Until then the SPA renders but every
> data call returns 401/404. Sign-in itself works against the existing `/auth/login` + `/auth/me`.

> ⚠️ **Build prerequisite (this machine):** the `C:` drive is full, and Node/SWC/`next/font` use
> `C:`'s temp dir. Free space on `C:` before running `npm install` / `npm run dev` / `npm run build`,
> or builds will fail with `ENOSPC` / "No space left on device". The source itself lives on `D:`.

## How it works

- **Auth** (`lib/auth.tsx`): `POST /auth/login` → store JWT in `localStorage` → `GET /auth/me`;
  the session is rejected client-side unless `role === "admin"`. A global 401 listener logs out.
- **API client** (`lib/api.ts`): tiny `fetch` wrapper that attaches the bearer token, normalizes
  errors into `ApiError`, and broadcasts 401s.
- **Endpoints** (`lib/endpoints.ts`): every backend call in one place, 1:1 with the contract.
- **Data** (`lib/hooks.ts`): `useQuery` / `useMutation` / `useDebounced` — no react-query needed.
- **i18n** (`lib/i18n.tsx`): `useI18n().t(key)` + `ln(locationRef)` for localized region names.

## Structure

```
app/
  layout.tsx                root layout + fonts + providers
  page.tsx                  → redirect to /dashboard
  login/                    public login (split brand panel)
  (dashboard)/              auth-guarded shell (sidebar + topbar)
    layout.tsx              guard + responsive shell
    dashboard/              stats overview
    drivers/  clients/  admins/        list · create · [id] · [id]/edit
    orders/   withdrawals/ support/    list + detail/actions
    payments/ transactions/            read-only ledgers
components/                 UI kit (ui, data, Modal, StatCard, status, nav, shell, user-actions…)
lib/                        api, auth, i18n, hooks, endpoints, types, format, toast
API_CONTRACT.md             the backend spec to implement
```

## Notes for the backend team

Read `API_CONTRACT.md`. Drivers and clients share one shape — a single `UserResource` +
controller trait covers both. List endpoints must return `{ data, meta:{current_page,last_page,
per_page,total} }`. All money is integer UZS. CORS must allow the admin origin with the
`Authorization` header.
