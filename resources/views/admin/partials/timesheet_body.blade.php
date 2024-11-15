@foreach ($timesheet as $userTimesheet)
<tr>
    <td>{{ $userTimesheet['name'] }}</td>
    @foreach ($userTimesheet['days'] as $day => $time)
        @php
            $date = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, $day);
            $isWeekend = $date->isSaturday() || $date->isSunday();

            // Convert time to minutes if it's not "-"
            $timeInMinutes = $time !== '-' ? \Carbon\Carbon::parse($time)->hour * 60 + \Carbon\Carbon::parse($time)->minute : 0;
        @endphp
        <td style="{{ $time !== '-' && $timeInMinutes < 510 ? 'background-color: lightcoral;' : '' }}">
            @if ($time === '-' && $isWeekend)
                <i class="fas fa-star text-warning"></i>
            @else
                {{ $time }}
            @endif
        </td>
    @endforeach
    <td>{{ $userTimesheet['total'] }}</td>
</tr>
@endforeach