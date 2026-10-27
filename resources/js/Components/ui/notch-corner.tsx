import type { ReactNode } from 'react';

/**
 * Kontur dan cakram untuk kartu yang sudut kanan bawahnya dipotong takik.
 * Potongannya dibuat oleh kelas `notch-cut` pada kartu (masker SVG data URI di app.css, 74px): lekuk dengan sudut
 * dalam sepusat dengan cakram (cakram 44px, jari-jari lekuk 36px, jarak seragam 14px) dan dua ujung luar membulat 16px.
 * Komponen ini hanya menggaris kontur potongan dengan --border/60 (sama seperti tepi kartu) dan memasang cakram panah
 * biru gelap (--primary, hover --nav-hover sesuai RULE-052) yang rapat ke pojok.
 * Pasang di pembungkus `relative group` yang sisi luarnya sama dengan sisi luar kartu, bungkus kartu saja (bukan
 * cakram) dengan `drop-lift group-hover:drop-lift-hover`, dan kartunya `notch-cut shadow-none!`.
 */
export function NotchCorner({ children }: { children: ReactNode }) {
  return (
    <>
      <svg aria-hidden="true" viewBox="0 0 74 74" className="pointer-events-none absolute right-0 bottom-0 size-[74px]">
        <path
          d="M73.5 0A15.5 15.5 0 0 1 58 15.5L52 15.5A36.5 36.5 0 0 0 15.5 52L15.5 58A15.5 15.5 0 0 1 0 73.5"
          className="fill-none stroke-border/60"
          strokeWidth="1"
        />
      </svg>
      <span
        aria-hidden="true"
        className="absolute right-0 bottom-0 grid size-11 place-items-center rounded-full bg-primary text-on-primary transition-[background-color,scale] duration-300 group-hover:scale-105 group-hover:bg-nav-hover"
      >
        {children}
      </span>
    </>
  );
}
