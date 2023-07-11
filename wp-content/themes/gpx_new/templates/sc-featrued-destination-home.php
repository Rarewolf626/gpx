<?php /** @var stdClass $prop */ ?>
<li class="w-item">
	<div class="cnt">
		<a href="/resort-profile/?resort=<?=$prop->id?>">
			<figure><img src="<?=$prop->ImagePath1?>" alt="<?=$prop->ResortName?>"></figure>
			<h3><?=$prop->Town?>, <?=$prop->Region?></h3>
			<p><?=$prop->ResortName?></p>
			<div data-link="/resort-profile/?resort=<?=$prop->id?>" class="dgt-btn sbt-btn">Book Now </div>
		</a>
	</div>
</li>
