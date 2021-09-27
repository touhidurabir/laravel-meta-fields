<?php

namespace Touhidurabir\MetaFields\Tests\App\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Touhidurabir\MetaFields\WithMetaFields;

class StoreUser extends FormRequest {

    use WithMetaFields;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {

        return true;
    }
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {

        return [
            'email' => [
                'required',
                'string',
                'email',
            ],
            'password'   => [
                'required',
                'string',
                'min:3',
            ],
        ];
    }


    /**
     * The meta fields validation rules
     *
     * @return array
     */
    public function metaRules() : array {

        return [
            'bio' => [
                'required',
                'string',
                'min:1',
            ],
            'dob' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
    }

}
