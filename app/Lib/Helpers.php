<?php

use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\BusinessManagement\Entities\ExternalConfiguration;
use Modules\BusinessManagement\Entities\ReferralEarningSetting;
use Modules\UserManagement\Entities\User;
use Pusher\Pusher;
use Pusher\PusherException;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Modules\BusinessManagement\Entities\BusinessSetting;
use Modules\AdminModule\Repositories\ActivityLogRepository;
use Modules\BusinessManagement\Entities\FirebasePushNotification;

if (!function_exists('translate')) {
    function translate($key, $replace = []): array|string|Translator|null
    {
        $local = app()->getLocale();
        try {
            $langArray = include(base_path('resources/lang/' . $local . '/lang.php'));
            $processedKey = ucfirst(str_replace('_', ' ', removeSpecialCharacters($key)));
            $key = removeSpecialCharacters($key);
            if (!array_key_exists($key, $langArray)) {
                $langArray[$key] = $processedKey;
                $str = "<?php return " . var_export($langArray, true) . ";";
                file_put_contents(base_path('resources/lang/' . $local . '/lang.php'), $str);
                $result = $processedKey;
            } else {
                $result = trans('lang.' . $key);
            }
        } catch (\Exception $exception) {
            $result = trans('lang.' . $key);
        }
        return $result;
    }
}
if (!function_exists('defaultLang')) {
    function defaultLang()
    {
        if (strpos(url()->current(), '/api')) {
            $lang = App::getLocale();
        } elseif (session()->has('locale')) {
            $lang = session('locale');
        } elseif (businessConfig('system_language', 'language_settings')) {
            $data = businessConfig('system_language', 'language_settings')->value;
            $code = 'en';
            $direction = 'ltr';
            foreach ($data as $ln) {
                if (array_key_exists('default', $ln) && $ln['default']) {
                    $code = $ln['code'];
                    if (array_key_exists('direction', $ln)) {
                        $direction = $ln['direction'];
                    }
                }
            }
            session()->put('locale', $code);
            session()->put('direction', $direction);
            $lang = $code;
        } else {
            $lang = App::getLocale();
        }
        return $lang;
    }
}


if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters(string|null $text): string|null
    {
        return str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $text));
    }
}

if (!function_exists('fileUploader')) {
    function fileUploader(string $dir, string $format, $image = null, $oldImage = null)
    {
        if ($image == null) {
            return $oldImage ?? 'def.png';
        }
        if (is_array($oldImage) && !empty($oldImage)) {
            // Handle the case when $oldImage is an array (multiple images)
            foreach ($oldImage as $file) {
                Storage::disk('public')->delete($dir . $file);
            }
        } elseif (is_string($oldImage) && !empty($oldImage)) {
            // Handle the case when $oldImage is a single image (string)
            Storage::disk('public')->delete($dir . $oldImage);
        }

        $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }
}

if (!function_exists('fileRemover')) {
    function fileRemover(string $dir, $image)
    {
        if (!isset($image)) return true;

        if (Storage::disk('public')->exists($dir . $image)) Storage::disk('public')->delete($dir . $image);

        return true;
    }
}

if (!function_exists('paginationLimit')) {
    function paginationLimit()
    {
        return getSession('pagination_limit') == false ? 10 : getSession('pagination_limit');
    }
}

if (!function_exists('stepValue')) {
    function stepValue()
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        return 1 / pow(10, $points);
    }
}
if (!function_exists('businessConfig')) {
    function businessConfig($key, $settingsType = null)
    {
        try {
            $config = BusinessSetting::query()
                ->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('newBusinessConfig')) {
    function newBusinessConfig($key, $settingsType = null)
    {
        $businessSettings = Cache::rememberForever(CACHE_BUSINESS_SETTINGS, function () {
            return BusinessSetting::all();
        });

        try {
            $config = $businessSettings->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first()?->value;
        } catch (Exception $exception) {
            return null;
        }
        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('referralEarningSetting')) {
    function referralEarningSetting($key, $settingsType = null)
    {
        try {
            $config = ReferralEarningSetting::query()
                ->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('externalConfig')) {
    function externalConfig($key)
    {
        try {
            $config = ExternalConfiguration::query()
                ->where('key', $key)
                ->first();
        } catch (Exception $exception) {
            return null;
        }
        return (isset($config)) ? $config : null;
    }
}
if (!function_exists('checkExternalConfiguration')) {
    function checkExternalConfiguration($externalBaseUrl, $externalTokem, $drivemondToken)
    {
        $activationMode = externalConfig('activation_mode')?->value;
        $martBaseUrl = externalConfig('mart_base_url')?->value;
        $martToken = externalConfig('mart_token')?->value;
        $systemSelfToken = externalConfig('system_self_token')?->value;
        return $activationMode == 1 && $martBaseUrl == $externalBaseUrl && $martToken == $externalTokem && $systemSelfToken == $drivemondToken;
    }
}
if (!function_exists('checkSelfExternalConfiguration')) {
    function checkSelfExternalConfiguration()
    {
        $activationMode = externalConfig('activation_mode')?->value;
        $martBaseUrl = externalConfig('mart_base_url')?->value;
        $martToken = externalConfig('mart_token')?->value;
        $systemSelfToken = externalConfig('system_self_token')?->value;
        return $activationMode == 1 && $martBaseUrl != null && $martToken != null && $systemSelfToken != null;
    }
}

if (!function_exists('generateReferralCode')) {
    function generateReferralCode($user = null)
    {
        $refCode = strtoupper(Str::random(10));
        if (User::where('ref_code', $refCode)->exists()) {
            generateReferralCode();
        }
        if ($user) {
            $user->ref_code = $refCode;
            $user->save();
        }
        return $refCode;
    }
}


if (!function_exists('responseFormatter')) {
    function responseFormatter($constant, $content = null, $limit = null, $offset = null, $errors = []): array
    {
        $data = [
            'total_size' => isset($limit) ? $content->total() : null,
            'limit' => $limit,
            'offset' => $offset,
            'data' => $content,
            'errors' => $errors,
        ];
        $responseConst = [
            'response_code' => $constant['response_code'],
            'message' => translate($constant['message']),
        ];
        return array_merge($responseConst, $data);
    }
}

if (!function_exists('errorProcessor')) {
    function errorProcessor($validator)
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => translate($error[0])];
        }
        return $errors;
    }
}


if (!function_exists('autoTranslator')) {
    function autoTranslator($q, $sl, $tl): array|string
    {
        $res = file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=" . $sl . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q), $_SERVER['DOCUMENT_ROOT'] . "/transes.html");
        $res = json_decode($res);
        return str_replace('_', ' ', $res[0][0][0]);
    }
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $countryCode): string
    {
        foreach (LANGUAGES as $locale) {
            if ($countryCode == $locale['code']) {
                return $countryCode;
            }
        }
        return "en";
    }

}

if (!function_exists('exportData')) {
    function exportData($data, $file, $viewPath)
    {
        return match ($file) {
            'csv' => (new FastExcel($data))->download(time() . '-file.csv'),
            'excel' => (new FastExcel($data))->download(time() . '-file.xlsx'),
            'pdf' => Pdf::loadView($viewPath, ['data' => $data])->download(time() . '-file.pdf'),
            default => view($viewPath, ['data' => $data]),
        };
    }
}


if (!function_exists('log_viewer')) {

    function log_viewer($request)
    {
        $search = $request['search'] ?? null;
        $attributes['logable_type'] = $request['logable_type'];
        if (array_key_exists('id', $request)) {
            $attributes['logable_id'] = $request['id'];
        }
        if (array_key_exists('search', $request)) {
            $attributes['search'] = $request['search'];
        }

        if (array_key_exists('user_type', $request)) {
            $attributes['user_type'] = $request['user_type'];
        }

        $logs = new ActivityLogRepository;

        if (array_key_exists('file', $request)) {
            $logs = $logs->get(attributes: $attributes, export: true);
            $data = $logs->map(function ($item) {
                $objects = explode("\\", $item->logable_type);
                return [
                    'edited_date' => date('Y-m-d', strtotime($item->created_at)),
                    'edited_time' => date('h:i A', strtotime($item->created_at)),
                    'email' => $item->users?->email,
                    'edited_object' => end($objects),
                    'before' => json_encode($item?->before),
                    'after' => json_encode($item?->after)
                ];
            });
            return exportData($data, $request['file'], 'adminmodule::log-print');
        }
        $logs = $logs->get(attributes: $attributes);

        return view('adminmodule::activity-log', compact('logs', 'search'));
    }
}


if (!function_exists('get_cache')) {
    function get_cache($key)
    {
        if (!Cache::has($key)) {
            $config = businessConfig($key)?->value;
            if (!$config) {
                return null;
            }
            Cache::put($key, $config);
        }
        return Cache::get($key);
    }
}

if (!function_exists('getSession')) {
    function getSession($key)
    {
        if (!Session::has($key)) {
            $config = businessConfig($key)?->value;
            if (!$config) {
                return false;
            }
            Session::put($key, $config);
        }
        return Session::get($key);
    }
}

if (!function_exists('haversineDistance')) {
    function haversineDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}

if (!function_exists('getDateRange')) {
    function getDateRange($request)
    {
        if (is_array($request)) {
            return [
                'start' => Carbon::parse($request['start'])->startOfDay(),
                'end' => Carbon::parse($request['end'])->endOfDay(),
            ];
        }

        return match ($request) {
            TODAY => [
                'start' => Carbon::parse(now())->startOfDay(),
                'end' => Carbon::parse(now())->endOfDay()
            ],
            PREVIOUS_DAY => [
                'start' => Carbon::yesterday()->startOfDay(),
                'end' => Carbon::yesterday()->endOfDay(),
            ],
            THIS_WEEK => [
                'start' => Carbon::parse(now())->startOfWeek(),
                'end' => Carbon::parse(now())->endOfWeek(),
            ],
            THIS_MONTH => [
                'start' => Carbon::parse(now())->startOfMonth(),
                'end' => Carbon::parse(now())->endOfMonth(),
            ],
            LAST_7_DAYS => [
                'start' => Carbon::today()->subDays(7)->startOfDay(),
                'end' => Carbon::parse(now())->endOfDay(),
            ],
            LAST_WEEK => [
                'start' => Carbon::now()->subWeek()->startOfWeek(),
                'end' => Carbon::now()->subWeek()->endOfWeek(),
            ],
            LAST_MONTH => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth(),
            ],
            THIS_YEAR => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
            ],
            ALL_TIME => [
                'start' => Carbon::parse(BUSINESS_START_DATE),
                'end' => Carbon::now(),
            ]
        };
    }
}
if (!function_exists('getCustomDateRange')) {
    function getCustomDateRange($dateRange)
    {
        list($startDate, $endDate) = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate));
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate));
        return [
            'start' => Carbon::parse($startDate)->startOfDay(),
            'end' => Carbon::parse($endDate)->endOfDay(),
        ];


    }
}

if (!function_exists('configSettings')) {
    function configSettings($key, $settingsType)
    {
        try {
            $config = DB::table('settings')->where('key_name', $key)
                ->where('settings_type', $settingsType)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('languageLoad')) {
    function languageLoad()
    {
        if (\session()->has(LANGUAGE_SETTINGS)) {
            $language = \session(LANGUAGE_SETTINGS);
        } else {
            $language = businessConfig(SYSTEM_LANGUAGE)?->value;
            \session()->put(LANGUAGE_SETTINGS, $language);
        }
        return $language;
    }

}

if (!function_exists('set_currency_symbol')) {
    function set_currency_symbol($amount)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        $symbol = getSession('currency_symbol') ?? '$';

        if ($position == 'left') {
            return $symbol . ' ' . number_format($amount, $points);
        }
        return number_format($amount, $points) . ' ' . $symbol;
    }
}

if (!function_exists('getCurrencyFormat')) {
    function getCurrencyFormat($amount)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        if (session::has('currency_symbol')) {
            $symbol = session()->get('currency_symbol');
        } else {
            $symbol = businessConfig('currency_symbol', 'business_information')->value ?? "$";
        }

        if ($position == 'left') {
            return $symbol . ' ' . number_format($amount, $points);
        } else {
            return number_format($amount, $points) . ' ' . $symbol;
        }
    }
}


if (!function_exists('getNotification')) {
    function getNotification($key)
    {
        $notification = FirebasePushNotification::query()->firstWhere('name', $key);
        return [
            'title' => $notification['name'] ?? ' ',
            'description' => $notification['value'] ?? ' ',
            'status' => (bool)$notification['status'] ?? 0,
        ];
    }
}

if (!function_exists('getMainDomain')) {
    function getMainDomain($url)
    {
        // Remove protocol from the URL
        $url = preg_replace('#^https?://#', '', $url);

        // Split the URL by slashes
        $parts = explode('/', $url);

        // Extract the domain part
        // Return the subdomain and domain
        return $parts[0];
    }
}

if (!function_exists('getRoutes')) {
    function getRoutes(array $originCoordinates, array $destinationCoordinates, array $intermediateCoordinates = [], array $drivingMode = ["DRIVE"])
    {
        $apiKey = businessConfig(GOOGLE_MAP_API)?->value['map_api_key_server'] ?? '';
        $encoded_polyline = null;
        $responses = [];
        $origin = implode(',', $originCoordinates);
        $destination = implode(',', $destinationCoordinates);
        // Convert waypoints to string format
        $waypointsFormatted = [];
        if ($intermediateCoordinates && !is_null($intermediateCoordinates[0][0])) {
            foreach ($intermediateCoordinates as $wp) {
                $waypointsFormatted[] = $wp[0] . ',' . $wp[1];
            }
        }
        $waypointsString = implode('|', $waypointsFormatted);
        $response = Http::get("https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&departure_time=now&waypoints=$waypointsString&key=$apiKey");
        if ($response->successful()) {
            $result = $response->json();
            $distance = 0;
            $duration = 0;
            $durationInTraffic = 0;

            // Process the JSON response data here
            foreach ($result['routes'] as $route) {
                $encoded_polyline = $route['overview_polyline']['points'];
                foreach ($route['legs'] as $leg) {
                    $distance += $leg['distance']['value'];
                    $duration += $leg['duration']['value'];
                    $durationInTraffic += $leg['duration_in_traffic']['value'] ?? $leg['duration']['value']; // Fallback to regular duration if traffic data is missing
                }
            }

            $distance = str_replace(',', '', $distance);
            $convert_to_bike = 1.2;

            $responses[0] = [
                'distance' => (double)str_replace(',', '', number_format(($distance ?? 0) / 1000, 2)),
                'distance_text' => number_format(($distance ?? 0) / 1000, 2) . ' ' . 'km',
                'duration' => number_format((($duration / 60) / $convert_to_bike), 2) . ' ' . 'min',
                'duration_sec' => (int)($duration / $convert_to_bike),
                'duration_in_traffic' => number_format((($durationInTraffic / 60) / $convert_to_bike), 2) . ' ' . 'min',
                'duration_in_traffic_sec' => (int)($durationInTraffic / $convert_to_bike),
                'status' => "OK",
                'drive_mode' => 'TWO_WHEELER',
                'encoded_polyline' => $encoded_polyline,
            ];

            $responses[1] = [
                'distance' => (double)str_replace(',', '', number_format(($distance ?? 0) / 1000, 2)),
                'distance_text' => number_format(($distance ?? 0) / 1000, 2) . ' ' . 'km',
                'duration' => number_format(($duration / 60), 2) . ' ' . 'min',
                'duration_sec' => (int)$duration,
                'duration_in_traffic' => number_format(($durationInTraffic / 60), 2) . ' ' . 'min',
                'duration_in_traffic_sec' => (int)$durationInTraffic,
                'status' => "OK",
                'drive_mode' => 'DRIVE',
                'encoded_polyline' => $encoded_polyline,
            ];

            return $responses;
        } else {
            // Handle the error if the request was not successful
            return $response->status();
        }

    }
}

if (!function_exists('onErrorImage')) {
    function onErrorImage($data, $src, $error_src, $path)
    {
        if (isset($data) && strlen($data) > 1 && Storage::disk('public')->exists($path . $data)) {
            return $src;
        }
        return $error_src;
    }
}

if (!function_exists('checkPusherConnection')) {
    function checkPusherConnection($event)
    {
        try {
            // Pusher configuration
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
//            if (!empty($event)) {
//                $event;
//            }


            return response()->json(['message' => 'Pusher connection established successfully']);
        } catch (PusherException $e) {

        } catch (\Exception $e) {
            // If cURL error 52 occurs
            if (strpos($e->getMessage(), 'cURL error 52') !== false) {
                return true;
            }
            return true;
        }
    }
}
if (!function_exists('spellOutNumber')) {
    function spellOutNumber($number)
    {
        $number = strval($number);
        $digits = [
            "zero", "one", "two", "three", "four",
            "five", "six", "seven", "eight", "nine"
        ];
        $tens = [
            "", "", "twenty", "thirty", "forty",
            "fifty", "sixty", "seventy", "eighty", "ninety"
        ];
        $teens = [
            "ten", "eleven", "twelve", "thirteen", "fourteen",
            "fifteen", "sixteen", "seventeen", "eighteen", "nineteen"
        ];

        $result = '';

        if (strlen($number) > 15) {
            $quadrillions = substr($number, 0, -15);
            $number = substr($number, -15);
            $result .= spellOutNumber($quadrillions) . ' quadrillion ';
        }

        if (strlen($number) > 12) {
            $trillions = substr($number, 0, -12);
            $number = substr($number, -12);
            $result .= spellOutNumber($trillions) . ' trillion ';
        }

        if (strlen($number) > 9) {
            $billions = substr($number, 0, -9);
            $number = substr($number, -9);
            $result .= spellOutNumber($billions) . ' billion ';
        }

        if (strlen($number) > 6) {
            $millions = substr($number, 0, -6);
            $number = substr($number, -6);
            $result .= spellOutNumber($millions) . ' million ';
        }

        if (strlen($number) > 3) {
            $thousands = substr($number, 0, -3);
            $number = substr($number, -3);
            $result .= spellOutNumber($thousands) . ' thousand ';
        }

        if (strlen($number) > 2) {
            $hundreds = substr($number, 0, -2);
            $number = substr($number, -2);
            $result .= $digits[intval($hundreds)] . ' hundred ';
        }

        if ($number > 0) {
            if ($number < 10) {
                $result .= $digits[intval($number)];
            } elseif ($number < 20) {
                $result .= $teens[$number - 10];
            } else {
                $result .= $tens[$number[0]];
                if ($number[1] > 0) {
                    $result .= '-' . $digits[intval($number[1])];
                }
            }
        }

        return trim($result);
    }
}
if (!function_exists('abbreviateNumber')) {
    function abbreviateNumber($number)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $abbreviations = ['', 'K', 'M', 'B', 'T'];
        $abbreviated_number = $number;
        $abbreviation_index = 0;

        while ($abbreviated_number >= 1000 && $abbreviation_index < count($abbreviations) - 1) {
            $abbreviated_number /= 1000;
            $abbreviation_index++;
        }

        return round($abbreviated_number, $points) . $abbreviations[$abbreviation_index];
    }
}

if (!function_exists('abbreviateNumberWithSymbol')) {
    #TODO
    function abbreviateNumberWithSymbol($number)
    {
        $points = (int)getSession('currency_decimal_point') ?? 0;
        $position = getSession('currency_symbol_position') ?? 'left';
        if (session::has('currency_symbol')) {
            $symbol = session()->get('currency_symbol');
        } else {
            $symbol = businessConfig('currency_symbol', 'business_information')->value ?? "$";
        }
        $abbreviations = ['', 'K', 'M', 'B', 'T'];
        $abbreviated_number = $number;
        $abbreviation_index = 0;

        // while ($abbreviated_number >= 1000 && $abbreviation_index < count($abbreviations) - 1) {
        //     $abbreviated_number /= 1000;
        //     $abbreviation_index++;
        // }

        if ($position == 'left') {
            return $symbol . ' ' . round($abbreviated_number, $points);
            // . $abbreviations[$abbreviation_index];
        } else {
              return round($abbreviated_number, $points) . ' ' . $symbol;
            // return round($abbreviated_number, $points) . $abbreviations[$abbreviation_index] . ' ' . $symbol;
        }

    }
}
if (!function_exists('removeInvalidCharcaters')) {
    function removeInvalidCharcaters($str)
    {
        return str_ireplace(['\'', '"', ';', '<', '>'], ' ', $str);
    }
}

if (!function_exists('textVariableDataFormat')) {
    function textVariableDataFormat($value, $tipsAmount = null, $levelName = null, $walletAmount = null, $tripId = null,
                                    $userName = null, $withdrawNote = null, $paidAmount = null, $methodName = null, $referralRewardAmount = null, $otp = null)
    {
        $data = $value;
        if ($value) {
            if ($tipsAmount) {
                $data = str_replace("{tipsAmount}", $tipsAmount, $data);
            }
            if ($paidAmount) {
                $data = str_replace("{paidAmount}", $paidAmount, $data);
            }
            if ($methodName) {
                $data = str_replace("{methodName}", $methodName, $data);
            }

            if ($levelName) {
                $data = str_replace("{levelName}", $levelName, $data);
            }
            if ($levelName == "") {
                $data = str_replace("and reached level {levelName}", ".", $data);
            }

            if ($walletAmount) {
                $data = str_replace("{walletAmount}", $walletAmount, $data);
            }
            if ($referralRewardAmount) {
                $data = str_replace("{referralRewardAmount}", $referralRewardAmount, $data);
            }

            if ($tripId) {
                $data = str_replace("{tripId}", $tripId, $data);
            }
            if ($otp) {
                $data = str_replace("{otp}", $otp, $data);
            }
            if ($userName) {
                $data = str_replace("{userName}", $userName, $data);
            }
            if ($withdrawNote) {
                $data = str_replace("{withdrawNote}", ('Please read carefully this note : ' . $withdrawNote . " If you have any questions, feel free to contact our support team"), $data);
            }
        }

        return $data;
    }
}
if (!function_exists('smsTemplateDataFormat')) {
    function smsTemplateDataFormat($value, $customerName = null, $parcelId = null, $trackingLink = null)
    {
        $data = $value;
        if ($value) {
            if ($customerName) {
                $data = str_replace("{CustomerName}", $customerName, $data);
            }
            if ($parcelId) {
                $data = str_replace("{ParcelId}", $parcelId, $data);
            }
            if ($trackingLink) {
                $data = str_replace("{TrackingLink}", $trackingLink, $data);
            }
        }

        return $data;
    }
}
if (!function_exists('checkMaintenanceMode')) {
    function checkMaintenanceMode(): array
    {
        $maintenanceSystemArray = ['user_app', 'driver_app'];
        $selectedMaintenanceSystem = businessConfig('maintenance_system_setup')?->value ?? [];

        $maintenanceSystem = [];
        foreach ($maintenanceSystemArray as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        $selectedMaintenanceDuration = businessConfig('maintenance_duration_setup')?->value ?? [];
        $maintenanceStatus = (integer)(businessConfig('maintenance_mode')?->value ?? 0);

        $status = 0;
        if ($maintenanceStatus == 1) {
            if (isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change') {
                $status = $maintenanceStatus;
            } else {
                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                    $start = Carbon::parse($selectedMaintenanceDuration['start_date']);
                    $end = Carbon::parse($selectedMaintenanceDuration['end_date']);
                    $today = Carbon::now();
                    if ($today->between($start, $end)) {
                        $status = 1;
                    }
                }
            }
        }

        return [
            'maintenance_status' => $status,
            'selected_maintenance_system' => count($maintenanceSystem) > 0 ? $maintenanceSystem : null,
            'maintenance_messages' => businessConfig('maintenance_message_setup')?->value ?? null,
            'maintenance_type_and_duration' => count($selectedMaintenanceDuration) > 0 ? $selectedMaintenanceDuration : null,
        ];
    }
}

if (!function_exists('insertBusinessSetting')) {
    function insertBusinessSetting($keyName, $settingType = null, $value = null)
    {
        $data = BusinessSetting::where('key_name', $keyName)->where('settings_type', $settingType)->first();
        if (!$data) {
            BusinessSetting::updateOrCreate(['key_name' => $keyName, 'settings_type' => $settingType], [
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return true;
    }
}

if (!function_exists('hexToRgb')) {
    function hexToRgb($hex)
    {
        // Remove the hash at the start if it's there
        $hex = ltrim($hex, '#');

        // If the hex code is in shorthand (3 characters), convert to full form
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Convert hex to RGB values
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "$r, $g, $b";
    }
}

if (!function_exists('formatCustomDate')) {
    function formatCustomDate($date)
    {
        $carbonDate = Carbon::parse($date);
        $now = Carbon::now();

        if ($carbonDate->isToday()) {
            return $carbonDate->format('g:i A'); // e.g., 3:53 PM
        } elseif ($carbonDate->isYesterday()) {
            return 'Yesterday';
        } elseif ($carbonDate->diffInDays($now) <= 5) {
            // Returns "X days ago" for dates within the last 5 days
            return $carbonDate->diffInDays($now) . ' days ago';
        } else {
            return $carbonDate->format('d M Y'); // e.g., 17 Nov 2024
        }
    }
}


if (!function_exists('formatCustomDateForTooltip')) {
    function formatCustomDateForTooltip($dateTime)
    {
        $timestamp = strtotime($dateTime);
        $now = time();

        if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
            return date('h:i A', $timestamp); // Format as 01:43 PM
        }

        $oneWeekAgo = strtotime('-1 week', $now);
        if ($timestamp > $oneWeekAgo) {
            return date('l h:i A', $timestamp);
        }

        return date('d M Y', $timestamp);
    }
}

if (!function_exists('getExtensionIcon')) {
    function getExtensionIcon($document)
    {
        $extension = pathinfo($document, PATHINFO_EXTENSION);
        $asset = asset('public/assets/admin-module/img/file-format/svg');
        return match ($extension) {
            'pdf' => $asset . '/pdf.svg',
            'cvc' => $asset . '/cvc.svg',
            'csv' => $asset . '/csv.svg',
            'doc', 'docx' => $asset . '/doc.svg',
            'jpg' => $asset . '/jpg.svg',
            'jpeg' => $asset . '/jpeg.svg',
            'webp' => $asset . '/webp.svg',
            'png' => $asset . '/png.svg',
            'xls' => $asset . '/xls.svg',
            'xlsx' => $asset . '/xlsx.svg',
            default => asset('public/assets/admin-module/img/document-upload.png'),
        };
    }
}

if (!function_exists('convertTimeToSecond')) {
    function convertTimeToSecond($time, $type)
    {
        $time = floatval($time);

        return match (strtolower($type)) {
            'second' => $time,
            'minute' => $time * 60,
            'hour' => $time * 3600,
            default => null,
        };
    }
}


