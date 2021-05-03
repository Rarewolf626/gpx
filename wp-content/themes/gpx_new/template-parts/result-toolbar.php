<div class="left">
<?php
echo '<pre>'.print_r($props, true).'</pre>';
  $cntResults = 0;
  if(!empty($props))
      $cntResults = count($props);
?>
    <h3><?=$cntResults?> Search Results</h3>
</div>
<div class="right">
    <ul class="status">
        <li>
            <div class="status-all">
                <p>All-Inclusive</p>
            </div>
        </li>
        <li>
            <div class="status-exchange">
                <p>Exchange</p>
            </div>
        </li>
        <li>
            <div class="status-rental">
                <p>Rental</p>
            </div>
        </li>
    </ul>
    <a href="" class="dgt-btn call-modal-filter">Filter Results</a>
</div>