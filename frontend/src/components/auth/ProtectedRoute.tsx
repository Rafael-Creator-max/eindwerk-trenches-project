'use client';

import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';

export default function ProtectedRoute({
  children,
  requireAuth = true,
  redirectTo = '/login',
}: {
  children: React.ReactNode;
  requireAuth?: boolean;
  redirectTo?: string;
}) {
  const { isAuthenticated, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading) {
      if (requireAuth && !isAuthenticated) {
        router.push(redirectTo);
      } else if (!requireAuth && isAuthenticated) {
        router.push('/dashboard');
      }
    }
  }, [isAuthenticated, loading, requireAuth, redirectTo, router]);

  if (loading || (requireAuth && !isAuthenticated) || (!requireAuth && isAuthenticated)) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  return <>{children}</>;
}
