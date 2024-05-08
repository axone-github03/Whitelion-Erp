<script>
    var settingType = "{{$data['type']}}";
    $("#setting_type").val(settingType);
    $("#setting_type").change(function() {
        var settingType = $("#setting_type").val();
        var mainSettingURL = "{{route('crm.setting')}}";
        window.location.href = mainSettingURL + "?type=" + settingType;
    });
</script>