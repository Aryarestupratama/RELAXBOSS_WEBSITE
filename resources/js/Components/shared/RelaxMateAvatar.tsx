type RelaxMateAvatarProps = {
  size?: 'sm' | 'md' | 'lg';
  className?: string;
};

const SIZES = { sm: 'size-8', md: 'size-12', lg: 'size-16' } as const;

/**
 * Avatar RelaxMate: kepala robot statis di lingkaran `--soft-lavender`. Hiasan (`aria-hidden`), tidak membawa informasi;
 * pengirim pesan sudah dinyatakan lewat teks tersembunyi di `ChatBubble`.
 */
export default function RelaxMateAvatar({ size = 'sm', className = '' }: RelaxMateAvatarProps) {
  return (
    <span
      aria-hidden="true"
      className={`inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-soft-lavender ${SIZES[size]} ${className}`}
    >
      <img src="/images/relaxmate/relaxmate-avatar.webp" width={192} height={192} alt="" className="size-full object-contain p-[10%]" />
    </span>
  );
}
