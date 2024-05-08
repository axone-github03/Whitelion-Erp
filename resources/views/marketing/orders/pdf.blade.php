<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Whitelion </title>

  <style type="text/css">
    * {
      font-family: sans-serif;
    }

    table {
      font-size: x-small;
    }

    tfoot tr td {
      font-weight: bold;
      font-size: x-small;
    }

    .gray {
      background-color: lightgray
    }
  </style>

</head>

<body>

  <table width="100%">
    <tr>
      <td valign="top"><img src="{{asset('assets/images/order-detail-logo.png')}}" alt="" width="50" /></td>
      <td align="right">

        <h3>Order #{{$data['id']}}</h3>

        <pre>
               <b>Channel Partner Details</b>
               @php
               $channelPartner = getChannelPartners();

               @endphp
                {{$data['channel_partner_email']}}
                {{$data['channel_partner_dialing_code']}} {{$data['channel_partner_phone_number']}}
                Company Name: {{$data['channel_partner_firm_name']}}
                Name: {{$data['channel_partner_first_name']}} {{$data['channel_partner_last_name']}}
                Type: {{$channelPartner[$data['channel_partner_type']]['short_name']}}
                GST Number: {{$data['gst_number']}}
                Payment Mode: {{getPaymentModeName($data['payment_mode'])}}
                Credit Days: {{$data['channel_partner_credit_days']}}
                Credit Limit: {{$data['channel_partner_credit_limit']}}
                Credit Pending: {{$data['channel_partner_pending_credit']}}
            </pre>
      </td>
    </tr>

  </table>

  <table width="100%">
    <tr>
      <td><strong>Billed To:</strong><br>
        {{$data['bill_address_line1']}}
        @if($data['bill_address_line2']!="")
        <br>
        {{$data['bill_address_line2']}}
        @endif
        <br>
        {{$data['bill_pincode']}}
        <br>
        {{getCityName($data['bill_city_id'])}}, {{getStateName($data['bill_state_id'])}}, {{getCountryName($data['bill_country_id'])}}

      </td>
      <td><strong>Shipped To:</strong><br> {{$data['d_address_line1']}}
        @if($data['d_address_line2']!="")
        <br>
        {{$data['d_address_line2']}}
        @endif
        <br>
        {{$data['d_pincode']}}
        <br>
        {{getCityName($data['d_city_id'])}}, {{getStateName($data['d_state_id'])}}, {{getCountryName($data['d_country_id'])}}
      </td>
    </tr>

  </table>

  <br />

  <table width="100%">
    <thead style="background-color: lightgray;">
      <tr>
        <th>#</th>

        <th>Code</th>
        <th>QTY </th>


      </tr>
    </thead>
    <tbody>

      @foreach($data['items'] as $key=>$value)

      <tr>
        <th scope="row">{{$key+1}}</th>
        <td align="right">{{$value['product_code_name']}}</td>
        <td align="right">{{$value['qty']}}</td>

      </tr>

      @endforeach
    </tbody>
  </table>
  <table width="100%">
    <tfoot>
      <tr>
        <td colspan="5"></td>
        <td align="right">Total MRP </td>
        <td align="right">{{$data['total_mrp']}}</td>
      </tr>
      <tr>
        <td colspan="5"></td>
        <td align="right">Ex. GST (Order value)</td>
        <td align="right">{{$data['total_mrp_minus_disocunt']}}</td>
      </tr>

      <tr>
        <td colspan="5"></td>
        <td align="right">Estimated Tax (GST) ({{$data['gst_percentage']}}%)</td>
        <td align="right">{{$data['gst_tax']}}</td>
      </tr>
      <tr>
        <td colspan="5"></td>
        <td align="right">Delivery Charges</td>
        <td align="right">{{$data['delievery_charge']}}</td>
      </tr>
      <tr>
        <td colspan="5"></td>
        <td align="right">Total </td>
        <td align="right" class="gray">{{$data['total_payable']}}</td>
      </tr>
    </tfoot>
  </table>

</body>

</html>