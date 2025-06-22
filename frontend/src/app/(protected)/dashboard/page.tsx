'use client';

import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import FollowedCryptos from '@/components/FollowedCryptos';
import CryptocurrencyTable from '@/components/CryptocurrencyTable';

type TabType = 'market' | 'watchlist';

export default function DashboardPage() {
  const { user, isAuthenticated, loading: authLoading, logout } = useAuth();
  const [activeTab, setActiveTab] = useState<TabType>('market');
  const router = useRouter();

  useEffect(() => {
    // If not loading and not authenticated, redirect to login
    if (!authLoading && !isAuthenticated) {
      console.log('Dashboard: Not authenticated, redirecting to login');
      router.replace(`/login?from=${encodeURIComponent('/dashboard')}`);
    }
  }, [isAuthenticated, authLoading, router]);

  // Show loading state while checking auth
  if (authLoading || !isAuthenticated) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
      <nav className="bg-white dark:bg-gray-800 shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="flex-shrink-0 flex items-center">
                <h1 className="text-xl font-bold text-gray-900 dark:text-white">Crypto Tracker</h1>
              </div>
            </div>
            <div className="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
              <div className="flex space-x-1 rounded-md bg-gray-100 dark:bg-gray-700 p-1">
                <button
                  onClick={() => setActiveTab('market')}
                  className={`px-4 py-2 text-sm font-medium rounded-md ${activeTab === 'market' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' 
                    : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'}`}
                >
                  Market
                </button>
                <button
                  onClick={() => setActiveTab('watchlist')}
                  className={`px-4 py-2 text-sm font-medium rounded-md ${activeTab === 'watchlist' 
                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' 
                    : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'}`}
                >
                  My Watchlist
                </button>
              </div>
              <div className="ml-3 relative">
                <div>
                  <button
                    type="button"
                    className="bg-white dark:bg-gray-700 rounded-full flex text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    id="user-menu"
                    aria-expanded="false"
                    aria-haspopup="true"
                  >
                    <span className="sr-only">Open user menu</span>
                    <div className="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                      {user?.name?.charAt(0).toUpperCase()}
                    </div>
                  </button>
                </div>
              </div>
              <button
                onClick={() => {
                  console.log('Logging out...');
                  logout().then(() => {
                    console.log('Logout successful, redirecting to home...');
                    router.push('/');
                  }).catch(error => {
                    console.error('Logout error:', error);
                  });
                }}
                className="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Sign out
              </button>
            </div>
          </div>
        </div>
      </nav>

      <header className="bg-white dark:bg-gray-800 shadow">
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
            {activeTab === 'market' ? 'Cryptocurrency Market' : 'My Watchlist'}
          </h1>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {activeTab === 'market' 
              ? 'Browse and track the latest cryptocurrency prices and market data.'
              : 'Your personalized list of followed cryptocurrencies.'}
          </p>
        </div>
      </header>

      <main className="py-6">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between">
              <div>
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {user?.name}!</h2>
                <p className="text-gray-600 dark:text-gray-300">
                  {activeTab === 'market' 
                    ? 'Explore the latest cryptocurrency market data.'
                    : 'Manage your cryptocurrency watchlist.'}
                </p>
              </div>
              <div className="mt-4 md:mt-0 flex space-x-2">
                <div className="sm:hidden flex rounded-md bg-gray-100 dark:bg-gray-700 p-1">
                  <button
                    onClick={() => setActiveTab('market')}
                    className={`px-3 py-1.5 text-xs font-medium rounded ${activeTab === 'market' 
                      ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' 
                      : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'}`}
                  >
                    Market
                  </button>
                  <button
                    onClick={() => setActiveTab('watchlist')}
                    className={`px-3 py-1.5 text-xs font-medium rounded ${activeTab === 'watchlist' 
                      ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow' 
                      : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'}`}
                  >
                    Watchlist
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div className="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div className="p-6">
              {activeTab === 'market' ? (
                <>
                  <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Cryptocurrency Market</h2>
                    <p className="text-gray-600 dark:text-gray-300">Browse and add cryptocurrencies to your watchlist.</p>
                  </div>
                  <CryptocurrencyTable />
                </>
              ) : (
                <>
                  <div className="mb-6">
                    <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Your Watchlist</h2>
                    <p className="text-gray-600 dark:text-gray-300">
                      {activeTab === 'watchlist' && 'Cryptocurrencies you\'re currently following.'}
                    </p>
                  </div>
                  <FollowedCryptos />
                </>
              )}
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
