import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const protectedRoutes = ['/dashboard', '/profile', '/settings'];
const authRoutes = ['/login', '/register'];

export function middleware(request: NextRequest) {
  // For API routes, let them handle their own auth
  if (request.nextUrl.pathname.startsWith('/api')) {
    return NextResponse.next();
  }
  
  // For static files and assets, skip auth check
  if (request.nextUrl.pathname.startsWith('/_next') || 
      request.nextUrl.pathname.startsWith('/favicon.ico') ||
      request.nextUrl.pathname.match(/\.(?:svg|png|jpg|jpeg|gif|webp)$/)) {
    return NextResponse.next();
  }
  
  // For all other routes, let the client handle the auth redirect
  // This is because we can't access localStorage in middleware
  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - public folder
     */
    '/((?!api|_next/static|_next/image|favicon.ico|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)',
  ],
};
