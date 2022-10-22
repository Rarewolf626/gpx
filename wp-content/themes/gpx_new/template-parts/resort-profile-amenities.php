<?php
/**
 * @var array $ammenitiesList
 */

$showThisAttrList = false;
foreach($ammenitiesList as $alk=>$alv)
{
    if(isset($resort->$alk))
    {
        $showThisAttrList = true;
        break;
    }
}
if($showThisAttrList)
{
?>

<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Amenities</h4>
</div>
<div class="cnt-list flex-list">
<?php
foreach($ammenitiesList as $alk=>$alv)
{
    if(isset($resort->$alk))
    {
?>
	<ul class="list-cnt">
		<li>
			<p><strong><?=$alv?></strong>
		</li>
<?php

        $amms = json_decode($resort->$alk);
        foreach($amms as $amm)
        {
        ?>
    	<li>
    		<p><?=$amm?></p>
    	</li>
        <?php
        }
?>
	</ul>
<?php
    }
}
?>
</div>
<?php
}
?>
