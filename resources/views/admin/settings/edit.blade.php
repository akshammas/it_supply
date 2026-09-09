@extends('admin.layouts.admin')
@section('title', 'Settings')

@section('content')
    <h3 class="mb-4">Website Settings</h3>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Company Info</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if(!empty($settings['logo']))<img src="{{ Storage::url($settings['logo']) }}" class="mt-2" style="max-height:40px">@endif
                </div>
                <div class="col-md-3">
                    <label class="form-label">Favicon</label>
                    <input type="file" name="favicon" class="form-control" accept="image/*">
                    @if(!empty($settings['favicon']))<img src="{{ Storage::url($settings['favicon']) }}" class="mt-2" style="max-height:24px">@endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" class="form-control" placeholder="971501234567">
                    <div class="form-text">International format, no + or spaces (used for wa.me links)</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address', $settings['address'] ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Google Maps URL</label>
                    <input type="url" name="google_maps_url" value="{{ old('google_maps_url', $settings['google_maps_url'] ?? '') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Social Links</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Facebook</label>
                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Instagram</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">LinkedIn</label>
                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $settings['linkedin_url'] ?? '') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Business Settings</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $settings['currency'] ?? 'AED') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Default VAT %</label>
                    <input type="number" step="0.01" name="vat_percent" value="{{ old('vat_percent', $settings['vat_percent'] ?? 5) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Default Enquiry Message Template</label>
                    <textarea name="default_enquiry_message" rows="3" class="form-control">{{ old('default_enquiry_message', $settings['default_enquiry_message'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Homepage</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">SEO Defaults</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Site-wide Meta Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $settings['seo_title'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Site-wide Meta Description</label>
                    <input type="text" name="seo_description" value="{{ old('seo_description', $settings['seo_description'] ?? '') }}" class="form-control">
                </div>
            </div>
        </div>

        <button class="btn btn-primary btn-lg">Save Settings</button>
    </form>
@endsection
