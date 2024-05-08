@if($data['type']==0)
<a href="{{route('users.admin.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==1)

<a href="{{route('users.company.admin.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==2)

<a href="{{route('users.sale.person.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==3)

<a href="{{route('users.account.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==4)

<a href="{{route('users.dispatcher.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==5)

<a href="{{route('users.production.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==6)

<a href="{{route('users.marketing.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>

@elseif($data['type']==7)

<a href="{{route('users.marketing.dispatcher.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>
@elseif($data['type']==8)

<a href="{{route('users.thirdparty.export')}}" target="_blank" class="btn btn-info" type="button" ><i class="bx bx-export font-size-16 align-middle me-2"></i>Export </a>


@endif




<button id="addBtnUser" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalUser" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>User</button>