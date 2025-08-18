<?php

namespace admin\categories\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [                   
            'parent_category_id' => 'nullable|numeric',        
            'title' => 'required|string|min:3|max:100|unique:categories,title',
            'sort_order' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
                Rule::unique('categories', 'sort_order')
                ->where(function ($query) {
                    if ($this->parent_category_id) {
                        // Subcategory → unique within its parent
                        $query->where('parent_category_id', $this->parent_category_id);
                    } else {
                        // Main category → unique among other categories
                         $query->where(function ($q) {
                            $q->whereNull('parent_category_id')
                            ->orWhere('parent_category_id', 0);
                        });
                    }
                    $query->whereNull('deleted_at');
                }),
            ],
            'status' => 'required|in:0,1',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}

