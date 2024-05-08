@if($params['transaction_data']['is_deal'] == 1)
    <h1>#D{{$params['transaction_data']['id']}}</h1>
@else 
    <h1>#L{{$params['transaction_data']['id']}}</h1>
@endif

<p>Client Name: {{$params['transaction_data']['first_name']}}  {{$params['transaction_data']['last_name']}}</p>
<p>Lead Owner: {{$params['transaction_data']['lead_owner']}}</p>
<p>Lead Source Type: {{$params['transaction_data']['source_type']}}</p>
<p>Lead Source: {{$params['transaction_data']['source']}}</p>
<p>Client Address: <br>{{$params['transaction_data']['house_no']}} - {{$params['transaction_data']['addressline1']}}, <br>
    {{$params['transaction_data']['addressline2']}}, {{$params['transaction_data']['area']}}<br>
    {{$params['transaction_data']['pincode']}} - {{$params['transaction_data']['city']['city_list_name']}}, <br>
    {{$params['transaction_data']['city']['state_list_name']}}, {{$params['transaction_data']['city']['country_list_name']}}</p>
<h3>Quotation Amount: INR. {{$params['transaction_data']['quotation_amt']}} /-</h3>
