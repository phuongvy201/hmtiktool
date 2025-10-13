<?php

namespace App\Http\Requests;

use App\Models\SystemSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class PasswordUpdateRequest extends FormRequest
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
        $minLength = SystemSetting::getValue('password_min_length', 8);

        return [
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:' . $minLength,
                'confirmed',
                Rules\Password::defaults(),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $minLength = SystemSetting::getValue('password_min_length', 8);

        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => "Mật khẩu phải có ít nhất {$minLength} ký tự.",
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
