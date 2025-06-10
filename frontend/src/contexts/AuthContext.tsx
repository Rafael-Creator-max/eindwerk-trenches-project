'use client';

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { User } from '@/lib/auth';
import * as auth from '@/lib/auth';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<User>;
  register: (name: string, email: string, password: string, password_confirmation: string) => Promise<User>;
  logout: () => Promise<void>;
  error: string | null;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    const loadUser = async () => {
      try {
        const currentUser = await auth.getCurrentUser();
        setUser(currentUser);
        setIsAuthenticated(!!currentUser);
      } catch (error) {
        console.error('Failed to load user', error);
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setLoading(false);
      }
    };

    loadUser();
  }, []);

  const login = async (email: string, password: string) => {
    setError(null);
    setLoading(true);
    try {
      const user = await auth.login(email, password);
      setUser(user);
      setIsAuthenticated(true);
      return user;
    } catch (error: any) {
      const errorMessage = error.message || 'Failed to log in';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const register = async (name: string, email: string, password: string, password_confirmation: string) => {
    setError(null);
    setLoading(true);
    try {
      const user = await auth.register(name, email, password, password_confirmation);
      setUser(user);
      setIsAuthenticated(true);
      return user;
    } catch (error: any) {
      const errorMessage = error.message || 'Failed to create an account';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      setLoading(true);
      await auth.logout();
      setUser(null);
      setIsAuthenticated(false);
    } catch (error: any) {
      console.error('Failed to log out', error);
      const errorMessage = error.message || 'Failed to log out';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  const value = {
    user,
    loading,
    login,
    register,
    logout,
    error,
    isAuthenticated,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
