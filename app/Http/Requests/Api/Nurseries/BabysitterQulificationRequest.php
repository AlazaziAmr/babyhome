<?php

namespace App\Http\Requests\Api\Nurseries;

use Illuminate\Foundation\Http\FormRequest;

class BabysitterQulificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'qualification_id' => 'required|exists:qualifications,id',
            'description' => 'required|string',
        ];
    }
}
