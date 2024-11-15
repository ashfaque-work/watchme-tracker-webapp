<table>
    <thead>
        <tr>
            <th>Employee</th>
            @foreach (array_keys($timesheet[0]['days']) as $date)
                <th>{{ $date }}</th>
            @endforeach
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($timesheet as $userTimesheet)
        <tr>
            <td>{{ $userTimesheet['name'] }}</td>
            @foreach ($userTimesheet['days'] as $time)
                <td>{{ $time }}</td>
            @endforeach
            <td>{{ $userTimesheet['total'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>