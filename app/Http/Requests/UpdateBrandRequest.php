<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $brandId = $this->route('brand') ? $this->route('brand')->id : null;

        return [
            'name' => 'required|string|max:255|unique:brands,name,'.$brandId,
            'slug' => 'required|string|max:255|unique:brands,slug,'.$brandId,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ];
    }
}
