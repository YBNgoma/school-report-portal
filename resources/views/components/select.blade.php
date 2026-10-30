@props(['options' => [], 'selected' => null, 'placeholder' => null])

<select {{ $attributes->merge(['name' => null])->class(['field']) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach (collect($options) as $key => $option)
        <option value="{{ $key }}" @selected(! is_null($selected) && (string) $key === (string) $selected)>{{ $option }}</option>
    @endforeach
</select>
