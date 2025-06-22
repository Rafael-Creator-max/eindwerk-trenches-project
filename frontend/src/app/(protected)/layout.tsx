'use client';

import { useEffect, useState } from 'react';
import { usePathname, useSearchParams, useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

export default function ProtectedLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { isAuthenticated, loading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [initialLoad, setInitialLoad] = useState(true);

  useEffect(() => {
    // Skip the initial mount effect
    if (initialLoad) {
      setInitialLoad(false);
      return;
    }

    // Only redirect if we're not already on the login page
    if (!loading && !isAuthenticated && !pathname.startsWith('/login')) {
      console.log('ProtectedLayout - User not authenticated, redirecting to login...');
      const returnUrl = pathname + (searchParams?.toString() ? `?${searchParams.toString()}` : '');
      router.replace(`/login?from=${encodeURIComponent(returnUrl || '/dashboard')}`);
    }
  }, [isAuthenticated, loading, router, pathname, searchParams, initialLoad]);

  // Show loading state while checking auth
  if (loading || (!isAuthenticated && !initialLoad)) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  // Only render children if authenticated
  return isAuthenticated ? <>{children}</> : null;
}
