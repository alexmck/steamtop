<?php

namespace App\Http\Controllers;

use App\Charts\DailySpeed;
use App\Model\Speed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function index($country = 'AUS', $days = 30)
    {
        $allowed_days = ['7', '30', '90', '180', '360'];
        $countries = DB::table('asns')
            ->select('country')
            ->groupby('country')
            ->orderby('country', 'asc')
            ->get();

        if ( ! in_array($days, $allowed_days)) {
            $days = 30;
        }

        $selected_country = strtoupper($country);
        $selected_days = $days;

        $speed_data = Speed::recent($days)
            ->country($country)
            ->orderBy('asns.name', 'asc')
            ->orderBy('speeds.created_at', 'asc')->get();

        $asns = $speed_data->pluck('name', 'asn_id')->unique()->all();
        $colors = $speed_data->pluck('color', 'asn_id')->unique()->all();

        $dates = $speed_data->pluck('date')->unique()->transform(function($date, $key) {
            return Carbon::parse($date)->format('D dS M Y'); //Y-m-d
        })->all();

        $chart = new DailySpeed();
        $chart->labels($dates);
        $chart->options(['tooltips' => [
            'mode' => 'x',
            'displayColors' => true,
        ],
            'legendPosition' => 'right',
            'labels' => [
                'padding' => 40,
            ]
        ]);

        foreach ($asns as $k => $asn) {
            $asn_speed_data = $speed_data->where('asn_id', $k);

            $line_data = $asn_speed_data->pluck('average_speed')->all();

            $chart->dataset($asn, 'line', $line_data)->options([
                'color' => $colors[$k],
                'backgroundColor' => $colors[$k],
                'fill' => false,
                'borderWidth' => 4,
            ])->color($colors[$k]);

            //dd($colors[$k]);
        }

        //return view('index', compact('chart'));
        return view('index', compact('chart', 'countries', 'allowed_days', 'selected_country', 'selected_days'));
    }
}
