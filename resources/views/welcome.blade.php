<x-app-layout>
    <div class="container d-flex flex-column mt-5 relative">
        <h1 class="fs-1 fw-bold text-center">Temperature Analytics</h1>
        <div class="d-flex absolute right-0">
            <h4 class="btn btn-danger fs-4 fw-bold me-2" id="cutoff">Emergency Cutoff</h4>
            <h4 class="btn btn-success fs-4 fw-bold" id="powerOn">Power On</h4>
        </div>
        <h4 class="fs-4 fw-bold text-center bg-success rounded-pill py-2 px-4 text-white mx-auto my-3" style="width: 300px"><span id="temp">--</span>°C</h4>
        <h4 class="fs-4 fw-bold text-center ms-4">Status: <span id="status">OK</span></h4>
        <div>
            <canvas id="myChart" class="w-100"></canvas>
        </div>
        <a class="btn btn-success fs-4 fw-bold my-5" href="{{route('view.log')}}">View Power Interrupt Logs</a>
    </div>

    <script>
        // Initialize the chart
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Temperature',
                    data: [],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const maxDataPoints = 20;

        function updateChart(time, temperature) {
            const formattedTime = new Date(time).toLocaleTimeString();
            myChart.data.labels.push(formattedTime);
            myChart.data.datasets[0].data.push(temperature);

            if (myChart.data.labels.length > maxDataPoints) {
                myChart.data.labels.shift();
                myChart.data.datasets[0].data.shift();
            }

            myChart.update();
        }

        async function fetchTemperature() {
            try {
                const response = await fetch('{{ route('get.temp') }}');
                if (!response.ok) {
                    console.log('Network response was not ok');
                }
                const data = await response.json();
                const statusSpan = document.getElementById('status');
                const tempSpan = document.getElementById('temp');
                const temp = data.data.temperature;
                const time = data.data.time;

                tempSpan.innerText = temp;

                updateChart(time, temp);

                if (temp > 40) {
                    statusSpan.innerText = 'HIGH';
                    statusSpan.classList.add('text-danger');
                    tempSpan.parentElement.classList.add('bg-danger');
                    tempSpan.parentElement.classList.remove('bg-success');
                } else {
                    statusSpan.innerText = 'OK';
                    statusSpan.classList.remove('text-danger');
                    tempSpan.parentElement.classList.remove('bg-danger');
                    tempSpan.parentElement.classList.add('bg-success');
                }
            } catch (error) {
                console.error('There has been a problem with your fetch operation:', error);
                document.getElementById('temperature').innerText = 'Error fetching temperature data';
            }
        }

        setInterval(fetchTemperature, 1500);

        fetchTemperature();

        document.getElementById('cutoff').addEventListener('click', function() {
            fetch('{{ route('cutoff') }}');
        });

        document.getElementById('powerOn').addEventListener('click', function() {
            fetch('{{ route('power.on') }}');
        });
    </script>
</x-app-layout>
