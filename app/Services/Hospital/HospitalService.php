<?php

namespace App\Services\Hospital;

use App\Constants\General\AppConstants;
use App\Constants\User\UserConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Mail\SendUserLoginDetailsMail;
use App\Models\General\Service;
use App\Models\Hospital\Hospital;
use Illuminate\Http\UploadedFile;
use App\Models\General\Department;
use App\Models\Hospital\HospitalUser;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;



class HospitalService
{
    public function validate(array $data): array
    {
        $request = request();
        $id = $request->route('id') ?? null;
        $validator = Validator::make($data, [
            'hospital_name' => 'required|string|max:255',
            'hospital_type' => 'required|in:private,public,teaching,specialist,general',
            'registration_number' => 'required|string|max:255',
            'hospital_phone' => 'required|string|max:255',
            'hospital_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('hospitals', 'email')->ignore($id)
            ],
            'logo_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'street_address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'google_maps_location' => 'nullable|string',
            'number_of_beds' => 'nullable|integer',
            'license_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'request_onsite_setup' => 'boolean',
            'delivery_available' => 'boolean',
            'terms_accepted' => 'boolean',
            'status' => 'in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function getById($key, $column = "id"): Hospital
    {
        $model = Hospital::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Hospital not found");
        }
        return $model;
    }

    public function createHospital(array $data): Hospital
    {
        $this->validate($data);

        $hospital = Hospital::create([
            'uuid' => Str::uuid(),
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['hospital_name'],
            'type' => $data['hospital_type'],
            'registration_number' => $data['registration_number'],
            'phone' => $data['hospital_phone'],
            'email' => $data['hospital_email'],
            'logo_path' => $this->handleFileUpload($data['logo'] ?? null, 'hospital-logos'),
            'street_address' => $data['street_address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'google_maps_location' => $data['google_maps_location'] ?? null,
            'number_of_beds' => $data['number_of_beds'] ?? null,
            'license_path' => $this->handleFileUpload($data['license'] ?? null, 'hospital-licenses'),
            'request_onsite_setup' => $data['request_onsite_setup'] ?? false,
            'terms_accepted' => $data['accept_terms'] ?? false,
            'status' => $data['status'] ?? 'pending',
        ]);

        $hospital_contact = $hospital->contacts()->create([
            'uuid' => Str::uuid(),
            'name' => $data['primary_contact_name'],
            'email' => $data['primary_contact_email'],
            'phone' => $data['primary_contact_phone'],
            'role' => $data['primary_contact_role'],
            'type' => 'primary'
        ]);

        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $user->hospital_contact_id = $hospital_contact->id;
                $user->role = $data['role'] ?? UserConstants::USER;
                $user->save();
            }
            if ($user && $user->hospitalUser) {
                $user->hospitalUser->hospital_id = $hospital->id;
                $user->hospitalUser->role = $data['role'] ?? UserConstants::ADMIN;
                $user->hospitalUser->save();
            }
        }

        if (isset($data['departments']) && is_array($data['departments'])) {
            $departmentIds = [];
            foreach ($data['departments'] as $deptName) {
                $department = Department::firstOrCreate(['name' => $deptName]);
                $departmentIds[] = $department->id;
            }
            $hospital->departments()->attach($departmentIds);
        }

        if (isset($data['services']) && is_array($data['services'])) {
            $serviceIds = [];
            foreach ($data['services'] as $serviceName) {
                $service = Service::firstOrCreate(['name' => $serviceName]);
                $serviceIds[] = $service->id;
            }
            $hospital->services()->attach($serviceIds);
        }

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
        return $hospital->load(['user', 'contacts', 'departments', 'services', 'operatingHours']);
    }


    public function updateHospital(array $data, $hospital_id): Hospital
    {
        return DB::transaction(function () use ($hospital_id, $data) {
            // $this->validate($data);

            // Update hospital basic info
            $hospital = $this->getById($hospital_id);
            $hospital->update([
                'name' => $data['hospital_name'] ?? $hospital->name,
                'type' => $data['hospital_type'] ?? $hospital->type,
                'registration_number' => $data['registration_number'] ?? $hospital->registration_number,
                'phone' => $data['hospital_phone'] ?? $hospital->phone,
                'email' => $data['hospital_email'] ?? $hospital->email,
                'street_address' => $data['street_address'] ?? $hospital->street_address,
                'city' => $data['city'] ?? $hospital->city,
                'state' => $data['state'] ?? $hospital->state,
                'country' => $data['country'] ?? $hospital->country,
                'google_maps_location' => $data['google_maps_location'] ?? $hospital->google_maps_location,
                'number_of_beds' => $data['number_of_beds'] ?? $hospital->number_of_beds,
                'request_onsite_setup' => $data['request_onsite_setup'] ?? $hospital->request_onsite_setup,
                'terms_accepted' => $data['accept_terms'] ?? $hospital->terms_accepted,
                'status' => $data['status'] ?? $hospital->status,
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

            // Update or create primary contact
            if (
                isset($data['primary_contact_name']) ||
                isset($data['primary_contact_phone']) ||
                isset($data['primary_contact_role']) ||
                isset($data['primary_contact_email'])
            ) {
                $primaryContact = $hospital->primaryContact;
                if ($primaryContact) {
                    $primaryContact->update([
                        'name' => $data['primary_contact_name'] ?? $primaryContact->name,
                        'phone' => $data['primary_contact_phone'] ?? $primaryContact->phone,
                        'role' => $data['primary_contact_role'] ?? $primaryContact->role,
                        'email' => $data['primary_contact_email'] ?? $primaryContact->email,
                    ]);
                } else {
                    $hospital_contact =  $hospital->contacts()->create([
                        'uuid' => Str::uuid(),
                        'name' => $data['primary_contact_name'] ?? '',
                        'phone' => $data['primary_contact_phone'] ?? '',
                        'role' => $data['primary_contact_role'] ?? '',
                        'email' => $data['primary_contact_email'] ?? '',
                        'type' => 'primary',
                    ]);
                }

                // Attach user as hospital owner if not already attached
                if (isset($data['user_id'])) {
                    $hospital->users()->firstOrCreate(
                        ['user_id' => $data['user_id']],
                        [
                            'hospital_id' => $hospital->id,
                            'role' => UserConstants::ADMIN,
                            'user_account_id' => $data['user_id'],
                        ]
                    );
                }

                // If user_id is provided, link the contact to the user
                if (isset($data['user_id']) && isset($hospital_contact)) {
                    $user = $hospital->user()->where('user_id', $data['user_id'])->first();
                    if ($user) {
                        $user->hospital_contact_id = $hospital_contact->id;
                        $user->save();
                    }
                }
            }

            // Sync departments (create if missing)
            if (isset($data['departments']) && is_array($data['departments'])) {
                $departmentIds = [];
                foreach ($data['departments'] as $deptName) {
                    $department = Department::firstOrCreate(['name' => $deptName]);
                    $departmentIds[] = $department->id;
                }
                $hospital->departments()->sync($departmentIds);
            }

            // Sync services (create if missing)
            if (isset($data['services']) && is_array($data['services'])) {
                $serviceIds = [];
                foreach ($data['services'] as $serviceName) {
                    $service = Service::firstOrCreate(['name' => $serviceName]);
                    $serviceIds[] = $service->id;
                }
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
                        'is_closed' => empty($hours['start']) && empty($hours['end']),
                    ]);
                }
            }

            return $hospital->load(['user', 'contacts', 'departments', 'services', 'operatingHours']);
        });
    }

    public function listHospitals(array $filters = []): Collection
    {
        $query = Hospital::query();

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        if (!empty($filters['type'])) {
            $type = strtolower($filters['type']);
            $query->whereRaw('LOWER(type) = ?', [$type]);
        }


        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        return $query->get();
    }

    public function getHospital(): ?Hospital
    {
        return Hospital::where('user_id', User::getAuthenticatedUser()->id)->firstOrFail();
    }

    public function getUserHospital(): ?Hospital
    {
        $user = User::getAuthenticatedUser();

        // User is hospital owner (admin)
        $hospital = Hospital::where('user_id', $user->id)->first();
        if ($hospital) {
            return $hospital;
        }

        // User is hospital staff (frontdesk/doctor/etc.)
        $hospitalUser = HospitalUser::where('user_id', $user->id)->with('hospital')->first();
        if ($hospitalUser && $hospitalUser->hospital) {
            return $hospitalUser->hospital;
        }

        return null;
    }



    public function approveHospital(Hospital $hospital): Hospital
    {
        $hospital->update(['status' => 'approved']);
        $this->sendLoginDetailsDuringhospitalRegistration($hospital->user->id);
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

    private function sendLoginDetailsDuringhospitalRegistration($user_id)
    {
        try {
            $user = User::findOrFail($user_id);
            $random_password = Str::random(10);
            $user->password = Hash::make($random_password);
            $user->save();
            Mail::to($user->email)->send(new SendUserLoginDetailsMail($user, $random_password));
            return $user->toArray();
        } catch (\Exception $e) {
            return ['error_message' => 'An error occurred while sending login details to user.'];
        }
    }
}
