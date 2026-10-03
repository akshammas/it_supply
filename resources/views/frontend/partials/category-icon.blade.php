@if($category->image)
    <img src="{{ Storage::url($category->image) }}" alt=""
         style="width:{{ $size ?? 32 }}px; height:{{ $size ?? 32 }}px; object-fit:contain;">
@else
    <i class="bi {{ $category->icon ?: 'bi-box2' }} {{ $iconClass ?? 'fs-5 text-danger' }}"></i>
@endif