  @php
  $accessTypes=getElectricians();
  @endphp
  <div class="d-flex flex-wrap gap-2 userscomman justify-content-center">

    <!-- @foreach($accessTypes as $key=>$value)
                                            <a href="{{$value['url']}}?view_mode={{$data['viewMode']}}" class="btn btn-outline-primary waves-effect waves-light">{{$value['name']}}</a>
                                          @endforeach -->
    <div class="right w-25">
      <select id="filter_electrician_advance">
        <option value="-1">All</option>
        <option value="1">Sort By Max Inquiry Provided By</option>
      </select>
    </div>

  </div>