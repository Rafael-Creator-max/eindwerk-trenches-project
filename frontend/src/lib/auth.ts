import api from './api';

export type User = {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string | null;
  created_at: string;
  updated_at: string;
};

type LoginResponse = {
  access_token: string;
  token_type: string;
  user: User;
};

export const login = async (email: string, password: string): Promise<User> => {
  try {
    console.log('[Auth] Starting login process...');
    
    // Clear any existing token
    if (typeof window !== 'undefined') {
      localStorage.removeItem('token');
    }
    delete api.defaults.headers.common['Authorization'];
    
    // Perform the login
    console.log('[Auth] Sending login request...');
    const response = await api.post<LoginResponse>(
      '/login',
      { email, password },
      {
        withCredentials: true,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      }
    );
    
    console.log('[Auth] Login response:', {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    });
    
    // Save the token to cookies and set it in the API client
    if (response.data.access_token) {
      const token = response.data.access_token;
      console.log('[Auth] Saving token to localStorage');
      if (typeof window !== 'undefined') {
        localStorage.setItem('token', token);
      }
      
      // Update the default Authorization header for subsequent requests
      console.log('[Auth] Setting Authorization header');
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      
      // If we have user data in the response, return it
      if (response.data.user) {
        console.log('[Auth] User data received in login response:', response.data.user);
        return response.data.user;
      }
      
      // Otherwise, fetch the user data
      console.log('[Auth] Fetching user data...');
      try {
        const userResponse = await api.get<User>('/user');
        console.log('[Auth] User data fetched successfully');
        return userResponse.data;
      } catch (userError) {
        console.error('[Auth] Failed to fetch user data:', userError);
        throw new Error('Logged in but failed to load user profile');
      }
    }
    
    throw new Error('No access token received in response');
    
  } catch (error: unknown) {
    const err = error as {
      message?: string;
      response?: {
        status?: number;
        statusText?: string;
        data?: {
          message?: string;
          errors?: Record<string, string[]>;
        };
      };
      request?: unknown;
    };

    console.error('[Auth] Login error:', {
      message: err.message,
      response: err.response ? {
        status: err.response.status,
        statusText: err.response.statusText,
        data: err.response.data,
      } : 'No response',
    });
    
    // Clear any potentially invalid auth state
    if (typeof window !== 'undefined') {
      localStorage.removeItem('token');
    }
    delete api.defaults.headers.common['Authorization'];
    
    // Format error message from response if available
    if (err.response?.data?.message) {
      throw new Error(err.response.data.message);
    } else if (err.response?.data?.errors) {
      // Handle validation errors
      const errorMessages = Object.values(err.response.data.errors)
        .flat()
        .join('\n');
      throw new Error(errorMessages || 'Validation failed');
    } else if (err.message) {
      throw new Error(err.message);
    } else {
      throw new Error('An unknown error occurred during login');
    }
  }
};

export const register = async (name: string, email: string, password: string, password_confirmation: string): Promise<User> => {
  const response = await api.post<LoginResponse>(
    '/register',
    { name, email, password, password_confirmation }
  );
  
  if (response.status >= 400) {
    const errorData = response.data as {
      errors?: Record<string, string[]>;
      message?: string;
    };
    let errorMessage = 'Registration failed';
    
    if (errorData?.errors) {
      const errorMessages = Object.values(errorData.errors).flat();
      errorMessage = errorMessages.join('\n');
    } else if (errorData?.message) {
      errorMessage = errorData.message;
    }
    
    throw new Error(errorMessage);
  }
  
  if (!response.data.user) {
    throw new Error('No user data received');
  }
  
  console.log('[Auth] Registration successful, user:', response.data.user);
  return response.data.user;
};

export const logout = async (): Promise<void> => {
  try {
    console.log('[Auth] Starting logout process...');
    
    // Perform the logout
    console.log('[Auth] Sending logout request...');
    await api.post('/logout');
    
    console.log('[Auth] Logout successful');
  } catch (error: unknown) {
    const err = error as { message?: string };
    console.error('[Auth] Logout error:', err.message || 'Unknown error');
    // Continue with cleanup even if the request fails
  } finally {
    // Always clear the token and any user data
    localStorage.removeItem('token');
    console.log('[Auth] Token removed from localStorage');
  }
};

// Cache to prevent multiple simultaneous requests
let userRequest: Promise<User | null> | null = null;

export const getCurrentUser = async (): Promise<User | null> => {
  // If we already have a request in progress, return that instead of making a new one
  if (userRequest) {
    console.log('[Auth] Using existing user request');
    return userRequest;
  }
  
  // If we have a token in localStorage, try to get the user
  const token = localStorage.getItem('token');
  if (!token) {
    console.log('[Auth] No token found in localStorage');
    return null;
  }
  
  console.log('[Auth] Getting current user...');
  
  try {
    userRequest = (async (): Promise<User | null> => {
      try {
        // Get the user
        const response = await api.get<User>('/user');
        
        console.log('[Auth] Current user response:', {
          status: response.status,
          statusText: response.statusText,
        });
        
        if (response.status === 200) {
          console.log('[Auth] User authenticated:', response.data);
          return response.data;
        }
        
        // If we get here, the token might be invalid
        console.warn('[Auth] Failed to get current user, status:', response.status);
        localStorage.removeItem('token');
        return null;
      } catch (error: unknown) {
        const err = error as { message?: string };
        console.error('[Auth] Error getting current user:', err.message || 'Unknown error');
        localStorage.removeItem('token');
        return null;
      } finally {
        // Clear the request promise so we can try again
        userRequest = null;
      }
    })();
    
    return await userRequest;
  } catch (error: unknown) {
    const err = error as { message?: string };
    console.error('[Auth] Error in getCurrentUser:', err.message || 'Unknown error');
    userRequest = null;
    return null;
  }
};

export const isAuthenticated = async (): Promise<boolean> => {
  try {
    const user = await getCurrentUser();
    return !!user;
  } catch (error) {
    console.error('Error checking authentication status:', error);
    return false;
  }
};


// Helper function to get the auth token
export const getAuthToken = (): string | null => {
  return localStorage.getItem('token');
};

// Helper function to set the auth token
export const setAuthToken = (token: string): void => {
  localStorage.setItem('token', token);
};
