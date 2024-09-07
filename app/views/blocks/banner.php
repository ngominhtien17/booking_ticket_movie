<div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
  <div class="carousel-inner">
    <?php 
      $i = 1;
      foreach($info as $item) {
        $active = $i == 1 ? 'active' : '';
        echo "<div class='carousel-item $active'>
          <img class='d-block w-100' src='"._WEB_ROOT."/public/assets/client/img/$item->banner_path' alt='...'/>
        </div>";
        $i++;
      }
    ?>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>