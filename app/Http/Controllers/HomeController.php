<?php

namespace App\Http\Controllers;

use App\Models\ChemotherapySession;
use App\Models\PatientAppointments;
use App\Models\PatientHealthReport;
use App\Models\PatientNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function patientHome()
    {

        $sessions = ChemotherapySession::query()
            ->where('user_id', Auth::id())
            ->whereDate('session_datetime', Carbon::today()->toDateString())->get();
        $appointments = PatientAppointments::query()
            ->where('user_id', Auth::id())
            ->whereDate('datetime', Carbon::today()->toDateString())->get();

        $unifiedResources = [];

        foreach ($sessions as $session) {
            $unifiedResources[] = [
                'type' => 'session',
                'id' => $session->id,
                'datetime' => $session->session_datetime,
                'title' => $session->session_number,
            ];
        }

        foreach ($appointments as $appointment) {
            $unifiedResources[] = [
                'type' => 'appointment',
                'id' => $appointment->id,
                'datetime' => $appointment->datetime,
                'title' => $appointment->doctor_name,
            ];
        }



        return $unifiedResources;
    }
}
