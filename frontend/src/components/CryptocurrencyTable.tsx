'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';
import { FiArrowUp, FiArrowDown, FiRefreshCw, FiStar, FiPlus, FiCheck } from 'react-icons/fi';
import { useAuth } from '@/contexts/AuthContext';

interface Cryptocurrency {
  id: number;
  name: string;
  symbol: string;
  slug: string;
  current_price: number | string;
  price_change_24h: number | string;
  market_cap: number | string;
  volume_24h: number | string;
  image_url?: string;
  is_watchlisted?: boolean;
}

export default function CryptocurrencyTable() {
  const [cryptos, setCryptos] = useState<Cryptocurrency[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [lastUpdated, setLastUpdated] = useState<string>('');
  const { isAuthenticated } = useAuth();
  const router = useRouter();

  const fetchCryptos = useCallback(async (forceRefresh = false) => {
    try {
      setLoading(true);
      const response = await api.get('/cryptocurrencies', {
        params: { 
          _t: new Date().getTime(), // Prevent caching
          force_refresh: forceRefresh // Force refresh from the server
        }
      });
      
      // Check if user is authenticated and fetch watchlist
      if (isAuthenticated) {
        try {
          const watchlistResponse = await api.get('/user/cryptocurrencies');
          const watchlist = watchlistResponse.data.map((crypto: any) => crypto.id.toString());
          
          // Mark watchlisted cryptos
          const cryptosWithWatchlist = response.data.map((crypto: Cryptocurrency) => ({
            ...crypto,
            is_watchlisted: watchlist.includes(crypto.id.toString())
          }));
          
          setCryptos(cryptosWithWatchlist);
        } catch (err) {
          console.error('Error fetching watchlist:', err);
          setCryptos(response.data);
        }
      } else {
        setCryptos(response.data);
      }
      
      const now = new Date();
      setLastUpdated(now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      }));
    } catch (err) {
      setError('Failed to fetch cryptocurrencies');
      console.error('Error fetching cryptocurrencies:', err);
    } finally {
      setLoading(false);
    }
  }, [isAuthenticated]);

  useEffect(() => {
    // Initial fetch
    fetchCryptos();
    
    // Set up interval for auto-refresh (5 minutes)
    const intervalId = setInterval(() => {
      console.log('Auto-refreshing cryptocurrency data...');
      fetchCryptos();
    }, 5 * 60 * 1000); // 5 minutes in milliseconds
    
    // Clean up interval on component unmount
    return () => clearInterval(intervalId);
  }, [fetchCryptos]);

  const toggleWatchlist = async (cryptoId: number, isCurrentlyWatchlisted: boolean) => {
    if (!isAuthenticated) {
      router.push(`/login?from=${encodeURIComponent('/dashboard')}`);
      return;
    }

    try {
      if (isCurrentlyWatchlisted) {
        await api.delete(`/cryptocurrencies/${cryptoId}/follow`);
      } else {
        await api.post(`/cryptocurrencies/${cryptoId}/follow`);
      }
      
      // Update the local state to reflect the change
      setCryptos(prevCryptos => 
        prevCryptos.map(crypto => 
          crypto.id === cryptoId 
            ? { ...crypto, is_watchlisted: !isCurrentlyWatchlisted } 
            : crypto
        )
      );
    } catch (err) {
      console.error('Error updating watchlist:', err);
      setError('Failed to update watchlist');
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 8,
    }).format(value);
  };

  const formatMarketCap = (value: number) => {
    if (value >= 1e12) return `$${(value / 1e12).toFixed(2)}T`;
    if (value >= 1e9) return `$${(value / 1e9).toFixed(2)}B`;
    if (value >= 1e6) return `$${(value / 1e6).toFixed(2)}M`;
    return formatCurrency(value);
  };

  const formatPercentage = (value: number | string | null) => {
    const numValue = typeof value === 'string' ? parseFloat(value) : value;
    if (numValue === null || isNaN(numValue)) return 'N/A';
    const isPositive = numValue >= 0;
    const colorClass = isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    const ArrowIcon = isPositive ? FiArrowUp : FiArrowDown;
    return (
      <span className={`${colorClass} flex items-center justify-end gap-1`}>
        <ArrowIcon className="inline" /> {Math.abs(numValue).toFixed(2)}%
      </span>
    );
  };

  const getCryptoImageUrl = (symbol: string, name: string): string => {
    return `https://cryptoicons.org/api/icon/${symbol.toLowerCase()}/200`;
  };

  const getFallbackImageUrl = (symbol: string, name: string): string => {
    const symbolLower = symbol.toLowerCase();
    const nameLower = name.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    return `data:image/svg+xml;base64,${btoa(`
      <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
        <rect width="32" height="32" rx="16" fill="#f3f4f6"/>
        <text x="50%" y="50%" font-family="Arial" font-size="10" text-anchor="middle" dy=".3em" fill="#6b7280">
          ${symbol.slice(0, 3).toUpperCase()}
        </text>
      </svg>
    `)}`;
  };

  const filteredCryptos = cryptos.filter(crypto => 
    crypto.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    crypto.symbol.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (loading && cryptos.length === 0) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div className="relative w-full sm:w-64">
          <input
            type="text"
            placeholder="Search coins..."
            className="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <div className="absolute left-3 top-2.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        <div className="flex items-center text-sm text-gray-500 dark:text-gray-400">
          {lastUpdated && (
            <span className="mr-3">Last updated: {lastUpdated}</span>
          )}
          <button
            onClick={() => fetchCryptos(true)}
            disabled={loading}
            className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? (
              <FiRefreshCw className="animate-spin mr-1.5 h-3.5 w-3.5" />
            ) : (
              <FiRefreshCw className="mr-1.5 h-3.5 w-3.5" />
            )}
            Refresh
          </button>
        </div>
      </div>

      {error && (
        <div className="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
          <div className="flex">
            <div className="flex-shrink-0">
              <svg className="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="ml-3">
              <p className="text-sm text-red-700 dark:text-red-300">{error}</p>
            </div>
          </div>
        </div>
      )}

      {filteredCryptos.length === 0 ? (
        <div className="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow">
          <svg
            className="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={1}
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">No cryptocurrencies found</h3>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Try adjusting your search to find what you're looking for.
          </p>
        </div>
      ) : (
        <div className="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead className="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                  <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Coin</th>
                  <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                  <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">24h %</th>
                  <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Market Cap</th>
                  <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                {filteredCryptos.map((crypto, index) => (
                  <tr 
                    key={crypto.id} 
                    className="hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                      {index + 1}
                    </td>
                    <td 
                      className="px-6 py-4 whitespace-nowrap cursor-pointer"
                      onClick={() => router.push(`/cryptocurrencies/${crypto.slug}`)}
                    >
                      <div className="flex items-center">
                        <div className="relative flex-shrink-0 w-8 h-8 mr-3">
                          <img
                            src={crypto.image_url || getCryptoImageUrl(crypto.symbol, crypto.name)}
                            alt={crypto.name}
                            className="w-full h-full rounded-full object-cover bg-gray-100 dark:bg-gray-600"
                            onError={(e) => {
                              const target = e.target as HTMLImageElement;
                              target.onerror = null;
                              target.src = getFallbackImageUrl(crypto.symbol, crypto.name);
                            }}
                            loading="lazy"
                          />
                          {!crypto.image_url && (
                            <div className="absolute inset-0 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded-full text-xs font-medium text-gray-500 dark:text-gray-300">
                              {crypto.symbol.slice(0, 3).toUpperCase()}
                            </div>
                          )}
                        </div>
                        <div>
                          <div className="font-medium text-gray-900 dark:text-white">{crypto.name}</div>
                          <div className="text-sm text-gray-500 dark:text-gray-400">{crypto.symbol.toUpperCase()}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                      {formatCurrency(Number(crypto.current_price))}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {formatPercentage(Number(crypto.price_change_24h))}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                      {formatMarketCap(Number(crypto.market_cap))}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          toggleWatchlist(crypto.id, !!crypto.is_watchlisted);
                        }}
                        className={`inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium ${
                          crypto.is_watchlisted 
                            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' 
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        }`}
                        title={crypto.is_watchlisted ? 'Remove from watchlist' : 'Add to watchlist'}
                      >
                        {crypto.is_watchlisted ? (
                          <>
                            <FiStar className="mr-1.5 h-4 w-4" />
                            Watching
                          </>
                        ) : (
                          <>
                            <FiPlus className="mr-1.5 h-4 w-4" />
                            Watch
                          </>
                        )}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
