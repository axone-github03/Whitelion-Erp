  @php
$accessTypes=getUsersAccess(Auth::user()->type);
 @endphp
                                          <div class="d-flex flex-wrap gap-2 userscomman">

                                           @foreach($accessTypes as $key=>$value)
                                            <a href="{{$value['url']}}" class="btn btn-outline-primary waves-effect waves-light">{{$value['name']}}</a>
                                          @endforeach

</div>