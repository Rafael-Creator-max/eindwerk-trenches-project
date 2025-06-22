'use client';

'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/AuthContext';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [logs, setLogs] = useState<string[]>([]);
  const router = useRouter();
  const { login, isAuthenticated } = useAuth();
  
  // Redirect if already authenticated
  useEffect(() => {
    // Only run this effect on the client side
    if (typeof window === 'undefined') return;
    
    // Get the redirect URL from the query parameters first
    const urlParams = new URLSearchParams(window.location.search);
    let from = urlParams.get('from') || '/dashboard';
    
    // Ensure we don't redirect back to login
    if (from.startsWith('/login')) {
      from = '/dashboard';
    }
    
    console.log('LoginPage - Auth state:', { 
      isAuthenticated, 
      loading, 
      path: window.location.pathname,
      from 
    });
    
    // Only redirect if we're authenticated and not loading
    if (isAuthenticated && !loading) {
      console.log('LoginPage - User is authenticated, redirecting to:', from);
      
      // Use a small timeout to ensure the state is fully updated
      const timer = setTimeout(() => {
        console.log('LoginPage - Executing redirect to:', from);
        window.location.href = from; // Force a full page reload
      }, 50);
      
      return () => clearTimeout(timer);
    }
  }, [isAuthenticated, loading]);

  // Function to add logs to the state
  const addLog = (message: string) => {
    console.log(message);
    setLogs(prev => [...prev, message]);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    // Clear any previous errors
    setError('');
    setLoading(true);
    addLog('=== Form submission started ===');
    addLog(`Attempting to log in with: ${email}`);
    
    try {
      console.log('Starting login process...');
      addLog('Authenticating...');
      
      // Perform the login
      const user = await login(email, password);
      console.log('Login successful, user:', user);
      addLog('Authentication successful');
      
      // Get the redirect URL from query params or default to dashboard
      const urlParams = new URLSearchParams(window.location.search);
      let redirectTo = urlParams.get('from') || '/dashboard';
      
      // Ensure we don't redirect back to login
      if (redirectTo.startsWith('/login')) {
        redirectTo = '/dashboard';
      }
      
      console.log('Redirecting to:', redirectTo);
      addLog(`Redirecting to: ${redirectTo}`);
      
      // Use replace to prevent going back to login page
      router.replace(redirectTo);
      
    } catch (error: unknown) {
      console.error('Login error:', error);
      let errorMessage = 'Failed to log in';
      
      const err = error as { 
        response?: { 
          status?: number;
          data?: { message?: string } 
        }; 
        request?: unknown; 
        message?: string 
      };
      
      if (err.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        if (err.response.status === 401) {
          errorMessage = 'Invalid email or password';
        } else if (err.response.data?.message) {
          errorMessage = err.response.data.message;
        }
      } else if (err.request) {
        // The request was made but no response was received
        errorMessage = 'No response from server. Please check your internet connection.';
      } else if (err.message) {
        // Something happened in setting up the request
        errorMessage = err.message;
      }
      
      setError(errorMessage);
      addLog(`Login failed: ${errorMessage}`);
    } finally {
      setLoading(false);
      addLog('=== Form submission completed ===');
    }
    
    return false;
  };

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8 mb-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Sign in to your account
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Or{' '}
            <a href="/register" className="font-medium text-blue-600 hover:text-blue-500">
              create a new account
            </a>
          </p>
        </div>
        {error && (
          <div className="bg-red-50 border-l-4 border-red-400 p-4">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <p className="text-sm text-red-700">{error}</p>
              </div>
            </div>
          </div>
        )}
        <form className="mt-8 space-y-6" onSubmit={handleSubmit} action="#" method="POST">
          <input type="hidden" name="remember" defaultValue="true" />
          <div className="rounded-md shadow-sm -space-y-px">
            <div>
              <label htmlFor="email-address" className="sr-only">
                Email address
              </label>
              <input
                id="email-address"
                name="email"
                type="email"
                autoComplete="email"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Email address"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
              />
            </div>
            <div>
              <label htmlFor="password" className="sr-only">
                Password
              </label>
              <input
                id="password"
                name="password"
                type="password"
                autoComplete="current-password"
                required
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
              />
            </div>
          </div>

          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <input
                id="remember-me"
                name="remember-me"
                type="checkbox"
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label htmlFor="remember-me" className="ml-2 block text-sm text-gray-900">
                Remember me
              </label>
            </div>

            <div className="text-sm">
              <a href="/forgot-password" className="font-medium text-blue-600 hover:text-blue-500">
                Forgot your password?
              </a>
            </div>
          </div>

          <div>
            <button
              type="submit"
              disabled={loading}
              className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Signing in...' : 'Sign in'}
            </button>
          </div>
        </form>
      </div>
      
      {/* Debug Logs */}
      <div className="mt-8 w-full max-w-4xl">
        <div className="bg-gray-800 text-green-400 p-4 rounded-lg font-mono text-xs overflow-auto max-h-64">
          <div className="font-bold mb-2 text-white">Debug Logs:</div>
          <div className="space-y-1">
            {logs.map((log, index) => (
              <div key={index} className="border-b border-gray-700 pb-1">
                {log}
              </div>
            ))}
            {logs.length === 0 && (
              <div className="text-gray-500">No logs yet. Submit the form to see logs.</div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
