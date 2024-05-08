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


  <table  width="100%">
        <tr>

          <td colspan="2" rowspan="2"> BOX NO. : <span style="font-size:16px; border: 1px solid black;padding:0 3px;float: right;text-align: right;">{{$data['no_of_box']}}</span></td>

      </tr>


  </table>

  <table width="100%" >


    <tr> <td><b>Date:</b> {{$data['packed_date']}}</td><td ></td></tr>
    <tr><td><b>Weight:</b> {{$data['total_weight']}}</td><td >  </td></tr>



    <tr><td colspan="2" style="border-top: 0.5px solid gainsboro;" >To,</td><tr>

     <tr><td colspan="2" ><b>{{$data->channel_partner_firm_name}}</b></td><tr>
     <tr ><td  colspan="2" ><b>{{$data->d_address_line1}} </b></td><tr>
     <tr><td colspan="2" ><b>{{$data->d_address_line2}}</b></td><tr>
     <tr><td colspan="2" ><b>{{$data->d_city_name}} - {{$data->d_pincode}}</b></td><tr>
     <tr><td colspan="2" ><b>Mo. {{$data->channel_partner_dialing_code}} {{$data->channel_partner_phone_number}}</b></td></tr>
     <tr><td></td><td style="text-align: right;"></td><tr>

     <tr><td colspan="2" style="border-top: 0.5px solid gainsboro;" >From:</td><tr>
     <tr ><td colspan="2"><b>{{$data['from_compnay_name']}}</b> </td><tr>
<!--      <tr><td colspan="2" ><b>{{$data['from_address_line1']}}</b> </td><tr>
     <tr><td colspan="2" ><b>{{$data['from_address_line2']}} </b></td><tr> -->
     <!-- <tr><td colspan="2" ><b>{{$data['from_city_name']}} - {{$data['from_pincode']}}</b> </td><tr> -->
     <tr><td colspan="2" ><b>{{$data['from_city_name']}} - {{$data['from_pincode']}}</b> </td><tr>
     <tr><td colspan="2" style="border-bottom: 0.5px solid gainsboro;"  ><b>Mo. {{$data['from_phone_number']}}</b></td><tr>


 </table>

 <table  width="100%">
        <tr>


          <td><b>Courier : {{$data['courier_service']}}</b></td>

      </tr>
       <tr>
          <td><b> Track ID : {{$data['track_id']}}</b> </td>

      </tr>
  </table>

     <table  width="100%">
        <tr>


          <td><b>Department: </b> {{$data['department_name']}}</td>

      </tr>


  </table>
   <table width="100%" class="qtyTable" border="0.3" style="border-collapse: collapse;border: 0.3px solid black;text-align: center;" >

     <tr>
          <td></td>
          <td>Item Name</td>
          <td>Qty</td>
     </tr>
@foreach($data['invoice_packed_items'] as $key=>$invoice_packed_items)

    <tr  >
        <td > {{$key+1}} </td>
        <td  > {{$invoice_packed_items->product_brand_name}} {{$invoice_packed_items->product_code_name}}</td>
        <td > {{$invoice_packed_items->qty}} </td>
    </tr>
@endforeach




   </table>





</body>
</html>


