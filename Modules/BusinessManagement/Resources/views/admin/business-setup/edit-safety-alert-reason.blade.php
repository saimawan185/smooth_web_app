<form
    action="{{ route('admin.business.setup.safety-precaution.safety-alert-reason.update',  $safetyAlertReason?->id) }}"
    method="post" id="updateForm" class="">
    @csrf
    <div class="modal-header border-0">
        <button type="button" class="btn-close outline-none shadow-none" data-bs-dismiss="modal"
                aria-label="Close">
        </button>
    </div>
    <div class="modal-body pt-0">
        <div class="mb-20">
            <h5 class="mb-2">{{ translate('Edit Safety Alert Reason') }}</h5>
            <div class="fs-12">
                {{ translate('Here you can set Safety Alert Reason') }}
            </div>
        </div>
        <div class="p-30 rounded bg-F6F6F6">
            <div class="p-30 rounded bg-F6F6F6">
                <div>
                    <label
                        class="form-label">{{ translate('Safety Alert Reasons') }}
                        <span class="text-danger">*</span>
                    </label>
                    <div class="character-count">
                        <fieldset class="input-group input--group ">
                            <input type="text"
                                   class="form-control character-count-field" name="reason"
                                   placeholder="{{ translate('Ex: Driver Taking Unusual Route') }}"
                                   maxlength="150" data-max-character="150"
                                   value="{{ $safetyAlertReason->reason }}"
                                   required>
                            <select class="form-select" name="reason_for_whom" required>
                                <option value="{{ DRIVER }}"
                                    {{ $safetyAlertReason->reason_for_whom == DRIVER ? 'selected' : '' }}
                                > {{ translate(DRIVER) }}</option>
                                <option
                                    value="{{ CUSTOMER }}"
                                    {{ $safetyAlertReason->reason_for_whom == CUSTOMER ? 'selected' : '' }}
                                > {{ translate(CUSTOMER) }}</option>
                            </select>
                        </fieldset>
                        <span class="d-flex justify-content-end">{{ translate('0/150') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="d-flex gap-3 justify-content-end">
            <button class="btn btn-light h-40px min-w-100px justify-content-center fw-semibold"
                    data-bs-dismiss="modal" type="button">{{ translate('Cancel') }}</button>
            <button type="submit"
                    class="btn btn-primary h-40px min-w-100px justify-content-center fw-semibold">{{ translate('Update') }}</button>
        </div>
    </div>
</form>
