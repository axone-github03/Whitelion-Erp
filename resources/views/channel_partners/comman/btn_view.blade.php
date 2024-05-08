@if(!isset($data['isRequest']) || (isset($data['isRequest']) && $data['isRequest']==0) && isCreUser() == 0)
<a href="{{route('channel.partners.export.view')}}?type={{$data['type']}}" target="_blank" class="btn btn-info" type="button" >
  <i class="bx bx-export font-size-16 align-middle me-2"></i>Export 
</a>


@endif