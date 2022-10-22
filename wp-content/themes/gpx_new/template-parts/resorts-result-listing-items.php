<?php
/**
 * @var stdClass[] $resorts
 */

$resorts = $resorts ?? [];

foreach($resorts as $resort)
{
?>
<li class="w-item-view filtered" data-subregions='<?=$resort->SubRegion?>' data-resorttype='<?=$resort->ResortType?>'>
    <div class="view">
        <div class="view-cnt">
            <img src="<?=$resort->ImagePath1;?>" alt="<?=$resort->ResortName;?>">
        </div>
        <div class="view-cnt">
            <div class="descrip">
                <hgroup>
                    <h2><?=$resort->ResortName;?></h2>
                    <span><?=$resort->Region.", ".$resort->Country;?></span>
                </hgroup>
                <a href="/resort-profile/?resort=<?=$resort->id?>" class="dgt-btn">View Resort</a>
            </div>
            <div class="w-status">
                <ul class="status">
                	<?php
                	   $status = array('status-exchange'=>'ExchangeWeek','status-rental'=>'BonusWeek');
                	   foreach($status as $key=>$value)
                	   {
                	       if(in_array($value, $resort->WeekType))
                	       {
                	        ?>
                     <li>
                        <div class="<?=$key;?>"></div>
                    </li>
                	        <?php
                	       }
                	   }
                	?>
                </ul>
            </div>
        </div>
    </div>
</li>
<?php
}
?>
