<div class="modal fade" id="modal_add_master_data_excel" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalMasterExcelUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalMasterExcelUploadLabel">Upload Master Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCompanyAdd" action="{{route('cmimport')}}" method="POST" enctype="multipart/form-data">

                <div class="d-flex" style="text-align: center;margin-top: 10px;margin-left: 105px;margin-bottom: 10px;">
                    <input class="form-check-input" type="hidden" name="datatype" id="datatype" value="COMPANY">

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" id="CompanyMaster" value="COMPANY" checked>
                        <label class="form-check-label" for="CompanyMaster">Company</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" value="CATEGORY" id="CategoryMaster">
                        <label class="form-check-label " for="CategoryMaster">Category</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" value="GROUP" id="GroupMaster">
                        <label class="form-check-label " for="GroupMaster">Group</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" value="SUBGROUP" id="SubGroupMaster">
                        <label class="form-check-label " for="SubGroupMaster">Sub Group</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" value="ITEM" id="ItemMaster">
                        <label class="form-check-label " for="ItemMaster">Item</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="radioFilter" value="ITEMPRICE" id="ItemPriceMaster">
                        <label class="form-check-label" for="ItemPriceMaster">Item Price</label>
                    </div>

                </div>

                @csrf
                <input type="file" id="uploaded_file" name="uploaded_file" class="form-control">
                <br>

                <button type="button" onclick="truncateClick()" class="btn btn-secondary waves-effect">
                    Truncate
                </button>

                <button type="submit" class="btn btn-success">
                    Import User Data
                </button>

            </form>

        </div>
    </div>
</div>
</div>


<script type="text/javascript">
    var count = 0;

    function companyClick() {
        count++;
        if (count == 8) {
            $("#modal_add_master_data_excel").modal('show');
            count = 0;
        }
    }

    function truncateClick() {
        var ajaxTruncateDataURL = '{{route("cmtruncatedata")}}';
        $.ajax({
            type: 'GET',
            url: ajaxTruncateDataURL,
            data: {
                "radioFilter": $('#datatype').val(),
            },
            success: function(resultData) {

                if (resultData['status'] == 1) {
                    toastr["success"](resultData['msg']);
                    $("#modal_add_master_data_excel").modal('hide');

                } else if (resultData['status'] == 0) {

                    toastr["error"](resultData['msg']);

                }


            }
        });
    }

    function exportClick() {
       
    }
    $(document).ready(function() {
        $('input[type=radio][name=radioFilter]').change(function() {
            // alert(this.value);
            $('#datatype').val(this.value);
        });
    });
</script>