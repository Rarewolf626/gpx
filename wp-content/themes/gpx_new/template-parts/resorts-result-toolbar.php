<?php
/**
* @var array $resorts
 */
?>
<div class="left">
    <h3><?=count($resorts)?>
    <?php
    if(isset($_GET['select_region']))
        echo $resorts[0]->Region.", ".$resorts[0]->Country;
    else
        echo 'Resorts'
    ?>
    </h3>
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
    <a href="" class="dgt-btn call-modal-filter-resort">Filter Result</a>
</div>
