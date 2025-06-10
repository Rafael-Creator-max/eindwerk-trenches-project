import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const protectedRoutes = ['/dashboard', '/profile', '/settings'];
const authRoutes = ['/login', '/register'];

export function middleware(request: NextRequest) {
  const token = request.cookies.get('auth_token')?.value;
  const isAuthenticated = !!token;
  const isAuthRoute = authRoutes.some(route => request.nextUrl.pathname.startsWith(route));
  const isProtectedRoute = protectedRoutes.some(route => request.nextUrl.pathname.startsWith(route));

  // Redirect to dashboard if user is authenticated and tries to access auth routes
  if (isAuthenticated && isAuthRoute) {
    return NextResponse.redirect(new URL('/dashboard', request.url));
  }

  // Redirect to login if user is not authenticated and tries to access protected routes
  if (!isAuthenticated && isProtectedRoute) {
    const loginUrl = new URL('/login', request.url);
    loginUrl.searchParams.set('from', request.nextUrl.pathname);
    return NextResponse.redirect(loginUrl);
  }

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
    '/((?!api|_next/static|_next/image|favicon.ico|.*\.(?:svg|png|jpg|jpeg|gif|webp)$).*)',
  ],
};
