<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Whitelion </title>

<style type="text/css">
    * {
        font-family: sans-serif;
        margin: 0;
        padding: 0;
    }
    table{
        font-size: 5px;
    }
    tfoot tr td{
        font-weight: bold;
       font-size: 5px;
    }
    .gray {
        background-color: lightgray
    }



</style>

</head>
<body>




  <table width="100%">
       <tr> <td><b>Date:</b>________________</td><td ></td></tr>
       <tr><td><b>Weight:</b>________________</td><td >  </td></tr>
        <td><strong>To:</strong><br>
        <b>{{$data['invoice']->channel_partner_firm_name}}</b>
            <br>

                                                         <b>{{$data['invoice']->d_address_line1}}</b>

                                                        @if($data['invoice']->d_address_line2!="")
                                                        <br>
                                                         <b>{{$data['invoice']->d_address_line2}}</b>
                                                        @endif
                                                        <br>
                                                          <b>{{$data['invoice']->d_pincode}}</b>
                                                        <br>
   <b>{{getCityName($data['invoice']->d_city_id)}}, {{getStateName($data['invoice']->d_state_id)}}, {{getCountryName($data['invoice']->bill_country_id)}}</b>

  <br>
<span class="solidline">---------------------------------------</span>
  <br>
   <b>{{$data['invoice']->channel_partner_first_name}}  {{$data['invoice']->channel_partner_last_name}}</b>
                                                        <br>
                                                            <b>{{$data['invoice']->channel_partner_dialing_code}} {{$data['invoice']->channel_partner_phone_number}}</b>

        </td>

    </tr>

  </table>

    <table  width="100%" style="text-align: left;">

        <tr>
              <td><strong>From:</strong><br>     <b>{{$data['billFrom']}}</b>
                 <br>

                                                       <!--  <b>{{$data['billFromAddressline1']}}</b>

                                                        @if($data['billFromAddressline2']!="")
                                                        <br>
                                                        <b>{{$data['billFromAddressline2']}}</b>
                                                        @endif
                                                       <br>
                                                          <b>{{$data['billPincode']}}</b>
                                                        <br> -->
     <b>{{$data['billCityName']}}, {{$data['billStateName']}}, {{$data['billCountryName']}}</b>
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


          <td> <br><b>Courier : {{$data['courier_service']}} |  Track ID : {{$data['track_id']}}</b> </td>

      </tr>
  </table>


      @foreach($data['invoice_items'] as $invoiceItemK=>$invoiceItem)

       <table  width="100%">
        <tr>


          <td><b>Department: </b> Sales</td>

          <td colspan="2" rowspan="2"> <span style="font-size:16px; border: 1px solid black;padding:0 3px;"> {{$invoiceItemK+1}}/{{$data['no_of_total_box']}}</span></td>

      </tr>
      <tr>
        <td><b>Weight</b>: ____</td>

      </tr>

  </table>


      <table  width="100%" border="0.3" style="border-collapse: collapse;border: 0.3px solid black;">


       <tr>



          <td style="text-align:center" colspan="2" > <b>Item Name<b></td>
          <td style="text-align:center" > <b>PCS<b></td>

      </tr>

      @foreach($invoiceItem['items'] as $PItem=>$PItemVal)



        <tr>

            <td style="width:10px;text-align:center">{{ $PItem+1}}</td>



          <td style="text-align:center"  > {{$PItemVal['product_brand_name']}}  {{$PItemVal['product_code_name']}}</td>
          <td style="text-align:center" > {{$PItemVal['qty']}}</td>

      </tr>
      @endforeach


        <tr>


          <td style="text-align:right;;" colspan="2" >S. Total&nbsp;&nbsp;&nbsp;</td>
          <td style="text-align:center" ><b>{{$invoiceItem['total_items']}}</b></td>

      </tr>

        </table>


      @endforeach








</body>
</html>