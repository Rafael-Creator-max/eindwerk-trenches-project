/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  async rewrites() {
    return [
      // Rewrite API requests to the DDEV backend
      {
        source: '/api/:path*',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/api/:path*`,
      },
      // Rewrite Sanctum endpoints
      {
        source: '/sanctum/:path*',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/sanctum/:path*`,
      },
      // Rewrite login/logout endpoints
      {
        source: '/(login|logout|register|forgot-password|reset-password)',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/$1`,
      },
    ];
  },
  async headers() {
    return [
      {
        // Apply these headers to all routes
        source: '/:path*',
        headers: [
          {
            key: 'X-Requested-With',
            value: 'XMLHttpRequest',
          },
        ],
      },
    ];
  },
  // Enable CORS for all API routes
  async headers() {
    return [
      {
        source: '/api/:path*',
        headers: [
          { key: 'Access-Control-Allow-Credentials', value: 'true' },
          { key: 'Access-Control-Allow-Origin', value: '*' },
          { key: 'Access-Control-Allow-Methods', value: 'GET,OPTIONS,PATCH,DELETE,POST,PUT' },
          { key: 'Access-Control-Allow-Headers', value: 'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version' },
        ],
      },
    ];
  },
};

module.exports = nextConfig;
