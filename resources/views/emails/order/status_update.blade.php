@component('mail::message')
Hello {{ $user->first_name ?? 'Customer' }},

Your order (ID: {{ $order->id }}) at {{ $pharmacy->name ?? 'the pharmacy' }} has been updated.

**Order Status:** {{ ucfirst($order->status) }}

@isset($order->priority)
**Priority:** {{ ucfirst($order->priority) }}
@endisset

@isset($order->total_price)
**Total Price:** {{ formatMoney($order->total_price) }}
@endisset

@if($order->status === 'delivered' || $order->status === 'completed')
We value your feedback! Please let us know about your experience with this order.
[Leave Feedback]({{ $feedback_url ?? '#' }})
@endif

Thank you for choosing {{ $pharmacy->name ?? 'our pharmacy' }}!

Regards,<br>
{{ env('APP_NAME') }}
@endcomponent
