<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_language_id' => 'required',
            'due_date' => Rule::requiredIf(fn () => request()->input('immediate') == 'no'),
            'due_time' => [
                Rule::requiredIf(fn () => request()->input('immediate') == 'no'),
                new DateTimeValidation()
            ],
            'customer_phone_type' => Rule::requiredIf(fn () => request()->input('immediate') == 'no'),
            'duration' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'from_language_id' => 'Du måste fylla in alla fält',
            'due_date' => 'Du måste fylla in alla fält',
            'due_time' => 'Du måste fylla in alla fält',
            'customer_phone_type' => 'Du måste fylla in alla fält',
            'duration' => 'Du måste fylla in alla fält',
        ];
    }
}
