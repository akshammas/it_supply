@if($banners->isNotEmpty())
    @php
        $carouselId = $carouselId ?? 'topBannerCarousel';
        $first = $banners->first();
        $file  = Storage::disk('public')->path($first->image);
        $size  = is_file($file) ? @getimagesize($file) : false;
        $w = $size[0] ?? 1600; $h = $size[1] ?? 400;
    @endphp

    <div id="{{ $carouselId }}" class="carousel slide carousel-fade mb-4 rounded overflow-hidden"
         data-bs-ride="carousel" data-bs-interval="5000">
        @if($banners->count() > 1)
            <div class="carousel-indicators">
                @foreach($banners as $i => $b)
                    <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}"
                            class="{{ $loop->first ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
        @endif

        <div class="carousel-inner">
            @foreach($banners as $b)
                @php $tag = $b->link_url ? 'a href='.e($b->link_url) : 'div'; @endphp
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <{!! $tag !!} class="d-block position-relative text-decoration-none">
                        <img src="{{ Storage::url($b->image) }}" alt="{{ $b->title ?? 'Banner' }}"
                             class="img-fluid w-100 d-block" width="{{ $w }}" height="{{ $h }}"
                             style="aspect-ratio: {{ $w }} / {{ $h }};" decoding="async">
                        @if($b->title)
                            <div class="d-none d-md-block position-absolute bottom-0 start-0 w-100 p-3 text-white"
                                 style="background:linear-gradient(transparent, rgba(0,0,0,.65));">
                                <div class="fw-semibold fs-5">{{ $b->title }}</div>
                                @if($b->subtitle)<div class="small opacity-75">{{ $b->subtitle }}</div>@endif
                            </div>
                        @endif
                    </{!! explode(' ', $tag)[0] !!}>
                </div>
            @endforeach
        </div>

        @if($banners->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
@endif