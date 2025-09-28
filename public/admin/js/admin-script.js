(function ($) {

    "use strict";

    $(".mobile-toggle").click(function () {
        $(".nav-menus").toggleClass("open");
    });

    $(".mobile-search").click(function () {
        $(".form-control-plaintext").toggleClass("open");
    });

    $(".form-control-plaintext").keyup(function (e) {
        if (e.target.value) {
            $("body").addClass("offcanvas");
        } else {
            $("body").removeClass("offcanvas");
        }
    });

    $('.loader-wrapper').fadeOut('fast', function () {
        $(this).remove();
    });

    $(window).on('scroll', function () {
        if ($(this).scrollTop() > 600) {
            $('.tap-top').fadeIn();
        } else {
            $('.tap-top').fadeOut();
        }
    });

    $('.tap-top').click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });

    var $ripple = $(".js-ripple");
    $ripple.on("click.ui.ripple", function (e) {
        var $this = $(this);
        var $offset = $this.parent().offset();
        var $circle = $this.find(".c-ripple__circle");
        var x = e.pageX - $offset.left;
        var y = e.pageY - $offset.top;
        $circle.css({
            top: y + "px",
            left: x + "px"
        });
        $this.addClass("is-active");
    });

    $ripple.on("animationend webkitAnimationEnd oanimationend MSAnimationEnd", function (e) {
        $(this).removeClass("is-active");
    });

    $(".chat-menu-icons .toogle-bar").click(function () {
        $(".chat-menu").toggleClass("show");
    });

})(jQuery);

function toggleFullScreen() {
    if ((document.fullScreenElement && document.fullScreenElement !== null) ||
        (!document.mozFullScreen && !document.webkitIsFullScreen)) {
        if (document.documentElement.requestFullScreen) {
            document.documentElement.requestFullScreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullScreen) {
            document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        }
    } else {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        }
    }
}

$(".header-search").click(function () {
    $(".search-full").addClass("open");
});

$(".close-search").click(function () {
    $(".search-full").removeClass("open");
});
