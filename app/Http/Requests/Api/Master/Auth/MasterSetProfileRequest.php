<?php

namespace App\Http\Requests\Api\Master\Auth;

use Illuminate\Foundation\Http\FormRequest;

class MasterSetProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return [
                'master_id' => 'required|numeric',
//            'first_name'         => 'required|array',
                'first_name.ar' => 'required|string|max:255|min:4',
                'first_name.en' => 'required|string|max:255|min:4',
//            'last_name'         => 'required|array',
                'last_name.ar' => 'required|string|max:255|min:4',
                'last_name.en' => 'required|string|max:255|min:4',
//            'gender'         => 'required|array',
                'gender.ar' => 'required|string|max:255|min:3',
                'gender.en' => 'required|string|max:255|min:4',
                'card_expiration_date' => 'required|date',
                'date_of_birth' => 'required|date',
//                'email' => 'required|string|email|max:191|unique:masters,email',
                'email' => 'required|string|email|max:191',
//                'password'    => 'required|string|min:6|confirmed',
//                'password_confirmation' => 'required',
//                'phone' => 'required|max:15|phone_number|unique:masters,phone',
                'national_id' => 'required|max:15|string|unique:masters,national_id',
                'nationality_id' => 'required|exists:nationalities,id',
                'address' => 'required|string',
                'latitude' => 'required|between:0,99.99',
                'longitude' => 'required|between:0,99.99',
            ];
        } else {
            return [
//                'master_id' => 'required|numeric',
                'name' => 'required|string|max:255|min:4',
//                'email' => 'required|string|email|max:191|unique:masters,email',
//                'password' => 'required|string|min:6|confirmed',
//                'password_confirmation' => 'required',
//                'phone' => 'required|max:15|phone_number|unique:masters,phone',
                'national_id' => 'required|max:15|string|unique:masters,national_id',
//                'nationality_id' => 'required|exists:nationalities,id',
                'address' => 'required|string',
                'latitude' => 'required|between:0,99.99',
                'longitude' => 'required|between:0,99.99',
            ];
        }
    }
}
