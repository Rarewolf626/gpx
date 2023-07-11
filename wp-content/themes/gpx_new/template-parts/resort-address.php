<?php
/** @var stdClass $resort */
$resort = $args['resort'] ?? null;
if(!$resort) return;
?>
<div>
    <span><?=$resort->Address1?></span>
    <?php
    if(!empty($resort->Address2))
        echo "<span>".$resort->Address2."</span>";
    ?>
    <span><?=$resort->Town?>, <?=$resort->Region?> <?=$resort->PostCode?></span>
    <span><?=$resort->Country?></span>
</div>
