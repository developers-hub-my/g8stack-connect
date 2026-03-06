@props(['wire:model.live' => null, 'wire:model' => null])

<input type="text" inputmode="numeric" maxlength="6" {{ $attributes->merge(['class' => 'block w-full rounded-md border-zinc-300 text-center text-lg tracking-widest shadow-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white']) }} />
