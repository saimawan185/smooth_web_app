<?php

namespace Modules\TripManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RideRequestCreate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pickup_coordinates' => 'required_if:trip_request_id,null',
            'destination_coordinates' => 'required_if:trip_request_id,null',
            'customer_coordinates' => 'required_if:trip_request_id,null',
            'customer_request_coordinates' => 'required_if:trip_request_id,null',
            'pickup_address' => 'required_if:trip_request_id,null',
            'destination_address' => 'required_if:trip_request_id,null',

            // Trip and Fare Information
            'estimated_distance' => 'required_if:trip_request_id,null',
            'estimated_time' => 'required_if:trip_request_id,null',
            'estimated_fare' => 'required_if:trip_request_id,null|numeric',
            'bid' => 'required|bool',
            'actual_fare' => ['nullable',
                Rule::requiredIf(function () {
                    return $this->bid;
                }), 'numeric'],

            // Return and Cancellation Fees
            'return_fee' => [
                Rule::requiredIf(function () {
                    return $this->type === 'parcel' && is_null($this->trip_request_id);
                })
            ],
            'cancellation_fee' => [
                Rule::requiredIf(function () {
                    return $this->type === 'parcel' && is_null($this->trip_request_id);
                })
            ],

            // Vehicle Category
            'vehicle_category_id' => [
                'nullable',
                Rule::requiredIf(function ()  {
                    return $this->type === 'ride_request' && is_null($this->trip_request_id);
                }), 'uuid'
            ],

            // Other Fields
            'note' => 'sometimes',
            'type' => 'required|in:parcel,ride_request',

            // Sender and Receiver Information (required only for parcel type)
            'sender_name' => 'required_if:type,parcel',
            'sender_phone' => 'required_if:type,parcel',
            'sender_address' => 'required_if:type,parcel',
            'receiver_name' => 'required_if:type,parcel',
            'receiver_phone' => 'required_if:type,parcel',
            'receiver_address' => 'required_if:type,parcel',

            // Parcel Information (required for parcel type)
            'parcel_category_id' => 'required_if:type,parcel',
            'weight' => 'required_if:type,parcel',
            'payer' => 'required_if:type,parcel',

            // Additional Fare Fields
            'extra_estimated_fare' => 'sometimes|numeric',
            'extra_discount_fare' => 'sometimes|numeric',
            'extra_discount_amount' => 'sometimes|numeric',
            'extra_return_fee' => 'sometimes|numeric',
            'extra_cancellation_fee' => 'sometimes|numeric',
            'extra_fare_amount' => 'sometimes|numeric',
            'extra_fare_fee' => 'sometimes|numeric',

            // Encoded Polyline and Zone Information
            'encoded_polyline' => 'sometimes',
            'zone_id' => 'required|uuid|exists:zones,id',
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
