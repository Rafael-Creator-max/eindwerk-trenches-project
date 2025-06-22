'use client';

import { useEffect, useState } from 'react';
import axios from '@/lib/api';

interface Cryptocurrency {
  id: string;
  name: string;
  symbol: string;
  current_price: number;
  price_change_percentage_24h: number;
  image?: string;
}

export default function FollowedCryptos() {
  const [cryptos, setCryptos] = useState<Cryptocurrency[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchFollowedCryptos = async () => {
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
    };

    fetchFollowedCryptos();
  }, []);

  if (loading) {
    return (
      <div className="flex justify-center py-8">
        <div className="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return <div className="text-red-500 text-center py-4">{error}</div>;
  }

  if (cryptos.length === 0) {
    return (
      <div className="text-center py-8 text-gray-500">
        You're not following any cryptocurrencies yet.
      </div>
    );
  }

  return (
    <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
      <table className="min-w-full divide-y divide-gray-300">
        <thead className="bg-gray-50">
          <tr>
            <th scope="col" className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
              Coin
            </th>
            <th scope="col" className="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
              Price
            </th>
            <th scope="col" className="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">
              24h Change
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-200 bg-white">
          {cryptos.map((crypto) => (
            <tr key={crypto.id}>
              <td className="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                <div className="flex items-center">
                  {crypto.image && (
                    <img className="h-6 w-6 rounded-full mr-3" src={crypto.image} alt={crypto.name} />
                  )}
                  <div>
                    <div className="font-medium text-gray-900">{crypto.name}</div>
                    <div className="text-gray-500">{crypto.symbol.toUpperCase()}</div>
                  </div>
                </div>
              </td>
              <td className="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-500">
                ${crypto.current_price?.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 6 })}
              </td>
              <td className={`whitespace-nowrap px-3 py-4 text-right text-sm ${
                crypto.price_change_percentage_24h >= 0 ? 'text-green-600' : 'text-red-600'
              }`}>
                {crypto.price_change_percentage_24h?.toFixed(2)}%
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
