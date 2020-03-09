<?php

namespace App\Console\Commands;

use App\Model\Speed;
use App\Models\Asn;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import legacy Steamtop.com.au data';

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
        $data_location = storage_path('stockpile');

        if ( ! file_exists($data_location)) {
            $this->error("No stockpile folder.");
        }

        $data_files = glob($data_location . '/*.{txt}', GLOB_BRACE);

        $bar = $this->output->createProgressBar(count($data_files));
        $bar->start();

        foreach ($data_files as $file) {

            $handle = fopen($file, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    // process the line read.
                    $data = explode(',', $line);
                    $date = $this->getDateString(basename($file));

                    $asn = Asn::whereName(str_replace('_', ' ', $data[0]))->first();

                    if (empty($asn)) {
                        $asn = new Asn([
                            'name' => str_replace('_', ' ', $data[0]),
                            'country' => 'AUS',
                            'color' => '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        $asn->save();
                    }

                    $speed = new Speed([
                        'asn_id' => $asn->id,
                        'average_speed' => $data[1],
                        'data' => null,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $speed->save();

                    /*
                     * DB::table('ans')->insertOrIgnore([
                        ['name' => $data[0], 'country' => 'AUS'],
                    ]);
                     */

                }

                fclose($handle);
            } else {
                $this->error("Error opening {$file}.");
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info("Finished importing data.");
    }

    protected function getDateString($filename)
    {
        $string = str_replace('.txt', '', $filename);

        $string_array = explode('-', $string);

        return Carbon::parse($string_array[2] . '-' . $string_array[0] . '-' . $string_array[1])->format('Y-m-d');
    }
}
