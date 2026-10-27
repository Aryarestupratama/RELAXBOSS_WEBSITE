import * as React from "react"
import { Progress as ProgressPrimitive } from "radix-ui"
import { cn } from "cn"

/**
 * Progress gaya shadcn (Radix). Jalur memakai --border, isi --primary (15,85:1 dengan putih).
 * Wajib diberi `aria-label`. Posisi isi lewat atribut `style` (diizinkan CSP: style-src-attr).
 */
function Progress({
  className,
  value,
  max = 100,
  ...props
}: React.ComponentProps<typeof ProgressPrimitive.Root>) {
  const percent = max > 0 ? Math.min(100, Math.max(0, ((value ?? 0) / max) * 100)) : 0

  return (
    <ProgressPrimitive.Root
      data-slot="progress"
      value={value}
      max={max}
      className={cn("relative h-2 w-full overflow-hidden rounded-full bg-border", className)}
      {...props}
    >
      <ProgressPrimitive.Indicator
        data-slot="progress-indicator"
        className="h-full w-full rounded-full bg-primary transition-transform duration-300 ease-out motion-reduce:transition-none"
        style={{ transform: `translateX(-${100 - percent}%)` }}
      />
    </ProgressPrimitive.Root>
  )
}

export { Progress }
