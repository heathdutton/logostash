<?php namespace HeathDutton\LogoStash\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use HeathDutton\LogoStash\Classes\EmployerHelper;
use HeathDutton\LogoStash\Models\Settings;
use HeathDutton\LogoStash\Models\Logo;
use HeathDutton\LogoStash\Services\Glassdoor;

class LogoUpdate extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'heathdutton:logostash';
    protected $signature = 'heathdutton:logostash {--timeout=55} {--limit=}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Updates logos in the logo stash.';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {

        $timeout = intval($this->option('timeout'));
        $start_time = time();

        $auto_update_limit = intval(Settings::get('auto_update_limit', 20));
        $limit_option = $this->option('limit');
        if ($limit_option !== null) {
            $auto_update_limit = max(intval($limit_option), 10000);
        }

        // Connect to Glassdoor.
        $glassdoor_partner_id = Settings::get('glassdoor_partner_id');
        $glassdoor_partner_key = Settings::get('glassdoor_partner_key');
        if (!$glassdoor_partner_id || !$glassdoor_partner_key) {
            $this->error('Please insert Glassdoor credentials in the backend.');
            return;
        }
        $glassdoor = new Glassdoor($glassdoor_partner_id, $glassdoor_partner_key);

        $auto_update_attempt_limit = intval(Settings::get('auto_update_attempt_limit', 5));
        $logos = Logo::new($auto_update_limit, $auto_update_attempt_limit)
            ->get();
        $processed = [];
        if (!$logos) {
            $this->info('No new logos to find.');
        } else {
            $this->info('New logos to find: ' . count($logos));
            foreach ($logos as $logo) {
                // Normalize the employer name to reduce duplicates.
                $logo->employer_name = EmployerHelper::normalize($logo->employer_name);

                // Remove invalid employers.
                if (!$logo->employer_name) {
                    $logo->delete();
                    continue;
                }

                // Fetch a logo.
                $logo_location = $glassdoor->getEmployerLogo($logo->employer_name);
                if ($logo_location) {
                    $logo->logo_location = $logo_location;
                    $this->info('Found initial logo for "' . $logo->employer_name . '".');
                    // Reset attempts to 0 on success.
                    $logo->attempts = 0;
                } else {
                    $logo->attempts++;
                }
                $logo->updated_at = new Carbon;
                $logo->save();
                $processed[] = $logo->id;

                // Break if we are close to the nest scheduled cron run.
                if ($timeout && time() - $start_time > $timeout) {
                    $this->info('Stopping new logo search due to timeout.');
                    return;
                }
            }
        }

        if (count($processed) < $auto_update_limit) {
            $logos = Logo::old($auto_update_limit - count($processed), $auto_update_attempt_limit)
                ->whereNotIn('id', $processed)
                ->get();
            if (!$logos) {
                $this->info('No old logos to update.');
                return;
            } else {
                $this->info('Old logos to update: ' . count($logos));
                foreach ($logos as $logo) {
                    // Normalize the employer name to reduce duplicates.
                    $logo->employer_name = EmployerHelper::normalize($logo->employer_name);

                    // Remove invalid employers.
                    if (!$logo->employer_name) {
                        $logo->delete();
                        continue;
                    }

                    // Fetch a logo.
                    $logo_location = $glassdoor->getEmployerLogo($logo->employer_name);
                    if ($logo_location && $logo->logo_location != $logo_location) {
                        $logo->logo_location = $logo_location;
                        $this->info('Found updated logo for "' . $logo->employer_name . '".');
                        // Reset attempts to 0 on success.
                        $logo->attempts = 0;
                    } elseif (!$logo_location) {
                        $logo->attempts++;
                    }
                    $logo->updated_at = new Carbon;
                    $logo->save();
                    $processed[] = $logo->id;

                    // Break if we are close to the nest scheduled cron run.
                    if ($timeout && time() - $start_time > $timeout) {
                        $this->info('Stopping old logo update due to timeout.');
                        return;
                    }
                }
            }
        }
    }
}