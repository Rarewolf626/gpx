<?php include 'header.php'?>
<div class="right_col" role="main">
    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3><?= $title ?? 'Page Not Found' ?></h3>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12">

                <div class="x_content">
                    <?php if($message ?? null):?>
                        <div><?= $message ?></div>
                    <?php else: ?>
                        <p>The page you requested could not be found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?php include 'footer.php'?>
