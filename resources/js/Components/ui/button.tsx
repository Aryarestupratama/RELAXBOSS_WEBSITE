import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { cn } from "cn"
import { Slot } from "radix-ui"

/**
 * Tombol RelaxBoss (Design bagian 2, CMP-001).
 * - Varian: default, outline, ghost, destructive. `secondary` dan `link` tidak dibawa.
 * - Ukuran: default dan icon (44 x 44). Target sentuh minimal 44px.
 * - Cincin fokus: outline 2px --brand-strong, offset 3px (aturan global di app.css).
 */
const buttonVariants = cva(
  "group/button inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border-[1.5px] border-transparent text-base font-medium whitespace-nowrap transition duration-150 ease-out select-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-5",
  {
    variants: {
      variant: {
        default:
          "border-primary bg-primary text-primary-foreground hover:brightness-130",
        outline:
          "border-primary bg-transparent text-primary hover:bg-primary hover:text-primary-foreground",
        ghost:
          "text-foreground hover:bg-muted",
        destructive:
          "border-destructive bg-transparent text-destructive hover:bg-crisis-bg hover:text-crisis-text",
      },
      size: {
        default: "min-h-11 px-6 py-2.5",
        icon: "size-11 p-0",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

function Button({
  className,
  variant = "default",
  size = "default",
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
  }) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="button"
      data-variant={variant}
      data-size={size}
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Button, buttonVariants }
