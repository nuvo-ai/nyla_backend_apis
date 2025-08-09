<?php

namespace App\Services\Hospital;

use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Constants\User\UserConstants;
use App\Http\Resources\User\UserResource;
use App\Models\Hospital\Appointment;
use App\Models\Hospital\Doctor;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalUser;
use App\Notifications\SendAppointmentNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'hospital_id'       => ['required', 'exists:hospitals,id'],
            'scheduler_id'       => ['required', 'exists:users,id'],
            'doctor_id'         => [
                'nullable',
                'exists:doctors,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        if (!Doctor::find($value)) {
                            $fail('The selected doctor does not exist.');
                        }
                    }
                }
            ],
            'title'             => ['nullable', 'string', 'max:255'],
            'patient_name'      => ['required', 'string', 'max:255'],
            'appointment_type'  => ['required', 'string', 'max:100'],
            'appointment_date'  => ['required', 'date', 'after_or_equal:today'],
            'appointment_time'  => ['required', 'date_format:H:i'], // 24-hour format, e.g., 14:30
            'note'              => ['nullable', 'string'],
            'source'            => ['required', 'string'],
            'status'            => ['nullable', 'in:' . implode(',', StatusConstants::SCHEDULE_STATUSES)],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function book(array $data): HospitalAppointment
    {
        return DB::transaction(function () use ($data) {
            $validatedData = $this->validate($data);

            if (HospitalAppointment::hasConflict($validatedData, true)) {
                throw ValidationException::withMessages([
                    'duplicate' => ['You already have a pending appointment for this date and time.'],
                ]);
            }

            if (HospitalAppointment::hasConflict($validatedData)) {
                throw ValidationException::withMessages([
                    'conflict' => ['This time slot is already taken. Please choose a different time.'],
                ]);
            }


            $appointment = HospitalAppointment::create([
                'hospital_id'       => $validatedData['hospital_id'],
                'scheduler_id'      => $validatedData['scheduler_id'] ?? auth()->user()->id,
                'doctor_id'         => $validatedData['doctor_id'] ?? null,
                'title'             => $validatedData['title'] ?? null,
                'patient_name'      => $validatedData['patient_name'],
                'appointment_type'  => $validatedData['appointment_type'],
                'appointment_date'  => $validatedData['appointment_date'],
                'appointment_time'  => $validatedData['appointment_time'],
                'note'              => $validatedData['note'] ?? null,
                'source'            => $validatedData['source'],
                'status'            => $validatedData['status'] ?? StatusConstants::PENDING,
            ]);

            $appointment->load(['hospital', 'doctor', 'scheduler']);

            $doctorUser = optional($appointment->doctor)->user;
            $schedulerUser = auth()->user();

            if ($doctorUser) {
                Notification::send($doctorUser, new SendAppointmentNotification($appointment, $doctorUser));
            }

            if ($schedulerUser) {
                Notification::send($schedulerUser, new SendAppointmentNotification($appointment, $schedulerUser));
            }

            return $appointment;
        });
    }

    public function update(array $data, $id): HospitalAppointment
    {
        return DB::transaction(function () use ($data, $id) {
            $appointment = HospitalAppointment::findOrFail($id);

            $validatedData = $this->validate(array_merge($appointment->toArray(), $data));

            $oldDate = $appointment->appointment_date;
            $oldTime = $appointment->appointment_time;

            // Check for conflicts if date/time is being changed
            if (
                (isset($validatedData['appointment_date']) && $validatedData['appointment_date'] != $oldDate) ||
                (isset($validatedData['appointment_time']) && $validatedData['appointment_time'] != $oldTime)
            ) {
                if (HospitalAppointment::hasConflict($validatedData, true, $id)) {
                    throw ValidationException::withMessages([
                        'duplicate' => ['You already have a pending appointment for this date and time.'],
                    ]);
                }
                if (HospitalAppointment::hasConflict($validatedData, false, $id)) {
                    throw ValidationException::withMessages([
                        'conflict' => ['This time slot is already taken. Please choose a different time.'],
                    ]);
                }
            }

            $appointment->update([
                'title'             => $validatedData['title'] ?? $appointment->title,
                'appointment_date'  => $validatedData['appointment_date'],
                'appointment_time'  => $validatedData['appointment_time'],
                'patient_name'      => $validatedData['patient_name'],
                'hospital_id'       => $validatedData['hospital_id'],
                'doctor_id'         => $validatedData['doctor_id'] ?? null,
                'appointment_type'  => $validatedData['appointment_type'],
                'note'              => $validatedData['note'] ?? null,
                'source'            => $validatedData['source'],
                'status'            => $validatedData['status'] ?? StatusConstants::PENDING,
            ]);

            $appointment->load(['doctor', 'scheduler']);

            if (
                ($oldDate != $appointment->appointment_date) ||
                ($oldTime != $appointment->appointment_time)
            ) {
                $doctorUser = optional($appointment->doctor)->user;
                $schedulerUser = $appointment->scheduler;

                if ($doctorUser) {
                    Notification::send($doctorUser, new SendAppointmentNotification($appointment, $doctorUser, true));
                }
                if ($schedulerUser) {
                    Notification::send($schedulerUser, new SendAppointmentNotification($appointment, $schedulerUser, true));
                }
            }

            return $appointment;
        });
    }

    public function updateStatus(Request $request, int $appointment_id)
    {
        $status = $request->status;
        if (!in_array($status, StatusConstants::SCHEDULE_STATUSES)) {
            throw ValidationException::withMessages([
                'status' => ['Invalid status provided.']
            ]);
        }
        $appointment = HospitalAppointment::findOrFail($appointment_id);
        $appointment->status = $status;
        $appointment->save();
        $appointment->load(['hospital', 'doctor', 'scheduler']);
        return [
            'id' => $appointment->id,
            'status' => $appointment->status,
            'patient_name' => $appointment->patient_name,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time->format('H:i'),
            'scheduler' => new UserResource($appointment->scheduler),
            'updated_at' => $appointment->updated_at->toDateTimeString(),
        ];
    }

    public function delete($id): bool
    {
        $appointment = HospitalAppointment::findOrFail($id);
        $userRole = auth()->user()->role ?? null;
        if ($appointment->scheduler_id !== auth()->id() && $userRole !== UserConstants::ADMIN) {
            throw new ModelNotFoundException("You do not have permission to delete this appointment.");
        }
        return $appointment->delete();
    }
    public function listAppointments(array $filters = []): Collection
    {
        $query = HospitalAppointment::where('scheduler_id', auth()->id())->with(['hospital', 'doctor', 'scheduler']);

        if (!empty($filters['period'])) {
            switch ($filters['period']) {
                case 'today':
                    $query->whereDate('appointment_date', now()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('appointment_date', [
                        now()->startOfWeek()->toDateString(),
                        now()->endOfWeek()->toDateString()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('appointment_date', now()->month)
                        ->whereYear('appointment_date', now()->year);
                    break;
                case 'all':
                default:
                    break;
            }
        }

        return $query->get();
    }

    public function getAppointment($id): HospitalAppointment
    {
        $appointment = HospitalAppointment::where('scheduler_id', auth()->id())->with(['hospital', 'doctor', 'scheduler'])->find($id);
        if (!$appointment) {
            throw new ModelNotFoundException("Appointment not found");
        }
        return $appointment;
    }

    public function getDoctorAppointments($doctorId): Collection
    {
        return HospitalAppointment::with(['hospital', 'doctor', 'scheduler'])
            ->where('doctor_id', $doctorId)
            ->get();
    }
}
