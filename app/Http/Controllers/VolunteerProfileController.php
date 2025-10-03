<?php

namespace App\Http\Controllers;

use App\Models\VolunteerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class VolunteerProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if (!$user->isVolunteer()) {
            abort(403, 'Only volunteers can access this page!');
        }
        $profile = $user->volunteerProfile ?? VolunteerProfile::create(['user_id' => $user->user_id]);
        return view('volunteer.profile', compact('profile'));
    }

    public function edit()
    {
        $user = Auth::user();
        if (!$user->isVolunteer()) {
            abort(403, 'Only volunteers can access this page!');
        }

        $profile = $user->volunteerProfile ?? VolunteerProfile::create(['user_id' => $user->user_id]);
        return view('volunteer.edit-profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can update profile',
            ], 403);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'occupation' => 'nullable|string|max:100',
                'education_level' => 'nullable|in:High School,Diploma,Bachelor,Master,PhD',
                'university' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:1000',
                'skills' => 'nullable|string',
                'interests' => 'nullable|string',
                'availability' => 'nullable|in:Weekdays,Weekends,Flexible,Full-time',
                'volunteer_experience' => 'nullable|string',
                'preferred_location' => 'nullable|string|max:100',
                'transportation' => 'nullable|in:Motorbike,Car,Public Transport,Walking',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        try {
            $profile = $user->volunteerProfile;
            if (!$profile) {
                $profile = VolunteerProfile::create(['user_id' => $user->user_id]);
            }
            $profile->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ], 500);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Failed to update profile',
                    500
                ]
            );
        }
    }

    public function updateSkills(Request $request)
    {
        $user = Auth::user();

        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can update skills'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'skills' => 'required|array',
            'skills.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $profile = $user->volunteerProfile;
        $profile->update([
            'skills' => implode(',', $request->skills)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skills updated successfully'
        ]);
    }
    public function updateInterests(Request $request)
    {
        $user = Auth::user();

        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can update interests'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'interests' => 'required|array',
            'interests.*' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $profile = $user->volunteerProfile;
        $profile->update([
            'interests' => implode(',', $request->interests)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Interests updated successfully'
        ]);
    }

    /**
     * Update availability
     */
    public function updateAvailability(Request $request)
    {
        $user = Auth::user();

        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can update availability'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'availability' => 'required|in:Weekdays,Weekends,Flexible,Full-time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $profile = $user->volunteerProfile;
        $profile->update([
            'availability' => $request->availability
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully'
        ]);
    }

    /**
     * Get volunteer statistics
     */
    public function statistics()
    {
        $user = Auth::user();

        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can view statistics'
            ], 403);
        }

        $profile = $user->volunteerProfile;

        $stats = [
            'total_hours' => $profile->total_volunteer_hours,
            'rating' => $profile->volunteer_rating,
            'applications' => $user->applications()->count(),
            'accepted_applications' => $user->applications()->where('status', 'Accepted')->count(),
            'completed_activities' => $user->volunteerActivities()->where('status', 'Verified')->count(),
            'reviews_count' => $user->reviewsReceived()->where('is_approved', true)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get volunteer achievements
     */
    public function achievements()
    {
        $user = Auth::user();

        if (!$user->isVolunteer()) {
            return response()->json([
                'success' => false,
                'message' => 'Only volunteers can view achievements'
            ], 403);
        }

        $profile = $user->volunteerProfile;
        $achievements = [];

        // Bronze Badge - 10 hours
        if ($profile->total_volunteer_hours >= 10) {
            $achievements[] = [
                'name' => 'Bronze Volunteer',
                'description' => 'Completed 10 volunteer hours',
                'icon' => 'fas fa-medal',
                'color' => 'bronze'
            ];
        }

        // Silver Badge - 50 hours
        if ($profile->total_volunteer_hours >= 50) {
            $achievements[] = [
                'name' => 'Silver Volunteer',
                'description' => 'Completed 50 volunteer hours',
                'icon' => 'fas fa-medal',
                'color' => 'silver'
            ];
        }

        // Gold Badge - 100 hours
        if ($profile->total_volunteer_hours >= 100) {
            $achievements[] = [
                'name' => 'Gold Volunteer',
                'description' => 'Completed 100 volunteer hours',
                'icon' => 'fas fa-medal',
                'color' => 'gold'
            ];
        }

        // Top Rated
        if ($profile->volunteer_rating >= 4.5) {
            $achievements[] = [
                'name' => 'Top Rated Volunteer',
                'description' => 'Maintained 4.5+ rating',
                'icon' => 'fas fa-star',
                'color' => 'yellow'
            ];
        }

        return response()->json([
            'success' => true,
            'achievements' => $achievements,
            'total_hours' => $profile->total_volunteer_hours,
            'rating' => $profile->volunteer_rating
        ]);
    }
}
