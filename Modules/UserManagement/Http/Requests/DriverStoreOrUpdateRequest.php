<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverStoreOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->id;
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:17|unique:users,phone,' . $id,
            'password' => !is_null($this->password) ? 'required|min:8' : 'nullable',
            'confirm_password' => [
                Rule::requiredIf(function (){
                    return $this->password != null;
                }),
                'same:password'],
            'profile_image' => [
                Rule::requiredIf(empty($id)),
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:10000'
            ],
            // 'identification_type' => 'required|in:passport,driving_license,nid',
            'identification_type' => 'required|in:passport,driving_license,nin',
            
            // HR validation
            'passport_number' => 'required_if:identification_type,passport',
            'driving_license_number' => 'required_if:identification_type,driving_license',
            'nin_number' => 'required_if:identification_type,nin',
            'dob' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'expiry_date' => 'required|date|after_or_equal:today',
            'residential_address' => 'required|string|max:255',
            'state_city' => 'required|string|max:255',
            'postal_code' => 'required|regex:/^\d{5,10}$/',
            // 'identification_number' => 'required',
            // HR validation
            
            'identity_images' => 'array',
            'existing_documents' => 'nullable|array',
            'other_documents' => 'array',
            'other_documents.*' => [
                Rule::requiredIf(empty($id)),
                'max:10000'
            ],
            'identity_images.*' => [
                Rule::requiredIf(empty($id)),
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:10000'
            ],
            
            // Contact information
            'mobile_number' => 'required|regex:/^[0-9]{10,15}$/',
            'alternative_number' => 'nullable|regex:/^[0-9]{10,15}$/',
            'email_address' => 'required|email',
            
            // Vehicle information
            'vehicle_make_model' => 'required|string|max:255',
            'vehicle_registration' => 'required|regex:/^[A-Za-z0-9-]{6,15}$/',
            'year_of_manufacture' => 'required|integer|between:1900,2025',
            'insurance_policy' => 'required|regex:/^[A-Za-z0-9-]{6,20}$/',
            'insurance_expiry' => 'required|date|after_or_equal:today',
            
            // Guarantor 1
            'full_name1' => 'required|regex:/^[A-Za-z\s]+$/',
            'relationship1' => 'required|string|max:255',
            'residential_address1' => 'required|string|max:255',
            'mobile_number1' => 'required|regex:/^\d{10,15}$/',
            'email_address1' => 'required|email',
            'occupation1' => 'required|string|max:255',
            'nin1' => 'required|regex:/^\d{11}$/',
            
            // Guarantor 2 
            'full_name2' => 'required|regex:/^[A-Za-z\s]+$/',
            'relationship2' => 'required|string|max:255',
            'residential_address2' => 'required|string|max:255',
            'mobile_number2' => 'required|regex:/^\d{10,15}$/',
            'email_address2' => 'required|email',
            'occupation2' => 'required|string|max:255',
            'nin2' => 'required|regex:/^\d{11}$/',
            
            // Emergency contact information
            'full_name' => 'required|regex:/^[A-Za-z\s]+$/',
            'relationship' => 'required|string|max:255',
            'mobile_number' => 'required|regex:/^\d{10,15}$/',
            'alternative_number' => 'nullable|regex:/^\d{10,15}$/',
            'address' => 'required|string|max:255',
            
            // Declaration Section
            'declarant_name' => 'required|string|max:255',
            'signature' => 'required|string|max:255',
            'declaration_date' => 'required|date',
    
            // Official Use Section
            'application_status' => 'required|in:approved,rejected',
            'reviewed_by' => 'required|string|max:255',
            'review_date' => 'required|date',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
