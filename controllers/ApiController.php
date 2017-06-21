<?php namespace HeathDutton\LogoStash\Controllers;

use Illuminate\Routing\Controller;
use HeathDutton\LogoStash\Models\Settings;
use HeathDutton\LogoStash\Models\Logo;
use Illuminate\Support\Facades\Redirect;
use Request;

/**
 * Class ApiController
 * @package HeathDutton\LogoStash\Controllers
 */
class ApiController extends Controller
{

    /**
     * Given an employer, redirect to a logo if known,
     * otherwise log the employer for logo lookup.
     *
     * @return mixed
     */
    public function employer()
    {
        $default_logo_location = '/plugins/heathdutton/logostash/assets/default.jpg';
        $logo_location = $default_logo_location;
        if (Settings::get('api', 1)) {

            // Get the employer from the URI to allow spaces in the employer name.
            $route = 'api/logostash/';
            $uri = Request::path();
            $employer = urldecode(preg_replace('/^' . preg_quote($route, '/') . '/', '', $uri));

            $logo = new Logo();
            $logo_location = $logo->getLocation($employer, $logo_location);
        }
        return Redirect::to($logo_location, $logo_location === $default_logo_location ? 302 : 301);
    }
}