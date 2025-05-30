@extends('adminmodule::layouts.master')

@section('title', translate('Update_Driver'))

@section('content')

@php
    $driverDetails = $driver?->driverDetails['additional_info'] ?? null;

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
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                <h2 class="fs-22">{{ translate('update_Driver') }}</h2>
            </div>

            <form action="{{ route('admin.driver.update', ['id' => $driver->id]) }}" method="post"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row gy-4">
                            <div class="col-lg-8">
                                <h5 class="text-primary text-uppercase mb-4">{{ translate('general_info') }}</h5>

                                <div class="row align-items-end">
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="f_name"
                                                   class="mb-2 text-capitalize">{{ translate('first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" required value="{{ $driver?->first_name }}"
                                                   name="first_name" id="f_name" class="form-control"
                                                   placeholder="{{ translate('ex') }}: {{ translate('Maximilian') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="l_name" class="mb-2">{{ translate('last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" required value="{{ $driver?->last_name }}"
                                                   name="last_name" id="l_name" class="form-control"
                                                   placeholder="{{ translate('ex') }}: {{ translate('Schwarzmüller') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="p_email" class="mb-2">{{ translate('email') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" required value="{{ $driver->email }}" name="email"
                                                   id="p_email" class="form-control"
                                                   placeholder="{{ translate('ex') }}: {{ translate('company@company.com') }}">
                                        </div>
                                    </div>
                                    <!--<div class="col-sm-6">-->
                                    <!--    <div class="mb-4">-->
                                    <!--        <label for="identity_type" class="mb-2">{{ translate('identity_type') }}-->
                                    <!--            <span class="text-danger">*</span></label>-->
                                    <!--        <select name="identification_type" class="js-select text-capitalize"-->
                                    <!--                id="identity_type" required>-->
                                    <!--            <option value="passport"-->
                                    <!--                {{ $driver->identification_type == 'passport' ? 'selected' : '' }}>-->
                                    <!--                {{ translate('passport') }}</option>-->
                                    <!--            <option value="nid"-->
                                    <!--                {{ $driver->identification_type == 'nid' ? 'selected' : '' }}>-->
                                    <!--                {{ translate('NID') }}</option>-->
                                    <!--            <option value="driving_license"-->
                                    <!--                {{ $driver->identification_type == 'driving_license' ? 'selected' : '' }}>-->
                                    <!--                {{ translate('driving_license') }}</option>-->
                                    <!--        </select>-->
                                    <!--    </div>-->
                                    <!--</div>-->
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="identity_type" class="mb-2">{{ translate('identity_type') }}
                                                <span class="text-danger">*</span></label>
                                            <select name="identification_type" class="js-select text-capitalize"
                                                    id="identity_type" required>
                                                <option disabled selected>-- {{ translate('select_identity_type') }}
                                                    --
                                                </option>
                                                <option value="passport">{{ translate('International Passport') }}</option>
                                                <option value="driving_license">{{ translate('Driving License') }}</option>
                                                <option value="nin">{{ translate('NIN') }}</option>
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div id="identity_input_container"></div>
                                    
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="dob" class="mb-2">{{ translate('Date of Birth') }} <span class="text-danger">*</span></label>
                                            <input type="date" name="dob" id="dob" class="form-control" value="{{$driverDetails['general_info']['dob'] ?? ''}}" required>
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="gender" class="mb-2">{{ translate('Gender') }} <span class="text-danger">*</span></label>
                                            <select name="gender" id="gender" class="form-control" required>
                                                <option disabled selected>-- {{ translate('Select Gender') }} --</option>
                                                <option value="male" >{{ translate('Male') }}</option>
                                                <option value="female" >{{ translate('Female') }}</option>
                                                <option value="other" >{{ translate('Other') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="expiry_date" class="mb-2">{{ translate('Expiry Date') }} <span class="text-danger">*</span></label>
                                            <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{$driverDetails['general_info']['expiry_date'] ?? ''}}" required>
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="residential_address" class="mb-2">{{ translate('Current Residential Address') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="residential_address" id="residential_address" class="form-control"
                                                placeholder="Enter your full residential address" value="{{$driverDetails['general_info']['residential_address'] ?? ''}}" required>
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="state_city" class="mb-2">{{ translate('State/City') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="state_city" id="state_city" class="form-control" placeholder="Enter your state or city" value="{{$driverDetails['general_info']['state_city'] ?? ''}}" required>
                                        </div>
                                    </div>
                                
                                    <div class="col-sm-6">
                                        <div class="mb-4">
                                            <label for="postal_code" class="mb-2">{{ translate('Postal Code') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="postal_code" id="postal_code" class="form-control"
                                                placeholder="Enter postal code (5-10 digits)" required pattern="[0-9]{5,10}" title="Postal Code must be between 5 and 10 digits." value="{{$driverDetails['general_info']['postal_code'] ?? ''}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-column justify-content-around gap-3">
                                    <h5 class="text-center text-capitalize">{{ translate('driver_image') }}</h5>

                                    <div class="d-flex justify-content-center">
                                        <div class="upload-file auto">
                                            <input type="file" name="profile_image" class="upload-file__input"
                                                   accept=".jpg, .jpeg, .png, .webp">
                                            <span class="edit-btn show">
                                                <img
                                                    src="{{ asset('public/assets/admin-module/img/svg/edit-circle.svg') }}"
                                                    alt="" class="svg">
                                            </span>
                                            <div
                                                class="upload-file__img border-gray d-flex justify-content-center align-items-center w-180 h-180 p-0">
                                                <img class="upload-file__img__img h-100" width="180" height="180"
                                                     loading="lazy"
                                                     src="{{ onErrorImage(
                                                    $driver?->profile_image,
                                                    asset('storage/app/public/driver/profile') . '/' . $driver?->profile_image,
                                                    asset('public/assets/admin-module/img/avatar/avatar.png'),
                                                    'driver/profile/',
                                                ) }}"
                                                     alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="opacity-75 mx-auto max-w220">
                                        {{ translate('JPG, JPEG, PNG, WEBP Less Than 1MB') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column justify-content-around gap-3">
                                            <h5 class="">{{ translate('identity_card_images') }}</h5>

                                            <div class="gap-3 d-flex image-contain">
                                                @if ($driver?->identification_image)
                                                    @foreach ($driver?->identification_image as $img)
                                                        <div class="upload-file__img upload-file__img_banner">
                                                            <img src="{{ onErrorImage(
                                                                $img,
                                                                asset('storage/app/public/driver/identity') . '/' . $img,
                                                                asset('public/assets/admin-module/img/media/banner-upload-file.png'),
                                                                'driver/identity/',
                                                            ) }}"
                                                                 class="rounded-circle dark-support"
                                                                 width="100%"
                                                                 alt="">
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column justify-content-around gap-3">
                                            <h5 class="">{{ translate('update_identity_card_images') }}</h5>
                                            <div class="gap-3 d-flex custom" id="multi_image_picker">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('account_information') }}</h5>

                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="phone_number" class="mb-2">{{ translate('phone') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" pattern="[0-9]{1,14}" required value="{{ $driver->phone }}"
                                           id="phone_number" class="form-control w-100 text-dir-start"
                                           placeholder="{{ translate('ex') }}: xxxxx xxxxxx">
                                    <input type="hidden" id="phone_number-hidden-element" name="phone">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="password" class="mb-2">{{ translate('password') }}</label>
                                    <input type="password" name="password" id="password" class="form-control"
                                           placeholder="{{ translate('ex') }}: ********">
                                    <i id="password-eye" class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4 input-group_tooltip">
                                    <label for="confirm_password"
                                           class="mb-2">{{ translate('confirm_password') }}</label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                           class="form-control" placeholder="{{ translate('ex') }}: ********'">
                                    <i id="conf-password-eye"
                                       class="mt-3 bi bi-eye-slash-fill text-primary tooltip-icon"
                                       data-bs-toggle="tooltip" data-bs-title=""></i>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-4">{{ translate('other_documents') }}</h5>
                        <div class="d-flex flex-wrap gap-3">
                            @if ($driver->other_documents != null)
                                @foreach ($driver->other_documents as $document)
                                    <div class="show-image">
                                        <div class="file__value bg-transparent border border-C5D2D2 remove_outside"
                                             data-document="{{ $document }}">
                                            <img class="file__value--icon"
                                                 src="{{ getExtensionIcon($document) }}"
                                                 alt="">
                                            <div class="file__value--text">{{ $document }}</div>
                                            <div class="file__value--remove fw-bold"
                                                 data-id="{{$document}}">
                                                <img
                                                    src="{{ asset('public/assets/admin-module/img/icons/close-circle.svg') }}"
                                                    alt="">
                                            </div>
                                            <input type="hidden" name="existing_documents[]"
                                                   value="{{ $document }}">
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            <div class="d-flex flex-wrap gap-3" id="selected-files-container1"></div>
                            <div id="input-data"></div>
                            <!-- Upload New Documents -->
                            <div class="upload-file file__input" id="file__input">
                                <input type="file" class="upload-file__input2" multiple="multiple"
                                >
                                <div class="upload-file__img2">
                                    <div class="upload-box rounded media gap-4 align-items-center p-4 px-lg-5">
                                        <i class="bi bi-cloud-arrow-up-fill fs-20"></i>
                                        <div class="media-body">
                                            <p class="text-muted mb-2 fs-12">{{ translate('upload') }}</p>
                                            <h6 class="fs-12 text-capitalize">{{ translate('file_or_image') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('contact_information') }}</h5>

                        <div class="row align-items-end">
                            <div class="col-sm-4">
                                <div class="mb-4">
                                    <label for="mobile_number" class="mb-2">{{ translate('Mobile Number') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile_number" id="mobile_number" class="form-control"
                                        placeholder="Enter your mobile number" required pattern="[0-9]{10,15}" title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['contact_info']['mobile_number'] ?? ''}}">
                                </div>
                            </div>
                        
                            <div class="col-sm-4">
                                <div class="mb-4">
                                    <label for="alternative_number" class="mb-2">{{ translate('Alternative Number') }}</label>
                                    <input type="tel" name="alternative_number" id="alternative_number" class="form-control"
                                        placeholder="Enter an alternative number (optional)" pattern="[0-9]{10,15}" title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['contact_info']['alternative_number'] ?? ''}}">
                                </div>
                            </div>
                        
                            <div class="col-sm-4">
                                <div class="mb-4">
                                    <label for="email_address" class="mb-2">{{ translate('Email Address') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email_address" id="email_address" class="form-control"
                                        placeholder="Enter your email address" value="{{$driverDetails['contact_info']['email_address'] ?? ''}}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vehicle Information -->
                
                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('vehicle_information') }}</h5>

                        <div class="row align-items-end">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="vehicle_make_model" class="mb-2">{{ translate('Vehicle Make & Model') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_make_model" id="vehicle_make_model" class="form-control"
                                        placeholder="Enter vehicle make and model" value="{{$driverDetails['vehicle_info']['vehicle_make_model'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="vehicle_registration" class="mb-2">{{ translate('Vehicle Registration Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="vehicle_registration" id="vehicle_registration" class="form-control"
                                        placeholder="Enter registration number" required pattern="[A-Za-z0-9-]{6,15}"
                                        title="Registration number should be 6 to 15 alphanumeric characters or may contain dashes (-)." value="{{$driverDetails['vehicle_info']['vehicle_registration'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="year_of_manufacture" class="mb-2">{{ translate('Year of Manufacture') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="year_of_manufacture" id="year_of_manufacture" class="form-control"
                                        placeholder="Enter year (e.g., 2015)" required min="1900" max="2025"  value="{{$driverDetails['vehicle_info']['year_of_manufacture'] ?? ''}}"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="insurance_policy" class="mb-2">{{ translate('Insurance Policy Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="insurance_policy" id="insurance_policy" class="form-control"
                                        placeholder="Enter policy number" required pattern="[A-Za-z0-9-]{6,20}"
                                        title="Policy number should be 6 to 20 alphanumeric characters or may contain dashes (-)." value="{{$driverDetails['vehicle_info']['insurance_policy'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="insurance_expiry" class="mb-2">{{ translate('Insurance Expiry Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="insurance_expiry" id="insurance_expiry" class="form-control" value="{{$driverDetails['vehicle_info']['insurance_expiry'] ?? ''}}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!--Guarantor Information-->
                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('guarantor_information ') }}</h5>
                        <h6 class="text-primary text-uppercase mb-4">{{ translate('guarantor_1 ') }}</h6>

                        <div class="row align-items-end">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="full_name1" class="mb-2">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name1" id="full_name1" class="form-control"
                                           placeholder="Enter full name" required pattern="[A-Za-z\s]+"
                                           title="Only alphabets and spaces are allowed." value="{{$driverDetails['guarantor_info']['guarantor1']['full_name1'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="relationship1" class="mb-2">{{ translate('Relationship to Applicant') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="relationship1" id="relationship1" class="form-control"
                                           placeholder="Enter relationship (e.g., Father, Sister, Friend)" value="{{$driverDetails['guarantor_info']['guarantor1']['relationship1'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="residential_address1" class="mb-2">{{ translate('Residential Address') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="residential_address1" id="residential_address1" class="form-control"
                                           placeholder="Enter full residential address" value="{{$driverDetails['guarantor_info']['guarantor1']['residential_address1'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="mobile_number1" class="mb-2">{{ translate('Mobile Number') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile_number1" id="mobile_number1" class="form-control"
                                           placeholder="Enter mobile number" required pattern="^\d{10,15}$"
                                           title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['guarantor_info']['guarantor1']['mobile_number1'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="email_address1" class="mb-2">{{ translate('Email Address') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email_address1" id="email_address1" class="form-control"
                                           placeholder="Enter email address" value="{{$driverDetails['guarantor_info']['guarantor1']['email_address1'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="occupation1" class="mb-2">{{ translate('Occupation') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="occupation1" id="occupation1" class="form-control"
                                           placeholder="Enter occupation" value="{{$driverDetails['guarantor_info']['guarantor1']['occupation1'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="nin1" class="mb-2">{{ translate('National Identification Number (NIN)') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="nin1" id="nin1" class="form-control"
                                           placeholder="Enter NIN (11 digits)" required pattern="^\d{11}$"
                                           title="NIN must be exactly 11 digits." value="{{$driverDetails['guarantor_info']['guarantor1']['nin1'] ?? ''}}">
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="text-primary text-uppercase mb-4">{{ translate('guarantor_2 ') }}</h6>
                        
                        <div class="row align-items-end">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="full_name2" class="mb-2">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name2" id="full_name2" class="form-control"
                                           placeholder="Enter full name" required pattern="[A-Za-z\s]+"
                                           title="Only alphabets and spaces are allowed." value="{{$driverDetails['guarantor_info']['guarantor2']['full_name2'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="relationship2" class="mb-2">{{ translate('Relationship to Applicant') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="relationship2" id="relationship2" class="form-control"
                                           placeholder="Enter relationship (e.g., Mother, Uncle, Friend)" value="{{$driverDetails['guarantor_info']['guarantor2']['relationship2'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="residential_address2" class="mb-2">{{ translate('Residential Address') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="residential_address2" id="residential_address2" class="form-control"
                                           placeholder="Enter full residential address" value="{{$driverDetails['guarantor_info']['guarantor2']['residential_address2'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="mobile_number2" class="mb-2">{{ translate('Mobile Number') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile_number2" id="mobile_number2" class="form-control"
                                           placeholder="Enter mobile number" required pattern="^\d{10,15}$"
                                           title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['guarantor_info']['guarantor2']['mobile_number2'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="email_address2" class="mb-2">{{ translate('Email Address') }} <span class="text-danger">*</span></label>
                                    <input type="email" name="email_address2" id="email_address2" class="form-control"
                                           placeholder="Enter email address" value="{{$driverDetails['guarantor_info']['guarantor2']['email_address2'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="occupation2" class="mb-2">{{ translate('Occupation') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="occupation2" id="occupation2" class="form-control"
                                           placeholder="Enter occupation" value="{{$driverDetails['guarantor_info']['guarantor2']['occupation2'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="nin2" class="mb-2">{{ translate('National Identification Number (NIN)') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="nin2" id="nin2" class="form-control"
                                           placeholder="Enter NIN (11 digits)" required pattern="^\d{11}$"
                                           title="NIN must be exactly 11 digits." value="{{$driverDetails['guarantor_info']['guarantor2']['nin2'] ?? ''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Emergency Contact Information -->
                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('emergency_contact_information ') }}</h5>

                        <div class="row align-items-end">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="full_name" class="mb-2">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" id="full_name" class="form-control"
                                           placeholder="Enter full name" required pattern="[A-Za-z\s]+"
                                           title="Only alphabets and spaces are allowed."  value="{{$driverDetails['emergency_info']['full_name'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="relationship" class="mb-2">{{ translate('Relationship') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="relationship" id="relationship" class="form-control"
                                           placeholder="Enter relationship (e.g., Friend, Brother, Parent)" value="{{$driverDetails['emergency_info']['relationship'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="mobile_number" class="mb-2">{{ translate('Mobile Number') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="mobile_number" id="mobile_number" class="form-control"
                                           placeholder="Enter mobile number" required pattern="^\d{10,15}$"
                                           title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['emergency_info']['mobile_number'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="alternative_number" class="mb-2">{{ translate('Alternative Number') }}</label>
                                    <input type="tel" name="alternative_number" id="alternative_number" class="form-control"
                                           placeholder="Enter alternative number (optional)" pattern="^\d{10,15}$"
                                           title="Enter a valid mobile number (10 to 15 digits)." value="{{$driverDetails['emergency_info']['alternative_number'] ?? ''}}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="address" class="mb-2">{{ translate('Address') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="address" id="address" class="form-control"
                                           placeholder="Enter full address" value="{{$driverDetails['emergency_info']['address'] ?? ''}}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Declaration -->
                <div class="card overflow-visible mt-3">
                    <div class="card-body">
                        <h5 class="text-primary text-uppercase mb-4">{{ translate('declaration ') }}</h5>

                        <div class="row align-items-end">
                            <!-- Declaration Section -->
                            <div class="col-12 mb-4">
                                <p>
                                    I, <input type="text" name="declarant_name" id="declarant_name" class="border-0 border-bottom w-50" value="{{$driverDetails['declaration_info']['declarant_name'] ?? ''}}" required>, 
                                    declare that the information provided in this application is true and correct to the best of my knowledge. 
                                    I understand that any false information provided may result in the rejection of my application.
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="signature" class="mb-2">Signature:</label>
                                    <input type="text" name="signature" id="signature" class="border-0 border-bottom w-100" value="{{$driverDetails['declaration_info']['signature'] ?? ''}}" required>
                                </div>
                            </div>
                        
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="declaration_date" class="mb-2">Date:</label>
                                    <input type="date" name="declaration_date" id="declaration_date" class="border-0 border-bottom w-100" value="{{$driverDetails['declaration_info']['declaration_date'] ?? ''}}" required>
                                </div>
                            </div>
                        
                            <!-- Official Use Section -->
                            <div class="col-12 mt-4 mb-4">
                                <h5 class="fw-bold">FOR OFFICIAL USE ONLY</h5>
                            </div>
                            <div class="col-12">
                                <div class="mb-4">
                                    <label for="application_status" class="mb-2">Application Status:</label>
                                    <input type="checkbox" id="approved" name="application_status" value="approved" {{(isset($driverDetails['official_use_info']['application_status']) && $driverDetails['official_use_info']['application_status'] == 'approved') ? 'checked' : ''}}>
                                    <label for="approved">Approved</label>
                                    <input type="checkbox" id="rejected" name="application_status" value="rejected" {{(isset($driverDetails['official_use_info']['application_status']) && $driverDetails['official_use_info']['application_status'] == 'rejected') ? 'checked' : ''}} class="ms-3">
                                    <label for="rejected">Rejected</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-4">
                                    <label for="reviewed_by" class="mb-2">Reviewed by:</label>
                                    <input type="text" name="reviewed_by" id="reviewed_by" class="border-0 border-bottom w-100" value="{{$driverDetails['official_use_info']['reviewed_by'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-4">
                                    <label for="review_date" class="mb-2">Date:</label>
                                    <input type="date" name="review_date" id="review_date" class="border-0 border-bottom w-100" value="{{$driverDetails['official_use_info']['review_date'] ?? ''}}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-4">
                                    <label for="remarks" class="mb-2">Remarks:</label>
                                    <input type="text" name="remarks" id="remarks" class="border-0 border-bottom w-100" value="{{$driverDetails['official_use_info']['remarks'] ?? ''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                

                <div class="d-flex justify-content-end gap-3 mt-3">
                    <button class="btn btn-primary" type="submit">{{ translate('save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- End Main Content -->

@endsection

@push('script')
    <link href="{{ asset('public/assets/admin-module/css/intlTelInput.min.css') }}" rel="stylesheet"/>
    <script src="{{ asset('public/assets/admin-module/js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/password.js') }}"></script>
    <script src="{{ asset('public/assets/admin-module/js/upload-files-create.js') }}"></script>

    <script>
        "use strict";
        initializePhoneInput("#phone_number", "#phone_number-hidden-element");
    </script>
    
    <script>
        $(document).ready(function () {
        
            $('#identity_type').change(function () {
                var selectedType = $(this).val();
                var inputField = '';
    
                if (selectedType === 'passport') {
                    inputField = `
                        <div class="mb-4">
                            <label for="passport_number" class="mb-2">{{ translate('Passport Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="passport_number" id="passport_number" class="form-control"
                                placeholder="Enter Passport Number" required
                                pattern="[A-Za-z0-9]{6,9}" title="Passport number should be 6 to 9 alphanumeric characters.">
                        </div>`;
                } else if (selectedType === 'driving_license') {
                    inputField = `
                        <div class="mb-4">
                            <label for="driving_license" class="mb-2">{{ translate('Driving License Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="driving_license_number" id="driving_license_number" class="form-control"
                                placeholder="Enter Driving License Number" required
                                pattern="[A-Za-z0-9-]{6,15}" title="Driving License should be 6 to 15 alphanumeric characters or may contain dashes (-).">
                        </div>`;
                } else if (selectedType === 'nin') {
                    inputField = `
                        <div class="mb-4">
                            <label for="nin_number" class="mb-2">{{ translate('NIN Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="nin_number" id="nin_number" class="form-control"
                                placeholder="Enter NIN Number" required
                                pattern="[0-9]{11}" title="NIN number must be exactly 11 digits." minlength="11" maxlength="11">
                        </div>`;
                }
    
                $('#identity_input_container').html('<div class="col-sm-6">' + inputField + '</div>');
    
                // Apply restrictions dynamically
                applyInputRestrictions();
            });
            
            function applyInputRestrictions() {
                $('#passport_number').on('input', function () {
                    this.value = this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, 9);
                });
    
                $('#driving_license').on('input', function () {
                    this.value = this.value.replace(/[^A-Za-z0-9-]/g, '').slice(0, 15);
                });
    
                $('#nin_number').on('input', function () {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
                });
            }
            
            $('#postal_code').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
            });

            $('#dob').on('change', function () {
                var dob = new Date(this.value);
                var today = new Date();
                if (dob >= today) {
                    alert("Date of Birth must be in the past.");
                    this.value = '';
                }
            });

            $('#expiry_date').on('change', function () {
                var expiry = new Date(this.value);
                var today = new Date();
                if (expiry <= today) {
                    alert("Expiry Date must be in the future.");
                    this.value = '';
                }
            });
            
            $('#insurance_expiry').on('change', function () {
                var expiry = new Date(this.value);
                var today = new Date();
                if (expiry <= today) {
                    alert("Insurance Expiry Date must be in the future.");
                    this.value = '';
                }
            });
            
            $('#nin1, #nin2').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });
            
            
            $('#gender').val('{{$driverDetails["general_info"]["gender"] ?? ""}}').trigger("change");
            
            let selectedIdentityType = "{{$driverDetails['general_info']['identification_type'] ?? ''}}";

            const identitySelect = $("#identity_type");
            const identityInputContainer = $("#identity_input_container");
        
            // Auto-select in edit mode
            if (selectedIdentityType) {
                identitySelect.val(selectedIdentityType).trigger("change");
            }
            
            // dynamic field set value
            let selectedIdentityType_field = "{{$driverDetails['general_info']['identification_type'] ?? ''}}" + '_number';
        
            $("#"+selectedIdentityType_field).val('{{ $driverDetails["general_info"][$dynamic_value] ?? '' }}');
        
        });
        
    </script>
@endpush
