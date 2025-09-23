<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Unique>>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof \App\Models\Product ? $product->id : (is_numeric($product) ? (int) $product : null);

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'sometimes',
                'string',
                'max:5000',
                'min:10',
            ],
            'price' => [
                'sometimes',
                'numeric',
                'min:0.01',
                'max:999999.99',
            ],
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'sometimes',
                'integer',
                'exists:brands,id',
            ],
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'stock_quantity' => [
                'sometimes',
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
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'اسم المنتج يجب أن يكون على الأقل 3 أحرف',
            'name.max' => 'اسم المنتج لا يمكن أن يتجاوز 255 حرف',
            'description.min' => 'وصف المنتج يجب أن يكون على الأقل 10 أحرف',
            'description.max' => 'وصف المنتج لا يمكن أن يتجاوز 5000 حرف',
            'price.numeric' => 'سعر المنتج يجب أن يكون رقماً',
            'price.min' => 'سعر المنتج يجب أن يكون أكبر من 0',
            'price.max' => 'سعر المنتج لا يمكن أن يتجاوز 999999.99',
            'category_id.exists' => 'فئة المنتج المحددة غير موجودة',
            'brand_id.exists' => 'علامة المنتج التجارية المحددة غير موجودة',
            'sku.unique' => 'رمز المنتج (SKU) مستخدم بالفعل',
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
     *
     * @return array<string, string>
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
        $data = [];

        if ($this->has('name')) {
            $name = $this->input('name');
            $data['name'] = is_string($name) ? trim($name) : '';
        }

        if ($this->has('description')) {
            $description = $this->input('description');
            $data['description'] = is_string($description) ? trim($description) : '';
        }

        if ($this->has('sku')) {
            $sku = $this->input('sku');
            $data['sku'] = is_string($sku) ? strtoupper(trim($sku)) : '';
        }

        if ($this->has('tags')) {
            $tags = $this->input('tags');
            $data['tags'] = is_array($tags) ? array_map(fn ($tag): string => is_string($tag) ? trim($tag) : '', $tags) : [];
        }

        if (! empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator): void {
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

            // Check if price change is significant
            if ($this->has('price')) {
                $product = $this->route('product');
                $oldPrice = $product instanceof \App\Models\Product ? $product->price : null;
                $newPrice = $this->input('price');

                if ($oldPrice && $newPrice && is_numeric($oldPrice) && $oldPrice > 0) {
                    $oldPriceFloat = (float) $oldPrice;
                    $newPriceFloat = is_numeric($newPrice) ? (float) $newPrice : 0.0;
                    if ($oldPriceFloat > 0) {
                        $changePercentage = abs($newPriceFloat - $oldPriceFloat) / $oldPriceFloat * 100;

                        if ($changePercentage > 50) { // More than 50% change
                            $validator->warnings()->add('price', 'تغيير السعر بنسبة '.round($changePercentage, 2).'% - يرجى التأكد من صحة السعر');
                        }
                    }
                }
            }
        });
    }

    /**
     * Get the validated data from the request.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Add computed fields
        if (is_array($validated) && isset($validated['name'])) {
            $name = $validated['name'];
            $validated['slug'] = Str::slug(is_string($name) ? $name : '');
        }

        if (is_array($validated)) {
            $validated['updated_by'] = $this->user()?->id;
        }

        return $validated;
    }
}
