<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Whitelion </title>

<style type="text/css">
    * {
        font-family: sans-serif;
        font-size: 4.5px;
        padding: 0px;
        margin: 0px;
        line-height: 2px;
        font-weight: bolder;

    }
    table{

        padding: 2px;
        margin: 0px;




    }
    tfoot tr td{

        margin: 0;
        padding: 0;
        line-height: 2px;
         word-wrap: break-word;


    }

    .qtyTable td{
     font-size: 4px;
      line-height: 4px;
      border: 0.3px solid gray;
      text-align: center;
    }








</style>

</head>
<body>




  <table width="130" style="float:left;">

     <tr><td colspan="2" >Department Name: {{$data['department_name']}}<p style="text-align: right;">Box No : {{$data['sticker_box_no']}}</p></td><tr>
     <tr><td colspan="2">Weight : {{$data['total_weight']}}<p style="text-align: right;">Date : {{$data['packed_date']}}</p></td><tr>
    <tr><td colspan="2" >To,</td><td style="text-align: right;"></td><tr>
     <tr><td colspan="2" ><b>{{$data->channel_partner_firm_name}}</b></td><tr>
     <tr ><td  colspan="2" >{{$data->d_address_line1}} </td><tr>
     <tr><td colspan="2" >{{$data->d_address_line2}}</td><tr>
     <tr><td colspan="2" >{{$data->d_city_name}} - {{$data->d_pincode}}</td><tr>
     <tr><td colspan="2" >Mo. {{$data->channel_partner_dialing_code}} {{$data->channel_partner_phone_number}}</td></tr>
     <tr><td></td><td style="text-align: right;"></td><tr>


     <tr><td>From:</td><td style="text-align: right;"></td><tr>
     <tr ><td colspan="2"><b>{{$data['from_compnay_name']}}</b> </td><tr>
     <tr><td colspan="2" >{{$data['from_address_line1']}} </td><tr>
     <tr><td colspan="2" >{{$data['from_address_line2']}} </td><tr>
     <tr><td colspan="2" >{{$data['from_city_name']}} - {{$data['from_pincode']}} </td><tr>
     <tr><td colspan="2" >Mo. {{$data['from_phone_number']}}</td><tr>


 </table>
   <table width="70" class="qtyTable" style="float:left;border: 0.3px solid gray;border-collapse: collapse;" >

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


