'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

export default function Home() {
  const { isAuthenticated, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    // Only redirect if we're done loading
    if (!loading) {
      if (isAuthenticated) {
        console.log('User is authenticated, redirecting to dashboard');
        router.replace('/dashboard');
      } else {
        console.log('User is not authenticated, redirecting to login');
        router.replace('/login');
      }
    }
  }, [isAuthenticated, loading, router]);

  // Show loading state
  if (loading || isAuthenticated === undefined) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  // This will be quickly replaced by the useEffect redirect
  return null;
}
