


<p>Dear {{$params['user_name']}}, </p>
<p>I hope this email finds you well and in good spirits! I am thrilled to inform you that we have dispatched the gifts that you recently claimed. A warm congrats for that! </p>
<p>Here are the tracking details:</p>
<p>Tracking Number: {{$params['track_id']}}</p>
<p>Courier Name: {{$params['courier_service_name']}}</p>
<p>Request ID: {{$params['order_id']}}</p>
<p>Keep up the great performance!</p>

<p>Warm Regards,<br>
    Team Whitelion.</p>



{{-- <p>Woo hoo! Your order is on its way. Your order details can be found below.</p>

<p>COURIER SERVICE: <b>{{$params['courier_service_name']}}</b></p>
<p>TRACK ID: <b>{{$params['track_id']}}</b></p>

<p>Order : #<b>{{$params['order_id']}}</b></p>

<p>ORDER SUMMARY:</p>

<table border="1" cellpadding="0" cellspacing="0" class="body-wrap" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 30%; background-color: transparent; margin: 0;">


    @if($params['cash']!=0)
    <tr>
        <td>Cash</td>
        <td>{{$params['cash']}}</td>
    </tr>
    @endif

    @foreach($params['items'] as $key=>$value)
    <tr>
        <td>{{$value['name']}}</td>
        <td>{{$value['qty']}}</td>
    </tr>
    @endforeach
</table>


<p>Regards,<br>
    Whitelion Systems Pvt Ltd,</p> --}}