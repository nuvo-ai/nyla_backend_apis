<?php

namespace App\Services\Hospital;

use App\Models\General\Service;
use App\Models\Hospital\Hospital;
use Illuminate\Http\UploadedFile;
use App\Models\General\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class HospitalService
{

    public function createHospital(array $data): Hospital
    {
        return DB::transaction(function () use ($data) {
            // Create hospital
            $hospital = Hospital::create([
                'name' => $data['hospital_name'],
                'type' => $data['hospital_type'],
                'registration_number' => $data['registration_number'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'logo_path' => $this->handleFileUpload($data['logo'] ?? null, 'hospital-logos'),
                'street_address' => $data['street_address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'google_maps_location' => $data['google_maps_location'] ?? null,
                'number_of_beds' => $data['number_of_beds'] ?? null,
                'license_path' => $this->handleFileUpload($data['license'] ?? null, 'hospital-licenses'),
                'request_onsite_setup' => $data['request_onsite_setup'] ?? false,
                'terms_accepted' => $data['accept_terms'] ?? false,
            ]);

            // Create primary contact
            $hospital->contacts()->create([
                'name' => $data['primary_contact_name'],
                'phone' => $data['primary_contact_phone'],
                'role' => $data['primary_contact_role'],
                'type' => 'primary'
            ]);

            

            // Attach departments
            if (isset($data['departments']) && is_array($data['departments'])) {
                $departmentIds = Department::whereIn('name', $data['departments'])->pluck('id');
                $hospital->departments()->attach($departmentIds);
            }

            // Attach services
            if (isset($data['services']) && is_array($data['services'])) {
                $serviceIds = Service::whereIn('name', $data['services'])->pluck('id');
                $hospital->services()->attach($serviceIds);
            }

            // Create operating hours
            if (isset($data['operating_hours']) && is_array($data['operating_hours'])) {
                foreach ($data['operating_hours'] as $day => $hours) {
                    $hospital->operatingHours()->create([
                        'day_of_week' => $day,
                        'start_time' => $hours['start'] ?? null,
                        'end_time' => $hours['end'] ?? null,
                        'is_closed' => empty($hours['start']) && empty($hours['end'])
                    ]);
                }
            }

            return $hospital->load(['contacts', 'departments', 'services', 'operatingHours']);
        });
    }

    public function updateHospital(Hospital $hospital, array $data): Hospital
    {
        return DB::transaction(function () use ($hospital, $data) {
            // Update hospital basic info
            $hospital->update([
                'name' => $data['hospital_name'] ?? $hospital->name,
                'type' => $data['hospital_type'] ?? $hospital->type,
                'registration_number' => $data['registration_number'] ?? $hospital->registration_number,
                'phone' => $data['phone'] ?? $hospital->phone,
                'email' => $data['email'] ?? $hospital->email,
                'street_address' => $data['street_address'] ?? $hospital->street_address,
                'city' => $data['city'] ?? $hospital->city,
                'state' => $data['state'] ?? $hospital->state,
                'google_maps_location' => $data['google_maps_location'] ?? $hospital->google_maps_location,
                'number_of_beds' => $data['number_of_beds'] ?? $hospital->number_of_beds,
                'request_onsite_setup' => $data['request_onsite_setup'] ?? $hospital->request_onsite_setup,
            ]);

            // Handle file uploads
            if (isset($data['logo'])) {
                if ($hospital->logo_path) {
                    Storage::delete($hospital->logo_path);
                }
                $hospital->logo_path = $this->handleFileUpload($data['logo'], 'hospital-logos');
                $hospital->save();
            }

            if (isset($data['license'])) {
                if ($hospital->license_path) {
                    Storage::delete($hospital->license_path);
                }
                $hospital->license_path = $this->handleFileUpload($data['license'], 'hospital-licenses');
                $hospital->save();
            }

            // Update primary contact
            if (isset($data['primary_contact_name']) || isset($data['primary_contact_phone']) || isset($data['primary_contact_role'])) {
                $hospital->primaryContact()->update([
                    'name' => $data['primary_contact_name'] ?? $hospital->primaryContact->name,
                    'phone' => $data['primary_contact_phone'] ?? $hospital->primaryContact->phone,
                    'role' => $data['primary_contact_role'] ?? $hospital->primaryContact->role,
                ]);
            }

            // Sync departments
            if (isset($data['departments']) && is_array($data['departments'])) {
                $departmentIds = Department::whereIn('name', $data['departments'])->pluck('id');
                $hospital->departments()->sync($departmentIds);
            }

            // Sync services
            if (isset($data['services']) && is_array($data['services'])) {
                $serviceIds = Service::whereIn('name', $data['services'])->pluck('id');
                $hospital->services()->sync($serviceIds);
            }

            // Update operating hours
            if (isset($data['operating_hours']) && is_array($data['operating_hours'])) {
                $hospital->operatingHours()->delete();
                foreach ($data['operating_hours'] as $day => $hours) {
                    $hospital->operatingHours()->create([
                        'day_of_week' => $day,
                        'start_time' => $hours['start'] ?? null,
                        'end_time' => $hours['end'] ?? null,
                        'is_closed' => empty($hours['start']) && empty($hours['end'])
                    ]);
                }
            }

            return $hospital->load(['contacts', 'departments', 'services', 'operatingHours']);
        });
    }

    public function approveHospital(Hospital $hospital): Hospital
    {
        $hospital->update(['status' => 'approved']);
        return $hospital;
    }

    public function rejectHospital(Hospital $hospital): Hospital
    {
        $hospital->update(['status' => 'rejected']);
        return $hospital;
    }

    private function handleFileUpload(?UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store($directory, 'public');
    }


}
