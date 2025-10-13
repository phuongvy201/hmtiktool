<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Vui lòng chọn file ảnh.',
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Chỉ chấp nhận file: jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'Kích thước file không được vượt quá 2MB.',
            'avatar.dimensions' => 'Kích thước ảnh phải từ 100x100 đến 2000x2000 pixel.',
        ];
    }
}
