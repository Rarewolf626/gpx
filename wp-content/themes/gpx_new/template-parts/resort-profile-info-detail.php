<div class="info-detail">
    <ul class="details">
        <li>
            <p><strong>Address:</strong></p>
            <p>
              <?php if(!empty($maplink))
              {
              ?>
              <a href="<?=$maplink?>" target="_blank">
              <?php 
              }
              ?>
                <span><?=$resort->Address1?></span>
                <?php 
                    if(!empty($resort->Address2))
                        echo "<span>".$resort->Address2."</span>";
                ?>
                <span><?=$resort->Town?>, <?=$resort->Region?> <?=$resort->PostCode?></span>
                <span><?=$resort->Country?></span>
               <?php if(!empty($maplink))
              {
              ?>
              </a>
              <?php 
              }
              ?>
            </p>
        </li>
        <li>
            <p><strong>Website:</strong></p>
            <?php 
            $url = $resort->Website;
            if(substr($resort->Website, 0, 4) != 'http')
                $url = 'http://'.$resort->Website;
            $link = preg_replace("(^https?://)", "", $resort->Website );
            ?>
            <p><a href="<?=$url?>" target="_blank"><?=$link?></a></p>
        </li>
        <li>
            <p><strong>Phone:</strong></p>
            <p><a href="tel:<?=$resort->Phone?>"><?=$resort->Phone?></a></p>
        </li>
        <li>
            <p><strong>Fax:</strong></p>
            <p><?=$resort->Fax?></p>
        </li>
        <li>
            <p><strong>Closest Airport:</strong></p>
            <p><?=$resort->Airport?></p>
        </li>
        <li>
            <p><strong>Check In: <?=$resort->CheckInDays?></strong></p>
            <p>Earliest: <?=$resort->CheckInEarliest?></p>
            <p>Latest: <?=$resort->CheckInLatest?></p>
        </li>
        <li>
            <p><strong>Check Out:</strong></p>
            <p>Earliest: <?=$resort->CheckOutEarliest?></p>
            <p>Latest: <?=$resort->CheckOutLatest?></p>
        </li>
    </ul>
<?php 
if(isset($taURL))
{
?>
    <div class="ta-badge">
    	<p><a href="<?=$taURL?>" class="ta-link" target="_blank"><strong><?=$resort->ResortName?></strong></a></p>
    	<p>TripAdvisor Traveler Rating</p>
    	<p><a href="<?=$taURL?>" target="_blank"><img class="ta-star" src="/wp-content/themes/gpx_new/images/ta-stars<?=$starsclass?>.png" alt="<?=$starclass?>><br><span style="text-decoration: underline;"><?=$reviews?> Reviews</span></a></p>
    	<p><a href="<?=$taURL?>" target="_blank"><img src="/wp-content/themes/gpx_new/images/ta_logo.png" alt="TripAdvisor"></a></p>
    </div>
<?php 
}
?>
</div>