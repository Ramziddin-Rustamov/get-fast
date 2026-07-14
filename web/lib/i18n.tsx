"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import type { Locale, LocationRef } from "./types";

const LOCALE_KEY = "qadam_admin_locale";

type Dict = Record<string, string>;

/* eslint-disable @typescript-eslint/no-explicit-any */

const uz: Dict = {
  "app.name": "Qadam",
  "app.admin": "Admin panel",

  "nav.dashboard": "Boshqaruv paneli",
  "nav.drivers": "Haydovchilar",
  "nav.clients": "Mijozlar",
  "nav.admins": "Adminlar",
  "nav.orders": "Buyurtmalar",
  "nav.withdrawals": "Pul yechish",
  "nav.payments": "To'lovlar",
  "nav.transactions": "Tranzaksiyalar",
  "nav.support": "Murojaatlar",
  "nav.section.people": "Foydalanuvchilar",
  "nav.section.finance": "Moliya",
  "nav.section.ops": "Operatsiyalar",

  "action.logout": "Chiqish",
  "action.save": "Saqlash",
  "action.cancel": "Bekor qilish",
  "action.create": "Yaratish",
  "action.edit": "Tahrirlash",
  "action.delete": "O'chirish",
  "action.view": "Ko'rish",
  "action.search": "Qidirish",
  "action.filter": "Filtr",
  "action.confirm": "Tasdiqlash",
  "action.approve": "Tasdiqlash",
  "action.reject": "Rad etish",
  "action.send": "Yuborish",
  "action.close": "Yopish",
  "action.back": "Orqaga",
  "action.retry": "Qayta urinish",
  "action.refresh": "Yangilash",
  "action.send_sms": "SMS yuborish",
  "action.transfer": "Pul o'tkazish",
  "action.add_balance": "Balansga qo'shish",
  "action.deduct_balance": "Balansdan yechish",
  "action.mark_verified": "Tasdiqlangan deb belgilash",
  "action.mark_answered": "Javob berilgan deb belgilash",
  "action.change_status": "Holatni o'zgartirish",

  "common.loading": "Yuklanmoqda...",
  "common.saving": "Saqlanmoqda...",
  "common.empty": "Ma'lumot topilmadi",
  "common.error": "Xatolik yuz berdi",
  "common.none": "Yo'q",
  "common.yes": "Ha",
  "common.no": "Yo'q",
  "common.all": "Hammasi",
  "common.total": "Jami",
  "common.today": "Bugun",
  "common.amount": "Summa",
  "common.note": "Izoh",
  "common.optional": "ixtiyoriy",
  "common.required_field": "Bu maydon to'ldirilishi shart",
  "common.page_of": "{current} / {total} sahifa",
  "common.results": "{count} ta natija",
  "common.confirm_delete": "Haqiqatan ham o'chirmoqchimisiz?",
  "common.no_changes": "O'zgarish yo'q",

  "login.title": "Admin panelga kirish",
  "login.subtitle": "Telefon raqam va parol bilan kiring",
  "login.phone": "Telefon raqam",
  "login.password": "Parol",
  "login.submit": "Kirish",
  "login.signing_in": "Kirilmoqda...",
  "login.error_not_admin": "Bu hisob admin huquqiga ega emas",
  "login.error_invalid": "Telefon yoki parol noto'g'ri",

  "dashboard.title": "Boshqaruv paneli",
  "dashboard.subtitle": "Tizim ko'rsatkichlari sharhi",
  "dashboard.company_balance": "Kompaniya balansi",
  "dashboard.total_income": "Umumiy daromad",
  "dashboard.today_income": "Bugungi daromad",
  "dashboard.bookings": "Buyurtmalar",
  "dashboard.confirmed": "Tasdiqlangan",
  "dashboard.completed": "Yakunlangan",
  "dashboard.cancelled": "Bekor qilingan",
  "dashboard.clients": "Mijozlar",
  "dashboard.drivers": "Haydovchilar",
  "dashboard.active_users": "Faol foydalanuvchilar",
  "dashboard.inactive_users": "Nofaol foydalanuvchilar",
  "dashboard.cards": "Kartalar",
  "dashboard.transactions": "Tranzaksiyalar",
  "dashboard.drivers_breakdown": "Haydovchilar holati",

  "user.name": "Ism",
  "user.full_name": "F.I.Sh.",
  "user.first_name": "Ism",
  "user.last_name": "Familiya",
  "user.father_name": "Otasining ismi",
  "user.phone": "Telefon",
  "user.email": "Email",
  "user.balance": "Balans",
  "user.role": "Rol",
  "user.status": "Holat",
  "user.verified": "Tasdiqlangan",
  "user.unverified": "Tasdiqlanmagan",
  "user.region": "Viloyat",
  "user.district": "Tuman",
  "user.quarter": "Mahalla",
  "user.home": "Manzil",
  "user.joined": "Ro'yxatdan o'tgan",
  "user.password": "Parol",
  "user.password_hint": "Kamida 6 belgi",

  "drivers.title": "Haydovchilar",
  "drivers.new": "Yangi haydovchi",
  "drivers.search_placeholder": "Ism yoki telefon bo'yicha qidirish",
  "drivers.documents": "Hujjatlar",
  "drivers.vehicles": "Avtomobillar",
  "drivers.trips": "Sayohatlar",

  "clients.title": "Mijozlar",
  "clients.new": "Yangi mijoz",
  "clients.search_placeholder": "Ism yoki telefon bo'yicha qidirish",
  "clients.bookings": "Buyurtmalar",

  "admins.title": "Adminlar",
  "admins.new": "Yangi admin",

  "orders.title": "Buyurtmalar",
  "orders.passengers": "Yo'lovchilar",
  "orders.seats": "O'rindiqlar",
  "orders.price": "Narx",
  "orders.route": "Yo'nalish",
  "orders.date_filter": "Sana",
  "orders.filter.today": "Bugun",
  "orders.filter.week": "Shu hafta",
  "orders.filter.last_week": "O'tgan hafta",

  "withdrawals.title": "Pul yechish so'rovlari",
  "withdrawals.card_holder": "Karta egasi",
  "withdrawals.approve_confirm": "So'rovni tasdiqlaysizmi? Mablag' foydalanuvchi balansidan yechiladi.",
  "withdrawals.reject_confirm": "So'rovni rad etasizmi?",

  "payments.title": "To'lovlar",
  "payments.method": "Usul",
  "payments.pay_id": "To'lov ID",

  "transactions.title": "Kompaniya tranzaksiyalari",
  "transactions.incoming": "Kirim",
  "transactions.outgoing": "Chiqim",
  "transactions.balance_after": "Qoldiq",

  "support.title": "Murojaatlar",
  "support.message": "Xabar",
  "support.from": "Kimdan",
  "support.answered": "Javob berilgan",
  "support.pending": "Kutilmoqda",

  "status.none": "Belgilanmagan",
  "status.pending": "Kutilmoqda",
  "status.approved": "Tasdiqlangan",
  "status.rejected": "Rad etilgan",
  "status.blocked": "Bloklangan",
  "status.active": "Faol",
  "status.completed": "Yakunlangan",
  "status.cancelled": "Bekor qilingan",
  "status.expired": "Muddati o'tgan",
  "status.full": "To'lgan",
  "status.confirmed": "Tasdiqlangan",
  "status.answered": "Javob berilgan",
  "status.closed": "Yopilgan",

  "form.sms_message": "SMS matni",
  "form.sms_hint": "Maksimal 255 belgi",
  "form.select_card": "Kartani tanlang",
  "form.transfer_amount": "O'tkazma summasi",
  "form.new_status": "Yangi holat",
  "toast.saved": "Saqlandi",
  "toast.deleted": "O'chirildi",
  "toast.sms_sent": "SMS yuborildi",
  "toast.status_updated": "Holat yangilandi",
  "toast.transferred": "Mablag' o'tkazildi",
  "toast.approved": "Tasdiqlandi",
  "toast.rejected": "Rad etildi",
};

const ru: Dict = {
  "app.name": "Qadam",
  "app.admin": "Админ-панель",

  "nav.dashboard": "Панель",
  "nav.drivers": "Водители",
  "nav.clients": "Клиенты",
  "nav.admins": "Администраторы",
  "nav.orders": "Заказы",
  "nav.withdrawals": "Вывод средств",
  "nav.payments": "Платежи",
  "nav.transactions": "Транзакции",
  "nav.support": "Обращения",
  "nav.section.people": "Пользователи",
  "nav.section.finance": "Финансы",
  "nav.section.ops": "Операции",

  "action.logout": "Выйти",
  "action.save": "Сохранить",
  "action.cancel": "Отмена",
  "action.create": "Создать",
  "action.edit": "Редактировать",
  "action.delete": "Удалить",
  "action.view": "Просмотр",
  "action.search": "Поиск",
  "action.filter": "Фильтр",
  "action.confirm": "Подтвердить",
  "action.approve": "Одобрить",
  "action.reject": "Отклонить",
  "action.send": "Отправить",
  "action.close": "Закрыть",
  "action.back": "Назад",
  "action.retry": "Повторить",
  "action.refresh": "Обновить",
  "action.send_sms": "Отправить SMS",
  "action.transfer": "Перевод",
  "action.add_balance": "Пополнить баланс",
  "action.deduct_balance": "Списать с баланса",
  "action.mark_verified": "Отметить подтверждённым",
  "action.mark_answered": "Отметить отвеченным",
  "action.change_status": "Изменить статус",

  "common.loading": "Загрузка...",
  "common.saving": "Сохранение...",
  "common.empty": "Данные не найдены",
  "common.error": "Произошла ошибка",
  "common.none": "Нет",
  "common.yes": "Да",
  "common.no": "Нет",
  "common.all": "Все",
  "common.total": "Всего",
  "common.today": "Сегодня",
  "common.amount": "Сумма",
  "common.note": "Примечание",
  "common.optional": "необязательно",
  "common.required_field": "Это поле обязательно",
  "common.page_of": "Страница {current} из {total}",
  "common.results": "Найдено: {count}",
  "common.confirm_delete": "Вы действительно хотите удалить?",
  "common.no_changes": "Нет изменений",

  "login.title": "Вход в админ-панель",
  "login.subtitle": "Войдите с номером телефона и паролем",
  "login.phone": "Номер телефона",
  "login.password": "Пароль",
  "login.submit": "Войти",
  "login.signing_in": "Вход...",
  "login.error_not_admin": "Эта учётная запись не имеет прав администратора",
  "login.error_invalid": "Неверный телефон или пароль",

  "dashboard.title": "Панель управления",
  "dashboard.subtitle": "Обзор показателей системы",
  "dashboard.company_balance": "Баланс компании",
  "dashboard.total_income": "Общий доход",
  "dashboard.today_income": "Доход за сегодня",
  "dashboard.bookings": "Заказы",
  "dashboard.confirmed": "Подтверждено",
  "dashboard.completed": "Завершено",
  "dashboard.cancelled": "Отменено",
  "dashboard.clients": "Клиенты",
  "dashboard.drivers": "Водители",
  "dashboard.active_users": "Активные пользователи",
  "dashboard.inactive_users": "Неактивные пользователи",
  "dashboard.cards": "Карты",
  "dashboard.transactions": "Транзакции",
  "dashboard.drivers_breakdown": "Статусы водителей",

  "user.name": "Имя",
  "user.full_name": "ФИО",
  "user.first_name": "Имя",
  "user.last_name": "Фамилия",
  "user.father_name": "Отчество",
  "user.phone": "Телефон",
  "user.email": "Email",
  "user.balance": "Баланс",
  "user.role": "Роль",
  "user.status": "Статус",
  "user.verified": "Подтверждён",
  "user.unverified": "Не подтверждён",
  "user.region": "Область",
  "user.district": "Район",
  "user.quarter": "Махалля",
  "user.home": "Адрес",
  "user.joined": "Регистрация",
  "user.password": "Пароль",
  "user.password_hint": "Минимум 6 символов",

  "drivers.title": "Водители",
  "drivers.new": "Новый водитель",
  "drivers.search_placeholder": "Поиск по имени или телефону",
  "drivers.documents": "Документы",
  "drivers.vehicles": "Автомобили",
  "drivers.trips": "Поездки",

  "clients.title": "Клиенты",
  "clients.new": "Новый клиент",
  "clients.search_placeholder": "Поиск по имени или телефону",
  "clients.bookings": "Заказы",

  "admins.title": "Администраторы",
  "admins.new": "Новый администратор",

  "orders.title": "Заказы",
  "orders.passengers": "Пассажиры",
  "orders.seats": "Места",
  "orders.price": "Цена",
  "orders.route": "Маршрут",
  "orders.date_filter": "Дата",
  "orders.filter.today": "Сегодня",
  "orders.filter.week": "Эта неделя",
  "orders.filter.last_week": "Прошлая неделя",

  "withdrawals.title": "Запросы на вывод",
  "withdrawals.card_holder": "Держатель карты",
  "withdrawals.approve_confirm": "Одобрить запрос? Сумма будет списана с баланса пользователя.",
  "withdrawals.reject_confirm": "Отклонить запрос?",

  "payments.title": "Платежи",
  "payments.method": "Способ",
  "payments.pay_id": "ID платежа",

  "transactions.title": "Транзакции компании",
  "transactions.incoming": "Поступление",
  "transactions.outgoing": "Списание",
  "transactions.balance_after": "Остаток",

  "support.title": "Обращения",
  "support.message": "Сообщение",
  "support.from": "От кого",
  "support.answered": "Отвечено",
  "support.pending": "Ожидает",

  "status.none": "Не задан",
  "status.pending": "Ожидает",
  "status.approved": "Одобрен",
  "status.rejected": "Отклонён",
  "status.blocked": "Заблокирован",
  "status.active": "Активен",
  "status.completed": "Завершён",
  "status.cancelled": "Отменён",
  "status.expired": "Истёк",
  "status.full": "Заполнен",
  "status.confirmed": "Подтверждён",
  "status.answered": "Отвечено",
  "status.closed": "Закрыт",

  "form.sms_message": "Текст SMS",
  "form.sms_hint": "Максимум 255 символов",
  "form.select_card": "Выберите карту",
  "form.transfer_amount": "Сумма перевода",
  "form.new_status": "Новый статус",
  "toast.saved": "Сохранено",
  "toast.deleted": "Удалено",
  "toast.sms_sent": "SMS отправлено",
  "toast.status_updated": "Статус обновлён",
  "toast.transferred": "Средства переведены",
  "toast.approved": "Одобрено",
  "toast.rejected": "Отклонено",
};

const en: Dict = {
  "app.name": "Qadam",
  "app.admin": "Admin panel",

  "nav.dashboard": "Dashboard",
  "nav.drivers": "Drivers",
  "nav.clients": "Clients",
  "nav.admins": "Admins",
  "nav.orders": "Orders",
  "nav.withdrawals": "Withdrawals",
  "nav.payments": "Payments",
  "nav.transactions": "Transactions",
  "nav.support": "Support",
  "nav.section.people": "People",
  "nav.section.finance": "Finance",
  "nav.section.ops": "Operations",

  "action.logout": "Log out",
  "action.save": "Save",
  "action.cancel": "Cancel",
  "action.create": "Create",
  "action.edit": "Edit",
  "action.delete": "Delete",
  "action.view": "View",
  "action.search": "Search",
  "action.filter": "Filter",
  "action.confirm": "Confirm",
  "action.approve": "Approve",
  "action.reject": "Reject",
  "action.send": "Send",
  "action.close": "Close",
  "action.back": "Back",
  "action.retry": "Retry",
  "action.refresh": "Refresh",
  "action.send_sms": "Send SMS",
  "action.transfer": "Transfer",
  "action.add_balance": "Add to balance",
  "action.deduct_balance": "Deduct from balance",
  "action.mark_verified": "Mark verified",
  "action.mark_answered": "Mark answered",
  "action.change_status": "Change status",

  "common.loading": "Loading...",
  "common.saving": "Saving...",
  "common.empty": "No data found",
  "common.error": "Something went wrong",
  "common.none": "None",
  "common.yes": "Yes",
  "common.no": "No",
  "common.all": "All",
  "common.total": "Total",
  "common.today": "Today",
  "common.amount": "Amount",
  "common.note": "Note",
  "common.optional": "optional",
  "common.required_field": "This field is required",
  "common.page_of": "Page {current} of {total}",
  "common.results": "{count} results",
  "common.confirm_delete": "Are you sure you want to delete?",
  "common.no_changes": "No changes",

  "login.title": "Sign in to admin",
  "login.subtitle": "Sign in with your phone and password",
  "login.phone": "Phone number",
  "login.password": "Password",
  "login.submit": "Sign in",
  "login.signing_in": "Signing in...",
  "login.error_not_admin": "This account does not have admin access",
  "login.error_invalid": "Invalid phone or password",

  "dashboard.title": "Dashboard",
  "dashboard.subtitle": "Overview of system metrics",
  "dashboard.company_balance": "Company balance",
  "dashboard.total_income": "Total income",
  "dashboard.today_income": "Today's income",
  "dashboard.bookings": "Bookings",
  "dashboard.confirmed": "Confirmed",
  "dashboard.completed": "Completed",
  "dashboard.cancelled": "Cancelled",
  "dashboard.clients": "Clients",
  "dashboard.drivers": "Drivers",
  "dashboard.active_users": "Active users",
  "dashboard.inactive_users": "Inactive users",
  "dashboard.cards": "Cards",
  "dashboard.transactions": "Transactions",
  "dashboard.drivers_breakdown": "Driver statuses",

  "user.name": "Name",
  "user.full_name": "Full name",
  "user.first_name": "First name",
  "user.last_name": "Last name",
  "user.father_name": "Father's name",
  "user.phone": "Phone",
  "user.email": "Email",
  "user.balance": "Balance",
  "user.role": "Role",
  "user.status": "Status",
  "user.verified": "Verified",
  "user.unverified": "Unverified",
  "user.region": "Region",
  "user.district": "District",
  "user.quarter": "Quarter",
  "user.home": "Address",
  "user.joined": "Joined",
  "user.password": "Password",
  "user.password_hint": "At least 6 characters",

  "drivers.title": "Drivers",
  "drivers.new": "New driver",
  "drivers.search_placeholder": "Search by name or phone",
  "drivers.documents": "Documents",
  "drivers.vehicles": "Vehicles",
  "drivers.trips": "Trips",

  "clients.title": "Clients",
  "clients.new": "New client",
  "clients.search_placeholder": "Search by name or phone",
  "clients.bookings": "Bookings",

  "admins.title": "Admins",
  "admins.new": "New admin",

  "orders.title": "Orders",
  "orders.passengers": "Passengers",
  "orders.seats": "Seats",
  "orders.price": "Price",
  "orders.route": "Route",
  "orders.date_filter": "Date",
  "orders.filter.today": "Today",
  "orders.filter.week": "This week",
  "orders.filter.last_week": "Last week",

  "withdrawals.title": "Withdrawal requests",
  "withdrawals.card_holder": "Card holder",
  "withdrawals.approve_confirm": "Approve this request? The amount will be deducted from the user's balance.",
  "withdrawals.reject_confirm": "Reject this request?",

  "payments.title": "Payments",
  "payments.method": "Method",
  "payments.pay_id": "Payment ID",

  "transactions.title": "Company transactions",
  "transactions.incoming": "Incoming",
  "transactions.outgoing": "Outgoing",
  "transactions.balance_after": "Balance after",

  "support.title": "Support messages",
  "support.message": "Message",
  "support.from": "From",
  "support.answered": "Answered",
  "support.pending": "Pending",

  "status.none": "Unset",
  "status.pending": "Pending",
  "status.approved": "Approved",
  "status.rejected": "Rejected",
  "status.blocked": "Blocked",
  "status.active": "Active",
  "status.completed": "Completed",
  "status.cancelled": "Cancelled",
  "status.expired": "Expired",
  "status.full": "Full",
  "status.confirmed": "Confirmed",
  "status.answered": "Answered",
  "status.closed": "Closed",

  "form.sms_message": "SMS text",
  "form.sms_hint": "Max 255 characters",
  "form.select_card": "Select a card",
  "form.transfer_amount": "Transfer amount",
  "form.new_status": "New status",
  "toast.saved": "Saved",
  "toast.deleted": "Deleted",
  "toast.sms_sent": "SMS sent",
  "toast.status_updated": "Status updated",
  "toast.transferred": "Funds transferred",
  "toast.approved": "Approved",
  "toast.rejected": "Rejected",
};

const DICTS: Record<Locale, Dict> = { uz, ru, en };

export const LOCALES: { code: Locale; label: string; short: string }[] = [
  { code: "uz", label: "O'zbekcha", short: "UZ" },
  { code: "ru", label: "Русский", short: "RU" },
  { code: "en", label: "English", short: "EN" },
];

type TFunc = (key: string, vars?: Record<string, string | number>) => string;

interface I18nValue {
  locale: Locale;
  setLocale: (l: Locale) => void;
  t: TFunc;
  /** Pick the localized name from a region/district/quarter ref. */
  ln: (ref: LocationRef | null | undefined) => string;
}

const I18nContext = createContext<I18nValue | null>(null);

export function I18nProvider({ children }: { children: React.ReactNode }) {
  const [locale, setLocaleState] = useState<Locale>("uz");

  useEffect(() => {
    const stored = window.localStorage.getItem(LOCALE_KEY) as Locale | null;
    if (stored && DICTS[stored]) setLocaleState(stored);
  }, []);

  const setLocale = useCallback((l: Locale) => {
    setLocaleState(l);
    window.localStorage.setItem(LOCALE_KEY, l);
    document.documentElement.lang = l;
  }, []);

  const t = useCallback<TFunc>(
    (key, vars) => {
      let str = DICTS[locale][key] ?? DICTS.en[key] ?? key;
      if (vars) {
        for (const [k, v] of Object.entries(vars)) {
          str = str.replace(new RegExp(`\\{${k}\\}`, "g"), String(v));
        }
      }
      return str;
    },
    [locale],
  );

  const ln = useCallback<I18nValue["ln"]>(
    (ref) => {
      if (!ref) return "—";
      const byLocale =
        locale === "ru" ? ref.name_ru : locale === "en" ? ref.name_en : ref.name_uz;
      return byLocale || ref.name_uz || ref.name || ref.name_en || ref.name_ru || "—";
    },
    [locale],
  );

  const value = useMemo<I18nValue>(() => ({ locale, setLocale, t, ln }), [locale, setLocale, t, ln]);

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n(): I18nValue {
  const ctx = useContext(I18nContext);
  if (!ctx) throw new Error("useI18n must be used within I18nProvider");
  return ctx;
}
