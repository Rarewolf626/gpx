    <ul class="w-list w-list-items">
    <?php foreach($props as $prop): ?>
        <?php $thumb = "http://daelive.com/articles/profileimg.ashx?EndpointID=".$prop->WeekEndpointID."&ResortID=".$prop->resortId."&No=1";?>
       <li class="w-item">
            <div class="cnt">
                <a href="">
                    <figure>
                        <img src="<?=$thumb; ?>" alt="El Dorado Casitas a Gourmet Inclusive Resort">
                    </figure>
                </a>
                <div class="text">
                    <h3><a href=""><?=$prop->resortName;?></a></h3>
                    <h4><strong><?=$prop->country;?></strong></h4>
                    <p>Lorem ipsum dolor sit amet, dolor consectetur adipisicing elit, sed do eiusmod tempor incididunt amet...consectetur adipisicing elit sed do. </p>
                </div>
                <a class="dgt-btn">
                   Explore 
                </a>
            </div>
        </li>   
    <?php endforeach; ?>