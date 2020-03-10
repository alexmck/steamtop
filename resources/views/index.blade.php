@extends('app')

@section('title', 'SteamTop - ' . $selected_country . ' - ' . $selected_days . ' days')

@section('content')

    @if(isset($countries))
        <select name="country" id="country">
        @foreach($countries as $country)
            <option value="{{ $country->country }}"@if($country->country == $selected_country) selected @endif>{{ $country->country }}</option>
        @endforeach
        </select>
    @endif

    @if(isset($allowed_days))
        <select name="days" id="days">
            @foreach($allowed_days as $day)
                <option value="{{ $day }}" @if($day == $selected_days) selected @endif>{{ $day }} days</option>
            @endforeach
        </select>
    @endif

    {!! $chart->container() !!}

@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>
    {!! $chart->script() !!}

    <script>
        document.getElementById("country").addEventListener("change", goToData, false);
        document.getElementById("days").addEventListener("change", goToData, false);

        function goToData() {

            let country = document.getElementById("country");
            let days = document.getElementById("days");

            let selected_country = country.options[country.selectedIndex].value;
            let selected_day = days.options[days.selectedIndex].value;

            if (selected_day == 30) {
                window.location.href = "/country/" + selected_country.toLowerCase() + "/";
            } else {
                window.location.href = "/country/" + selected_country.toLowerCase() + "/" + selected_day + "/";
            }
        }
    </script>

@endsection
