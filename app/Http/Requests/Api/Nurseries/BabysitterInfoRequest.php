<?php

namespace App\Http\Requests\Api\Nurseries;

use Illuminate\Foundation\Http\FormRequest;

class BabysitterInfoRequest extends FormRequest
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
        return [
            'years_of_experince'  => 'required|integer',
//            'date_of_birth'  => 'required|date',
            'free_of_disease' => 'required|integer',
            'languages' => 'nullable|array',
//            'national_id' =>  'required',
//            'nationality'  => 'required|integer',
        ];
    }
}
