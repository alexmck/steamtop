<?php

namespace App\Console\Commands;

use App\Model\Speed;
use App\Models\Asn;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import daily data directly from Steam';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $date = Carbon::parse(Carbon::now())->format('m-d-Y'); // Month-Day-Year

        $response = Http::get('https://steamcdn-a.akamaihd.net/steam/publicstats/top_asns_per_country.jsonp?v=' . md5($date));

        $body = substr(str_replace('jsonpFetch.onCountryASNData(', '', $response->body()), 0, -2);

        $data = json_decode($body);

        foreach($data as $country => $providers) {
            foreach($providers as $provider) {

                if (empty($provider->asname)) {
                    continue;
                }

                $asn = Asn::whereName($provider->asname)->whereCountry($country)->first();

                if (empty($asn)) {
                    $asn = new Asn([
                        'name' => $provider->asname,
                        'country' => $country,
                        'color' => $this->getColor(),
                        'created_at' => $date,
                        'updated_at' => $date
                    ]);

                    $asn->save();
                }

                $speed = new Speed([
                    'asn_id' => $asn->id,
                    'average_speed' => $provider->avgmbps,
                    'data' => $this->getGigabytes($provider->totalbytes),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $speed->save();
            }
        }

    }

    protected function getColor() {
        return '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
    }

    protected function getGigabytes($bytes) {
        return $bytes / 1000 / 1000 / 1000;
    }
}
