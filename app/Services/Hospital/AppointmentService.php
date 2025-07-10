<?php

namespace App\Services\Hospital;

use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Models\Hospital\Appointment;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalUser;
use App\Notifications\SendAppointmentNotification;
use Illuminate\Http\Request;
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
                'exists:hospital_users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Check hospital_user role is doctor
                        $doctor = Doctor::find($value);
                        if (!$doctor || $doctor->role !== AppConstants::HOSPITAL_DOCTOR) {
                            $fail('The selected doctor does not have the correct role.');
                        }
                    }
                }
            ],
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
            'updated_at' => $appointment->updated_at->toDateTimeString(),
        ];
    }
}
