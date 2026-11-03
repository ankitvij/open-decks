@props(['name', 'label', 'type' => 'text', 'hint' => null, 'value' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @error($name) aria-invalid="true" @enderror
           {{ $attributes->merge(['class' => 'mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 placeholder:text-zinc-600']) }}>
    @if ($hint)
        <p class="mt-1 text-xs text-zinc-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
    @enderror
</div>
