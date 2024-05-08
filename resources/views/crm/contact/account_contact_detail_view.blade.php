<h2 class="px-3 pb-3">#{{$data['leadcontact']['id']}} {{$data['leadcontact']['first_name']}} {{$data['leadcontact']['last_name']}}</h2>
<div class="row ps-3">
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
</div>

<div class="d-flex flex-wrap pt-3 pb-2 px-3">
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Detail</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Lead</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Deal</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Service</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Contact</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Files</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Notes</button>
    <button type="button" class="btn btn-sm waves-effect waves-light bg-white py-2 px-3" onclick="">Action</button>
</div>

<div class="tab-content p-3 text-muted lead-custom-scroll-2" style="max-height: 600px; overflow: scroll;">
    <div class="tab-pane active" id="home" role="tabpanel">

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_detail">
            @include('crm.contact.contact_detail_tab.detail_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_lead_detail">
            @include('crm.contact.contact_detail_tab.detail_lead_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_deal_detail">
            @include('crm.contact.contact_detail_tab.detail_deal_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_service_detail">
            @include('crm.contact.contact_detail_tab.detail_service_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_contact_detail">
            @include('crm.contact.contact_detail_tab.detail_contact_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_file_detail">
            @include('crm.contact.contact_detail_tab.detail_file_tab')
        </div>

        <div class="card lead-detail" style="border-radius: 10px;" id="tab_notes_detail">
            @include('crm.contact.contact_detail_tab.detail_notes_tab')
        </div>
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
                            <p class="">Quatation upload - convert to deal</p><span>by Bhargav Thakkar 31/05/2023</span>
                        </div>
                    </li>
            </ul>
        </div>
    </div>
</div>