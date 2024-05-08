@foreach ($data as $board)
    <div class="d-flex">
        <div class="col-6 m-0 me-0 pb-3" style="border-right: solid #dbdbdb 1px; border-bottom: solid #dbdbdb 1px;">
            <div class="col-md-12">
                <h3 class="text-center my-1">{{$board['item_type']}}</h3>
            </div>
            <div class="text-capitalize d-flex mt-3">
                <div class="col-8">
                    <h4 class="font-size-18;" style="color: black;">Board {{ $board['board_name'] }}</h4>
                </div>
                <div class="col-4 d-flex justify-content-center align-items-center">
                    <div class="me-3">
                        <i class='bx bxs-trash-alt font-size-24 red-icon'  style='color:#ff0000' onclick="delete_board_Warning({{$board['quot_id']}}, {{$board['quotgroup_id']}}, {{$board['room_no']}}, {{$board['board_no']}})"></i>
                    </div>
                    <div>
                        <div class="form-check-success form-switch">
                            @if($board['isactiveboard'] == 1)
                                <input class="form-check-input" type="checkbox" role="switch" checked onchange="quotation_board_status(this, {{$board['quot_id']}}, {{$board['quotgroup_id']}}, {{$board['room_no']}}, {{$board['board_no']}}, 'checked')">
                            @else
                                <input class="form-check-input" type="checkbox" role="switch" onchange="quotation_board_status(this, {{$board['quot_id']}}, {{$board['quotgroup_id']}}, {{$board['room_no']}}, {{$board['board_no']}})">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex mt-3">
                <table class="table table-borderless custom-table">
                    <tr style="color: black; font-size: medium;">
                        <td class="text-center col-3">Hall | Entry</td>
                        <td class="col-4 ps-4">Item</td>
                        <td class="col-3">Brand</td>
                        <td class="col-2">Qty</td>
                    </tr>
                    <tr>
                        <td rowspan="{{ count($board['board_item']) + 1 }}" class="col-3">
                            <img src="{{ $board['image'] }}" style="width: 100%" alt="">
                        </td>
                    </tr>
                    @foreach ($board['board_item'] as $item)
                        <tr>
                            <td class="col-4 ps-4">{{ $item['itemname'] }}</td>
                            <td class="col-3">{{ $item['itemsubgroupname'] }}</td>
                            <td class="col-2 ps-3">{{ $item['qty'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        @if($board['item_type'] == "POSH")
            <div class="col-6 m-0 ps-3 pb-3" style="border-bottom: solid #dbdbdb 1px;">
                <div class="col-md-12">
                    <h3 class="text-center my-1">QUARTZ</h3>
                </div>
                <div class="text-capitalize d-flex mt-4">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" name="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}" id="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}_62" value="62" checked>
                        <label class="form-check-label" style="color: black;" for="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}">Quartz Black</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}" id="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}_63" value="63">
                        <label class="form-check-label" style="color: black;" for="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}">Quartz White</label>
                    </div>
                </div>
                <div class="row mt-3">
                    {{-- <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Accessories</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewAccessories({{$board['room_no']}}, {{$board['board_no']}}, '\'{{$board['item_type']}} \'', '\'{{$board['board_range']}} \'')"></i>
                        </div>
                        <div class="col-12">
                            <input type="hidden" id="accessories_count" value="1">
                            <table class="w-100">
                                <tbody id="AccessoriesTbody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="AccessoriesNewItem_{{$board['room_no']}}_{{$board['board_no']}}" data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="accessories_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="accessories_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="accessories_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="accessories_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> --}}
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Whitelion Model</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewWlModel({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '\{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <table class="w-100">
                                <tbody id="WhitelioModelTbody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="WhitelioModeNewItem_{{$board['room_no']}}_{{$board['board_no']}}"  data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="whiteliom_model_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="whiteliom_model_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="whiteliom_model_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="whiteliom_model_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Add - On</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewAddon({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <table class="w-100">
                                <tbody id="AddOnTBody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="AddOnNewItem_{{$board['room_no']}}_{{$board['board_no']}}" data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="addon_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="addon_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="addon_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="addon_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="" class="form-label">Notes</label>
                        <textarea type="text" class="form-control" id="board_notes_{{$board['room_no']}}_{{$board['board_no']}}" name="board_notes_{{$board['room_no']}}_{{$board['board_no']}}" placeholder="Notes"></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12 d-flex justify-content-center">
                        <button class="btn btn-outline-primary" onclick="SaveSingleBoard({{$board['quot_id']}}, {{$board['quotgroup_id']}}, {{$board['room_no']}}, {{$board['board_no']}}, 'QUARTZ')">Save</button>
                    </div>
                </div>
            </div>
        @else 
            <div class="col-6 m-0 ps-3 pb-3" style="border-bottom: solid #dbdbdb 1px;">
                <div class="col-md-12">
                    <h3 class="text-center my-1">POSH</h3>
                </div>
                <div class="col-md-12">
                    <div class="my-3 text-center">
                        <img src="https://whitelion.sgp1.digitaloceanspaces.com/erp.whitelion.in/quotation/board/658ab4931598e_10320_4333_3_1.png" alt="" style="height: 100px;">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Plate</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewPlate({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <input type="hidden" id="plate_count" value="1">
                            <table class="w-100">
                                <tbody id="PlateTbody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="PlateNewItem_{{$board['room_no']}}_{{$board['board_no']}}" data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="plate_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="plate_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="plate_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="plate_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Accessories</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewAccessories({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <input type="hidden" id="accessories_count" value="1">
                            <table class="w-100">
                                <tbody id="AccessoriesTbody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="AccessoriesNewItem_{{$board['room_no']}}_{{$board['board_no']}}" data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="accessories_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="accessories_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="accessories_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="accessories_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Whitelion Model</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewWlModel({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <table class="w-100">
                                <tbody id="WhitelioModelTbody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="WhitelioModeNewItem_{{$board['room_no']}}_{{$board['board_no']}}"  data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="whiteliom_model_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="whiteliom_model_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="whiteliom_model_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="whiteliom_model_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="col-12">
                            <label for="item_diamond_ct" class="form-label" style="color: black;">Add - On</label>
                            <i class="bx bx-plus-circle icon-small ml-2 float-end mb-2" onclick="addNewAddon({{$board['room_no']}}, {{$board['board_no']}}, '{{$board['item_type']}}', '{{$board['board_range']}}')"></i>
                        </div>
                        <div class="col-12">
                            <table class="w-100">
                                <tbody id="AddOnTBody_{{$board['room_no']}}_{{$board['board_no']}}">
                                    <tr class="AddOnNewItem_{{$board['room_no']}}_{{$board['board_no']}}" data-id="0">
                                        <td class="w-75">
                                            <select class="form-control select2-ajax" id="addon_{{$board['room_no']}}_{{$board['board_no']}}_0" aria-label="Disabled select example" name="addon_{{$board['room_no']}}_{{$board['board_no']}}_0" required></select>
                                        </td>
                                        <td class="w-25">
                                            <input type="number" class="form-control" id="addon_qty_{{$board['room_no']}}_{{$board['board_no']}}_0" name="addon_qty_{{$board['room_no']}}_{{$board['board_no']}}_0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="" class="form-label">Notes</label>
                        <textarea type="text" class="form-control" id="board_notes_{{$board['room_no']}}_{{$board['board_no']}}" name="board_notes_{{$board['room_no']}}_{{$board['board_no']}}" placeholder="Notes"></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12 d-flex justify-content-center">
                        <button class="btn btn-outline-primary" onclick="SaveSingleBoard({{$board['quot_id']}}, {{$board['quotgroup_id']}}, {{$board['room_no']}}, {{$board['board_no']}}, 'POSH')">Save</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endforeach

<script type="text/javascript">
    var ajaxURLSearchPlate = "{{ route('quot.convertion.search.plate') }}";
    var ajaxURLSearchAccessories = "{{ route('quot.convertion.search.accessories') }}";
    var ajaxURLSearchWhitelionModel = "{{ route('quot.convertion.search.whitelion.model') }}";
    var ajaxURLSearchAddon = "{{ route('quot.convertion.search.addon') }}";

    @foreach ($data as $board)
        $('#plate_{{$board['room_no']}}_{{$board['board_no']}}_0').select2({
            ajax: {
                url: ajaxURLSearchPlate,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        "quot_id" : function() {
                            return {{$board['quot_id']}};
                        },
                        "quotgroup_id" : function() {
                            return {{$board['quotgroup_id']}};
                        },
                        "type" : function() {
                            return '{{$board['item_type']}}';
                        },
                        "range_subgroup" : function() {
                            return '{{$board['board_range']}}';
                        }
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
            placeholder: 'Search for plate',
            dropdownParent: $('#PlateTbody_{{$board['room_no']}}_{{$board['board_no']}}')
        });
        
        $('#accessories_{{$board['room_no']}}_{{$board['board_no']}}_0').select2({
            ajax: {
                url: ajaxURLSearchAccessories,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        "quot_id" : function() {
                            return {{$board['quot_id']}};
                        },
                        "quotgroup_id" : function() {
                            return {{$board['quotgroup_id']}};
                        },
                        "sub_group" : function() {
                            return $('input[name="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}"]:checked').val();
                        },
                        "type" : function() {
                            return '{{$board['item_type']}}';
                        },
                        "range_subgroup" : function() {
                            return '{{$board['board_range']}}';
                        }
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
            placeholder: 'Search for accessories',
            dropdownParent: $('#AccessoriesTbody_{{$board['room_no']}}_{{$board['board_no']}}')
        });

        $('#whiteliom_model_{{$board['room_no']}}_{{$board['board_no']}}_0').select2({
            ajax: {
                url: ajaxURLSearchWhitelionModel,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        "quot_id" : function() {
                            return {{$board['quot_id']}};
                        },
                        "quotgroup_id" : function() {
                            return {{$board['quotgroup_id']}};
                        },
                        "sub_group" : function() {
                            return $('input[name="subgroup_checkbox_{{$board['room_no']}}_{{$board['board_no']}}"]:checked').val();
                        },
                        "type" : function() {
                            return '{{$board['item_type']}}';
                        },
                        "range_subgroup" : function() {
                            return '{{$board['board_range']}}';
                        }
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
            placeholder: 'Search for whitelion model',
            dropdownParent: $('#WhitelioModelTbody_{{$board['room_no']}}_{{$board['board_no']}}')
        });

        $('#addon_{{$board['room_no']}}_{{$board['board_no']}}_0').select2({
            ajax: {
                url: ajaxURLSearchAddon,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        "quot_id" : function() {
                            return {{$board['quot_id']}};
                        },
                        "quotgroup_id" : function() {
                            return {{$board['quotgroup_id']}};
                        },
                        "type" : function() {
                            return '{{$board['item_type']}}';
                        },
                        "range_subgroup" : function() {
                            return '{{$board['board_range']}}';
                        }
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
            placeholder: 'Search for addon',
            dropdownParent: $('#AddOnTBody_{{$board['room_no']}}_{{$board['board_no']}}')
        });
    @endforeach
</script>
