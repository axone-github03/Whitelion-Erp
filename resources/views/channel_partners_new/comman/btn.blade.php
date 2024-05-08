@if(!isset($data['isRequest']) || (isset($data['isRequest']) && $data['isRequest']==0)  && isCreUser() == 0)
<button id="addBtnDiscount" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" role="button"><i class='bx bxs-discount font-size-16 align-middle me-2'></i>Add Discount</button>
<a id="exportLink" href="{{ route('new.channel.partners.export') }}?type={{ $data['type'] }}" target="_blank" class="btn btn-info" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Export</a>

<button id="addBtnUser" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#modalUser" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Channel Partner</button>
@endif