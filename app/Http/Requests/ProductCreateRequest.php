<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Product::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'required',
                'string',
                'max:5000',
                'min:10',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'required',
                'integer',
                'exists:brands,id',
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],
            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999.99',
            ],
            'dimensions' => [
                'nullable',
                'array',
                'size:3',
            ],
            'dimensions.length' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999.99',
            ],
            'dimensions.width' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999.99',
            ],
            'dimensions.height' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999.99',
            ],
            'images' => [
                'nullable',
                'array',
                'max:10',
            ],
            'images.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
            ],
            'tags' => [
                'nullable',
                'array',
                'max:20',
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'is_active' => [
                'boolean',
            ],
            'is_featured' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المنتج مطلوب',
            'name.min' => 'اسم المنتج يجب أن يكون على الأقل 3 أحرف',
            'name.max' => 'اسم المنتج لا يمكن أن يتجاوز 255 حرف',
            'description.required' => 'وصف المنتج مطلوب',
            'description.min' => 'وصف المنتج يجب أن يكون على الأقل 10 أحرف',
            'description.max' => 'وصف المنتج لا يمكن أن يتجاوز 5000 حرف',
            'price.required' => 'سعر المنتج مطلوب',
            'price.numeric' => 'سعر المنتج يجب أن يكون رقماً',
            'price.min' => 'سعر المنتج يجب أن يكون أكبر من 0',
            'price.max' => 'سعر المنتج لا يمكن أن يتجاوز 999999.99',
            'category_id.required' => 'فئة المنتج مطلوبة',
            'category_id.exists' => 'فئة المنتج المحددة غير موجودة',
            'brand_id.required' => 'علامة المنتج التجارية مطلوبة',
            'brand_id.exists' => 'علامة المنتج التجارية المحددة غير موجودة',
            'sku.required' => 'رمز المنتج (SKU) مطلوب',
            'sku.unique' => 'رمز المنتج (SKU) مستخدم بالفعل',
            'stock_quantity.required' => 'كمية المخزون مطلوبة',
            'stock_quantity.integer' => 'كمية المخزون يجب أن تكون رقماً صحيحاً',
            'stock_quantity.min' => 'كمية المخزون لا يمكن أن تكون سالبة',
            'images.max' => 'يمكن رفع 10 صور كحد أقصى',
            'images.*.image' => 'الملف يجب أن يكون صورة',
            'images.*.mimes' => 'نوع الصورة يجب أن يكون: jpeg, png, jpg, gif, webp',
            'images.*.max' => 'حجم الصورة لا يمكن أن يتجاوز 5 ميجابايت',
            'tags.max' => 'يمكن إضافة 20 علامة كحد أقصى',
            'tags.*.max' => 'العلامة لا يمكن أن تتجاوز 50 حرف',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المنتج',
            'description' => 'وصف المنتج',
            'price' => 'سعر المنتج',
            'category_id' => 'فئة المنتج',
            'brand_id' => 'علامة المنتج التجارية',
            'sku' => 'رمز المنتج',
            'stock_quantity' => 'كمية المخزون',
            'weight' => 'الوزن',
            'dimensions' => 'الأبعاد',
            'images' => 'الصور',
            'tags' => 'العلامات',
            'is_active' => 'حالة النشاط',
            'is_featured' => 'المنتج المميز',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format data before validation
        $this->merge([
            'name' => trim($this->name),
            'description' => trim($this->description),
            'sku' => strtoupper(trim($this->sku)),
            'tags' => $this->tags ? array_map('trim', $this->tags) : null,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            if ($this->has('dimensions')) {
                $dimensions = $this->input('dimensions');
                if (is_array($dimensions) && count($dimensions) === 3) {
                    $total = array_sum($dimensions);
                    if ($total > 2000) { // 2000cm total
                        $validator->errors()->add('dimensions', 'مجموع الأبعاد لا يمكن أن يتجاوز 2000 سم');
                    }
                }
            }

            // Check if price is reasonable for the category
            if ($this->has('price') && $this->has('category_id')) {
                $price = $this->input('price');
                $categoryId = $this->input('category_id');

                // This would check against category price ranges
                // For now, we'll just log it
                \Log::info('Price validation for category', [
                    'price' => $price,
                    'category_id' => $categoryId,
                ]);
            }
        });
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Add computed fields
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['created_by'] = $this->user()?->id;

        return $validated;
    }
}
