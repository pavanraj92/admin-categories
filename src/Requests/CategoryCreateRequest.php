<?php

namespace admin\categories\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'sort_order' => 'required|numeric|min:0|max:2147483647|unique:categories,sort_order',
            'image' => 'required|image',
            'status' => 'required|in:0,1',
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

