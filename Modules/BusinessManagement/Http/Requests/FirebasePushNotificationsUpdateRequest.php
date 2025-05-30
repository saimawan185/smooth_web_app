<?php

namespace Modules\BusinessManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\BusinessManagement\Rules\FirebasePushNotificationsPlaceholders;


class FirebasePushNotificationsUpdateRequest extends FormRequest
{
    public function rules(): array
    {

        return [
            'notification.*.status' => 'nullable',
            'notification.*.value' => [function ($attribute, $value, $fail) {
                $notificationKey = explode('.', $attribute)[1];
                $status = $this->input('notification.' . $notificationKey . '.status');
                if ($status !== null && empty($value)) {
                    $fail('The value for ' . translate($notificationKey) . ' is required when status is active.');
                }
            },'max:191'],
            'notification.chatting_new_message.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['userName']),
            ],
            'notification.driver_tips_from_customer.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['tipsAmount']),
            ],
            'notification.customer_payment_successful.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['paidAmount', 'methodName']),
            ],
            'notification.driver_customer_rejected_bid.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['tripId']),
            ],
            'notification.customer_parcel_returning_otp.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['otp']),
            ],
            'notification.level_level_up.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['levelName']),
            ],
            'notification.fund_fund_added_by_admin.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['walletAmount']),
            ],
            'notification.withdraw_request_withdraw_request_rejected.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['withdrawNote']),
            ],
            'notification.referral_referral_reward_received.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['referralRewardAmount']),
            ],
            'notification.driver_parcel_amount_deducted.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId', 'approximateAmount']),
            ],
            'notification.driver_refund_accepted.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId']),
            ],
            'notification.customer_refund_accepted.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId']),
            ],
            'notification.driver_refund_denied.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId']),
            ],
            'notification.customer_refund_denied.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId']),
            ],
            'notification.driver_parcel_amount_debited.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['approximateAmount']),
            ],
            'notification.customer_refunded_to_wallet.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId','approximateAmount']),
            ],
            'notification.customer_refunded_as_coupon.value' => [
                'max:191',
                new FirebasePushNotificationsPlaceholders(['parcelId','approximateAmount']),
            ],

        ];
    }

    public function authorize(): bool
    {
        return Auth::check();
    }
}
