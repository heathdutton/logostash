<?php namespace HeathDutton\LogoStash\Classes;

class EmployerHelper
{

    /**
     * Normalize an employer name to reduce duplicates and aid in searches.
     *
     * @param $employer
     * @return string
     */
    public static function normalize($employer)
    {
        // Strip HTML, because some of these employers are trying to be sneaky.
        $employer = strip_tags($employer);

        // Convert to basic Latin characters.
        $employer = mb_convert_encoding($employer, 'ISO-8859-1');

        // Replace non alphanumeric characters and partials with spaces.
        $employer = str_replace(array(', inc', ', llc', '.com', '.net', '.org', '.co', 'www.', 'get.'), ' ', $employer);
        $employer = preg_replace("/[^A-Za-z0-9 \-&@.]/", ' ', $employer);

        // Lowercase.
        $employer = strtolower($employer);

        // Remove job-site segments (wherever the term job/career is used) since this is indicative of second party.
        $segments = explode(' ', $employer);
        foreach ($segments as $key => $segment) {
            if ($segment) {
                if (strpos($segment, 'job')) {
                    unset($segments[$key]);
                } elseif (strpos($segment, 'career')) {
                    unset($segments[$key]);
                }
            }
        }
        $employer = implode(' ', $segments);

        // Truncate extra whitespace.
        $employer = trim(preg_replace('/\s+/', ' ', $employer));

        // Shorten to 255 character maximum (DB constraint).
        $employer = substr($employer, 0, 255);
        return $employer;
    }

}