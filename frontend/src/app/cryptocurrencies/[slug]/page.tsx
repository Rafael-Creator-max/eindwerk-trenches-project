'use client';

import { useEffect, useState } from 'react';
import { useRouter, useParams } from 'next/navigation';
import api, { createApiWithTimeout } from '@/lib/api';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import Navbar from '@/components/Navbar';
import { FiArrowUp, FiArrowDown, FiArrowLeft, FiLink, FiTwitter, FiGlobe, FiRefreshCw, FiAlertCircle } from 'react-icons/fi';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

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
  image_url?: string;
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
  const [loadingChart, setLoadingChart] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [timeRange, setTimeRange] = useState('7');
  interface PriceData {
    date: string;
    price: number;
  }

  const [priceData, setPriceData] = useState<PriceData[]>([]);
  const [priceChange24h, setPriceChange24h] = useState<number>(0);

  const fetchData = async () => {
    try {
      setLoading(true);
      const response = await api.get(`/api/cryptocurrencies/${slug}`);
      const cryptoData = response.data.data;
      
      // Set the crypto data
      setCrypto(cryptoData);
      
      // Set the 24h price change if available
      if (cryptoData.price_change_24h !== undefined) {
        setPriceChange24h(parseFloat(cryptoData.price_change_24h));
      }
      
      await fetchPriceHistory(timeRange);
    } catch (err) {
      setError('Failed to fetch cryptocurrency details');
      console.error('Error fetching data:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchPriceHistory = async (days: string) => {
    if (!slug) return;
    
    try {
      setLoadingChart(true);
      const fetchPriceHistory = async (days = 7) => {
        try {
          console.log(`[${new Date().toISOString()}] Fetching price history for ${slug} (${days} days)`);
          setLoading(true);
          
          // Use a dedicated API client with a longer timeout for price history
          const priceHistoryApi = createApiWithTimeout(30000); // 30 second timeout for price history
          
          const response = await priceHistoryApi.get(`/api/cryptocurrencies/${slug}/chart`, {
            params: { 
              days,
              _t: Date.now() // Cache buster
            }
          });
          
          if (response.data?.data?.prices) {
            const prices = response.data.data.prices;
            const marketCaps = response.data.data.market_caps || [];
            const volumes = response.data.data.total_volumes || [];
            
            console.log(`[${new Date().toISOString()}] Received ${prices.length} price points for ${slug}`);
            
            // Process and validate price data
            const validPrices = prices
              .filter((item: any) => Array.isArray(item) && item.length >= 2 && !isNaN(item[0]) && !isNaN(item[1]))
              .map((item: [number, number]) => ({
                timestamp: item[0],
                price: Number(item[1])
              }));
            
            // Sort by timestamp just in case
            validPrices.sort((a: any, b: any) => a.timestamp - b.timestamp);
            
            // Format data for the chart
            const formattedData = validPrices.map((item: { timestamp: string | number | Date; price: any; }) => ({
              date: new Date(item.timestamp).toLocaleDateString(),
              price: item.price,
              timestamp: item.timestamp
            }));
            
            console.log(`[${new Date().toISOString()}] Processed ${formattedData.length} valid price points for ${slug}`);
            
            // Update state with the new data
            setPriceData(formattedData);
            
            // Calculate 24h price change if we have at least 2 data points
            if (validPrices.length >= 2) {
              const firstPrice = validPrices[0].price;
              const lastPrice = validPrices[validPrices.length - 1].price;
              const priceChange = ((lastPrice - firstPrice) / firstPrice) * 100;
              setPriceChange24h(priceChange);
              
              // Also update the crypto object if it exists
              if (crypto) {
                setCrypto({
                  ...crypto,
                  price_change_24h: priceChange.toString()
                });
              }
            }
            
            // Log if we got an empty but valid response
            if (validPrices.length === 0) {
              console.warn(`[${new Date().toISOString()}] No valid price data points for ${slug}`, {
                cryptoId: response.data.crypto_id,
                symbol: response.data.symbol,
                originalCount: prices.length,
                validCount: validPrices.length,
                message: response.data.message
              });
            }
          } else {
            // No valid price data in response
            console.warn(`[${new Date().toISOString()}] No valid price data in response for ${slug}`, {
              status: response.status,
              hasData: !!response.data,
              hasPrices: Array.isArray(response.data?.data?.prices),
              priceCount: Array.isArray(response.data?.data?.prices) ? response.data.data.prices.length : 0,
              message: response.data?.message
            });
            
            setPriceData([]);
          }
        } catch (err: any) {
          console.error(`[${new Date().toISOString()}] Error fetching price history for ${slug}:`, {
            error: err.message,
            status: err.response?.status,
            code: err.code,
            config: err.config
          });
          
          // Set empty data on error
          setPriceData([]);
          setPriceChange24h(0);
          
          // Show appropriate error message
          let errorMessage = 'Failed to load price history. ';
          
          if (err.code === 'ECONNABORTED') {
            errorMessage = 'Request timed out. The server is taking too long to respond.';
          } else if (err.response?.status === 429) {
            errorMessage = 'Too many requests. Please wait before trying again.';
          } else if (err.response?.data?.message) {
            errorMessage += err.response.data.message;
          } else if (err.message) {
            errorMessage += err.message;
          } else {
            errorMessage += 'Please try again later.';
          }
          
          // Show error toast
          toast.error(errorMessage);
          
          // If we have cached data, we might want to show it here
          // or implement a retry mechanism
        } finally {
          setLoading(false);
        }
      };
      await fetchPriceHistory(Number(days));
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch price history';
      
      console.error(`[${new Date().toISOString()}] Error fetching price history for ${slug}:`, {
        error: errorMessage,
        status: err.response?.status,
        code: err.code,
        config: {
          url: err.config?.url,
          method: err.config?.method,
          params: err.config?.params
        }
      });
      
      setError(errorMessage);
      setPriceData([]);
    } finally {
      setLoadingChart(false);
    }
  };

  useEffect(() => {
    if (slug) {
      fetchData();
    }
  }, [slug]);

  useEffect(() => {
    if (slug) {
      // Refresh both the price history and the main data when time range changes
      const refreshData = async () => {
        await fetchData();
        await fetchPriceHistory(timeRange);
      };
      refreshData();
    }
  }, [timeRange]);

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

  if (!crypto) {
    return <div>Loading...</div>; // or any other loading state
  }

  const isPositive = priceChange24h >= 0;
  const priceChangeClass = isPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
  const ArrowIcon = isPositive ? FiArrowUp : FiArrowDown;
  const coinGeckoUrl = `https://www.coingecko.com/en/coins/${crypto.external_id || ''}`;
  const twitterUrl = `https://twitter.com/search?q=$${crypto.symbol ? crypto.symbol.toUpperCase() : 'crypto'}`;

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Navbar />
      <ToastContainer position="bottom-right" autoClose={5000} />
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
                  src={
                    crypto.image_url || 
                    (crypto.symbol ? `https://cryptoicons.org/api/icon/${crypto.symbol.toLowerCase()}/200` : 
                    `https://cryptoicons.org/api/icon/${crypto.external_id || 'coin'}/200`)
                  }
                  alt={crypto.name}
                  className="w-12 h-12 rounded-full mr-4 bg-gray-100 dark:bg-gray-600 object-cover"
                  onError={(e) => {
                    const target = e.target as HTMLImageElement;
                    target.onerror = null;
                    // Try with external_id if symbol failed
                    if (crypto.external_id && crypto.symbol) {
                      target.src = `https://cryptoicons.org/api/icon/${crypto.external_id.toLowerCase()}/200`;
                    } else {
                      target.src = '/placeholder-coin.png';
                    }
                  }}
                />
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                    {crypto.name}{crypto.symbol && ` (${crypto.symbol.toUpperCase()})`}
                  </h2>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {crypto.asset_type?.name || 'Cryptocurrency'}
                  </p>
                </div>
              </div>
              <div className="mt-4 sm:mt-0">
                <div className="text-3xl font-bold text-gray-900 dark:text-white">
                  ${typeof crypto.current_price === 'string' || typeof crypto.current_price === 'number' 
                    ? parseFloat(crypto.current_price.toString()).toLocaleString(undefined, { 
                        minimumFractionDigits: 2, 
                        maximumFractionDigits: 6 
                      })
                    : 'N/A'}
                </div>
                <p className={`text-lg ${priceChangeClass} flex items-center`}>
                  {priceChange24h !== undefined && (
                    <>
                      <ArrowIcon className="mr-1" />
                      {Math.abs(priceChange24h).toFixed(2)}%
                    </>
                  )}
                </p>
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
                <p className={`text-lg ${priceChangeClass} flex items-center`}>
                  {priceChange24h !== undefined ? (
                    <>
                      <ArrowIcon className="mr-1" />
                      {Math.abs(priceChange24h).toFixed(2)}%
                    </>
                  ) : 'N/A'}
                </p>
              </div>
              
              <div className="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</h3>
                <p className="mt-1 text-sm text-gray-900 dark:text-white">
                  {new Date(crypto.updated_at).toLocaleString()}
                </p>
              </div>
            </div>

            <div className="mt-8">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-medium text-gray-900 dark:text-white">Price Chart</h3>
                <div className="flex space-x-2">
                  {['1', '7', '30', '90'].map((days) => (
                    <button
                      key={days}
                      onClick={() => setTimeRange(days)}
                      className={`px-3 py-1 text-sm rounded-md ${
                        timeRange === days
                          ? 'bg-blue-600 text-white'
                          : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                      }`}
                    >
                      {days}d
                    </button>
                  ))}
                </div>
              </div>
              
              <div className="h-80 bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                {loadingChart ? (
                  <div className="flex flex-col items-center justify-center h-full space-y-4">
                    <FiRefreshCw className="animate-spin h-8 w-8 text-blue-500" />
                    <p className="text-gray-500">Loading price data...</p>
                  </div>
                ) : priceData.length > 0 ? (
                  <div className="h-full w-full">
                    <ResponsiveContainer width="100%" height="100%">
                      <LineChart 
                        data={priceData}
                        margin={{ top: 10, right: 20, left: 0, bottom: 5 }}
                      >
                        <CartesianGrid 
                          strokeDasharray="3 3" 
                          stroke="#e5e7eb" 
                          vertical={false} 
                        />
                        <XAxis 
                          dataKey="date" 
                          tick={{ fill: '#6b7280', fontSize: 12 }}
                          tickLine={{ stroke: '#9ca3af' }}
                          tickMargin={10}
                        />
                        <YAxis 
                          domain={['auto', 'auto']}
                          tickFormatter={(value: number) => 
                            new Intl.NumberFormat('en-US', {
                              style: 'currency',
                              currency: 'USD',
                              minimumFractionDigits: 2,
                              maximumFractionDigits: 8
                            }).format(value)
                          }
                          tick={{ fill: '#6b7280', fontSize: 12 }}
                          tickLine={{ stroke: '#9ca3af' }}
                          width={100}
                        />
                        <Tooltip 
                          formatter={(value: any) => [
                            new Intl.NumberFormat('en-US', {
                              style: 'currency',
                              currency: 'USD',
                              minimumFractionDigits: 2,
                              maximumFractionDigits: 8
                            }).format(Number(value)),
                            'Price'
                          ]}
                          labelFormatter={(label: string) => `Date: ${label}`}
                          contentStyle={{
                            backgroundColor: 'white',
                            border: '1px solid #e5e7eb',
                            borderRadius: '0.5rem',
                            padding: '0.5rem',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                          }}
                        />
                        <Line 
                          type="monotone" 
                          dataKey="price" 
                          stroke="#3b82f6" 
                          strokeWidth={2} 
                          dot={false}
                          activeDot={{ 
                            r: 6, 
                            stroke: '#2563eb', 
                            strokeWidth: 2,
                            fill: '#3b82f6'
                          }}
                        />
                      </LineChart>
                    </ResponsiveContainer>
                  </div>
                ) : (
                  <div className="flex flex-col items-center justify-center h-full space-y-4 text-center p-4">
                    <FiAlertCircle className="h-12 w-12 text-gray-400" />
                    <div>
                      <h4 className="text-lg font-medium text-gray-900 dark:text-white">No Price Data Available</h4>
                      <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        We couldn't load the price history for this cryptocurrency at the moment.
                      </p>
                    </div>
                  </div>
                )}
              </div>
            </div>

            <div className="mt-8">
              <h3 className="text-lg font-medium text-gray-900 dark:text-white">About {crypto.name}</h3>
              <p className="mt-2 text-gray-600 dark:text-gray-300">
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
};
