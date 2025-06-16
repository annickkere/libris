// Initialize Books Swiper with looping, autoplay, and responsive breakpoints
var swiper = new Swiper(".books-slider", {
    loop: true,
    autoplay:{
        delay: 9500,
        disableOnInteraction: false,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 5,
      },
    },
});

// Hiding Search Icon when search bar contains text
let searchBox = document.getElementById("search-box");
let searchLabel = document.querySelector(".fas.fa-search");
searchBox.addEventListener("input", () => {
  if (searchBox.value.trim() !== "") {
    searchLabel.classList.add("inactive");
  } else {
    searchLabel.classList.remove("inactive");
  }
});