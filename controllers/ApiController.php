<?php namespace HeathDutton\LogoStash\Controllers;

use Illuminate\Routing\Controller;
use HeathDutton\LogoStash\Classes\EmployerHelper;
use HeathDutton\LogoStash\Models\Settings;
use HeathDutton\LogoStash\Models\Logo;
use HeathDutton\LogoStash\Services\Glassdoor;
use Illuminate\Support\Facades\Redirect;

//use Illuminate\Support\Facades\Input;
//use Illuminate\Http\Request;
use Request;

class ApiController extends Controller
{

    public function employer() {

        // Get the employer from the URI to allow spaces in the employer name.
        $route = 'api/logostash/';
        $uri = Request::path();
        $employer = urldecode(preg_replace('/^' . preg_quote($route, '/') . '/', '', $uri));

        $logo_location = null;
        // Normalize the employer name.
        $employer = EmployerHelper::normalize($employer);
        if ($employer) {
            // Check if we already have this logo in the database.
            $logo = Logo::where('employer_name', $employer)
                ->where('status', 1)
                ->first();
            if (count($logo)) {
                // Logo entry exists, but may not have a location yet.
                if (!empty($logo->logo_location)) {
                    // Logo location is known, we can redirect to it.
                    return Redirect::to($logo->logo_location, 301);
                }
            } else {
                // No entry for this logo, let's create one.
                $logo = new Logo();
                $logo->employer_name = $employer;
                $logo->save();
            }
        }
        // Temporally redirect to the default logo.
        return Redirect::to('/plugins/heathdutton/logostash/assets/default.jpg', 302);
    }
}