// pre loader
const preloader = document.getElementById("preloader");
// window.addEventListener("load", () => {
//     setTimeout(() => {
//         preloader.style.cssText = `opacity: 0; visibility: hidden;`;
//     }, 1000);
// });

// active nav item
const navItem = document.getElementsByClassName("nav-link");
for (const element of navItem) {
    element.addEventListener("click", () => {
        for (const ele of navItem) {
            ele.classList.remove("active");
        }
        element.classList.add("active");
    });
}

// input file preview
const previewImage = (id) => {
    document.getElementById(id).src = URL.createObjectURL(event.target.files[0]);
};

// toggle sidebar
const setSidebarExpanded = (id, expanded) => {
    document.querySelectorAll(`[aria-controls="${id}"]`).forEach((trigger) => {
        trigger.setAttribute("aria-expanded", String(expanded));
    });
};

window.toggleSidebar = (id) => {
    const element = document.getElementById(id);
    if (!element) return false;
    const expanded = element.classList.toggle("active");
    setSidebarExpanded(id, expanded);
    return expanded;
};
window.openSidebar = (id) => {
    const element = document.getElementById(id);
    if (!element) return false;
    element.classList.add("active");
    setSidebarExpanded(id, true);
    return true;
};
window.removeClass = (id) => {
    const element = document.getElementById(id);
    if (!element) return false;
    element.classList.remove("active");
    setSidebarExpanded(id, false);
    return true;
};

window.toggleSportsMore = (button) => {
    const menuId = button && button.getAttribute("aria-controls");
    const menu = menuId ? document.getElementById(menuId) : null;
    if (!menu) return false;

    const open = menu.hidden;
    menu.hidden = !open;
    button.setAttribute("aria-expanded", String(open));
    return open;
};

document.addEventListener("click", (event) => {
    const categoryBar = document.getElementById("sportsCategoryBar");
    if (!categoryBar || categoryBar.contains(event.target)) return;

    const menu = document.getElementById("moreSportsMenu");
    const button = categoryBar.querySelector(".category-more");
    if (menu && !menu.hidden) menu.hidden = true;
    if (button) button.setAttribute("aria-expanded", "false");
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    const menu = document.getElementById("moreSportsMenu");
    const button = document.querySelector("#sportsCategoryBar .category-more");
    if (menu) menu.hidden = true;
    if (button) button.setAttribute("aria-expanded", "false");
    const rightbar = document.getElementById("rightbar");
    if (rightbar && rightbar.classList.contains("active")) window.removeClass("rightbar");
});

// Admin-managed home promotions: touch-scrollable, keyboard-accessible slides.
document.querySelectorAll("[data-home-promo-carousel]").forEach((carousel) => {
    const track = carousel.querySelector("[data-home-promo-track]");
    const slides = Array.from(carousel.querySelectorAll(".home-promo-slide"));
    const dots = Array.from(carousel.querySelectorAll("[data-promo-slide]"));
    const previous = carousel.querySelector("[data-promo-previous]");
    const next = carousel.querySelector("[data-promo-next]");

    if (!track || slides.length < 2) return;

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let activeIndex = 0;
    let timer = null;
    let scrollFrame = null;
    let paused = false;

    const updateControls = () => {
        dots.forEach((dot, index) => {
            const active = index === activeIndex;
            dot.classList.toggle("is-active", active);
            dot.setAttribute("aria-current", active ? "true" : "false");
        });
    };

    const goTo = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        track.scrollTo({
            left: slides[activeIndex].offsetLeft,
            behavior: prefersReducedMotion ? "auto" : "smooth",
        });
        updateControls();
    };

    const stopTimer = () => {
        if (timer) window.clearInterval(timer);
        timer = null;
    };

    const startTimer = () => {
        stopTimer();
        if (paused || document.hidden) return;
        timer = window.setInterval(() => goTo(activeIndex + 1), 5500);
    };

    if (previous) previous.addEventListener("click", () => {
        goTo(activeIndex - 1);
        startTimer();
    });
    if (next) next.addEventListener("click", () => {
        goTo(activeIndex + 1);
        startTimer();
    });
    dots.forEach((dot) => dot.addEventListener("click", () => {
        goTo(Number(dot.dataset.promoSlide));
        startTimer();
    }));

    track.addEventListener("scroll", () => {
        if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
        scrollFrame = window.requestAnimationFrame(() => {
            const trackLeft = track.getBoundingClientRect().left;
            let closestIndex = 0;
            let closestDistance = Infinity;

            slides.forEach((slide, index) => {
                const distance = Math.abs(slide.getBoundingClientRect().left - trackLeft);
                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = index;
                }
            });

            activeIndex = closestIndex;
            updateControls();
        });
    }, { passive: true });

    carousel.addEventListener("pointerenter", () => {
        paused = true;
        stopTimer();
    });
    carousel.addEventListener("pointerleave", () => {
        paused = false;
        startTimer();
    });
    carousel.addEventListener("focusin", () => {
        paused = true;
        stopTimer();
    });
    carousel.addEventListener("focusout", (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            paused = false;
            startTimer();
        }
    });
    document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopTimer();
        else startTimer();
    });

    startTimer();
});

// // dark mode
// const darkMode = () => {
//     document.body.classList.toggle("dark-mode");
// };

$(document).ready(function () {
    // owl carousel
    $(".testimonials").owlCarousel({
        loop: true,
        margin: 25,
        nav: false,
        dots: true,
        autoplay: true,
        autoplayTimeout: 3000,
        responsive: {
            0: {
                items: 1,
            },
            768: {
                items: 2,
            },
            992: {
                items: 3,
            },
        },
    });
    $(".live-matches-slider").owlCarousel({
        loop: true,
        margin: 15,
        nav: false,
        dots: true,
        autoplay: true,
        autoplayTimeout: 3000,
        responsive: {
            0: {
                items: 1,
            },
            768: {
                items: 2,
            },
            992: {
                items: 2,
            },
            1200: {
                items: 4,
            },
        },
    });

    // AOS ANIMATION
    // AOS.init();

    // COUNTER UP
    // $(".counter").counterUp({
    //    delay: 10,
    //    time: 3000,
    // });

    // SCROLL TOP
    $(".scroll-up").fadeOut();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $(".scroll-up").fadeIn();
        } else {
            $(".scroll-up").fadeOut();
        }
    });
});

// horizontal scroll
// const element = document.querySelector("#categories");
// element.addEventListener("wheel", (event) => {
//     event.preventDefault();
//     element.scrollBy({
//         left: event.deltaY < 0 ? -30 : 30,
//     });
// });
$(document).on('submit', '#login-form', function (e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var formData = new FormData($(this)[0]);

    $('.emailError').text(``);
    $('.usernameError').text(``);
    $('.passwordError').text(``);

    $.ajax({
        type: "post",
        url: url,
        data: formData,
        cache: false,
        async: false,
        processData: false,
        contentType: false,
        success: function (data) {
            $('.login-auth-btn').html(`<i class="fa fa-spinner"></i> Processing..`);
            setTimeout(function () {
                location.href = data;
                $('.login-auth-btn').text(`Success`);
            }, 2000);
        },
        error: function (res) {
            if (res.status == 422) {
                $('.emailError').text(res.responseJSON.errors.email);
                $('.usernameError').text(res.responseJSON.errors.username);
                $('.passwordError').text(res.responseJSON.errors.password);
            }
            if (res.status == 429) {
                $('.emailError').text(res.responseJSON.errors.email);
                $('.usernameError').text(res.responseJSON.errors.username);
            }
            else if (res.status == 401) {
                $('.usernameError').text(res.responseJSON);
            }
        }
    })
});
$(document).on('submit', '#signup-form', function (e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var formData = new FormData($(this)[0]);
    $('.text-danger').text(``);

    $.ajax({
        type: "post",
        url: url,
        data: formData,
        cache: false,
        async: false,
        processData: false,
        contentType: false,
        success: function (data) {


            $('.login-signup-auth-btn').html(`<i class="fa fa-spinner"></i> Loading`);
            setTimeout(function () {
                location.href = data;
                $('.login-signup-auth-btn').text(`Success`);
            }, 2000);
        },
        error: function (res) {
            if (res.status == 422) {
                $('.firstnameError').text(res.responseJSON.errors.firstname);
                $('.lastnameError').text(res.responseJSON.errors.lastname);
                $('.usernameError').text(res.responseJSON.errors.username);
                $('.emailError').text(res.responseJSON.errors.email);
                $('.phoneError').text(res.responseJSON.errors.phone);
                $('.passwordError').text(res.responseJSON.errors.password);
            }
            if (res.status == 429) {
                $('.emailError').text(res.responseJSON.errors.email);
                $('.usernameError').text(res.responseJSON.errors.username);
            }
        }
    })
});
