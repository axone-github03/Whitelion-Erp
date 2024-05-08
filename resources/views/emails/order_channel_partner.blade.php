{{-- Subject: New Order Placed --}}
<p>Dear {{ $params['user_name'] }},</p>
<p>We would like to inform you that we have received your order. The order is processed from our end. You shall expect
    the dispatch of materials within 5 working days from our wearhouse.</p>
<p>The details of the order is here:</p>
<p>Order Date : {{ $params['order_date'] }}</p>
<p>Order ID : {{ $params['id'] }}</p>
<p>Order By : {{ $params['order_by'] }}</p>
<p>Order Value : {{ $params['order_amount'] }}</p>
<p>For your any kind of query, you can contact our representative.</p>
<p>Representative Name: Namrata Bhawagar</p>
<p>Representative Contact No. : +91 9016203763</p>

<p>Thank you for choosing our services.</p>
<p>Warm Regards,<br>
    Team Whitelion.</p>
