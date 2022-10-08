<?php

namespace App\Http\Controllers\Admin;
use App\Models\Generalsetting;
use Artisan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;

class CacheController extends Controller
{

    public function clear()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        return redirect()->route('admin.dashboard')->with('cache', 'System Cache Has Been Removed.');
        
    }

}
