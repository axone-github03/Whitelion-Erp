
@if(isAdminOrCompanyAdmin()==1 || isMarketingDispatcherUser()==1)
<a href="{{route('electricians.export')}}?type={{$data['type']}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
@endif


@if($data['viewMode']==0)
<button id="addBtnUser" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalUser" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Electrician</button>

@endif