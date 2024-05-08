<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Whitelion </title>

<style type="text/css">
    * {
        font-family: sans-serif;
    }
    table{
        font-size: x-small;
    }
    tfoot tr td{
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
       <tr><td></td><td style="text-align: right;"><b>Date:</b> ________________</td></tr>
       <tr><td></td><td style="text-align: right;"><b>Weight:</b>  ________________</td></tr>
        <td><strong>To:</strong><br>
        <b>{{$data['invoice']->channel_partner_firm_name}}</b>
            <br>

                                                        {{$data['invoice']->d_address_line1}}

                                                        @if($data['invoice']->d_address_line2!="")
                                                        <br>
                                                        {{$data['invoice']->d_address_line2}}
                                                        @endif
                                                        <br>
                                                         {{$data['invoice']->d_pincode}}
                                                        <br>
  {{getCityName($data['invoice']->d_city_id)}}, {{getStateName($data['invoice']->d_state_id)}}, {{getCountryName($data['invoice']->bill_country_id)}}

  <br>
<span class="solidline">---------------------------------------</span>
  <br>
   <b>{{$data['invoice']->channel_partner_first_name}}  {{$data['invoice']->channel_partner_last_name}}</b>
                                                        <br>
                                                           {{$data['invoice']->channel_partner_dialing_code}} {{$data['invoice']->channel_partner_phone_number}}

        </td>

    </tr>

  </table>

    <table  width="100%" style="text-align: right;">

        <tr>
              <td><strong>From:</strong><br>     <b>{{$data['billFrom']}}</b>
                 <br>

                                                        {{$data['billFromAddressline1']}}

                                                        @if($data['billFromAddressline2']!="")
                                                        <br>
                                                        {{$data['billFromAddressline2']}}
                                                        @endif
                                                       <br>
                                                         {{$data['billPincode']}}
                                                        <br>
    {{$data['billCityName']}}, {{$data['billStateName']}}, {{$data['billCountryName']}}
  <br>
<span class="solidline">---------------------------------------</span>
  <br>
    <b>DISPATCH DEPT</b>
    <br>
    {{$data['dispater_mobile_no']}}


</td>
        </tr>

    </table>

  <table  width="100%">
        <tr>


          <td> <br>Courier : {{$data['courier_service']}} |  Track ID : {{$data['track_id']}} | Number Of Boxes :  ________________</td>

      </tr>
  </table>
  <table  width="50%" border="1" style="border-collapse: collapse;">
        <tr>


          <td> <b>MODEL NAME<b></td>
          <td> <b>PCS<b></td>

      </tr>

      @foreach($data['invoice_items'] as $invoiceItemK=>$invoiceItem)

        <tr>


          <td> {{$invoiceItem->product_brand_name}}  {{$invoiceItem->product_code_name}}</td>
          <td> {{$invoiceItem->qty}}</td>

      </tr>
      @endforeach


  </table>





</body>
</html>