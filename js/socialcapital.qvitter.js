var social_capital = $("#social_capital").val();

var html = '<div class="example-number"><a title="Social Capital Index" href="http://gnusocial.local/socialcapital">' + social_capital + '</a></div>';

/// wait 3 seconds
setTimeout(function() {
    $('.profile-header-inner').prepend(html);
}, 2000);

$('body').on('click', function(){
    setTimeout(function() {
        $('.profile-header-inner').prepend(html);
    }, 1000);
});
