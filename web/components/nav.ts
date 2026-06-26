import { Icon } from "./icons";

export interface NavItem {
  href: string;
  /** i18n key for the label. */
  labelKey: string;
  icon: keyof typeof Icon;
}

export interface NavSection {
  /** i18n key for the section heading (null = no heading). */
  titleKey: string | null;
  items: NavItem[];
}

export const NAV: NavSection[] = [
  {
    titleKey: null,
    items: [{ href: "/dashboard", labelKey: "nav.dashboard", icon: "Dashboard" }],
  },
  {
    titleKey: "nav.section.people",
    items: [
      { href: "/drivers", labelKey: "nav.drivers", icon: "Drivers" },
      { href: "/clients", labelKey: "nav.clients", icon: "Clients" },
      { href: "/admins", labelKey: "nav.admins", icon: "Admins" },
    ],
  },
  {
    titleKey: "nav.section.ops",
    items: [
      { href: "/orders", labelKey: "nav.orders", icon: "Orders" },
      { href: "/support", labelKey: "nav.support", icon: "Support" },
    ],
  },
  {
    titleKey: "nav.section.finance",
    items: [
      { href: "/withdrawals", labelKey: "nav.withdrawals", icon: "Withdraw" },
      { href: "/payments", labelKey: "nav.payments", icon: "Payments" },
      { href: "/transactions", labelKey: "nav.transactions", icon: "Transactions" },
    ],
  },
];
