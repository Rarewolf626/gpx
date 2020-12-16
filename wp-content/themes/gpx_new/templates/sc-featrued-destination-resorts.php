<li class="w-item">
	<div class="cnt">
		<a href="/resort-profile/?resort=<?=$prop->id?>">
			<figure><img src="<?=$prop->ImagePath1?>" alt="<?=$prop->ResortName?>"></figure>
			<div class="text">
    			<h3><?=$prop->ResortName?></h3>
    			<?php 
    			//cap
    			?>
    			<h4><?=ucfirst($prop->Town)?>, <?=$prop->Region?></h4>
    			<p><?=$prop->Country?></p>
			</div>
			<a href="/resort-profile/?resort=<?=$prop->id?>" class="dgt-btn">Explore</a>
		</a>
	</div>
</li>
