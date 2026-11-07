{{--
    Checkbox list with "Select all".
    Expects: $name (input name without []), $title, $items (array of ['id' => .., 'label' => ..]),
             $selected (array of ids), optional $help
--}}
@php
    $uid = 'tick_'.$name;
    $selectedIds = array_map('intval', (array) old($name, $selected ?? []));
@endphp
<div class="col-12">
    <label class="form-label fw-semibold">{{ $title }}</label>
    <div class="border rounded p-2" id="{{ $uid }}">
        <div class="form-check border-bottom pb-2 mb-2">
            <input type="checkbox" class="form-check-input" id="{{ $uid }}_all" data-tick-all>
            <label class="form-check-label fw-semibold" for="{{ $uid }}_all">Select all</label>
        </div>
        <div class="row" style="max-height:260px; overflow:auto;">
            @forelse($items as $item)
                <div class="col-md-4 col-sm-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" data-tick-item name="{{ $name }}[]"
                               value="{{ $item['id'] }}" id="{{ $uid }}_{{ $item['id'] }}" @checked(in_array((int) $item['id'], $selectedIds, true))>
                        <label class="form-check-label" for="{{ $uid }}_{{ $item['id'] }}">{{ $item['label'] }}</label>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted small">Nothing to choose from yet.</div>
            @endforelse
        </div>
    </div>
    @isset($help)<div class="form-text">{{ $help }}</div>@endisset
</div>
<script>
    (function () {
        const box = document.getElementById(@json($uid));
        const all = box.querySelector('[data-tick-all]');
        const items = box.querySelectorAll('[data-tick-item]');
        const sync = () => { all.checked = items.length > 0 && [...items].every(i => i.checked); };
        all.addEventListener('change', () => items.forEach(i => i.checked = all.checked));
        items.forEach(i => i.addEventListener('change', sync));
        sync();
    })();
</script>
