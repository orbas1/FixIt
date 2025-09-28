// Hide header on scroll down js
$(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
        $('header').addClass('active')
    } else {
        $('header').removeClass('active')
    }
});

/// Language Select ///
const lang = document.querySelectorAll(".onhover-show-div .lang")
const languageSelected = document.querySelector("#languageSelected span")
for (var i = 0; i < lang.length; ++i) {
    lang[i].addEventListener('click', function () {
        languageSelected.innerHTML = this.innerHTML;
    })
}

/// Unit Select ///
const unit = document.querySelectorAll(".onhover-show-div .currency")
const unitSelected = document.querySelector("#unitSelected span")
for (var i = 0; i < unit.length; ++i) {
    unit[i].addEventListener('click', function () {
        unitSelected.innerHTML = this.innerHTML;
    })
}

/// Location Select ///
const area = document.querySelectorAll(".onhover-show-div .location")
const areaSelected = document.querySelector("#locationSelected span")
for (var i = 0; i < area.length; ++i) {
    area[i].addEventListener('click', function () {
        areaSelected.innerHTML = this.innerHTML;
    })
}

/*=====================
Image to background js
==========================*/
$(".bg-top").parent().addClass("b-top");
$(".bg-bottom").parent().addClass("b-bottom");
$(".bg-center").parent().addClass("b-center");
$(".bg-left").parent().addClass("b-left");
$(".bg-right").parent().addClass("b-right");
$(".bg_size_content").parent().addClass("b_size_content");
$(".bg-img").parent().addClass("bg-size");
$(".bg-img.blur-up").parent().addClass("blur-up lazyload");
$(".bg-img").each(function () {
    var el = $(this),
        src = el.attr("src"),
        parent = el.parent();

    parent.css({
        "background-image": "url(" + src + ")",
        "background-size": "cover",
        "background-position": "center",
        "background-repeat": "no-repeat",
        display: "block",
    });
    el.hide();
});


// select phone dropdown
document.querySelector('.select-btn')?.addEventListener('click', function () {
    document.querySelector('.select-show-div').classList.toggle('active');
});

document.querySelectorAll('.select-show-div-item').forEach(function (item) {
    item.addEventListener('click', function () {
        var itemValue = this.getAttribute('data-value');

        console.log(itemValue);

        var buttonSpan = document.querySelector('.select-btn span');
        buttonSpan.innerHTML = '';
        var clonedContent = this.cloneNode(true);
        buttonSpan.appendChild(clonedContent);

        buttonSpan.parentNode.setAttribute('data-value', itemValue);

        document.querySelector('.select-show-div').classList.toggle('active');
    });
});


// close footer menu

document.querySelector('.btn-close')?.addEventListener('click', function () {
    document.querySelector('.navbar-collapse').classList.remove('show');
});


document.querySelectorAll(".nav-folderized h3").forEach(function (element) {
    element.addEventListener("click", function () {
        var parentNav = this.closest(".nav");
        if (parentNav) {
            parentNav.classList.toggle("open");
            var offsetTop = this.getBoundingClientRect().top + window.scrollY - 170;
            window.scrollTo({
                top: offsetTop,
                behavior: "smooth"
            });
        }
    });
});

// sidebar filter open
// document.querySelector('.filter-btn')?.addEventListener('click', function () {
//     document.querySelector('.filter').classList.toggle('open');
// });

$(".filter-btn").click(function () {
    $(".filter").addClass("open");
});

$(".filter-close").click(function () {
    $(".filter").removeClass("open");
});

// select radio button
document.querySelectorAll(".action .radio").forEach(function (radio) {
    radio.addEventListener("change", function () {
        var btns = document.querySelectorAll('.action .btn');
        btns.forEach(function (btn) {
            if (btn !== this.nextElementSibling) {
                btn.textContent = "Select this";
            }
        }, this);

        if (this.checked) {
            this.nextElementSibling.textContent = "Selected";
        } else {
            this.nextElementSibling.textContent = "Select this";
        }
    });
    if (radio.checked) {
        radio.nextElementSibling.textContent = "Selected";
    } else {
        radio.nextElementSibling.textContent = "Select this";
    }
});

// loading code start
var contentLoaded = false;
function setContentLoaded() {
    contentLoaded = true;
}
document.addEventListener("DOMContentLoaded", function () {
    setContentLoaded();
    setTimeout(fadeout, 100);
});
function fadeout() {
    var loader = document.getElementById('loader');
    if (contentLoaded) {
        loader.style.display = "none";
        removeNotLoadedClass();
        return;
    }

    setTimeout(function () {
        loader.style.transition = "opacity 1s";
        loader.style.opacity = 0;
        setTimeout(function () {
            loader.style.display = "none";
            removeNotLoadedClass();
        }, 300);
    }, 1000);
}
function removeNotLoadedClass() {
    var notLoadedElements = document.querySelectorAll('.notLoaded');
    notLoadedElements.forEach(function (element) {
        element.classList.remove('notLoaded');
    });
}
// $('#user-table').DataTable( {
//     responsive: true
// } );

// var contentLoaded = false;

// function setContentLoaded() {
//     contentLoaded = true;
// }

// document.addEventListener("DOMContentLoaded", function () {
//     setContentLoaded();
//     fadeout(); // Start fading out as soon as DOM is ready
// });

// // This will check the loading state and fade out the loader
// function fadeout() {
//     var loader = document.getElementById('loader');

//     if (contentLoaded) {
//         // Add fade-out class to start the transition
//         loader.classList.add('fade-out');
//         // Add the 'loaded' class to the body to reveal the content
//         document.body.classList.add('loaded');
//     } else {
//         // Wait a bit and check again if the content is not loaded yet
//         setTimeout(fadeout, 500);
//     }
// }






// Filter Sidebar Toggle
// const element = document.querySelector('.filter');
// const addButton = document.querySelector('.filter-btn');
// const removeButton = document.querySelector('.close-box');

// addButton.addEventListener('click', () => {
//     element.classList.add('open');
// });

// removeButton.addEventListener('click', () => {
//     element.classList.remove('open');
// });

// ==================

const locationBox = document.getElementById('locationBox');
const addBtn = document.getElementById('add-btn');
const removeBtn = document.getElementById('remove-btn');

addBtn.addEventListener('click', () => {
    locationBox.classList.add('show');
})

/*====================
filter sidebar js
=======================*/
// const filterButton = document.querySelector(".filter-btn");
// const filterSideBar = document.querySelector(".filter-sidebar");
// const filter = document.querySelector(".filter");
// const closeBtns = document.querySelectorAll(".filter-close"); // Select all close buttons

// // Add class to the element
// filterButton.addEventListener("click", function () {
//     filterSideBar.classList.add("open");
// });

// // Loop through each close button and add event listener
// closeBtns.forEach(function (closeBtn) {
//     closeBtn.addEventListener("click", function () {
//         filterSideBar.classList.remove("open");
//         filter.classList.remove("open");
//     });
// });

// /*====================
// profile sidebar js
// =======================*/
// const showButton = document.querySelector(".show-btn");
// const profileSideBar = document.querySelector(".profile-sidebar");
// const closeButton = document.querySelectorAll(".close"); // Select all close buttons

// // Add class to the element
// showButton.addEventListener("click", function () {
//     profileSideBar.classList.add("open");
// });

// // Loop through each close button and add event listener
// closeButton.forEach(function (closeButton) {
//     closeButton.addEventListener("click", function () {
//         profileSideBar.classList.remove("open");
//     });
// });
