<div class="cookieset"
  <?php 
    foreach($cookie as $key=>$value)
    {
        echo 'data-'.$key.'="'.$value.'"';       
    }
  ?>
 ></div>