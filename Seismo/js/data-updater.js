function updateData() {
    fetch('get_latest_data.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('vibrationValue').textContent = data.vibration_value;
            document.getElementById('vibrationBar').style.width =
                (data.vibration_value / data.vibration_threshold * 100) + '%';

            const accelElements = document.querySelectorAll('.acceleration-g');
            accelElements[0].textContent = data.accel_x + 'g';
            accelElements[1].textContent = data.accel_y + 'g';
            accelElements[2].textContent = data.accel_z + 'g';

            const accelXWidth = Math.min((Math.abs(data.accel_x) / data.accel_threshold * 100), 100);
            const accelYWidth = Math.min((Math.abs(data.accel_y) / data.accel_threshold * 100), 100);
            const accelZWidth = Math.min((Math.abs(data.accel_z) / data.accel_threshold * 100), 100);

            document.getElementById('accelXBar').style.width = `${accelXWidth}%`;
            document.getElementById('accelYBar').style.width = `${accelYWidth}%`;
            document.getElementById('accelZBar').style.width = `${accelZWidth}%`;

            document.getElementById('vibrationStatus').textContent =
                data.vibration_value >= data.vibration_threshold ? 'HIGH' : 'LOW';

            const mpuHigh = Math.abs(data.accel_x) > data.accel_threshold ||
                Math.abs(data.accel_y) > data.accel_threshold ||
                Math.abs(data.accel_z) > data.accel_threshold;
            document.getElementById('mpuStatus').textContent = mpuHigh ? 'HIGH' : 'LOW';

            document.getElementById('relayStatus').textContent = data.relay_status;

            const earthquakeCard = document.getElementById('earthquakeStatus');
            if (data.earthquake_detected === 'YES') {
                earthquakeCard.style.display = 'flex';
                document.querySelector('.time').textContent =
                    new Date(data.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                showNotification();
            } else {
                earthquakeCard.style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
}

setInterval(updateData, 3000);

updateData();
