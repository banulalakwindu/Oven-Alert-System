<x-app-layout>
    <div class="container d-flex flex-column mt-5 relative">
        <h1 class="fs-1 fw-bold text-center my-5">Current Interrupt Log</h1>
        <table class="table my-5">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Date</th>
                <th scope="col">Time</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $index => $log)
                <tr>
                    <th scope="row">{{ $index+1 }}</th>
                    <td>{{ $log->interrupt_time->format('Y-m-d') }}</td>
                    <td>{{ $log->interrupt_time->format('g:i A') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
