<div class="container mt-3">

  <!-- Form -->
    {$form}

  <!-- Minimal Price -->

  <!-- Category List -->
  <div class="category-list mt-4">
    <ul class="list-group list-group-flush">
      <h3 class="text-primary">{l s='Categories' d='Modules.GoFreedelivery.Shop'}</h3>
        {foreach from=$categories item=category}
          <li class="list-group-item">
              {$category}
          </li>
        {/foreach}
    </ul>
  </div>

  <!-- Error Alerts -->
    {if isset($errors) && $errors}
      <div class="alert alert-danger">
          {foreach from=$errors item=error}
              {$error}<br>
          {/foreach}
      </div>
    {/if}

  <!-- Success Alerts -->
    {if isset($success) && $success}
      <div class="alert alert-success">
          {$success}
      </div>
    {/if}
</div>



<div class="panel">
  <div class="panel-heading"><a href="https://grupago.pl/"><img src="/modules/go_freedelivery/logo.svg"></a></div>


  <div class="alert alert-info">
    <b>GRUPAGO</b> Specjalizujemy się w sklepach opartych na platformie Prestashop. Szczególnie w:<br>
    <ul>
      <li>Modyfikacji backendu oraz frontendu sklepów</li>
      <li>Tworzeniu dedykowanych modułów oraz modyfikacji istniejących</li>
      <li>Integracji sklepów z zewnętrznymi systemiami</li>
      <li>Optymalizacjami wydajnościowymi</li>
      <li>Optymalizacjami SEO</li>
      <li>Marketingiem internetowym</li>
    </ul>
    <br>
    Jeśli potrzebują Państwo spersonalizowanych rozwiązań lub zmian w swoim sklepie,<br>
    serdecznie zachęcamy do kontaktu pod adresem
    <a style="color: #000;" href="mailto:biuro@grupago.pl">biuro@grupago.pl</a>
    lub numerem telefonu<a style="color: #000;" href="tel:+48665875342"> +48 665 875 342</a> <br><b>Zespół GRUPAGO Rafał Senetra</b>
  </div>

</div>
