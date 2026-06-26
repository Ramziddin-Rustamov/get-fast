# Qadam Admin — API Contract

The Next.js admin panel in `web/` calls the Laravel API **directly from the browser** with a
JWT bearer token. This document is the contract the backend must implement. It is the single
source of truth — the TypeScript types in `lib/types.ts` and the calls in `lib/endpoints.ts`
mirror it exactly.

> The client/driver mobile endpoints already exist. **Everything under `/admin/*` is new** and
> must be built (the current admin panel is Blade + sessions only).

## Conventions

- **Base URL:** `NEXT_PUBLIC_API_BASE_URL` (default `http://127.0.0.1:8000/api/v1`).
- **Auth:** `Authorization: Bearer <jwt>` on every `/admin/*` request. Reuse the existing
  `auth:api` (tymon/jwt) guard **plus** an admin check (`role === 'admin'`). Return **401** for a
  missing/expired token and **403** if the authenticated user is not an admin.
- **Money:** integers in UZS (so'm). No decimals, no formatting.
- **Dates:** ISO-8601 (`2026-06-26T10:00:00Z`) or SQL (`2026-06-26 10:00:00`) — both are accepted.
- **Validation errors:** HTTP **422** with `{ "message": string, "errors": { "field": ["msg"] } }`.
- **CORS:** the API must allow the admin origin (e.g. `http://localhost:3000`) with
  `Authorization` header and credentials not required (token is sent explicitly).

### List envelope

All list endpoints return a normalized paginator:

```json
{
  "data": [ /* items */ ],
  "meta": { "current_page": 1, "last_page": 5, "per_page": 20, "total": 92 }
}
```

If you keep Laravel's default `paginate()`, map `last_page`/`per_page`/`total`/`current_page` into
`meta` (a thin API Resource `collection` wrapper does this).

---

## Auth (already exists — reused as-is)

| Method | Path | Body | Returns |
| --- | --- | --- | --- |
| POST | `/auth/login` | `{ phone, password }` | `{ access_token, token_type?, expires_in? }` |
| GET | `/auth/me` | — | `AuthUser` |
| POST | `/auth/logout` | — | `204` |
| POST | `/auth/update-user-language` | `{ language: "uz"\|"ru"\|"en" }` | `204` |

`AuthUser`:
```json
{ "id": 1, "first_name": "Ali", "last_name": "Valiyev", "father_name": null,
  "email": null, "phone": "998901234567", "role": "admin", "image": null }
```
The admin SPA calls `/auth/me` after login and **rejects** the session client-side if
`role !== "admin"`. Returning a correct `role` is required.

---

## Dashboard

### `GET /admin/dashboard` → `DashboardStats`
```json
{
  "company_balance": 12500000, "total_income": 89000000, "today_income": 450000,
  "total_bookings": 1320, "confirmed_bookings": 210, "cancelled_bookings": 64,
  "completed_bookings": 1046, "total_clients": 5400, "total_drivers": 820,
  "drivers_approved": 540, "drivers_rejected": 30, "drivers_pending": 210,
  "drivers_blocked": 40, "active_users": 6100, "inactive_users": 120,
  "total_cards": 980, "total_transactions": 4300
}
```

### `GET /admin/company/transactions?page=` → `Paginated<CompanyTransaction>`
```json
{ "id": 9, "type": "incoming", "amount": 15000, "balance_before": 100000,
  "balance_after": 115000, "reason": "Booking #123", "trip_id": 55,
  "booking_id": 123, "created_at": "2026-06-26 10:00:00" }
```
`type`: `"incoming" | "outgoing"`.

---

## Drivers & Clients (shared shape)

Drivers live under `/admin/drivers`, clients under `/admin/clients`. **Identical request/response
shapes** except drivers filter by `status` (verification) and clients filter by `verified`.

### `GET /admin/{drivers|clients}` → `Paginated<UserListItem>`
Query: `search` (name or phone), `page`, and **one of**:
- drivers: `status` = `none|pending|approved|rejected|blocked`
- clients: `verified` = `1|0`

`UserListItem`:
```json
{ "id": 1, "first_name": "Ali", "last_name": "Valiyev", "father_name": null,
  "phone": "998901234567", "email": null, "image": null, "role": "driver",
  "is_verified": true, "driving_verification_status": "approved",
  "balance": 250000, "created_at": "2026-01-01 09:00:00" }
```

### `GET /admin/{drivers|clients}/{id}` → `UserDetail`
`UserListItem` + :
```json
{ "region": { "id": 1, "name_uz": "Toshkent", "name_ru": "Ташкент", "name_en": "Tashkent" },
  "district": { "id": 5, "name_uz": "...", "name_ru": "...", "name_en": "..." },
  "quarter": { "id": 12, "name": "Chilonzor" },
  "home": "12-uy", "birth_date": null,
  "driving_licence_number": null, "driving_licence_expiry": null,
  "balance_detail": { "balance": 250000, "locked_balance": 0, "currency": "UZS" },
  "vehicles_count": 2, "trips_count": 18, "bookings_count": 0,
  "updated_at": "2026-06-01 09:00:00" }
```

### `POST /admin/{drivers|clients}` → `UserDetail` (201)
Body (`UserPayload`): `first_name*`, `last_name`, `father_name`, `phone*` (unique),
`email`, `password*` (min 6), `region_id`, `district_id`, `quarter_id`, `home`.
For drivers, set `role = driver`; for clients, `role = client`.

### `PUT /admin/{drivers|clients}/{id}` → `UserDetail`
Same fields, all optional. `password` only changes when present.

### `DELETE /admin/{drivers|clients}/{id}` → `204`

### Actions (return `204`, or `422` on validation)
| Method | Path | Body | Effect |
| --- | --- | --- | --- |
| POST | `/admin/{drivers\|clients}/{id}/status` | `{ status }` | Set `driving_verification_status` (and `role` per existing logic). `status` ∈ `none\|pending\|approved\|rejected\|blocked`. Sends SMS as today. |
| POST | `/admin/{drivers\|clients}/{id}/send-sms` | `{ message }` (≤255) | Queue SMS via existing `SmsService`. |
| POST | `/admin/{drivers\|clients}/{id}/transfer` | `{ card_id, amount }` | Hamkorbank transfer to the user's card; debits user balance + company ledger (existing `refund()` logic). |
| POST | `/admin/{drivers\|clients}/{id}/pay` | `{ amount, note? }` | Credit user balance (existing `payToUserToBalance`). |
| POST | `/admin/{drivers\|clients}/{id}/withdraw` | `{ amount, note? }` | Debit user balance (existing `withdrawFromUser`). |
| GET | `/admin/{drivers\|clients}/{id}/cards` | — | `Card[]` — the user's saved cards (for the transfer dropdown). |
| GET | `/admin/{drivers\|clients}/{id}/transactions?page=` | — | `Paginated<BalanceTransaction>`. |

### Client-only
| Method | Path | Effect |
| --- | --- | --- |
| POST | `/admin/clients/{id}/verify` | Mark `is_verified = 1`. |
| GET | `/admin/clients/{id}/bookings?page=` | `Paginated<BookingListItem>`. |

### Driver-only
| Method | Path | Returns |
| --- | --- | --- |
| GET | `/admin/drivers/{id}/vehicles?page=` | `Paginated<Vehicle>` |
| GET | `/admin/drivers/{id}/trips?page=` | `Paginated<Trip>` |
| GET | `/admin/drivers/{id}/documents` | `{ "images": [{ "id", "type", "url" }] }` (passport / licence / etc.) |

`Card`:
```json
{ "id": 3, "number": "8600****1234", "expiry": "12/27", "label": "Humo",
  "phone": "998901234567", "is_default": true, "status": "verified" }
```

`BalanceTransaction`:
```json
{ "id": 1, "type": "credit", "amount": 15000, "balance_before": 0, "balance_after": 15000,
  "status": "success", "reason": "Top-up", "trip_id": null, "reference_id": null,
  "created_at": "2026-06-26 10:00:00" }
```
`type`: `"credit" | "debit"`.

`Vehicle`:
```json
{ "id": 1, "model": "Cobalt", "car_number": "01A123BC", "tech_passport_number": "AAA1234567",
  "seats": 4, "color": { "title_uz": "Oq", "code": "#ffffff" },
  "vehicle_images": [{ "id": 1, "type": "front", "side": "front", "url": "https://..." }],
  "created_at": "2026-01-01 09:00:00" }
```

`Trip`:
```json
{ "id": 55, "start_region": "Toshkent", "end_region": "Samarqand",
  "start_district": "Chilonzor", "end_district": "Markaz",
  "start_quarter": null, "end_quarter": null,
  "start_time": "2026-06-27 08:00:00", "end_time": "2026-06-27 12:00:00",
  "price_per_seat": 80000, "total_seats": 4, "available_seats": 2,
  "status": "active", "google_map_url": "https://maps.google.com/...",
  "created_at": "2026-06-26 09:00:00" }
```
`status`: `active|completed|cancelled|expired|full`. Localize region/district/quarter names
server-side based on the admin's language if you wish; the UI shows them verbatim.

---

## Admins

`/admin/admins` — same CRUD as above (`UserListItem` / `UserDetail` / `UserPayload`), `role = admin`.
No status/verify/money actions are used by the UI. Search by name/phone supported.

---

## Orders (bookings — read + passenger cancel)

### `GET /admin/orders` → `Paginated<BookingListItem>`
Query: `status` = `confirmed|completed|cancelled`, `date` = `today|week|last_week`, `page`.

`BookingListItem`:
```json
{ "id": 123, "status": "confirmed", "seats_booked": 2, "total_price": 160000,
  "created_at": "2026-06-26 10:00:00",
  "trip": { "id": 55, "start_region": "Toshkent", "end_region": "Samarqand",
            "start_time": "2026-06-27 08:00:00", "status": "active" },
  "user": { "id": 1, "first_name": "Ali", "last_name": "Valiyev", "phone": "998901234567" } }
```

### `GET /admin/orders/{id}` → `BookingDetail`
`BookingListItem` with the full `Trip` object and a `passengers` array:
```json
{ "id": 123, "status": "confirmed", "seats_booked": 2, "total_price": 160000,
  "created_at": "2026-06-26 10:00:00", "trip": { /* full Trip */ },
  "user": { "id": 1, "first_name": "Ali", "last_name": "Valiyev", "phone": "998901234567" },
  "passengers": [ { "id": 9, "name": "Hasan", "phone": "998901112233",
                    "status": "active", "latitude": null, "longitude": null } ] }
```

### `POST /admin/orders/{bookingId}/passengers/{passengerId}/cancel` → `204`
Cancel a single passenger (existing `cancelPassenger` logic: refund client minus service fee,
charge driver, SMS both). Sets that passenger's `status` to `cancelled`.

---

## Withdrawals

### `GET /admin/withdrawals` → `Paginated<WithdrawRequest>`
Query: `status` = `pending|approved|rejected`, `page`.
```json
{ "id": 7, "role": "driver", "amount": 200000, "card_id": 3, "card_holder": "ALI VALIYEV",
  "status": "pending", "created_at": "2026-06-26 10:00:00",
  "user": { "id": 1, "first_name": "Ali", "last_name": "Valiyev", "phone": "998901234567" } }
```
| Method | Path | Effect |
| --- | --- | --- |
| POST | `/admin/withdrawals/{id}/approve` | Existing approve flow (debit user, credit company). Only when `pending`. |
| POST | `/admin/withdrawals/{id}/reject` | Set `rejected`, no balance change. |

---

## Payments (read-only)

### `GET /admin/payments?page=` → `Paginated<Payment>`
```json
{ "id": 1, "amount": 160000, "status": "success", "payment_method": "card",
  "pay_id": "HB-998877", "created_at": "2026-06-26 10:00:00",
  "user": { "id": 1, "first_name": "Ali", "last_name": "Valiyev", "phone": "998901234567" },
  "card": { "id": 3, "number": "8600****1234" } }
```
(`GET /admin/payments/{id}` may return a single `Payment` — defined in the client but not yet used.)

---

## Support

### `GET /admin/support?page=` → `Paginated<SupportMessage>`
```json
{ "id": 1, "name": "Hasan", "email": "h@example.com", "message": "Salom...",
  "status": "pending", "created_at": "2026-06-26 10:00:00" }
```
`status`: `pending|answered|closed`.
| Method | Path | Effect |
| --- | --- | --- |
| GET | `/admin/support/{id}` | single `SupportMessage` |
| POST | `/admin/support/{id}/answer` | set `status = answered` |
| DELETE | `/admin/support/{id}` | delete |

---

## Shared location lookups (already exist, no admin auth needed)

| Method | Path | Returns |
| --- | --- | --- |
| GET | `/regions` | `LocationRef[]` (`{ id, name_uz, name_ru, name_en }`) |
| GET | `/districts/region/{regionId}` | `LocationRef[]` |
| GET | `/quarters/districts/{districtId}` | `LocationRef[]` (`{ id, name, district_id }`) |

Used by the create/edit forms for the region → district → quarter cascade.

---

## Endpoint checklist

```
GET    /admin/dashboard
GET    /admin/company/transactions
GET    /admin/drivers                         GET    /admin/clients
POST   /admin/drivers                         POST   /admin/clients
GET    /admin/drivers/{id}                    GET    /admin/clients/{id}
PUT    /admin/drivers/{id}                    PUT    /admin/clients/{id}
DELETE /admin/drivers/{id}                    DELETE /admin/clients/{id}
POST   /admin/drivers/{id}/status             POST   /admin/clients/{id}/status
POST   /admin/drivers/{id}/send-sms           POST   /admin/clients/{id}/send-sms
POST   /admin/drivers/{id}/transfer           POST   /admin/clients/{id}/transfer
POST   /admin/drivers/{id}/pay                POST   /admin/clients/{id}/pay
POST   /admin/drivers/{id}/withdraw           POST   /admin/clients/{id}/withdraw
GET    /admin/drivers/{id}/cards              GET    /admin/clients/{id}/cards
GET    /admin/drivers/{id}/transactions       GET    /admin/clients/{id}/transactions
GET    /admin/drivers/{id}/vehicles           POST   /admin/clients/{id}/verify
GET    /admin/drivers/{id}/trips              GET    /admin/clients/{id}/bookings
GET    /admin/drivers/{id}/documents
GET    /admin/admins  POST /admin/admins  GET/PUT/DELETE /admin/admins/{id}
GET    /admin/orders     GET /admin/orders/{id}
POST   /admin/orders/{bookingId}/passengers/{passengerId}/cancel
GET    /admin/withdrawals  POST /admin/withdrawals/{id}/approve  POST .../reject
GET    /admin/payments   (GET /admin/payments/{id})
GET    /admin/support    GET /admin/support/{id}  POST .../answer  DELETE /admin/support/{id}
```
