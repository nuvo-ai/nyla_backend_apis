<?php

namespace App\Services\User;

use App\Models\General\ModulePreference;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModulePreferenceService
{
    public function listPreferences()
    {
        return ModulePreference::all();
    }

    public function getUserPreferences(int $userId)
    {
        $user = User::with('modulePreferences')->findOrFail($userId);
        return $user->modulePreferences;
    }

    public function addPreferences(int $userId, array $preferenceIds)
    {
        $user = User::findOrFail($userId);

        // $existingPreferenceIds = $user->modulePreferences()->pluck('module_preferences.id')->toArray();
        // $newPreferenceIds = array_diff($preferenceIds, $existingPreferenceIds);

        // if (empty($newPreferenceIds)) {
        //     throw ValidationException::withMessages([
        //         'preference_ids' => ['The selected preferences are already assigned to the user.']
        //     ]);
        // }

        $preferences = ModulePreference::whereIn('id', $preferenceIds)->get();

        if ($preferences->isEmpty()) {
            throw new ValidationException("Invalid preference provided.");
        }

        DB::transaction(function () use ($user, $preferences) {
            $user->modulePreferences()->detach();
            foreach ($preferences as $preference) {
                $user->modulePreferences()->attach($preference->id);
            }
        });

        return $user->modulePreferences;
    }

    public function removePreference(int $userId, int $preferenceId)
    {
        $user = User::findOrFail($userId);
        $user->modulePreferences()->detach($preferenceId);

        return $user->modulePreferences;
    }
}
