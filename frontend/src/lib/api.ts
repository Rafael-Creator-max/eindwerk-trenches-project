import axios, { InternalAxiosRequestConfig } from 'axios';

// Debug log environment variables
console.log('Environment:', {
  NODE_ENV: process.env.NODE_ENV,
  NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api',
  NEXT_PUBLIC_APP_ENV: process.env.NEXT_PUBLIC_APP_ENV
});

// Determine the base URL
const getBaseUrl = () => {
  // In development, use the local Laravel backend
  if (process.env.NODE_ENV !== 'production') {
    return process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
  }
  
  // In production, use the production API URL without the /api prefix
  // as we'll handle it in the request interceptor
  return process.env.NEXT_PUBLIC_API_URL || '';
};

const baseURL = getBaseUrl();
console.log('Using API baseURL:', baseURL);

const api = axios.create({
  baseURL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true,
  timeout: 10000, // 10 seconds timeout
});

// Add token to requests if it exists
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Add /api prefix to all requests except those that already have a full URL or already start with /api
    if (config.url && !config.url.startsWith('http') && !config.url.startsWith('/api')) {
      config.url = `/api${config.url}`;
    }
    
    // Skip logging for certain endpoints if needed
    if (config.url && !config.url.includes('user') && !config.url.includes('sanctum')) {
      console.log(`[${config.method?.toUpperCase()}]`, config.url);
    }
    
    // Add token to request if it exists
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('token');
      if (token) {
        // Ensure headers is properly typed
        config.headers = config.headers || {};
        config.headers.Authorization = `Bearer ${token}`;
        config.headers['X-Requested-With'] = 'XMLHttpRequest';
      }
    }
    
    // Ensure credentials are sent with every request
    config.withCredentials = true;
    
    // Add timestamp to prevent caching
    if (config.method === 'get') {
      config.params = {
        ...config.params,
        _t: Date.now(),
      };
    }
    
    return config;
  },
  (error) => {
    console.error('Request error:', error);
    return Promise.reject(error);
  }
);

// Handle responses and errors
api.interceptors.response.use(
  (response) => {
    // Log successful responses if needed
    if (response.config.url && !response.config.url.includes('user') && !response.config.url.includes('sanctum')) {
      console.log(`[${response.config.method?.toUpperCase()}] ${response.status}`, response.config.url);
    }
    return response;
  },
  async (error) => {
    const originalRequest = error.config;
    
    // Log the error
    console.error('API Error:', {
      url: originalRequest?.url,
      method: originalRequest?.method,
      status: error.response?.status,
      message: error.message,
    });
    
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      // Only handle if we haven't retried yet
      if (!originalRequest._retry) {
        originalRequest._retry = true;
        
        // Clear the invalid token
        if (typeof window !== 'undefined') {
          localStorage.removeItem('token');
          delete api.defaults.headers.common['Authorization'];
          
          // Redirect to login if not already there
          if (!window.location.pathname.includes('/login')) {
            // Store the current URL to redirect back after login
            const returnUrl = window.location.pathname + window.location.search;
            window.location.href = `/login?from=${encodeURIComponent(returnUrl)}`;
          }
        }
      }
    }
    
    return Promise.reject(error);
  }
);

// For backward compatibility, but not used in token-based auth
export const getCSRFToken = async (): Promise<null> => {
  return null;
};

export default api;
