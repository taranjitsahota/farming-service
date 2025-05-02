<h2>Hello {{ $booking->user->name }},</h2>

<p>Your booking has been successfully confirmed!</p>

<ul>
    <li><strong>Service:</strong> {{ $booking->service->name }}</li>
    <li><strong>Date:</strong> {{ \Carbon\Carbon::parse($booking->slot_date)->format('d M Y') }}</li>
    <li><strong>Time:</strong> {{ $booking->start_time }} - {{ $booking->end_time }}</li>
    <li><strong>Total Amount:</strong> ₹{{ $booking->amount }}</li>
</ul>

<p>Thank you for choosing us.</p>
<p>— Team Ezykheti</p>
