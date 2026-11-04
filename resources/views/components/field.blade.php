@props(['name', 'label', 'type' => 'text', 'rows' => 4])

<div>
    <label for="{{ $name }}" class="block text-sm text-slate-300 mb-1">{{ $label }}</label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
                  {{ $attributes->merge(['class' => 'w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500']) }}></textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
               {{ $attributes->merge(['class' => 'w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500']) }}
               @if($type !== 'password') value="{{ old($name) }}" @endif>
    @endif

    @error($name)
        <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
    @enderror
</div>