  @php
if(Auth::user()->type!=3){
$accessTypes=getChannelPartners();
}else{
$accessTypes=getChannelPartnersForAccount();
}




 @endphp
                                          <div class="d-flex flex-wrap gap-2 userscomman">

                                           @foreach($accessTypes as $key=>$value)
                                            <a href="{{$value['url_view']}}" class="btn btn-outline-primary waves-effect waves-light">{{$value['name']}}</a>
                                          @endforeach

</div>