<?php

namespace App\Services\Hospital\Registration;

use App\Constants\General\AppConstants;
use App\Helpers\Helper;
use App\Models\Hospital;
use App\Models\Hospital\Hospital as HospitalHospital;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HotelRegistrationService
{
    // This service will handle the registration logic for hospitals.
    // It can include methods for creating hospital records, validating data,
    // and any other business logic related to hospital registration.

    public function __construct()
    {
        // Initialize any dependencies or services needed for hospital registration.
    }

    private function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:hospitals,email',
            'phone' => 'required|string|min:10',
            'address' => 'required|string|min:5',
            'hospital_type' => 'required|string|in:' . implode(',', AppConstants::HOSPITAL_TYPES),
            'registration_number' => 'required|string|min:3',
            'logo' => 'nullable',

            //Hospital Legal Compliance
            'license' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'request_on_site_setup' => 'nullable|boolean',
            'accept_terms' => 'required|boolean',

            // Hospital Services Location
            'street_address' => 'required|string|min:5',
            'city' => 'required|string|min:2',
            'state' => 'required|string|min:2',
            'google_maps_location' => 'nullable|string',
            'number_of_beds' => 'nullable|integer|min:0',

            'departments' => 'required|array|min:1',
            'departments.*' => 'string',
            'services' => 'required|array|min:1',
            'services.*' => 'string',

            'operating_hours' => 'nullable|array',
            'operating_hours.*.day' => 'required_with:operating_hours|string', // <-- Add this line
            'operating_hours.*.start' => 'nullable|date_format:H:i',
            'operating_hours.*.end' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator->validated();
    }

    public function registerHospital(array $data)
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = $data['user_id'] ?? auth()->id();
            $data = self::validate($data);
            if (isset($data['logo']) && is_array($data['logo'])) {
                $file_directory = 'files/hotel/logo';
                $data['logo_id'] = Helper::saveSingleFileRequest($data['logo'], $file_directory);
            }
            $hospital = HospitalHospital::create($data);

            // Create a hospital user record
            $hospital->users()->create([
                'user_id' => $data['user_id'],
                'hospital_id' => $hospital->id,
                'role' => AppConstants::ROLE_HOSPITAL_OWNER,
                'user_account_id' => $data['user_id'],
            ]);
            DB::commit();
            return $hospital->load('user');;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Hospital $hospital, array $data)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $hospital->update($data);
            DB::commit();
            return $hospital;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete(Hospital $hospital)
    {
        DB::beginTransaction();
        try {
            $hospital->delete();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
