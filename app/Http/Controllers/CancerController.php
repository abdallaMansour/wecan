<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\CancerResource;
use App\Models\Cancer;

class CancerController extends Controller
{
    public function index()
    {
        $cancers = Cancer::where('visible', true)->get();
        return ResponseHelper::success('Cancers retrieved successfully', CancerResource::collection($cancers));
    }
}
