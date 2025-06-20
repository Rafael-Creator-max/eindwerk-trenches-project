'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';
import Navbar from '@/components/Navbar';
import { FiArrowUp, FiArrowDown, FiRefreshCw } from 'react-icons/fi';

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
}

export default function CryptocurrenciesPage() {
  const [cryptos, setCryptos] = useState<Cryptocurrency[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  useEffect(() => {
    const fetchCryptos = async () => {
      try {
        setLoading(true);
        const response = await api.get('/api/cryptocurrencies', {
          params: { _t: new Date().getTime() } // Prevent caching
        });
        setCryptos(response.data);
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
    };

    // Initial fetch
    fetchCryptos();
    
    // Set up interval for auto-refresh (5 minutes)
    const intervalId = setInterval(() => {
      console.log('Auto-refreshing cryptocurrency data...');
      fetchCryptos();
    }, 5 * 60 * 1000); // 5 minutes in milliseconds
    
    // Clean up interval on component unmount
    return () => clearInterval(intervalId);
  }, []);

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

  const getImageUrl = (symbol: string, name: string) => {
    return `https://cryptoicons.org/api/icon/${symbol.toLowerCase()}/200`;
  };

  const getCryptoImageUrl = (symbol: string, name: string): string => {
    const symbolLower = symbol.toLowerCase();
    const nameLower = name.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    // Try different URL patterns for the image
    const patterns = [
      `https://cryptoicons.org/api/icon/${symbolLower}/200`,
      `https://cryptologos.cc/logos/${nameLower}-${symbolLower}-logo.png`,
      `https://cryptologos.cc/logos/${nameLower}-logo.png`,
      `https://cryptologos.cc/logos/${symbolLower}-logo.png`,
      `https://s2.coinmarketcap.com/static/img/coins/64x64/${symbolLower}.png`,
      `https://cryptoicons.org/api/icon/${symbolLower}/128`,
      `https://cryptoicons.org/api/icon/${symbolLower}/64`
    ];
    
    return patterns[0]; // Return first URL, handle fallbacks in onError
  };

  const getFallbackImageUrl = (symbol: string, name: string): string => {
    const symbolLower = symbol.toLowerCase();
    const nameLower = name.toLowerCase().replace(/[^a-z0-9]/g, '');
    
    // Return a placeholder with the first 3 letters of the symbol
    return `data:image/svg+xml;base64,${btoa(`
      <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
        <rect width="32" height="32" rx="16" fill="#f3f4f6"/>
        <text x="50%" y="50%" font-family="Arial" font-size="10" text-anchor="middle" dy=".3em" fill="#6b7280">
          ${symbol.slice(0, 3).toUpperCase()}
        </text>
      </svg>
    `)}`;
  };

  const [lastUpdated, setLastUpdated] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState('');

  const refreshData = async () => {
    try {
      setLoading(true);
      const response = await api.get('/api/cryptocurrencies', {
        params: { _t: new Date().getTime() } // Prevent caching
      });
      setCryptos(response.data);
      const now = new Date();
      setLastUpdated(now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      }));
    } catch (err) {
      console.error('Error fetching cryptocurrencies:', err);
      // Don't show error to user for auto-refresh, just log it
      if (!lastUpdated) {
        setError('Failed to fetch cryptocurrencies');
      }
    } finally {
      setLoading(false);
    }
  };
  
  // Memoized fetch function to prevent unnecessary re-renders
  const fetchCryptos = useCallback(async () => {
    await refreshData();
  }, [lastUpdated]); // Only recreate when lastUpdated changes

  const filteredCryptos = cryptos.filter(crypto => 
    crypto.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    crypto.symbol.toLowerCase().includes(searchTerm.toLowerCase())
  );

  if (loading && cryptos.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Navbar />
        <style jsx global>{`
          tr { transition: background-color 0.2s ease-in-out; }
          tr:active { transform: scale(0.99); }
        `}</style>
        <div className="flex items-center justify-center min-h-[calc(100vh-4rem)]">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p className="text-gray-600 dark:text-gray-300">Loading cryptocurrencies...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Navbar />
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Cryptocurrency Prices</h1>
            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {lastUpdated 
                ? `Last updated: ${lastUpdated}` 
                : 'Loading...'}
              {loading && (
                <span className="ml-2 inline-block">
                  <FiRefreshCw className="animate-spin h-3 w-3 inline" />
                </span>
              )}
            </p>
          </div>
          <div className="mt-4 md:mt-0 flex space-x-3">
            <div className="relative">
              <input
                type="text"
                placeholder="Search coins..."
                className="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full md:w-64"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
              <div className="absolute left-3 top-2.5 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            <button
              onClick={refreshData}
              disabled={loading}
              className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? (
                <FiRefreshCw className="animate-spin -ml-1 mr-2 h-4 w-4" />
              ) : (
                <FiRefreshCw className="-ml-1 mr-2 h-4 w-4" />
              )}
              Refresh
            </button>
          </div>
        </div>
      
        {error ? (
          <div className="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded">
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
        ) : null}

        {filteredCryptos.length === 0 ? (
          <div className="text-center py-12">
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
              Try adjusting your search or filter to find what you're looking for.
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
                  </tr>
                </thead>
                <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  {filteredCryptos.map((crypto, index) => (
                    <tr 
                      key={crypto.id} 
                      className="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                      onClick={() => router.push(`/cryptocurrencies/${crypto.slug}`)}
                    >
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        {index + 1}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
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
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
