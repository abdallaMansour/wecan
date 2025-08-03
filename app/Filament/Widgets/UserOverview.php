<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class UserOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $users = User::all();

        // Filter users to get counts for patients with preferred language set to Arabic
        $arabicPatientsCount = $users->where('account_type', 'patient')
            ->where('preferred_language', 'ar')
            ->count();

        // Filter users to get counts for patients with preferred language set to English
        $englishPatientsCount = $users->where('account_type', 'patient')
            ->where('preferred_language', 'en')
            ->count();

        // Filter users to get counts for doctors with profession set in Arabic
        $arabicDoctorsCount = $users->where('account_type', 'doctor')
            ->where('preferred_language', 'ar')
            ->count();

        // Filter users to get counts for doctors with profession set in English
        $englishDoctorsCount = $users->where('account_type', 'doctor')
            ->where('preferred_language', 'en')
            ->count();

        return [
            Stat::make(__('dashboard.arabic_users_count'), $arabicPatientsCount),
            Stat::make(__('dashboard.english_users_count'),  $englishPatientsCount),
            Stat::make(__('dashboard.arabic_doctors_count'), $arabicDoctorsCount),
            Stat::make(__('dashboard.english_doctors_count'), $englishDoctorsCount),
        ];
    }
}
