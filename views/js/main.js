$(document).ready(() => {
  prestashop.on('updateCart', function () {
    const updateCarousel = () => {

      const data = {
        ajax: 1,
        action: 'UpdateCarousel'
      }
      $.ajax({
        url: GoWidgetUrl,
        data: data,
        async: true,
        type: 'POST',

        success: function (data) {
          $('.product-carousel-wrapper').html(data);
          $('.product-carousel-wrapper').removeClass('hideCarousel');

        }

      })

    }
    updateCarousel();

  });
})
const GoFreeScrollAmount = 300;

function rightScroll() {
  const currentScroll = $('.product-carousel').scrollLeft();
  $('.product-carousel').scrollLeft(currentScroll + GoFreeScrollAmount);
};

function leftScroll() {
  const currentScroll = $('.product-carousel').scrollLeft();

  $('.product-carousel').scrollLeft(currentScroll - GoFreeScrollAmount);
};


function redirectSelf() {
  setTimeout(function () {
    window.location.reload();
  }, 150);
}
