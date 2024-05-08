<script type="text/javascript">

    // Select User Tab
var currentURL=window.location.href;
var loadedURLLink = $('.userscomman a[href="'+currentURL+'"]');
$(loadedURLLink).removeClass('btn-outline-primary');
$(loadedURLLink).addClass('btn-primary');


function reloadTable() {
      table.ajax.reload( null, false );
}




</script>