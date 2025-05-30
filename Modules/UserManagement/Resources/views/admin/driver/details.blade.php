@extends('adminmodule::layouts.master')

@section('title', translate('Driver_Details'))

@section('content')

@php
    $driverDetails = $commonData['driver']?->driverDetails['additional_info'] ?? null;

    if (is_string($driverDetails)) {
        $decodedDetails = json_decode($driverDetails, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $driverDetails = $decodedDetails; // Successfully decoded
        }
    }
    
    $dynamic_value = isset($driverDetails["general_info"]["identification_type"]) ? $driverDetails["general_info"]["identification_type"] . '_number' : '';
    
@endphp
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-3">{{ translate('driver') }} # {{ $commonData['driver']->id }}</h2>

            <div class="card mb-30">
                <div class="card-body">
                    <div class="row gy-5">
                        <div class="col-lg-6">
                            <div class="">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-4">
                                    <h5 class="text-capitalize d-flex align-items-center gap-2 text-primary">
                                        <i class="bi bi-person-fill-gear"></i>
                                        {{ translate('driver_information') }}
                                    </h5>
                                </div>

                                <div class="media flex-wrap gap-3 gap-lg-4">
                                    <div class="avatar avatar-135 rounded">
                                        <img src="{{ onErrorImage(
                                            $commonData['driver']?->profile_image,
                                            asset('storage/app/public/driver/profile') . '/' . $commonData['driver']?->profile_image,
                                            asset('public/assets/admin-module/img/avatar/avatar.png'),
                                            'driver/profile/',
                                        ) }}"
                                            class="rounded dark-support custom-box-size" alt=""
                                            style="--size: 136px">
                                    </div>
                                    <div class="media-body">
                                        <div class="d-flex flex-column align-items-start gap-1">
                                            <h6 class="mb-10">
                                                {{ $commonData['driver']?->first_name . ' ' . $commonData['driver']?->last_name }}
                                            </h6>
                                            <div class="d-flex gap-3 align-items-center mb-1">
                                                <div class="badge bg-primary text-capitalize">
                                                    {{ $commonData['driver']->level->name ?? translate('no_level_found') }}
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    {{ number_format($commonData['driver']->receivedReviews->avg('rating'), 1) }}
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="fw-bold">{{translate("phone")}}: </span>
                                                <a href="tel:{{ $commonData['driver']->phone }}">{{ $commonData['driver']->phone }}</a>
                                            </div>
                                            <div>
                                                <span class="fw-bold">{{translate("E-mail")}}: </span>
                                                <a href="mailto:{{ $commonData['driver']->email }}">{{ $commonData['driver']->email }}</a>
                                            </div>
                                            <div>
                                                <span class="fw-bold">{{translate("Service")}}: </span>
                                                <span>
                                                    
                                                    @if($commonData['driver']?->driverDetails?->service) 
                                                        @php
                                                            $service = $commonData['driver']?->driverDetails?->service;
                                                            if(is_string($service)) {
                                                                $service = json_decode($service, true); // Decode to an associative array
                                                            }
                                                        @endphp
                                                        @if(in_array('ride_request',$service) && in_array('parcel',$service))
                                                            {{translate("Ride Request")}}, {{translate("Parcel")}} ({{translate('capacity').'-'. ($commonData['driver']->vehicle?->parcel_weight_capacity != null ? ($commonData['driver']->vehicle?->parcel_weight_capacity . (businessConfig(key: 'parcel_weight_unit')?->value ?? 'kg')): translate('unlimited')) }})
                                                        @elseif(in_array('ride_request',$service))
                                                            {{translate("Ride Request")}}
                                                        @elseif(in_array('parcel',$service))
                                                            {{translate("Parcel")}} ({{translate('capacity').'-'. ($commonData['driver']->vehicle?->parcel_weight_capacity != null ? ($commonData['driver']->vehicle?->parcel_weight_capacity . (businessConfig(key: 'parcel_weight_unit')?->value ?? 'kg')): translate('unlimited')) }})
                                                        @endif
                                                    @else
                                                        {{translate("Ride Request")}}, {{translate("Parcel")}} ({{translate('capacity').'-'. ($commonData['driver']->vehicle?->parcel_weight_capacity != null ? ($commonData['driver']->vehicle?->parcel_weight_capacity . (businessConfig(key: 'parcel_weight_unit')?->value ?? 'kg')): translate('unlimited')) }})
                                                    @endif
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-4">
                                    <h5 class="d-flex align-items-center text-primary gap-2">
                                        <i class="bi bi-person-fill-gear text-primary text-capitalize"></i>
                                        {{ translate('driver_rate_info') }}
                                    </h5>
                                </div>

                                <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center">
                                    <div class="text-success text-capitalize">
                                        {{ translate('average_active_rate/day') }}</div>
                                    <div class="d-flex gap-2 align-items-center flex-grow-1">
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: {{ round($commonData['avg_active_day']) }}%"
                                                aria-valuenow="{{ round($commonData['avg_active_day'], 2) }}"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="text-success">{{ round($commonData['avg_active_day'], 2) }}%</div>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-around flex-wrap gap-3">
                                            <div class="d-flex align-items-center flex-column gap-3">
                                                <div class="circle-progress"
                                                    data-parsent="{{ round($commonData['driver_avg_earning'], 2) }}"
                                                    data-color="#56DBCB">
                                                    <div class="content">
                                                        <h6 class="persent fs-12">
                                                            {{ abbreviateNumber($commonData['driver_avg_earning']) }}{{ getSession('currency_symbol') }}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <h6 class="fw-semibold fs-12" style="color: #56DBCB">
                                                    {{ translate('avg._earning_value') }}</h6>
                                            </div>

                                            <div class="d-flex align-items-center flex-column gap-3">
                                                <div class="circle-progress"
                                                    data-parsent="{{ round($commonData['positive_review_rate']) ?? 0 }}"
                                                    data-color="#3B72FF">
                                                    <div class="content">
                                                        <h6 class="persent fs-12">
                                                            {{ round($commonData['positive_review_rate']) ?? 0 }}
                                                            %</h6>
                                                    </div>
                                                </div>
                                                <h6 class="fw-semibold fs-12 text-capitalize positive-review-color">
                                                    {{ translate('positive_review_rate') }}</h6>
                                            </div>

                                            <div class="d-flex align-items-center flex-column gap-3">
                                                <div class="circle-progress text-capitalize"
                                                    data-parsent="{{ round($commonData['success_rate'], 2) }}"
                                                    data-color="#76C351">
                                                    <div class="content">
                                                        <h6 class="persent fs-12">{{ round($commonData['success_rate'], 2) }}
                                                            %</h6>
                                                    </div>
                                                </div>
                                                <h6 class="fw-semibold fs-12 text-capitalize success-rate-color">
                                                    {{ translate('success_rate') }}</h6>
                                            </div>

                                            <div class="d-flex align-items-center flex-column gap-3">
                                                <div class="circle-progress"
                                                    data-parsent="{{ round($commonData['cancel_rate'], 2) }}"
                                                    data-color="#FF6767">
                                                    <div class="content">
                                                        <h6 class="persent fs-12">{{ round($commonData['cancel_rate'], 2) }}
                                                            %</h6>
                                                    </div>
                                                </div>
                                                <h6 class="fw-semibold fs-12 text-capitalize cancellation-rate-color">
                                                    {{ translate('cancelation_rate') }}</h6>
                                            </div>
                                            <div class="d-flex align-items-center flex-column gap-3">
                                                <div class="circle-progress"
                                                    data-parsent="{{ round($commonData['idle_rate_today'], 2) }}"
                                                    data-color="#FFA800">
                                                    <div class="content">
                                                        <h6 class="persent fs-12">{{ round($commonData['idle_rate_today'], 2) }}
                                                            %</h6>
                                                    </div>
                                                </div>
                                                <h6 class="fw-semibold fs-12" style="color: #FFA800">Today Idle Hour
                                                    Rate</h6>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($driverDetails)
                <div class="tab-pane fade active show mt-3 mb-30" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="d-flex align-items-center gap-2 text-primary text-capitalize">
                                        <i class="bi bi-person-fill-gear"></i>
                                        {{translate('driver_details')}}
                                    </h5>
                
                                    <div class=" my-4">
                                        <ul class="nav nav--tabs justify-content-around bg-white" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general_info-tab-pane"
                                                        aria-selected="true"
                                                        role="tab">{{translate('general_info')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link text-capitalize" data-bs-toggle="tab"
                                                        data-bs-target="#contact_info-tab-pane" aria-selected="false"
                                                        role="tab" tabindex="-1">{{translate('contact_information')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vehicle_info-tab-pane"
                                                        aria-selected="false" role="tab"
                                                        tabindex="-1">{{translate('vehicle_information')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guarantor_info-tab-pane"
                                                        aria-selected="false" role="tab"
                                                        tabindex="-1">{{translate('guarantor_information')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emergency_contact_info-tab-pane"
                                                        aria-selected="false" role="tab"
                                                        tabindex="-1">{{translate('emergency_contact_information')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#declaration_info-tab-pane"
                                                        aria-selected="false" role="tab"
                                                        tabindex="-1">{{translate('declaration ')}}</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#official_use_info-tab-pane"
                                                        aria-selected="false" role="tab"
                                                        tabindex="-1">{{translate('Official Use Infomation')}}</button>
                                            </li>
                                        </ul>
                                    </div>
                
                                    <div class="tab-content">
                                        <div class="tab-pane fade active show" id="general_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate('Identity type') }}</th>
                                                            <td>{{ucwords(str_replace('_', ' ', $driverDetails['general_info']['identification_type']))}}</td>
                                                            <th>{{ translate($dynamic_value) }}</th>
                                                            <td>{{$driverDetails['general_info'][$dynamic_value]}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Date of Birth ') }}</th>
                                                            <td>{{date('d-m-Y', strtotime($driverDetails['general_info']['dob']))}}</td>
                                                            <th>{{ translate(' Gender ') }}</th>
                                                            <td>{{ucwords($driverDetails['general_info']['gender'])}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate('Expiry Date') }}</th>
                                                            <td>{{date('d-m-Y', strtotime($driverDetails['general_info']['expiry_date']))}}</td>
                                                            <th>{{ translate(' Current Residential Address ') }}</th>
                                                            <td>{{$driverDetails['general_info']['residential_address']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' State/City ') }}</th>
                                                            <td>{{$driverDetails['general_info']['state_city']}}</td>
                                                            <th>{{ translate(' Postal Code ') }}</th>
                                                            <td>{{$driverDetails['general_info']['postal_code']}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="contact_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate('Mobile Number') }}</th>
                                                            <td>{{ucwords(str_replace('_', ' ', $driverDetails['contact_info']['mobile_number']))}}</td>
                                                            <th>{{ translate('Alternative Number') }}</th>
                                                            <td>{{$driverDetails['contact_info']['alternative_number']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate('Email Address') }}</th>
                                                            <td>{{$driverDetails['contact_info']['email_address']}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="vehicle_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate(' Vehicle Make & Model ') }}</th>
                                                            <td>{{ucwords(str_replace('_', ' ', $driverDetails['vehicle_info']['vehicle_make_model']))}}</td>
                                                            <th>{{ translate(' Vehicle Registration Number ') }}</th>
                                                            <td>{{$driverDetails['vehicle_info']['vehicle_registration']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Year of Manufacture ') }}</th>
                                                            <td>{{$driverDetails['vehicle_info']['year_of_manufacture']}}</td>
                                                            <th>{{ translate(' Insurance Policy Number ') }}</th>
                                                            <td>{{ucwords($driverDetails['vehicle_info']['insurance_policy'])}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Insurance Expiry Date ') }}</th>
                                                            <td>{{date('d-m-Y', strtotime($driverDetails['vehicle_info']['insurance_expiry']))}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="guarantor_info-tab-pane" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h5>{{ translate(' Guarantor 1 ') }}</h5>
                                                    <table class="table table-bordered table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th>{{ translate(' Full Name ') }}</th>
                                                                <td>{{ $driverDetails['guarantor_info']['guarantor1']['full_name1']}}</td>
                                                                <th>{{ translate(' Relationship to Applicant ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['relationship1']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate('Residential Address') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['residential_address1']}}</td>
                                                                <th>{{ translate(' Mobile Number ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['mobile_number1']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate(' Email Address ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['email_address1']}}</td>
                                                                <th>{{ translate(' Occupation ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['occupation1']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate(' National Identification Number (NIN) ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor1']['nin1']}}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5>{{ translate(' Guarantor 2 ') }}</h5>
                                                    <table class="table table-bordered table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th>{{ translate(' Full Name ') }}</th>
                                                                <td>{{ $driverDetails['guarantor_info']['guarantor2']['full_name2'] }}</td>
                                                                <th>{{ translate(' Relationship to Applicant ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['relationship2']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate('Residential Address') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['residential_address2']}}</td>
                                                                <th>{{ translate(' Mobile Number ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['mobile_number2']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate(' Email Address ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['email_address2']}}</td>
                                                                <th>{{ translate(' Occupation ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['occupation2']}}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>{{ translate(' National Identification Number (NIN) ') }}</th>
                                                                <td>{{$driverDetails['guarantor_info']['guarantor2']['nin2']}}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="emergency_contact_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate(' Full Name ') }}</th>
                                                            <td>{{$driverDetails['emergency_info']['full_name']}}</td>
                                                            <th>{{ translate(' Relationship ') }}</th>
                                                            <td>{{$driverDetails['emergency_info']['relationship']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Mobile Number ') }}</th>
                                                            <td>{{$driverDetails['emergency_info']['mobile_number']}}</td>
                                                            <th>{{ translate(' Alternative Number ') }}</th>
                                                            <td>{{$driverDetails['emergency_info']['alternative_number']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Address ') }}</th>
                                                            <td>{{$driverDetails['emergency_info']['address']}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="declaration_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate(' Declarant Name ') }}</th>
                                                            <td>{{$driverDetails['declaration_info']['declarant_name']}}</td>
                                                            <th>{{ translate(' Declaration Date ') }}</th>
                                                            <td>{{date('d-m-Y', strtotime($driverDetails['declaration_info']['declaration_date']))}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Signature ') }}</th>
                                                            <td>{{$driverDetails['declaration_info']['signature']}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="official_use_info-tab-pane" role="tabpanel">
                                            <div class="col-md-12">
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <th>{{ translate(' Application Status ') }}</th>
                                                            <td>{{ucwords($driverDetails['official_use_info']['application_status'])}}</td>
                                                            <th>{{ translate(' Reviewed By ') }}</th>
                                                            <td>{{$driverDetails['official_use_info']['reviewed_by']}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ translate(' Review Date ') }}</th>
                                                            <td>{{date('d-m-Y', strtotime($driverDetails['official_use_info']['review_date']))}}</td>
                                                            <th>{{ translate(' Remarks ') }}</th>
                                                            <td>{{$driverDetails['official_use_info']['remarks']}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-30">
                <div class="card-body">
                    <div class="row justify-content-between align-items-center g-2 mb-3">
                        <div class="col-sm-6">
                            <h5 class="text-capitalize d-flex align-items-center gap-2 text-primary">
                                <i class="bi bi-person-fill-gear"></i>
                                {{ translate('wallet_info') }}
                            </h5>
                        </div>
                    </div>
                    <div class="row g-4" id="order_stats">
                        <div class="col-lg-4">

                            <div class="card h-100 d-flex justify-content-center align-items-center">
                                <div class="card-body d-flex flex-column gap-10 align-items-center justify-content-center">
                                    <img width="48" src="{{ asset('public/assets/admin-module/img/media/cc.png') }}"
                                        alt="">
                                    <h3 class="fw-bold mb-0 fs-3">
                                        {{ getCurrencyFormat($commonData['collectable_amount']) }}</h3>
                                    <div class="fw-bold text-capitalize mb-30">
                                        {{ translate('collectable_cash') }}
                                    </div>
                                </div>
                                @if($commonData['collectable_amount']>0)
                                    <a href="{{ route('admin.driver.cash.index', [$commonData['driver']->id]) }}"
                                       class="text-capitalize btn btn-primary mb-4">{{ translate('collect_cash') }}</a>
                                @endif
                            </div>

                        </div>
                        <div class="col-lg-8">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card card-body h-100 justify-content-center py-5">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="fw-bold mb-1 fs-3">
                                                    {{ getCurrencyFormat($commonData['pending_withdraw']) }}</h3>
                                                <div class="text-capitalize mb-0 text-capitalize fw-bold">
                                                    {{ translate('pending_withdraw') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" class="mb-2"
                                                    src="{{ asset('public/assets/admin-module/img/media/pw.png') }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-body h-100 justify-content-center py-5">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="fw-bold mb-1 fs-3">
                                                    {{ getCurrencyFormat($commonData['already_withdrawn']) }}</h3>
                                                <div class="fw-bold text-capitalize mb-0">
                                                    {{ translate('already_withdrawn') }}</div>
                                            </div>
                                            <div>
                                                <img width="40"
                                                    src="{{ asset('public/assets/admin-module/img/media/aw.png') }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-body h-100 justify-content-center py-5">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fs-3 fw-bold">
                                                    {{ getCurrencyFormat($commonData['withdrawable_amount']) }}
                                                </h3>
                                                <div class="fw-bold text-capitalize mb-0">
                                                    {{ translate('withdrawable_amount') }}</div>
                                            </div>
                                            <div>
                                                <img width="40" class="mb-2"
                                                    src="{{ asset('public/assets/admin-module/img/media/withdraw.png') }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="card card-body h-100 justify-content-center py-5">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <div class="d-flex flex-column align-items-start">
                                                <h3 class="mb-1 fs-3 fw-bold">
                                                    {{ getCurrencyFormat($commonData['total_earning'] + $commonData['already_withdrawn']) }}
                                                </h3>
                                                <div class="text-capitalize mb-0 fw-bold">
                                                    {{ translate('total_earning') }}</div>
                                            </div>
                                            <div>
                                                <img width="40"
                                                    src="{{ asset('public/assets/admin-module/img/media/withdraw-icon.png') }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="d-flex mb-4">
                <ul class="nav nav--tabs p-1 rounded bg-white" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('admin.driver.show', ['id' => $commonData['driver']->id, 'tab' => 'overview']) }}"
                            class="nav-link {{ $commonData['tab'] == 'overview' ? 'active' : '' }}">{{ translate('overview') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $commonData['tab'] == 'vehicle' ? 'active' : '' }}"
                            href="{{ route('admin.driver.show', ['id' => $commonData['driver']->id, 'tab' => 'vehicle']) }}"
                            tabindex="-1">{{ translate('vehicle') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('admin.driver.show', ['id' => $commonData['driver']->id, 'tab' => 'trips']) }}"
                            class="nav-link {{ $commonData['tab'] == 'trips' ? 'active' : '' }}"
                            tabindex="-1">{{ translate('trips') }}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('admin.driver.show', ['id' => $commonData['driver']->id, 'tab' => 'transaction']) }}"
                            class="nav-link {{ $commonData['tab'] == 'transaction' ? 'active' : '' }}" role="tab"
                            tabindex="-1">{{translate("Transaction")}}</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('admin.driver.show', ['id' => $commonData['driver']->id, 'tab' => 'review', 'reviewed_by' => 'customer']) }}"
                            class="nav-link {{ $commonData['tab'] == 'review' ? 'active' : '' }}"
                            tabindex="-1">{{ translate('review') }}</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                @if ($commonData['tab'] == 'overview')
                    @include('usermanagement::admin.driver.partials.overview', [
                        'commonData' => $commonData,
                        'otherData' => $otherData,
                    ])
                @endif
                @if ($commonData['tab'] == 'vehicle')
                    @include('usermanagement::admin.driver.partials.vehicle', [
                        'commonData' => $commonData,
                        'otherData' => $otherData,
                    ])
                @endif
                @if ($commonData['tab'] == 'trips')
                    @include('usermanagement::admin.driver.partials.trips', [
                        'commonData' => $commonData,
                        'otherData' => $otherData,
                    ])
                @endif
                @if ($commonData['tab'] == 'transaction')
                    @include('usermanagement::admin.driver.partials.transaction', [
                        'commonData' => $commonData,
                        'otherData' => $otherData,
                    ])
                @endif
                @if ($commonData['tab'] == 'review')
                    @include('usermanagement::admin.driver.partials.review', [
                        'commonData' => $commonData,
                        'otherData' => $otherData,
                    ])
                @endif
            </div>

        </div>
    </div>
    <!-- End Main Content -->

@endsection

@push('script')
    <!-- Apex Chart -->
    <script src="{{ asset('public/assets/admin-module/plugins/apex/apexcharts.min.js') }}"></script>
@endpush
