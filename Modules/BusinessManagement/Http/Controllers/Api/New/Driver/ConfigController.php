<?php

namespace Modules\BusinessManagement\Http\Controllers\Api\New\Driver;

use DateTimeZone;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Modules\BusinessManagement\Service\Interface\BusinessSettingServiceInterface;
use Modules\BusinessManagement\Service\Interface\CancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interface\ParcelCancellationReasonServiceInterface;
use Modules\BusinessManagement\Service\Interface\QuestionAnswerServiceInterface;
use Modules\BusinessManagement\Service\Interface\SafetyAlertReasonServiceInterface;
use Modules\BusinessManagement\Service\Interface\SafetyPrecautionServiceInterface;
use Modules\BusinessManagement\Service\Interface\SettingServiceInterface;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\UserManagement\Service\Interface\UserLastLocationServiceInterface;
use Modules\ZoneManagement\Service\Interface\ZoneServiceInterface;

class ConfigController extends Controller
{
    protected $businessSettingService;
    protected $settingService;
    protected $cancellationReasonService;
    protected $parcelCancellationReasonService;
    protected $zoneService;
    protected $userLastLocationService;
    protected $tripRequestService;
    protected $questionAnswerService;
    protected $safetyAlertReasonService;

    protected $safetyPrecautionService;

    public function __construct(BusinessSettingServiceInterface    $businessSettingService, SettingServiceInterface $settingService,
                                CancellationReasonServiceInterface $cancellationReasonService, ParcelCancellationReasonServiceInterface $parcelCancellationReasonService,
                                ZoneServiceInterface               $zoneService, UserLastLocationServiceInterface $userLastLocationService,
                                TripRequestServiceInterface        $tripRequestService, QuestionAnswerServiceInterface $questionAnswerService,
                                SafetyAlertReasonServiceInterface  $safetyAlertReasonService, SafetyPrecautionServiceInterface $safetyPrecautionService)
    {
        $this->businessSettingService = $businessSettingService;
        $this->settingService = $settingService;
        $this->cancellationReasonService = $cancellationReasonService;
        $this->parcelCancellationReasonService = $parcelCancellationReasonService;
        $this->zoneService = $zoneService;
        $this->userLastLocationService = $userLastLocationService;
        $this->tripRequestService = $tripRequestService;
        $this->questionAnswerService = $questionAnswerService;
        $this->safetyAlertReasonService = $safetyAlertReasonService;
        $this->safetyPrecautionService = $safetyPrecautionService;
    }

    public function configuration()
    {
        $info = $this->businessSettingService->getAll(limit: 999, offset: 1);
        $loyaltyPoints = $info
            ->where('key_name', 'loyalty_points')
            ->firstWhere('settings_type', 'driver_settings')?->value;
        $appVersions = $this->businessSettingService->getBy(criteria: ['settings_type' => APP_VERSION]);
        $dataValues = $this->settingService->getBy(criteria: ['settings_type' => SMS_CONFIG]);
        if ($dataValues->where('live_values.status', 1)->isEmpty()) {
            $smsConfiguration = 0;
        } else {
            $smsConfiguration = 1;
        }
        $configs = [
            'is_demo' => (bool)env('APP_MODE') != 'live',
            'maintenance_mode' => checkMaintenanceMode(),
            'required_pin_to_start_trip' => (bool)$info->firstWhere('key_name', 'required_pin_to_start_trip')?->value ?? false,
            'add_intermediate_points' => (bool)$info->firstWhere('key_name', 'add_intermediate_points')?->value ?? false,
            'business_name' => $info->firstWhere('key_name', 'business_name')?->value ?? null,
            'logo' => $info->firstWhere('key_name', 'header_logo')?->value ?? null,
            'bid_on_fare' => (bool)$info->firstWhere('key_name', 'bid_on_fare')?->value ?? 0,
            'driver_completion_radius' => $info->firstWhere('key_name', 'driver_completion_radius')?->value ?? 10,
            'country_code' => $info->firstWhere('key_name', 'country_code')?->value ?? null,
            'business_address' => $info->firstWhere('key_name', 'business_address')->value ?? null,
            'business_contact_phone' => $info->firstWhere('key_name', 'business_contact_phone')?->value ?? null,
            'business_contact_email' => $info->firstWhere('key_name', 'business_contact_email')?->value ?? null,
            'business_support_phone' => $info->firstWhere('key_name', 'business_support_phone')?->value ?? null,
            'business_support_email' => $info->firstWhere('key_name', 'business_support_email')?->value ?? null,
            'conversion_status' => (bool)($loyaltyPoints['status'] ?? false),
            'conversion_rate' => (double)($loyaltyPoints['points'] ?? 0),
            'base_url' => url('/') . '/api/v1/',
            'websocket_url' => $info->firstWhere('key_name', 'websocket_url')?->value ?? null,
            'websocket_port' => (string)$info->firstWhere('key_name', 'websocket_port')?->value ?? 6001,
            'websocket_key' => env('PUSHER_APP_KEY'),
            'websocket_scheme' => env('PUSHER_SCHEME'),
            'review_status' => (bool)$info->firstWhere('key_name', DRIVER_REVIEW)?->value ?? null,
            'level_status' => (bool)$info->firstWhere('key_name', DRIVER_LEVEL)?->value ?? null,
            'image_base_url' => [
                'profile_image_customer' => asset('storage/app/public/customer/profile'),
                'profile_image_admin' => asset('storage/app/public/employee/profile'),
                'banner' => asset('storage/app/public/promotion/banner'),
                'vehicle_category' => asset('storage/app/public/vehicle/category'),
                'vehicle_model' => asset('storage/app/public/vehicle/model'),
                'vehicle_brand' => asset('storage/app/public/vehicle/brand'),
                'profile_image' => asset('storage/app/public/driver/profile'),
                'identity_image' => asset('storage/app/public/driver/identity'),
                'documents' => asset('storage/app/public/driver/document'),
                'pages' => asset('storage/app/public/business/pages'),
                'conversation' => asset('storage/app/public/conversation'),
                'parcel' => asset('storage/app/public/parcel/category'),
            ],
            'otp_resend_time' => (int)$info->firstWhere('key_name', 'otp_resend_time')?->value ?? 60,
            'currency_decimal_point' => $info->firstWhere('key_name', 'currency_decimal_point')?->value ?? null,
            'currency_code' => $info->firstWhere('key_name', 'currency_code')?->value ?? null,
            'currency_symbol' => $info->firstWhere('key_name', 'currency_symbol')->value ?? '$',
            'currency_symbol_position' => $info->firstWhere('key_name', 'currency_symbol_position')?->value ?? null,
            'about_us' => $info->firstWhere('key_name', 'about_us')?->value ?? null,
            'privacy_policy' => $info->firstWhere('key_name', 'privacy_policy')?->value ?? null,
            'terms_and_conditions' => $info->firstWhere('key_name', 'terms_and_conditions')?->value ?? null,
            'legal' => $info->firstWhere('key_name', 'legal')?->value,
            'refund_policy' => $info->firstWhere('key_name', 'refund_policy')?->value,
            'verification' => (bool)$info->firstWhere('key_name', 'driver_verification')?->value ?? 0,
            'sms_verification' => (bool)$info->firstWhere('key_name', 'sms_verification')?->value ?? 0,
            'email_verification' => (bool)$info->firstWhere('key_name', 'email_verification')?->value ?? 0,
            'facebook_login' => (bool)$info->firstWhere('key_name', 'facebook_login')?->value['status'] ?? 0,
            'google_login' => (bool)$info->firstWhere('key_name', 'google_login')?->value['status'] ?? 0,
            'self_registration' => (bool)$info->firstWhere('key_name', 'driver_self_registration')?->value ?? 0,
            'referral_earning_status' => (bool)referralEarningSetting('referral_earning_status', DRIVER)?->value,
            'parcel_return_time_fee_status' => (bool)businessConfig('parcel_return_time_fee_status', PARCEL_SETTINGS)?->value ?? false,
            'return_time_for_driver' => (int)businessConfig('return_time_for_driver', PARCEL_SETTINGS)?->value ?? 0,
            'return_time_type_for_driver' => businessConfig('return_time_type_for_driver', PARCEL_SETTINGS)?->value ?? "day",
            'return_fee_for_driver_time_exceed' => (double)businessConfig('return_fee_for_driver_time_exceed', PARCEL_SETTINGS)?->value ?? 0,
            'app_minimum_version_for_android' => (double)$appVersions->firstWhere('key_name', 'driver_app_version_control_for_android')?->value['minimum_app_version'] ?? 0,
            'app_url_for_android' => $appVersions->firstWhere('key_name', 'driver_app_version_control_for_android')?->value['app_url'] ?? null,
            'app_minimum_version_for_ios' => (double)$appVersions->firstWhere('key_name', 'driver_app_version_control_for_ios')?->value['minimum_app_version'] ?? 0,
            'app_url_for_ios' => $appVersions->firstWhere('key_name', 'driver_app_version_control_for_ios')?->value['app_url'] ?? null,
            'firebase_otp_verification' => (bool)$info->firstWhere('key_name', 'firebase_otp_verification_status')?->value == 1,
            'sms_gateway' => (bool)$smsConfiguration,
            'chatting_setup_status' => (bool)$info->firstWhere('key_name', 'chatting_setup_status')?->value == 1,
            'driver_question_answer_status' => (bool)$info->firstWhere('key_name', 'chatting_setup_status')?->value == 1 && (bool)$info->firstWhere('key_name', 'driver_question_answer_status')?->value == 1,
            'maximum_parcel_request_accept_limit_status_for_driver' => (bool)$info->firstWhere('key_name', 'maximum_parcel_request_accept_limit')?->value['status'] == 1,
            'maximum_parcel_request_accept_limit_for_driver' => (int)$info->firstWhere('key_name', 'maximum_parcel_request_accept_limit')?->value['limit'] ?? 0,
            'parcel_weight_unit' => businessConfig(key: 'parcel_weight_unit', settingsType: PARCEL_SETTINGS)?->value ?? 'kg',
            'safety_feature_status' => (bool)$info->firstWhere('key_name', 'safety_feature_status')?->value == 1,
            'safety_feature_minimum_trip_delay_time' => $info->firstWhere('key_name', 'safety_feature_status')?->value == 1 ? convertTimeToSecond(
                $info->firstWhere('key_name', 'for_trip_delay')?->value['minimum_delay_time'],
                $info->firstWhere('key_name', 'for_trip_delay')?->value['time_format']
            ) : null,
            'safety_feature_minimum_trip_delay_time_type' => $info->firstWhere('key_name', 'safety_feature_status')?->value == 1 ? $info->firstWhere('key_name', 'for_trip_delay')?->value['time_format'] : null,
            'after_trip_completed_safety_feature_active_status' => (bool)$info->firstWhere('key_name', 'safety_feature_status')?->value == 1 && (bool)$info->firstWhere('key_name', 'after_trip_complete')?->value['safety_feature_active_status'] == 1,
            'after_trip_completed_safety_feature_set_time' => $info->firstWhere('key_name', 'after_trip_complete')?->value['safety_feature_active_status'] == 1 ? convertTimeToSecond(
                $info->firstWhere('key_name', 'after_trip_complete')?->value['set_time'],
                $info->firstWhere('key_name', 'after_trip_complete_time_format')?->value
            )
                : null,
            'after_trip_completed_safety_feature_set_time_type' => $info->firstWhere('key_name', 'after_trip_complete')?->value['safety_feature_active_status'] == 1 ? $info->firstWhere('key_name', 'after_trip_complete_time_format')?->value : null,
            'safety_feature_emergency_govt_number' => $info->firstWhere('key_name', 'safety_feature_status')?->value == 1 ? $info->firstWhere('key_name', 'emergency_govt_number_for_call')?->value : null,
            'otp_confirmation_for_trip' => (bool)$info->firstWhere('key_name', 'driver_otp_confirmation_for_trip')?->value == 1,
            'fuel_types' => array_keys(FUEL_TYPES)
        ];

        return response()->json($configs);
    }

    public function cancellationReasonList()
    {
        $ongoingRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->cancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }

    public function parcelCancellationReasonList()
    {
        $ongoingRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'ongoing_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $acceptedRide = $this->parcelCancellationReasonService->getBy(criteria: ['cancellation_type' => 'accepted_ride', 'user_type' => 'driver', 'is_active' => 1])->pluck('title')->toArray();
        $data = [
            'ongoing_ride' => $ongoingRide,
            'accepted_ride' => $acceptedRide,
        ];
        return response(responseFormatter(DEFAULT_200, $data));
    }


    public function getZone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }

        $point = new Point($request->lat, $request->lng);
        $zone = $this->zoneService->getByPoints($point)->where('is_active', 1)->first();

        if ($zone) {
            return response()->json(responseFormatter(DEFAULT_200, $zone), 200);
        }

        return response()->json(responseFormatter(ZONE_RESOURCE_404), 403);
    }

    /**
     * Summary of placeApiAutocomplete
     * @param Request $request
     * @return JsonResponse
     */
    public function placeApiAutocomplete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }
        $mapKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $response = Http::get(MAP_API_BASE_URI . '/place/autocomplete/json?input=' . $request['search_text'] . '&key=' . $mapKey);
        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    public function distanceApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
            'mode' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }

        $mapKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $response = Http::get(MAP_API_BASE_URI . '/distancematrix/json?origins=' . $request['origin_lat'] . ',' . $request['origin_lng'] . '&destinations=' . $request['destination_lat'] . ',' . $request['destination_lng'] . '&travelmode=' . $request['mode'] . '&key=' . $mapKey);

        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    public function placeApiDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'placeid' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }
        $mapKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $response = Http::get(MAP_API_BASE_URI . '/place/details/json?placeid=' . $request['placeid'] . '&key=' . $mapKey);

        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    public function geocodeApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(DEFAULT_400, null, null, null, errorProcessor($validator)), 400);
        }
        $mapKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? null;
        $response = Http::get(MAP_API_BASE_URI . '/geocode/json?latlng=' . $request->lat . ',' . $request->lng . '&key=' . $mapKey);
        return response()->json(responseFormatter(DEFAULT_200, $response->json()), 200);
    }

    public function getRoutes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_request_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(responseFormatter(constant: DEFAULT_400, errors: errorProcessor($validator)), 403);
        }
        $trip = $this->tripRequestService->findOne(id: $request->trip_request_id, relations: ['coordinate', 'vehicleCategory']);
        if (!$trip) {

            return response()->json(responseFormatter(constant: TRIP_REQUEST_404, errors: errorProcessor($validator)), 403);
        }

        $pickupCoordinates = [
            auth()->user()->lastLocations->latitude,
            auth()->user()->lastLocations->longitude,
        ];

        $intermediateCoordinates = [];
        if ($trip->current_status == ONGOING) {
            $destinationCoordinates = [
                $trip->coordinate->destination_coordinates->latitude,
                $trip->coordinate->destination_coordinates->longitude,
            ];
            $intermediateCoordinates = $trip->coordinate->intermediate_coordinates ? json_decode($$trip->coordinate->intermediate_coordinates, true) : [];
        } else {
            $destinationCoordinates = [
                $trip->coordinate->pickup_coordinates->latitude,
                $trip->coordinate->pickup_coordinates->longitude,
            ];
        }

        $drivingMode = auth()->user()->vehicleCategory->category->type == 'motor_bike' ? 'TWO_WHEELER' : 'DRIVE';

        $getRoutes = getRoutes(
            originCoordinates: $pickupCoordinates,
            destinationCoordinates: $destinationCoordinates,
            intermediateCoordinates: $intermediateCoordinates,
        ); //["DRIVE", "TWO_WHEELER"]

        $result = [];
        foreach ($getRoutes as $route) {
            if ($route['drive_mode'] == $drivingMode) {
                if ($trip->current_status == 'completed' || $trip->current_status == 'cancelled') {
                    $result['is_dropped'] = true;
                } else {
                    $result['is_dropped'] = false;
                }
                if ($trip->current_status === PENDING || $trip->current_status === ACCEPTED) {
                    $result['is_picked'] = false;
                } else {
                    $result['is_picked'] = true;
                }
                return [array_merge($result, $route)];
            }
        }

    }

    public function predefinedQuestionAnswerList(): JsonResponse
    {
        $predefinedQAs = $this->questionAnswerService->getBy(criteria: ['is_active' => true], orderBy: ['created_at' => 'desc']);

        return response()->json(responseFormatter(DEFAULT_200, $predefinedQAs), 200);
    }

    public function otherEmergencyContactList(): JsonResponse
    {
        $criteria = [
            'settings_type' => SAFETY_FEATURE_SETTINGS,
            'key_name' => 'emergency_other_numbers_for_call'
        ];
        $emergencyOtherNumberList = businessConfig(key: 'emergency_number_for_call_status', settingsType: 'safety_feature_settings')?->value == 1 ? $this->businessSettingService->findOneBy(criteria: $criteria)?->value : null;
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $emergencyOtherNumberList));
    }

    public function safetyAlertReasonList(): JsonResponse
    {
        $criteria = [
            'is_active' => 1,
            'reason_for_whom' => DRIVER
        ];
        $safetyAlertReasons = businessConfig(key: 'safety_alert_reasons_status', settingsType: 'safety_feature_settings')?->value == 1
            ? $this->safetyAlertReasonService->getBy(criteria: $criteria)->pluck('reason')->map(function ($reason) {
                return ['reason' => $reason];
            })
            : null;
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $safetyAlertReasons));
    }

    public function safetyPrecautionList(): JsonResponse
    {
        $criteria = [
            'is_active' => 1,
            ['for_whom', 'like', '%' . DRIVER . '%'],
        ];
        $safetyPrecautions = $this->safetyPrecautionService->getBy(criteria: $criteria);
        $responseData = $safetyPrecautions->map(function ($item) {
            return [
                'title' => $item['title'],
                'description' => $item['description'],
            ];
        });
        return response()->json(responseFormatter(constant: DEFAULT_200, content: $responseData));
    }
}
