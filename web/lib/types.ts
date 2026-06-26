/**
 * Domain types for the Qadam admin API.
 *
 * These mirror the JSON contract documented in `API_CONTRACT.md`. The Laravel
 * backend should return exactly these shapes (snake_case fields) under
 * `/api/v1/admin/*`. All money values are integers in UZS (so'm).
 */

export type Locale = "uz" | "ru" | "en";

export type UserRole = "client" | "driver" | "admin";

export type DriverStatus =
  | "none"
  | "pending"
  | "approved"
  | "rejected"
  | "blocked";

export type TripStatus =
  | "active"
  | "completed"
  | "cancelled"
  | "expired"
  | "full";

export type BookingStatus = "pending" | "confirmed" | "completed" | "cancelled";

export type WithdrawStatus = "pending" | "approved" | "rejected";

export type SupportStatus = "pending" | "answered" | "closed";

export type TransactionType = "credit" | "debit";

export type CompanyTxnType = "incoming" | "outgoing";

/** Standard list envelope (Laravel paginator -> normalized). */
export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApiErrorBody {
  message: string;
  errors?: Record<string, string[]>;
}

/* ----------------------------- Auth ----------------------------- */

export interface AuthTokens {
  access_token: string;
  token_type?: string;
  expires_in?: number;
}

export interface LocationRef {
  id: number;
  name_uz?: string;
  name_ru?: string;
  name_en?: string;
  name?: string;
}

export interface AuthUser {
  id: number;
  first_name: string;
  last_name: string | null;
  father_name: string | null;
  email: string | null;
  phone: string;
  role: UserRole;
  image: string | null;
}

/* ----------------------------- Users ----------------------------- */

export interface UserBalance {
  balance: number;
  locked_balance?: number;
  currency?: string;
}

export interface UserListItem {
  id: number;
  first_name: string;
  last_name: string | null;
  father_name: string | null;
  phone: string;
  email: string | null;
  image: string | null;
  role: UserRole;
  is_verified: boolean;
  driving_verification_status: DriverStatus;
  balance: number;
  created_at: string;
}

export interface UserDetail extends UserListItem {
  region: LocationRef | null;
  district: LocationRef | null;
  quarter: LocationRef | null;
  home: string | null;
  birth_date: string | null;
  driving_licence_number: string | null;
  driving_licence_expiry: string | null;
  balance_detail: UserBalance | null;
  vehicles_count?: number;
  trips_count?: number;
  bookings_count?: number;
  updated_at: string;
}

/* --------------------------- Vehicles --------------------------- */

export interface VehicleImage {
  id: number;
  type: string;
  side: string;
  url: string;
}

export interface Color {
  id?: number;
  title_uz?: string;
  title_ru?: string;
  title_en?: string;
  code: string;
}

export interface Vehicle {
  id: number;
  model: string;
  car_number: string;
  tech_passport_number: string | null;
  seats: number;
  color: Color | null;
  vehicle_images: VehicleImage[];
  created_at: string;
}

/* ----------------------------- Trips ----------------------------- */

export interface Trip {
  id: number;
  start_region: string;
  end_region: string;
  start_district: string;
  end_district: string;
  start_quarter: string | null;
  end_quarter: string | null;
  start_time: string;
  end_time: string | null;
  price_per_seat: number;
  total_seats: number;
  available_seats: number;
  status: TripStatus;
  google_map_url: string | null;
  created_at: string;
}

/* --------------------------- Bookings --------------------------- */

export interface BookingPassenger {
  id: number;
  name: string;
  phone: string;
  status: string;
  latitude: number | null;
  longitude: number | null;
}

export interface BookingListItem {
  id: number;
  status: BookingStatus;
  seats_booked: number;
  total_price: number;
  created_at: string;
  trip: Pick<
    Trip,
    "id" | "start_region" | "end_region" | "start_time" | "status"
  > | null;
  user: Pick<AuthUser, "id" | "first_name" | "last_name" | "phone"> | null;
}

export interface BookingDetail extends BookingListItem {
  passengers: BookingPassenger[];
  trip: Trip | null;
}

/* ------------------------- Transactions ------------------------- */

export interface BalanceTransaction {
  id: number;
  type: TransactionType;
  amount: number;
  balance_before: number;
  balance_after: number;
  status: string;
  reason: string | null;
  trip_id: number | null;
  reference_id: string | null;
  created_at: string;
}

export interface CompanyTransaction {
  id: number;
  type: CompanyTxnType;
  amount: number;
  balance_before: number;
  balance_after: number;
  reason: string | null;
  trip_id: number | null;
  booking_id: number | null;
  created_at: string;
}

/* ---------------------------- Cards ----------------------------- */

export interface Card {
  id: number;
  number: string;
  expiry: string;
  label: string | null;
  phone: string | null;
  is_default: boolean;
  status: string;
}

/* --------------------------- Payments --------------------------- */

export interface Payment {
  id: number;
  amount: number;
  status: string;
  payment_method: string | null;
  pay_id: string | null;
  created_at: string;
  user: Pick<AuthUser, "id" | "first_name" | "last_name" | "phone"> | null;
  card: Pick<Card, "id" | "number"> | null;
}

/* ------------------------ Withdraw requests --------------------- */

export interface WithdrawRequest {
  id: number;
  role: UserRole;
  amount: number;
  card_id: number | null;
  card_holder: string | null;
  status: WithdrawStatus;
  created_at: string;
  user: Pick<AuthUser, "id" | "first_name" | "last_name" | "phone"> | null;
}

/* --------------------------- Support ---------------------------- */

export interface SupportMessage {
  id: number;
  name: string;
  email: string;
  message: string;
  status: SupportStatus;
  created_at: string;
}

/* -------------------------- Dashboard --------------------------- */

export interface DashboardStats {
  company_balance: number;
  total_income: number;
  today_income: number;
  total_bookings: number;
  confirmed_bookings: number;
  cancelled_bookings: number;
  completed_bookings: number;
  total_clients: number;
  total_drivers: number;
  drivers_approved: number;
  drivers_rejected: number;
  drivers_pending: number;
  drivers_blocked: number;
  active_users: number;
  inactive_users: number;
  total_cards: number;
  total_transactions: number;
}
