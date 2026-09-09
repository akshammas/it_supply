<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'model_number' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price_type' => ['required', Rule::in(['fixed', 'sale', 'on_request'])],
            'price' => ['nullable', 'required_if:price_type,fixed,sale', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'required_if:price_type,sale', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock', 'preorder'])],
            'condition' => ['required', Rule::in(['new', 'refurbished', 'used'])],
            'featured' => ['boolean'],
            'status' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],

            'specs' => ['nullable', 'array'],
            'specs.*' => ['nullable', 'string', 'max:255'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary_image_index' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
