<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Search is public
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],
            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'gte:min_price',
            ],
            'sort' => [
                'nullable',
                'string',
                'in:name,price,created_at,updated_at,popularity',
            ],
            'order' => [
                'nullable',
                'string',
                'in:asc,desc',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'tags' => [
                'nullable',
                'array',
                'max:10',
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'in_stock' => [
                'nullable',
                'boolean',
            ],
            'featured' => [
                'nullable',
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
            'q.required' => 'كلمة البحث مطلوبة',
            'q.min' => 'كلمة البحث يجب أن تكون على الأقل حرفين',
            'q.max' => 'كلمة البحث لا يمكن أن تتجاوز 255 حرف',
            'category_id.exists' => 'فئة المنتج المحددة غير موجودة',
            'brand_id.exists' => 'علامة المنتج التجارية المحددة غير موجودة',
            'min_price.numeric' => 'الحد الأدنى للسعر يجب أن يكون رقماً',
            'min_price.min' => 'الحد الأدنى للسعر لا يمكن أن يكون سالباً',
            'max_price.numeric' => 'الحد الأقصى للسعر يجب أن يكون رقماً',
            'max_price.min' => 'الحد الأقصى للسعر لا يمكن أن يكون سالباً',
            'max_price.gte' => 'الحد الأقصى للسعر يجب أن يكون أكبر من أو يساوي الحد الأدنى',
            'sort.in' => 'نوع الترتيب غير صحيح',
            'order.in' => 'اتجاه الترتيب غير صحيح',
            'page.min' => 'رقم الصفحة يجب أن يكون أكبر من 0',
            'page.max' => 'رقم الصفحة لا يمكن أن يتجاوز 1000',
            'per_page.min' => 'عدد العناصر في الصفحة يجب أن يكون أكبر من 0',
            'per_page.max' => 'عدد العناصر في الصفحة لا يمكن أن يتجاوز 100',
            'tags.max' => 'يمكن البحث بـ 10 علامات كحد أقصى',
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
            'q' => 'كلمة البحث',
            'category_id' => 'فئة المنتج',
            'brand_id' => 'علامة المنتج التجارية',
            'min_price' => 'الحد الأدنى للسعر',
            'max_price' => 'الحد الأقصى للسعر',
            'sort' => 'نوع الترتيب',
            'order' => 'اتجاه الترتيب',
            'page' => 'رقم الصفحة',
            'per_page' => 'عدد العناصر في الصفحة',
            'tags' => 'العلامات',
            'in_stock' => 'متوفر في المخزون',
            'featured' => 'منتج مميز',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format data before validation
        $data = [];

        if ($this->has('q')) {
            $data['q'] = is_string($this->q) ? trim($this->q) : '';
        }

        if ($this->has('tags')) {
            $data['tags'] = is_array($this->tags) ? array_map(fn ($tag) => is_string($tag) ? trim($tag) : '', $this->tags) : [];
        }

        if (! empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            if ($this->has('min_price') && $this->has('max_price')) {
                $minPrice = $this->input('min_price');
                $maxPrice = $this->input('max_price');

                if ($minPrice && $maxPrice && $minPrice > $maxPrice) {
                    $validator->errors()->add('max_price', 'الحد الأقصى للسعر يجب أن يكون أكبر من الحد الأدنى');
                }
            }

            // Check if search query is too generic
            if ($this->has('q')) {
                $query = $this->input('q');
                $genericTerms = ['منتج', 'product', 'item', 'thing', 'stuff'];

                if (is_string($query) && in_array(strtolower($query), $genericTerms)) {
                    $validator->warnings()->add('q', 'كلمة البحث عامة جداً - جرب كلمات أكثر تحديداً للحصول على نتائج أفضل');
                }
            }
        });
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Set default values
        if (is_array($validated)) {
            $validated['sort'] = $validated['sort'] ?? 'popularity';
            $validated['order'] = $validated['order'] ?? 'desc';
            $validated['page'] = is_numeric($validated['page'] ?? null) ? (int) $validated['page'] : 1;
            $validated['per_page'] = is_numeric($validated['per_page'] ?? null) ? (int) $validated['per_page'] : 15;
        }

        return $validated;
    }

    /**
     * Get search filters.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $filters = [];

        if ($this->has('category_id')) {
            $filters['category_id'] = $this->input('category_id');
        }

        if ($this->has('brand_id')) {
            $filters['brand_id'] = $this->input('brand_id');
        }

        if ($this->has('min_price')) {
            $filters['min_price'] = $this->input('min_price');
        }

        if ($this->has('max_price')) {
            $filters['max_price'] = $this->input('max_price');
        }

        if ($this->has('tags')) {
            $filters['tags'] = $this->input('tags');
        }

        if ($this->has('in_stock')) {
            $filters['in_stock'] = $this->input('in_stock');
        }

        if ($this->has('featured')) {
            $filters['featured'] = $this->input('featured');
        }

        return $filters;
    }

    /**
     * Get search query.
     */
    public function getQuery(): string
    {
        return $this->input('q', '');
    }

    /**
     * Get pagination parameters.
     *
     * @return array<string, int>
     */
    public function getPagination(): array
    {
        return [
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', 15),
        ];
    }

    /**
     * Get sorting parameters.
     *
     * @return array<string, string>
     */
    public function getSorting(): array
    {
        return [
            'sort' => $this->input('sort', 'popularity'),
            'order' => $this->input('order', 'desc'),
        ];
    }
}
