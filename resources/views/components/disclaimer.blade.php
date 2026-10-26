<div {{ $attributes->merge(['class' => 'rounded-2xl bg-soft-lavender p-4 text-sm text-text [&_a]:rounded [&_a]:bg-card [&_a]:mx-2 [&_a]:inline-block [&_a]:px-3 [&_a]:py-1.5 [&_a]:font-medium [&_a]:text-text [&_a]:no-underline']) }} role="note">
    {{ $slot }}
</div>
