<!DOCTYPE html>
<html>
<head>
    <title>Booking Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Ezykheti Booking Invoice</h2>
        <p>Booking ID: {{ $booking->id }}</p>
    </div>

    <div class="details">
        <strong>Customer:</strong> {{ $booking->user->name }}<br>
        <strong>Email:</strong> {{ $booking->user->email }}<br>
        <strong>Phone:</strong> {{ $booking->user->phone }}<br>
        <strong>Booking Date:</strong> {{ \Carbon\Carbon::parse($booking->slot_date)->format('d M Y') }}<br>
        <strong>Time:</strong> {{ $booking->start_time }} - {{ $booking->end_time }}<br>
    </div>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Rate per Kanal</th>
                <th>Area (Kanal)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $booking->service->name }}</td>
                <td>₹{{ $booking->service->rate_per_kanal }}</td>
                <td>{{ $booking->area }}</td>
                <td>₹{{ $booking->amount }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Thank you for booking with us!</p>
    </div>

</body>
</html>
