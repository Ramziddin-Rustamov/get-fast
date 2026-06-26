/**
 * Centralized admin API calls. Every function here corresponds 1:1 to an
 * endpoint in `API_CONTRACT.md`. UI code should call these rather than `api`
 * directly, so the contract stays in one place.
 */
import { api } from "./api";
import type {
  BalanceTransaction,
  BookingDetail,
  BookingListItem,
  Card,
  CompanyTransaction,
  DashboardStats,
  DriverStatus,
  LocationRef,
  Paginated,
  Payment,
  SupportMessage,
  Trip,
  UserDetail,
  UserListItem,
  Vehicle,
  WithdrawRequest,
} from "./types";

export interface UserListParams {
  search?: string;
  status?: DriverStatus | "";
  verified?: "1" | "0" | "";
  page?: number;
}

export interface UserPayload {
  first_name: string;
  last_name?: string;
  father_name?: string;
  phone: string;
  email?: string;
  password?: string;
  region_id?: number;
  district_id?: number;
  quarter_id?: number;
  home?: string;
}

export interface BalanceMovePayload {
  amount: number;
  note?: string;
}

export interface TransferPayload {
  card_id: number;
  amount: number;
}

/** Shared CRUD + money actions for a "user" resource (drivers / clients / admins). */
function userResource(base: string) {
  return {
    list: (params: UserListParams = {}) =>
      api.get<Paginated<UserListItem>>(base, {
        query: {
          search: params.search,
          status: params.status,
          verified: params.verified,
          page: params.page,
        },
      }),
    show: (id: number | string) => api.get<UserDetail>(`${base}/${id}`),
    create: (payload: UserPayload) => api.post<UserDetail>(base, payload),
    update: (id: number | string, payload: Partial<UserPayload>) =>
      api.put<UserDetail>(`${base}/${id}`, payload),
    remove: (id: number | string) => api.del<void>(`${base}/${id}`),

    updateStatus: (id: number | string, status: DriverStatus) =>
      api.post<void>(`${base}/${id}/status`, { status }),
    sendSms: (id: number | string, message: string) =>
      api.post<void>(`${base}/${id}/send-sms`, { message }),
    transfer: (id: number | string, payload: TransferPayload) =>
      api.post<void>(`${base}/${id}/transfer`, payload),
    addBalance: (id: number | string, payload: BalanceMovePayload) =>
      api.post<void>(`${base}/${id}/pay`, payload),
    deductBalance: (id: number | string, payload: BalanceMovePayload) =>
      api.post<void>(`${base}/${id}/withdraw`, payload),

    cards: (id: number | string) => api.get<Card[]>(`${base}/${id}/cards`),
    transactions: (id: number | string, page = 1) =>
      api.get<Paginated<BalanceTransaction>>(`${base}/${id}/transactions`, { query: { page } }),
  };
}

const driversBase = userResource("/admin/drivers");
const clientsBase = userResource("/admin/clients");

export const endpoints = {
  dashboard: {
    stats: () => api.get<DashboardStats>("/admin/dashboard"),
    companyTransactions: (page = 1) =>
      api.get<Paginated<CompanyTransaction>>("/admin/company/transactions", { query: { page } }),
  },

  drivers: {
    ...driversBase,
    vehicles: (id: number | string, page = 1) =>
      api.get<Paginated<Vehicle>>(`/admin/drivers/${id}/vehicles`, { query: { page } }),
    trips: (id: number | string, page = 1) =>
      api.get<Paginated<Trip>>(`/admin/drivers/${id}/trips`, { query: { page } }),
    documents: (id: number | string) =>
      api.get<{ images: { id: number; type: string; url: string }[] }>(
        `/admin/drivers/${id}/documents`,
      ),
  },

  clients: {
    ...clientsBase,
    verify: (id: number | string) => api.post<void>(`/admin/clients/${id}/verify`),
    bookings: (id: number | string, page = 1) =>
      api.get<Paginated<BookingListItem>>(`/admin/clients/${id}/bookings`, { query: { page } }),
  },

  admins: userResource("/admin/admins"),

  orders: {
    list: (params: { status?: string; date?: string; page?: number } = {}) =>
      api.get<Paginated<BookingListItem>>("/admin/orders", { query: params }),
    show: (id: number | string) => api.get<BookingDetail>(`/admin/orders/${id}`),
    cancelPassenger: (bookingId: number | string, passengerId: number | string) =>
      api.post<void>(`/admin/orders/${bookingId}/passengers/${passengerId}/cancel`),
  },

  withdrawals: {
    list: (params: { status?: string; page?: number } = {}) =>
      api.get<Paginated<WithdrawRequest>>("/admin/withdrawals", { query: params }),
    approve: (id: number | string) => api.post<void>(`/admin/withdrawals/${id}/approve`),
    reject: (id: number | string) => api.post<void>(`/admin/withdrawals/${id}/reject`),
  },

  payments: {
    list: (page = 1) => api.get<Paginated<Payment>>("/admin/payments", { query: { page } }),
    show: (id: number | string) => api.get<Payment>(`/admin/payments/${id}`),
  },

  support: {
    list: (page = 1) => api.get<Paginated<SupportMessage>>("/admin/support", { query: { page } }),
    show: (id: number | string) => api.get<SupportMessage>(`/admin/support/${id}`),
    answer: (id: number | string) => api.post<void>(`/admin/support/${id}/answer`),
    remove: (id: number | string) => api.del<void>(`/admin/support/${id}`),
  },

  locations: {
    regions: () => api.get<LocationRef[]>("/regions"),
    districts: (regionId: number | string) =>
      api.get<LocationRef[]>(`/districts/region/${regionId}`),
    quarters: (districtId: number | string) =>
      api.get<LocationRef[]>(`/quarters/districts/${districtId}`),
  },
};
