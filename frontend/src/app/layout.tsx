import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import AuthProviderWrapper from "@/providers/AuthProviderWrapper";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Crypto Tracker",
  description: "Track your favorite cryptocurrencies",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className={`${geistSans.variable} ${geistMono.variable} font-sans`}>
        <AuthProviderWrapper>
          {children}
        </AuthProviderWrapper>
      </body>
    </html>
  );
}
