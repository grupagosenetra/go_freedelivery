{literal}
  <style>
    .product-carousel-wrapper {
      position: relative;
    }

    .product-carousel {
      display: flex;
      overflow-x: scroll; /* Umożliwia przewijanie poziome */
      scroll-behavior: smooth;
      white-space: nowrap; /* Zapobiega przenoszeniu produktów do nowej linii */
    }

    .product {
      display: flex; /* Ustawia flexbox dla kontenera produktu */
      flex-direction: column; /* Ustawia elementy pionowo */
      align-items: center; /* Wyśrodkowuje elementy w poziomie */
      text-align: center; /* Wyśrodkowuje tekst */
      max-width: 300px; /* Maksymalna szerokość produktu */
      margin-right: 15px; /* Odstęp między produktami */
    }

    .product .product-name,
    .product .product-price,
    .product .product-description {
      white-space: normal; /* Pozwala na zawijanie tekstu */
      overflow: hidden; /* Zapobiega wychodzeniu tekstu poza element */
      text-overflow: ellipsis; /* Dodaje trzy kropki na końcu tekstu, jeśli jest za długi */
      max-width: 100%; /* Ogranicza szerokość tekstu do szerokości kontenera */
      word-wrap: break-word; /* Złamuje długie słowa, jeśli są za długie */
    }


    .arrow {
      position: absolute;
      top: 50%;
      cursor: pointer;
      /* Dodaj więcej stylów dla strzałek */
    }

    .product-carousel-header {
      margin-bottom: 20px; /* Odstęp od karuzeli */
      margin-top: 20px; /* Odstęp od karuzeli */
    }

    .left {
      left: 0;
    }

    .right {
      right: 0;
    }
    .hideCarousel{
       visibility: hidden;
    }
  </style>
{/literal}

<!-- product-carousel.tpl -->
<div class="product-carousel-wrapper {if $viewProductToFree != 1} hideCarousel {/if}" >
    {if $viewProductToFree == 1}

  <!-- Napis "Propozycja produktów" -->
  <div class="product-carousel-header">
    <h2>{l s='Example free delivery products header' d='Modules.GoFreedelivery.Shop'}</h2>
  </div>
  <div class="product-carousel">
      {foreach from=$products item=product}
        <div class="product">
          <div class="product-top">
            <a href="{$product.link}">
              <img class="product-image"
                   src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}"
                   alt="{$product.name}"/>
            </a>
          </div>
          <div class="product-bottom">
            <p class="product-name">{$product.name}</p>
            <p class="product-price">{Tools::displayPrice($product.price)}</p>

            <form action="{$urls.pages.cart}" method="post" class="add-to-cart-or-refresh">
              <input type="hidden" name="token" value="{$static_token}">
              <input type="hidden" name="add" value="1">
              <input type="hidden" name="id_product" value="{$product.id_product}" class="product_page_product_id">
              <input type="hidden" name="id_customization" value="0" class="product_customization_id">
              <button class="btn btn-primary add-to-cart" data-button-action="add-to-cart" onclick="redirectSelf()"
                      type="submit">{l s='Add to cart' d='Shop.Theme.Actions'}</button>

            </form>
          </div>
        </div>
      {/foreach}

  </div>
  <div class="arrow left" onclick="leftScroll()">←</div>
  <div class="arrow right" onclick="rightScroll()">→</div>
    {/if}

</div>

