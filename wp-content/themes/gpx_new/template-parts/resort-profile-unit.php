<?php 
    $unitconfigs = json_decode($resort->UnitConfig);
    if(is_array($unitconfigs))
    {
 ?>
<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Unit Configuration</h4>
</div>
<div class="cnt-list">
<?php 
foreach($configurationsList as $alk=>$alv)
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
