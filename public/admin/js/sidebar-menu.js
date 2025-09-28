
// $.sidebarMenu = function(menu) {
//   var animationSpeed = 300,
//       subMenuSelector = '.sidebar-submenu';
//   $(menu).on('click', 'li a', function(e) {
//     var $this = $(this);
//     var checkElement = $this.next();
//     if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
//       checkElement.slideUp(animationSpeed, function() {
//         checkElement.removeClass('menu-open');
//       });
//       checkElement.parent("li").removeClass("active");
//     }
//     else if ((checkElement.is(subMenuSelector)) && (!checkElement.is(':visible'))) {
//       var parent = $this.parents('ul').first();
//       var ul = parent.find('ul:visible').slideUp(animationSpeed);
//       ul.removeClass('menu-open');
//       var parent_li = $this.parent("li");
//       checkElement.slideDown(animationSpeed, function() {
//         checkElement.addClass('menu-open');
//         parent.find('li.active').removeClass('active');
//         parent_li.addClass('active');
//       });
//     }
//     if (checkElement.is(subMenuSelector)) {
//       e.preventDefault();
//     }
//   });
// }
// $.sidebarMenu($('.sidebar-menu'))
// $nav = $('.page-sidebar');
// $header = $('.page-main-header');
// $close_sidebar = $('#close-sidebar');
// $toggle_nav_top = $('#sidebar-toggle');
// $toggle_nav_top.click(function() {
//   $this = $(this);
//   $nav = $('.page-sidebar');
//   $nav.toggleClass('open');
//   $header.toggleClass('open');

// });
// $close_sidebar.click(function() {
//   $this = $(this);
//   $nav = $('.page-sidebar');
//   $nav.toggleClass('open');
//   $header.toggleClass('open');
// });

// $body_part_side = $('.body-part');
// $body_part_side.click(function(){
//   $nav.addClass('open');
//   $header.addClass('open');
// });

// //    responsive sidebar
// var $window = $(window);
// var widthwindow = $window.width();

// (function($) {
//   "use strict";
//   if(widthwindow <= 991) {
//     $nav.addClass("open");
//     $header.addClass('open');

//   }
// })(jQuery);


// var current = window.location.pathname
// $(".sidebar-menu>li a").filter(function() {

//   var link = $(this).attr("href");
//   if(link){
//     if (current.indexOf(link) != -1) {
//       $(this).parents('li').addClass('active');
//       $(this).addClass('active');
//       console.log(link + " found");
//     }
//   }
// });



// // $.sidebarMenu = function(menu) {
//   //   var animationSpeed = 300,
//   //       subMenuSelector = '.sidebar-submenu';

//   //   $(menu).on('click', 'li a', function(e) {
//   //     var $this = $(this);
//   //     logActiveClassState($this); 

//   //     var checkElement = $this.next(); 

//   //     if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
//   //       checkElement.slideUp(animationSpeed, function() {
//   //         checkElement.removeClass('menu-open');
//   //       });
//   //       checkElement.parent("li").removeClass("active");
//   //     }
//   //     else if (checkElement.is(subMenuSelector) && !checkElement.is(':visible')) {
//   //       var parent = $this.parents('ul').first();
//   //       var ul = parent.find('ul:visible').slideUp(animationSpeed);
//   //       ul.removeClass('menu-open');
//   //       var parent_li = $this.parent("li");
//   //       checkElement.slideDown(animationSpeed, function() {
//   //         checkElement.addClass('menu-open');
//   //         parent.find('li.active').removeClass('active');
//   //         parent_li.addClass('active');
//   //       });
//   //     }

//   //     scrollToMenuItem($this);

//   //     if (checkElement.is(subMenuSelector)) {
//   //       e.preventDefault();
//   //     }
//   //   });
//   // }

//   // $.sidebarMenu($('.sidebar-menu'));

//   // $nav = $('.page-sidebar');
//   // $header = $('.page-main-header');
//   // $close_sidebar = $('#close-sidebar');
//   // $toggle_nav_top = $('#sidebar-toggle');

//   // $toggle_nav_top.click(function() {
//   //   $nav.toggleClass('open');
//   //   $header.toggleClass('open');
//   // });

//   // $close_sidebar.click(function() {
//   //   $nav.addClass('open');
//   //   $header.addClass('open');
//   // });

//   // $body_part_side = $('.body-part');
//   // $body_part_side.click(function() {
//   //   $nav.addClass('open');
//   //   $header.addClass('open');
//   // });

//   // var $window = $(window);
//   // var widthwindow = $window.width();

//   // (function($) {
//   //   "use strict";
//   //   if (widthwindow <= 991) {
//   //     $nav.addClass("open");
//   //     $header.addClass('open');
//   //   }
//   // })(jQuery);

//   // var current = window.location.pathname;
//   // $(".sidebar-menu>li a").filter(function() {
//   //   var link = $(this).attr("href");
//   //   if (link) {
//   //     var relativePath = link.replace(window.location.origin, ''); 

//   //     if (current.indexOf(relativePath) !== -1) {
//   //       logActiveClassState($(this)); 
//   //       $(this).parents('li').addClass('active');
//   //       $(this).addClass('active');
//   //     }
//   //   }
//   // });

//   // function logActiveClassState($menuItem) {
//   //   if (!$menuItem || !$menuItem.length) return;

//   //   var link = $menuItem.attr("href");
//   //   var relativePath = link.replace(window.location.origin, '');

//   //   var activeLinks = JSON.parse(localStorage.getItem('activeLinks')) || [];

//   //   if (!activeLinks.includes(relativePath)) {
//   //     activeLinks.push(relativePath);
//   //   }

//   //   localStorage.setItem('activeLinks', JSON.stringify(activeLinks));
//   // }

//   // function scrollToMenuItem($menuItem) {
//   //   if (!$menuItem || !$menuItem.length) return;

//   //   var elem = $menuItem[0];
//   //   var topPos = elem.offsetTop - 50;  
//   //   console.log("fbdcvbfcb",topPos);
//   //   var scrollElement = document.querySelector('html, body'); 

//   //   scrollTo(scrollElement, topPos, 600);
//   // }

//   // function scrollTo(element, to, duration) {
//   //   var start = element.scrollTop,
//   //     change = to - start,
//   //     currentTime = 0,
//   //     increment = 20;

//   //   var animateScroll = function () {
//   //     currentTime += increment;
//   //     var val = Math.easeInOutQuad(currentTime, start, change, duration);
//   //     element.scrollTop = val;
//   //     if (currentTime < duration) {
//   //       setTimeout(animateScroll, increment);
//   //     }
//   //   };
//   //   animateScroll();
//   // }

//   // Math.easeInOutQuad = function (t, b, c, d) {
//   //   t /= d / 2;
//   //   if (t < 1) return (c / 2) * t * t + b;
//   //   t--;
//   //   return (-c / 2) * (t * (t - 2) - 1) + b;
//   // };

//   // function removeFromLocalStorage($menuItem) {
//   //   if (!$menuItem || !$menuItem.length) return;

//   //   var link = $menuItem.attr("href");
//   //   var relativePath = link.replace(window.location.origin, '');

//   //   var activeLinks = JSON.parse(localStorage.getItem('activeLinks')) || [];

//   //   activeLinks = activeLinks.filter(function(item) {
//   //     return item !== relativePath;
//   //   });

//   //   localStorage.setItem('activeLinks', JSON.stringify(activeLinks));
//   // }




// // Sidebar pin-drops
// (function () {
//   const pinTitle = document.querySelector(".pin-title");
//   let pinIcon = document.querySelectorAll("li .fa-solid.fa-thumbtack");
//   function togglePinnedName() {
//     if (document.getElementsByClassName("pined").length) {
//       if (!pinTitle.classList.contains("show")) pinTitle.classList.add("show");
//     } else {
//       pinTitle.classList.remove("show");
//     }
//   }

//   pinIcon.forEach((item, index) => {
//     var linkName = item.parentNode.querySelector("span").innerHTML;
//     var InitialLocalStorage = JSON.parse(localStorage.getItem("pins") || false);

//     if (InitialLocalStorage && InitialLocalStorage.includes(linkName)) {
//       item.parentNode.classList.add("pined");
//     }
//     item.addEventListener("click", (event) => {
//       var localStoragePins = JSON.parse(localStorage.getItem("pins") || false);
//       item.parentNode.classList.toggle("pined");

//       if (localStoragePins?.length) {
//         if (item.parentNode.classList.contains("pined")) {
//           !localStoragePins?.includes(linkName) && (localStoragePins = [...localStoragePins, linkName]);
//         } else {
//           localStoragePins?.includes(linkName) && localStoragePins.splice(localStoragePins.indexOf(linkName), 1);
//         }
//         localStorage.setItem("pins", JSON.stringify(localStoragePins));
//       } else {
//         localStorage.setItem("pins", JSON.stringify([linkName]));
//       }

//       var elem = item;
//       var topPos = elem.offsetTop;
//       togglePinnedName();
//       if (item.parentElement.parentElement.classList.contains("pined")) {
//         scrollTo(document.getElementsByClassName("sidebar-menu")[0], topPos - 30, 600);
//       } else {
//         scrollTo(document.getElementsByClassName("sidebar-menu")[0], elem.parentNode.offsetTop - 30, 600);
//       }
//     });

//     function scrollTo(element, to, duration) {
//       var start = element.scrollTop,
//         change = to - start,
//         currentTime = 0,
//         increment = 20;

//       var animateScroll = function () {
//         currentTime += increment;
//         var val = Math.easeInOutQuad(currentTime, start, change, duration);
//         element.scrollTop = val;
//         if (currentTime < duration) {
//           setTimeout(animateScroll, increment);
//         }
//       };
//       animateScroll();
//     }

//     Math.easeInOutQuad = function (t, b, c, d) {
//       t /= d / 2;
//       if (t < 1) return (c / 2) * t * t + b;
//       t--;
//       return (-c / 2) * (t * (t - 2) - 1) + b;
//     };
//   });
//   togglePinnedName();
// })();








$.sidebarMenu = function (menu) {
  var animationSpeed = 300,
    subMenuSelector = '.sidebar-submenu';
  $(menu).on('click', 'li a', function (e) {
    var $this = $(this);
    var checkElement = $this.next();
    if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
      checkElement.slideUp(animationSpeed, function () {
        checkElement.removeClass('menu-open');
      });
      checkElement.parent("li").removeClass("active");
    }
    else if ((checkElement.is(subMenuSelector)) && (!checkElement.is(':visible'))) {
      var parent = $this.parents('ul').first();
      var ul = parent.find('ul:visible').slideUp(animationSpeed);
      ul.removeClass('menu-open');
      var parent_li = $this.parent("li");
      checkElement.slideDown(animationSpeed, function () {
        checkElement.addClass('menu-open');
        parent.find('li.active').removeClass('active');
        parent_li.addClass('active');
      });
    }
    if (checkElement.is(subMenuSelector)) {
      e.preventDefault();
    }
  });
}

$bg_overlay = $('.bg-overlay')
$.sidebarMenu($('.sidebar-menu'))
$nav = $('.page-sidebar');
$header = $('.page-main-header');
$close_sidebar = $('#sidebar-toggle-btn');
$toggle_nav_top = $('#sidebar-toggle');
$toggle_nav_top.click(function () {
  $this = $(this);
  $nav = $('.page-sidebar');
  $nav.toggleClass('open');
  $header.toggleClass('open');

});
$close_sidebar.click(function () {
  $this = $(this);
  $nav = $('.page-sidebar');
  $nav.toggleClass('open');
  $header.toggleClass('open');
});

$body_part_side = $('.body-part');
$body_part_side.click(function () {
  $nav.addClass('open');
  $header.addClass('open');
  $bg_overlay.addClass('show');

});

//    responsive sidebar
var $window = $(window);
var widthwindow = $window.width();

(function ($) {
  "use strict";
  if (widthwindow <= 991) {
    $nav.addClass("open");
    $header.addClass('open');
    $bg_overlay.removeClass('show');

  }
})(jQuery);


var current = window.location.pathname
$(".sidebar-menu>li a").filter(function () {

  var link = $(this).attr("href");
  if (link) {
    if (current.indexOf(link) != -1) {
      $(this).parents('li').addClass('active');
      $(this).addClass('active');
      console.log(link + " found");
    }
  }
});