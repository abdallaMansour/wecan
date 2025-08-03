<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use App\Models\PatientMedications;
use App\Models\PatientHealthReport;
use App\Models\PatientNote;
use App\Models\CancerScreeningCenter;
use App\Models\HealthTip; // Add this line
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class FavoriteController extends Controller
{
public function toggleFavorite(Request $request)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'favorable_id' => 'required|integer', // ID of the item to be favorited/unfavorited
        'favorable_type' => 'required|string|in:User,PatientMedications,PatientHealthReport,PatientNote,CancerScreeningCenter,HealthTip', // Type of item
    ]);

    // Build the favorable type class dynamically
    $favorableType = "App\\Models\\" . $validatedData['favorable_type'];
    // Find the item being favorited/unfavorited
    $favorable = $favorableType::findOrFail($validatedData['favorable_id']);

    // Get the authenticated user ID
    $userId = auth()->id();

    // Find if the favorite already exists for this user and item
    $favorite = Favorite::where('user_id', $userId)
        ->where('favorable_id', $validatedData['favorable_id'])
        ->where('favorable_type', $favorableType)
        ->first();

    // Toggle favorite
    if ($favorite) {
        // If the favorite exists, we unfavorite the item
        $favorite->delete();
        $message = 'Item removed from favorites';
        $isFavorited = false;
    } else {
        // Otherwise, we add it to the favorites
        Favorite::create([
            'user_id' => $userId,
            'favorable_id' => $validatedData['favorable_id'],
            'favorable_type' => $favorableType,
        ]);
        $message = 'Item added to favorites';
        $isFavorited = true;
    }

    // Return the success response
    return ResponseHelper::success($message, [
        'is_favorited' => $isFavorited,
        'favorable_id' => $favorable->id,
        'favorable_type' => class_basename($favorable)
    ]);
}




    public function getFavorites(Request $request)
    {
        $favorites = auth()->user()->favorites()->with('favorable')->get();
       
        $groupedFavorites = $favorites->groupBy(function ($favorite) {
            return class_basename($favorite->favorable_type);
        })->map(function ($group) {
            return $group->map(function ($favorite) {
                $favorable = $favorite->favorable;
                if ($favorable instanceof HealthTip) {
                    // Convert 'visible' to boolean
                    $favorable->visible = (bool) $favorable->visible;
                    // Remove 'is_favorited' attribute
                    $favorable->makeHidden('is_favorited');
                }
                return $favorable;
            });
        })->mapWithKeys(function ($value, $key) {
            $newKey = $this->getReadableKey($key);
            return [$newKey => $value];
        });

        return ResponseHelper::success('Favorites retrieved successfully', $groupedFavorites);
    }

    private function getReadableKey($key)
    {
        switch ($key) {
            case 'User':
                return 'users';
            case 'PatientMedications':
                return 'patient_medications';
            case 'PatientHealthReport':
                return 'patient_health_reports';
            case 'PatientNote':
                return 'patient_notes';
            case 'CancerScreeningCenter':
                return 'cancer_screening_centers';
            case 'HealthTip':
                return 'health_tips';
            default:
                return strtolower(str_plural($key));
        }
    }
}