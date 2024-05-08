<div class="card-header bg-transparent" style="border-bottom: 1px solid #f1e9e9 !important;border-radius: 0px;">
    <b>Open action </b> 
    <div class="lds-spinner" id="open_action_loader" style="display: none;">
        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
      </div>
    <div class="float-end" style="width: 115px;">
        <select class="form-select" id="add_action_type" name="add_action_type"
            onchange="changeAddActionType()"
            style="height: 25px; font-size: 13px; padding: 2px 9px;">
            <option selected="" value="0">Add Action</option>
            <option value="1">Call</option>
            <option value="2">Meeting</option>
            <option value="3">Task</option>
        </select>
    </div>
</div>
<div class="card-body mb-0 text-center p-0">
    <table class="table table-sm table-bordered mb-0" border="0">
        <thead>
            <tr>
                <th class="px-2 col-4">Open Calls</th>
                <th style="background-color: #f3f3f3;" class="px-2 col-4">Open Meetings</th>
                <th class="px-2 col-4">Open Tasks</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>