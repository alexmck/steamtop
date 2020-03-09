<?php

namespace App\Console\Commands;

use App\Model\Speed;
use App\Models\Asn;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy';

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

        $max_date = null;
        $choice = 3;

        $max_id = DB::table('speeds')->max('id');

        // Check speeds table is empty
        if ($max_id > 0) {

            $answer = $this->choice('There are existing speeds. What would you like to do? 🤔', [1 => 'Continue the import', 2 => 'Start a fresh import', 3 => 'Quit']);

            switch ($answer) {
                case 'Continue the import':
                    $choice = 1;
                    $max_date = DB::table('speeds')->max('created_at');
                    break;
                case 'Start a fresh import':
                    $choice = 2;

                    if (! $this->confirm('Are you sure you want to start a new import?')) {
                        $choice = 3;
                    }

                    break;
                case 'Quit':
                    $choice = 3;
            }
        }

        // Quit if requested
        if ($choice == 3) {
            $this->exit();
        }

        // Empty speeds table if requested
        if ($choice == 2) {
            $this->info("Clearing speeds table. 🗑");
            DB::table("speeds")->truncate();
        }

        $data_files = glob($data_location . '/*.{txt}', GLOB_BRACE);

        $data_files = $this->filterDataFiles($data_files, $max_date);

        $bar = $this->output->createProgressBar(count($data_files));
        $bar->start();

        foreach ($data_files as $file) {

            $handle = fopen($file, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {

                    $data = explode(',', $line);
                    $date = $this->getDateString($file);

                    $asn = Asn::whereName($this->getRealName($data[0]))->first();

                    if (empty($asn)) {
                        $asn = new Asn([
                            'name' => $this->getRealName($data[0]),
                            'country' => 'AUS',
                            'color' => $this->getColor($data[0]),
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

                }

                fclose($handle);
            } else {
                $this->error("Error opening {$file}. ☹️");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);
        $this->info("Finished importing data. 🥳");
    }

    protected function getRealName($name) {
        return str_replace('_', ' ', $name);
    }

    protected function getDateString($filename)
    {
        $string = str_replace('.txt', '', basename($filename));

        $string_array = explode('-', $string);

        return Carbon::parse($string_array[2] . '-' . $string_array[0] . '-' . $string_array[1])->format('Y-m-d');
    }

    protected function getColor($name) {

        $name = $this->getRealName($name);

        $colors = [
            'Aussie Broadband' => '#5ea429',
            'Dodo' => '#33034F',
            'Exetel' => '#009f4d',
            'iiNet Limited' => '#ff8200',
            'iPrimus' => '#35cc99',
            'MyRepublic' => '#9d3192',
            'Optus' => '#fdcc08',
            'Telstra Internet' => '#2c74d3',
            'TPG Internet' => '#561666',
            'Vodafone Australia' => '#E60000',
        ];

        if (array_key_exists($name, $colors)) {
            return $colors[$name];
        } else {
            return '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
        }
    }

    protected function filterDataFiles($files, $max_date) {

        if (is_null($max_date)) {
            return $files;
        }

        $files_array = [];
        foreach($files as $file) {
            if (Carbon::parse($this->getDateString($file))->gt(Carbon::parse($max_date))) {
                $files_array[] = $file;
            }
        }

        if (count($files_array) > 0) {
            return $files_array;
        }

        $this->info("No recent files to import.");
        $this->exit();
    }

    protected function exit() {
        $this->info("Bye! 👋");
        exit();
    }
}
