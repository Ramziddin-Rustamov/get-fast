"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import {
  ApiError,
  api,
  getToken,
  setToken,
  UNAUTHORIZED_EVENT,
} from "./api";
import type { AuthTokens, AuthUser, Locale } from "./types";

interface AuthState {
  user: AuthUser | null;
  /** True while we resolve the existing token on first load. */
  loading: boolean;
  login: (phone: string, password: string) => Promise<void>;
  logout: () => void;
  setLanguage: (language: Locale) => Promise<void>;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  const logout = useCallback(() => {
    // Best-effort server logout, but clear locally regardless.
    api.post("/auth/logout").catch(() => {});
    setToken(null);
    setUser(null);
  }, []);

  // Resolve an existing token on mount.
  useEffect(() => {
    let cancelled = false;
    async function bootstrap() {
      if (!getToken()) {
        setLoading(false);
        return;
      }
      try {
        const me = await api.get<AuthUser>("/auth/me");
        if (!cancelled) setUser(me);
      } catch {
        if (!cancelled) {
          setToken(null);
          setUser(null);
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    }
    bootstrap();
    return () => {
      cancelled = true;
    };
  }, []);

  // Global 401 handler -> drop the session.
  useEffect(() => {
    function onUnauthorized() {
      setToken(null);
      setUser(null);
    }
    window.addEventListener(UNAUTHORIZED_EVENT, onUnauthorized);
    return () => window.removeEventListener(UNAUTHORIZED_EVENT, onUnauthorized);
  }, []);

  const login = useCallback(async (phone: string, password: string) => {
    const tokens = await api.post<AuthTokens>("/auth/login", { phone, password });
    setToken(tokens.access_token);
    let me: AuthUser;
    try {
      me = await api.get<AuthUser>("/auth/me");
    } catch (err) {
      setToken(null);
      throw err;
    }
    if (me.role !== "admin") {
      setToken(null);
      throw new ApiError(403, { message: "NOT_ADMIN" });
    }
    setUser(me);
  }, []);

  const setLanguage = useCallback(async (language: Locale) => {
    await api.post("/auth/update-user-language", { language });
  }, []);

  const value = useMemo<AuthState>(
    () => ({ user, loading, login, logout, setLanguage }),
    [user, loading, login, logout, setLanguage],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
