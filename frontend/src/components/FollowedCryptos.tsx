'use client';

import { useCallback, useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { FiArrowUp, FiArrowDown, FiRefreshCw, FiStar } from 'react-icons/fi';
import axios from '@/lib/api';

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

export default function FollowedCryptos() {
  const [cryptos, setCryptos] = useState<Cryptocurrency[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const router = useRouter();

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
    return `data:image/svg+xml;base64,${btoa(`
      <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
        <rect width="32" height="32" rx="16" fill="#f3f4f6"/>
        <text x="50%" y="50%" font-family="Arial" font-size="10" text-anchor="middle" dy=".3em" fill="#6b7280">
          ${symbol.slice(0, 3).toUpperCase()}
        </text>
      </svg>
    `)}`;
  };

  const fetchFollowedCryptos = useCallback(async () => {
    try {
      setLoading(true);
      const response = await axios.get('/user/cryptocurrencies');
      setCryptos(response.data);
    } catch (err: any) {
      console.error('Error fetching followed cryptos:', err);
      setError('Failed to load followed cryptocurrencies');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchFollowedCryptos();
  }, [fetchFollowedCryptos]);

  const handleUnfollow = async (cryptoId: number, e: React.MouseEvent) => {
    e.stopPropagation();
    try {
      await axios.delete(`/cryptocurrencies/${cryptoId}/follow`);
      // Refresh the list after unfollowing
      fetchFollowedCryptos();
    } catch (err) {
      console.error('Error unfollowing cryptocurrency:', err);
      setError('Failed to update watchlist');
    }
  };

  if (loading && cryptos.length === 0) {
    return (
      <div className="flex justify-center py-12">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return <div className="text-red-500 text-center py-4">{error}</div>;
  }

  if (cryptos.length === 0) {
    return (
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
        <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">No cryptocurrencies in watchlist</h3>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Add cryptocurrencies to your watchlist from the market view.
        </p>
      </div>
    );
  }

  return (
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
            {cryptos.map((crypto, index) => (
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
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    onClick={(e) => handleUnfollow(crypto.id, e)}
                    className="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
                    title="Remove from watchlist"
                  >
                    <FiStar className="mr-1.5 h-4 w-4" />
                    Watching
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
