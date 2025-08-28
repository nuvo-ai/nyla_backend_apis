<?php

namespace App\Services\Pharmacy;

use App\Constants\General\AppConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Mail\SendUserLoginDetailsMail;
use App\Models\General\Service;
use App\Models\Pharmacy\Pharmacy;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Pharmacy\PharmacyActivityService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PharmacyService
{
    public function validate(array $data): array
    {
        $request = request();
        $id = $request->route('id') ?? null;
        $validator = Validator::make($data, [
            'pharmacy_name' => 'required|string|max:255',
            'pharmacy_license_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pharmacies', 'license_number')->ignore($id)
            ],
            'pharmacist_in_charge_name' => 'required',
            'pharmacy_phone' => 'required|string|max:255',
            'pharmacy_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pharmacies', 'email')->ignore($id)
            ],
            'logo_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'street_address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'google_maps_location' => 'nullable|string',
            // 'pharmacy_license_number' => 'required|string|max:255',
            'request_onsite_setup' => 'boolean',
            'delivery_available' => 'boolean',
            'nafdac_certificate' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'accept_terms' => 'boolean',
            'status' => 'in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function getById($key, $column = "id"): Pharmacy
    {
        $model = Pharmacy::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Pharmacy not found");
        }
        return $model;
    }

    public function createPharmacy(array $data): Pharmacy
    {
        $this->validate($data);
        // Create Pharmacy
        $pharmacy = Pharmacy::create([
            'uuid' => Str::uuid(),
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['pharmacy_name'],
            'phone' => $data['pharmacy_phone'],
            'email' => $data['pharmacy_email'],
            'license_number' => $data['pharmacy_license_number'],
            'pharmacist_in_charge_name' => $data['pharmacist_in_charge_name'],
            'logo_path' => $this->handleFileUpload($data['logo_path'] ?? null, 'pharmacy-logos'),
            'street_address' => $data['street_address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'google_maps_location' => $data['google_maps_location'] ?? null,
            'request_onsite_setup' => $data['request_onsite_setup'] ?? false,
            'terms_accepted' => $data['accept_terms'] ?? false,
            'nafdac_certificate' => $this->handleFileUpload($data['nafdac_certificate'] ?? null, 'pharmacy-nafdac-certificates'),
            'delivery_available' => $data['delivery_available'] ?? false,
            'status' => $data['status'] ?? 'pending',
        ]);

        // Create primary contact
        $pharmacy_contact = $pharmacy->contacts()->create([
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
                $user->pharmacy_contact_id = $pharmacy_contact->id;
                $user->save();
            }
        }

        $data['pharmacy_contact_id'] = $pharmacy_contact->where('type', 'primary')->first()->id;
        $pharmacy->save();

        // Attach services
        if (isset($data['services']) && is_array($data['services'])) {
            $serviceIds = [];
            foreach ($data['services'] as $serviceName) {
                $service = Service::firstOrCreate(['name' => $serviceName]);
                $serviceIds[] = $service->id;
            }
            $pharmacy->services()->attach($serviceIds);
        }

        // Create operating hours
        if (isset($data['operating_hours']) && is_array($data['operating_hours'])) {
            foreach ($data['operating_hours'] as $day => $hours) {
                $pharmacy->operatingHours()->create([
                    'day_of_week' => $day,
                    'start_time' => $hours['start'] ?? null,
                    'end_time' => $hours['end'] ?? null,
                    'is_closed' => empty($hours['start']) && empty($hours['end'])
                ]);
            }
        }

        return $pharmacy->load(['user', 'contacts', 'services', 'operatingHours']);
    }

    public function updatePharmacy(array $data, $pharmacy_id): Pharmacy
    {
        return DB::transaction(function () use ($pharmacy_id, $data) {
            $this->validate($data);

            // Update Pharmacy basic info
            $pharmacy = $this->getById($pharmacy_id);
            $pharmacy->update([
                'name' => $data['pharmacy_name'] ?? $pharmacy->name,
                'license_number' => $data['pharmacy_license_number'] ?? $pharmacy->license_number,
                'pharmacist_in_charge_name' => $data['pharmacist_in_charge_name'] ?? $pharmacy->pharmacist_in_charge_name,
                'phone' => $data['pharmacy_phone'] ?? $pharmacy->phone,
                'email' => $data['pharmacy_email'] ?? $pharmacy->email,
                'street_address' => $data['street_address'] ?? $pharmacy->street_address,
                'city' => $data['city'] ?? $pharmacy->city,
                'state' => $data['state'] ?? $pharmacy->state,
                'country' => $data['country'] ?? $pharmacy->country,
                'google_maps_location' => $data['google_maps_location'] ?? $pharmacy->google_maps_location,
                'request_onsite_setup' => $data['request_onsite_setup'] ?? $pharmacy->request_onsite_setup,
                'terms_accepted' => $data['accept_terms'] ?? $pharmacy->terms_accepted,
                'delivery_available' => $data['delivery_available'] ?? $pharmacy->delivery_available,
                'logo_path' => $this->handleFileUpload($data['logo_path'] ?? null, 'Pharmacy-logos') ?? $pharmacy->logo_path,
                'nafdac_certificate' => $this->handleFileUpload($data['nafdac_certificate'] ?? null, 'pharmacy-nafdac-certificates') ?? $pharmacy->nafdac_certificate,
                'status' => $data['status'] ?? $pharmacy->status,
            ]);


            // Handle file uploads
            if (isset($data['logo'])) {
                if ($pharmacy->logo_path) {
                    Storage::delete($pharmacy->logo_path);
                }
                $pharmacy->logo_path = $this->handleFileUpload($data['logo_path'], 'pharmacy-logos');
                $pharmacy->save();
            }

            if (isset($data['nafdac_certificate'])) {
                if ($pharmacy->license_path) {
                    Storage::delete($pharmacy->nafdac_certificate);
                }
                $pharmacy->nafdac_certificate = $this->handleFileUpload($data['nafdac_certificate'], 'pharmacy-lnafdac-certificates');
                $pharmacy->save();
            }

            // Update or create primary contact
            if (
                isset($data['primary_contact_name']) ||
                isset($data['primary_contact_phone']) ||
                isset($data['primary_contact_role']) ||
                isset($data['primary_contact_email'])
            ) {
                $primaryContact = $pharmacy->primaryContact;
                if ($primaryContact) {
                    $primaryContact->update([
                        'name' => $data['primary_contact_name'] ?? $primaryContact->name,
                        'phone' => $data['primary_contact_phone'] ?? $primaryContact->phone,
                        'role' => $data['primary_contact_role'] ?? $primaryContact->role,
                        'email' => $data['primary_contact_email'] ?? $primaryContact->email,
                    ]);
                } else {
                    $pharmacy_contact =  $pharmacy->contacts()->create([
                        'uuid' => Str::uuid(),
                        'name' => $data['primary_contact_name'] ?? '',
                        'phone' => $data['primary_contact_phone'] ?? '',
                        'role' => $data['primary_contact_role'] ?? '',
                        'email' => $data['primary_contact_email'] ?? '',
                        'type' => 'primary',
                    ]);
                }
                if (isset($data['user_id']) && isset($pharmacy_contact)) {
                    $user = $pharmacy->user()->where('user_id', $data['user_id'])->first();
                    if ($user) {
                        $user->Pharmacy_contact_id = $pharmacy_contact->id;
                        $user->save();
                    }
                }
            }


            // Sync services (create if missing)
            if (isset($data['services']) && is_array($data['services'])) {
                $serviceIds = [];
                foreach ($data['services'] as $serviceName) {
                    $service = Service::firstOrCreate(['name' => $serviceName]);
                    $serviceIds[] = $service->id;
                }
                $pharmacy->services()->sync($serviceIds);
            }

            // Update operating hours
            if (isset($data['operating_hours']) && is_array($data['operating_hours'])) {
                $pharmacy->operatingHours()->delete();
                foreach ($data['operating_hours'] as $day => $hours) {
                    $pharmacy->operatingHours()->create([
                        'day_of_week' => $day,
                        'start_time' => $hours['start'] ?? null,
                        'end_time' => $hours['end'] ?? null,
                        'is_closed' => empty($hours['start']) && empty($hours['end']),
                    ]);
                }
            }

            // Attach user as Pharmacy owner if not already attached
            if (isset($data['user_id'])) {
                $pharmacy->users()->firstOrCreate(
                    ['user_id' => $data['user_id']],
                    [
                        'Pharmacy_id' => $pharmacy->id,
                        'role' => AppConstants::ROLE_PHARMACY_OWNER,
                        'user_account_id' => $data['user_id'],
                    ]
                );
            }

            // Log activity: Pharmacy profile updated
            $userId = $data['updated_by'] ?? auth()->id() ?? $pharmacy->user_id;
            PharmacyActivityService::log(
                $pharmacy->id,
                $userId,
                'Pharmacy profile updated',
                ['pharmacy_id' => $pharmacy->id]
            );

            return $pharmacy->load(['user', 'contacts', 'services', 'operatingHours']);
        });
    }

    public function listPharmacy(array $filters = []): Collection
    {
        $query = Pharmacy::query();

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        return $query->get();
    }

    public function getPharmacy(string $uuid): ?Pharmacy
    {
        return Pharmacy::where('uuid', $uuid)->firstOrFail();
    }

    public function approvePharmacy(Pharmacy $pharmacy): Pharmacy
    {
        $pharmacy->update(['status' => 'approved']);
        $this->sendLoginDetailsDuringPharmacyRegistration($pharmacy->user->id);
        return $pharmacy;
    }

    public function rejectPharmacy(Pharmacy $pharmacy): Pharmacy
    {
        $pharmacy->update(['status' => 'rejected']);
        return $pharmacy;
    }

    public function toggleActive($pharmacy_id)
    {
        $pharmacy = Pharmacy::findOrFail($pharmacy_id);
        $pharmacy->is_active = !$pharmacy->is_active;
        $pharmacy->save();
        // Log activity: Pharmacy active status toggled
        $userId = auth()->id() ?? $pharmacy->user_id;
        $action = $pharmacy->is_active ? 'Pharmacy activated' : 'Pharmacy deactivated';
        PharmacyActivityService::log(
            $pharmacy->id,
            $userId,
            $action,
            ['pharmacy_id' => $pharmacy->id, 'is_active' => $pharmacy->is_active]
        );
        return $pharmacy;
    }

    private function handleFileUpload(?UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store($directory, 'public');
    }

    private function sendLoginDetailsDuringPharmacyRegistration($user_id)
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
