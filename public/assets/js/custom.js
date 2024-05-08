function responsive(maxWidth, maxType) {
    if (maxWidth.matches == true && maxType == 'large') {
        $('[data-sidebar="dark"]').addClass('sidebar-enable vertical-collpsed');
    }
    else if(maxWidth.matches == true && maxType == 'small')
    {
        $('[data-sidebar="dark"]').removeClass('sidebar-enable vertical-collpsed');
    }
}

$(document).ready(function(){
    
    if(screen.width == 1024)
    {
        var maxWidth = window.matchMedia("(max-width: 1024px)");
        var maxType = 'large';
    }
    else if(screen.width == 768){
        var maxWidth = window.matchMedia("(max-width: 768px)");
        var maxType = 'small';
    }
    else
    {
        maxWidth = window.matchMedia(0);
        var maxType = 'extralarge';
    }
    responsive(maxWidth, maxType);
})