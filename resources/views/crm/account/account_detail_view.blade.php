<h2 class="px-3 pb-3">#{{ $data['user']['id'] }} {{ $data['user']['first_name'] }} {{ $data['user']['last_name'] }}</h2>
<div class="row ps-3 justify-content-between">
    <div class="col-3 col-lg-6 col-xl-3">
        <ul class="nav nav-tabs border-0 rounded-pill p-0" role="tablist" style="background: #f1f1f1;">

            <li class="nav-item w-50">
                <a class="nav-link border-0 text-center rounded-pill active" data-bs-toggle="tab" href="#home"
                    role="tab" aria-selected="true">
                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                    <span class="d-none d-sm-block">Overview</span>
                </a>
            </li>
            <li class="nav-item w-50">
                <a class="nav-link border-0 text-center rounded-pill" data-bs-toggle="tab" href="#profile"
                    role="tab" aria-selected="false">
                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                    <span class="d-none d-sm-block">Timeline</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="col-3 col-lg-6 col-xl-3">
        <span class="float-end"><button onclick="" class="btn btn-sm"
                style="background-color: #32C51A; color: #fff;">Client</button> </span>
    </div>
</div>

<div class="d-flex flex-wrap pt-3 pb-2 px-3">
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Detail</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Deal</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3"
        onclick="">Service</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3"
        onclick="">Contact</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Files</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3"
        onclick="">Quotation</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Notes</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Action</button>
</div>

<div class="tab-content p-3 text-muted lead-custom-scroll-2" style="max-height: 600px; overflow: scroll;">
    <div class="tab-pane active" id="home" role="tabpanel">

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
            @include('crm.account.account_detail_tab.detail_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_deal_detail">
            @include('crm.account.account_detail_tab.detail_deal_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_service_detail">
            @include('crm.account.account_detail_tab.detail_service_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact_detail">
            @include('crm.account.account_detail_tab.detail_contact_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_file_detail">
            @include('crm.account.account_detail_tab.detail_file_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_quotation_detail">
            @include('crm.account.account_detail_tab.detail_quotation_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes_detail">
            @include('crm.account.account_detail_tab.detail_notes_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_open_action_detail">
            @include('crm.account.account_detail_tab.detail_open_action_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_close_action_detail">
            @include('crm.account.account_detail_tab.detail_close_action_tab')
        </div>


        {{-- <div class="card-header bg-transparent border-bottom">
            <b>Detail</b>
        </div>
        <div class="card-body border-bottom">
            <form style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <label style="color: gray;text-decoration: underline;">Customer Detail</label>
                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">First name</label>
                            <div class="col-sm-9">
                                <input class="form-control" id="lead_detail_first_name" name="lead_detail_first_name" placeholder="First Name" value="{{$data['user']['first_name']}}" disabled>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Last name</label>
                            <div class="col-sm-9">
                                <input class="form-control" id="lead_detail_last_name" name="lead_detail_last_name" placeholder="Last Name" value="{{$data['user']['last_name']}}" disabled>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Phone number</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91
                                    </div>
                                    <input type="number" class="form-control" id="lead_detail_phone_number" name="lead_detail_phone_number" placeholder="Phone number" value="{{$data['user']['phone_number']}}" disabled>

                                </div>
                            </div>


                        </div>

                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input class="form-control" id="lead_detail_email" name="lead_detail_email" placeholder="Email" value="{{$data['user']['email']}}" disabled>
                            </div>
                        </div>




                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="lead_detail_addressline2" name="lead_detail_addressline2" placeholder="Addressline 2" value="{{$data['user']['address_line2']}}" disabled>
                            </div>
                        </div>


                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="lead_detail_pincode" name="lead_detail_pincode" placeholder="Pincode" value="{{$data['user']['pincode']}}" disabled>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="lead_detail_city_id" name="lead_detail_city_id" placeholder="Area" value="{{$data['user']['city']}}" disabled>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                    </div>
                </div>
            </form>
        </div>

        <div class="card-header bg-transparent border-bottom">
            <b>Deal Details</b>
        </div>
        <div class="border-bottom">
            <table class="table table-striped table-sm mb-0">

                <thead>
                    <tr>
                        <th>Deal Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Closing date</th>
                        <th>Stage</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($data['deals'] as $deal)
                    <tr id="tr_deal_{{$deal['id']}}">
                        <td><a target="_blank" href="{{$deal['url']}}"> {{$deal['first_name']}} {{$deal['last_name']}}</a></td>
                        <td>{{$deal['quotation']}}</td>
                        <td>{{$deal['status']}}</td>
                        <td>{{$deal['closing_date_time']}} </td>
                        <td>{{$deal['site_stage']}}</td>

                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="card-header bg-transparent border-bottom">
            <b>Contact Person</b>

            <button onclick="addLeadContactModal({{$data['user']['id']}})" class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end mr-2" type="button" style="margin-left:3px;"><i class="bx bx-plus font-size-16 align-middle "></i> </button>

            <button class="btn btn-sm btn-light btn-header-right waves-effect waves-light float-end " type="button">See All </button>

        </div>
        <div class="border-bottom">
            <table class="table table-striped table-sm mb-0">

                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Name</th>
                        <th>Mobile Number</th>
                        <th>Alernate Number</th>
                        <th>Email </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($data['contacts'] as $contact)
                    <tr id="tr_contact_{{$contact['id']}}">
                        <td>{{$contact['tag_name']}}</td>
                        <td>{{$contact['first_name']}} {{$contact['last_name']}}</td>
                        <td>{{$contact['phone_number']}} </td>
                        <td>{{$contact['alernate_phone_number']}}</td>
                        <td>{{$contact['email']}}</td>
                        <td><a class="btn btn-outline-secondary btn-sm edit" title="Edit" onclick="editLeadContact({{$contact['id']}})">
                                <i class="fas fa-pencil-alt"></i>
                            </a></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div> --}}
    </div>
    <div class="card lead-detail tab-pane" style="border-radius: 10px;" id="profile" role="tabpanel">
        <div class="card-body">

            <ul class="verti-timeline list-unstyled">

                <li class="event-list period">
                    <div class="timeline-info"></div>
                    <div class="timeline-marker"></div>
                    <p class="timeline-title">
                        31/05/2023</p>
                </li>
                <li class="event-list">
                    <div class="timeline-info"> <span>12:20 AM</span> </div>
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <p class="">Quatation upload - convert to deal</p><span>by Bhargav Thakkar
                            31/05/2023</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    var ajaxURLSearchUserTag = "{{ route('search.user.tag') }}";
    var ajaxURLUpdateUserDetail = "{{ route('save.user.detail') }}";

    var csrfToken = $("[name=_token").val();

    $("#user_tag_id").select2({
        ajax: {
            url: ajaxURLSearchUserTag,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for tag',
        dropdownParent: $("#lead_detail")
    });

    function saveDetailUpdate(id) {
        // if (isdetailload == 1) {
            $('#detail_loader').show();
            $.ajax({
                type: 'POST',
                url: ajaxURLUpdateUserDetail,
                data: {
                    "id": id,
                    "user_tag": $('#user_tag_id').val(),
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#detail_loader').hide();
                        toastr["success"](responseText['msg']);
                        // getDataDetail(id)
                    } else {
                        $('#detail_loader').hide();
                    }
                }
            })
        // }
    }
</script>
