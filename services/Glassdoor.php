<?php namespace HeathDutton\LogoStash\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class Glassdoor
 * @package HeathDutton\LogoStash\Services
 */
class Glassdoor
{

    private static $api_url = 'http://api.glassdoor.com/api/api.htm';

    private static $api_version = 1;

    private $partner_id;

    private $partner_key;

    /**
     * Glassdoor constructor.
     *
     * @param $partner_id
     * @param $partner_key
     */
    public function __construct($partner_id, $partner_key)
    {
        $this->partner_id = $partner_id;
        $this->partner_key = $partner_key;
    }

    /**
     * Given an employer name, get an employer overview from Glassdoor.
     *
     * @param $employer
     * @return mixed
     * @throws Exception
     */
    public function getEmployer($employer)
    {
        $result = null;
        $params = [
            'action' => 'employer-overview',
            'v' => self::$api_version,
            'format' => 'json',
            't.p' => $this->partner_id,
            't.k' => $this->partner_key,
            'employer' => $employer
        ];
        $client = new Client();
        $request = new Request('GET', self::$api_url . '?' . http_build_query($params));
        $response = $client->send($request);
        if ($response->getStatusCode() !== 200) {
            Log::error('Glassdoor API issue finding employer ' . $employer . ' ' . $response->getReasonPhrase() . ' ' . $response->getStatusCode());
        } else {
            $body = json_decode($response->getBody(), true);
            if (!$body || $body['status'] !== 'OK') {
                if ($body) {
                    $status = $body['status'];
                } else {
                    $status = $response->getReasonPhrase();
                }
                Log::error('Glassdoor could not find employer ' . $employer . ' ' . $status . ' ' . $response->getStatusCode());
            } else {
                $result = $body;
            }
        }
        return $result;
    }

    /**
     * Given an employer name, attempt to grab a logo from Glassdoor.
     *
     * @param $employer
     * @param bool $strict
     * @return null
     */
    public function getEmployerLogo($employer, $strict = false)
    {
        $result = null;
        $body = $this->getEmployer($employer);
        if (!empty($body['response']['squareLogo'])) {
            // Use the square photo if defined.
            $result = $body['response']['squareLogo'];
        } else {
            if (!$strict && !empty($body['response']['overviewPhoto'])) {
                // Fall back to cover photo if defined and there is no logo, strict is off.
                $result = $body['response']['squareLogo'];
            }
        }

        return $result;
    }
}