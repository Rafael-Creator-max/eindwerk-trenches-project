import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

export const useRequireAuth = (redirectUrl = '/login') => {
  const { isAuthenticated, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading && !isAuthenticated) {
      router.push(redirectUrl);
    }
  }, [isAuthenticated, loading, router, redirectUrl]);

  return { isAuthenticated, loading };
};
