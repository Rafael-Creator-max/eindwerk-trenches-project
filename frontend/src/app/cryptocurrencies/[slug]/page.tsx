'use client';

import { useEffect, useState } from 'react';
import { useRouter, useParams } from 'next/navigation';
import api from '@/lib/api';
import Navbar from '@/components/Navbar';
import { FiArrowUp, FiArrowDown, FiArrowLeft, FiLink, FiTwitter, FiGlobe } from 'react-icons/fi';

interface Cryptocurrency {
  id: number;
  name: string;
  symbol: string;
  slug: string;
  external_id: string;
  current_price: string | number;
  market_cap: string | number;
  volume_24h: string | number;
  price_change_24h: string | number;
  created_at: string;
  updated_at: string;
  asset_type: {
    name: string;
    description: string;
  };
}

export default function CryptocurrencyDetailPage() {
  const router = useRouter();
  const { slug } = useParams();
  const [crypto, setCrypto] = useState<Cryptocurrency | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [priceChange24h, setPriceChange24h] = useState<number>(0);

  useEffect(() => {
    const fetchCrypto = async () => {
      try {
        setLoading(true);
        const response = await api.get(`/api/cryptocurrencies/${slug}`);
        setCrypto(response.data);
        setPriceChange24h(Number(response.data.price_change_24h) || 0);
      } catch (err) {
        setError('Failed to fetch cryptocurrency details');
        console.error('Error fetching cryptocurrency:', err);
      } finally {
        setLoading(false);
      }
    };

    if (slug) {
      fetchCrypto();
    }
  }, [slug]);

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

  const formatVolume = (value: number) => {
    if (value >= 1e9) return `$${(value / 1e9).toFixed(2)}B`;
    if (value >= 1e6) return `$${(value / 1e6).toFixed(2)}M`;
    return formatCurrency(value);
  };

  const formatPercentage = (value: number) => {
    const isPositive = value >= 0;
    const colorClass = isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    const ArrowIcon = isPositive ? FiArrowUp : FiArrowDown;
    
    return (
      <span className={`${colorClass} flex items-center gap-1`}>
        <ArrowIcon className="inline" />
        {Math.abs(Number(value)).toFixed(2)}%
      </span>
    );
  };

  const getImageUrl = (symbol: string) => {
    return `https://cryptoicons.org/api/icon/${symbol.toLowerCase()}/200`;
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Navbar />
        <div className="flex items-center justify-center min-h-[calc(100vh-4rem)]">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p className="text-gray-600 dark:text-gray-300">Loading cryptocurrency details...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error || !crypto) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Navbar />
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <button
            onClick={() => router.back()}
            className="flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mb-6"
          >
            <FiArrowLeft className="mr-2" /> Back to Cryptocurrencies
          </button>
          
          <div className="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <p className="text-sm text-red-700 dark:text-red-300">
                  {error || 'Cryptocurrency not found'}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  const isPositive = priceChange24h >= 0;
  const priceChangeClass = isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
  const ArrowIcon = isPositive ? FiArrowUp : FiArrowDown;
  const coinGeckoUrl = `https://www.coingecko.com/en/coins/${crypto.external_id}`;
  const twitterUrl = `https://twitter.com/search?q=$${crypto.symbol.toUpperCase()}`;

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Navbar />
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <button
          onClick={() => router.back()}
          className="flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mb-6"
        >
          <FiArrowLeft className="mr-2" /> Back to Cryptocurrencies
        </button>

        <div className="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
          <div className="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
              <div className="flex items-center">
                <img
                  src={getImageUrl(crypto.symbol)}
                  alt={crypto.name}
                  className="w-12 h-12 rounded-full mr-4 bg-gray-100 dark:bg-gray-600"
                  onError={(e) => {
                    e.currentTarget.src = 'https://cryptoicons.org/api/icon/coin/200';
                  }}
                />
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                    {crypto.name} ({crypto.symbol.toUpperCase()})
                  </h2>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {crypto.asset_type?.name || 'Cryptocurrency'}
                  </p>
                </div>
              </div>
              <div className="mt-4 sm:mt-0">
                <div className="text-3xl font-bold text-gray-900 dark:text-white">
                  {formatCurrency(Number(crypto.current_price))}
                </div>
                <div className={`flex items-center justify-end ${priceChangeClass}`}>
                  <ArrowIcon className="mr-1" />
                  {Math.abs(priceChange24h).toFixed(2)}% (24h)
                </div>
              </div>
            </div>
          </div>

          <div className="px-4 py-5 sm:p-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Market Cap</h3>
                <p className="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                  {formatMarketCap(Number(crypto.market_cap))}
                </p>
              </div>
              
              <div className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">24h Trading Volume</h3>
                <p className="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                  {formatVolume(Number(crypto.volume_24h))}
                </p>
              </div>
              
              <div className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">24h Price Change</h3>
                <div className="mt-1">
                  {formatPercentage(priceChange24h)}
                </div>
              </div>
              
              <div className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</h3>
                <p className="mt-1 text-sm text-gray-900 dark:text-white">
                  {new Date(crypto.updated_at).toLocaleString()}
                </p>
              </div>
            </div>

            <div className="mt-8">
              <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-4">About {crypto.name}</h3>
              <p className="text-gray-600 dark:text-gray-300">
                {crypto.asset_type?.description || 'No description available.'}
              </p>
            </div>

            <div className="mt-8 flex space-x-4">
              <a
                href={coinGeckoUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                <FiLink className="mr-2" /> View on CoinGecko
              </a>
              <a
                href={twitterUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                <FiTwitter className="mr-2" /> Twitter
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
