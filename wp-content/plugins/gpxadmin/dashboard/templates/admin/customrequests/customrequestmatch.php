<?php

extract($static);
extract($data);
include $dir.'/templates/admin/header.php';

?>
    <div class="right_col" role="main">
        <div class="">

            <div class="page-title">
                <div class="title_left">
                    <h3>Custom Request Matcher</h3>
                </div>

            </div>

            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2>Custom Request Match Tester</h2>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <form id="form-request-check" action="<?php echo admin_url('admin-ajax.php')?>" method="post">
                                        <input type="hidden" name="action" value="gpx_check_custom_requests">
                                        <div class="form-row">
                                            <div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="debug" id="form-request-debug" value="1" checked>
                                                    <label class="form-check-label" for="form-request-debug">
                                                        Run in debug mode
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="debug" id="form-request-nodebug" value="0">
                                                    <label class="form-check-label" for="form-request-nodebug">
                                                        Run in live mode
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" type="submit">Check for Matches</button>
                                        </div>
                                        <div id="form-request-check-result" style="display:none;white-space:pre;font-family:monospace;background-color:black;color:white;overflow:auto;line-height:1;padding:10px;"></div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('form-request-check').addEventListener('submit', function(e){
            e.preventDefault();
            document.getElementById('form-request-check-result').style.display = "none";
            document.getElementById('form-request-check-result').innerHTML = '';
            jQuery.ajax({
                url:     jQuery(this).attr('action'),
                data:    jQuery(this).serialize(),
                type: 'POST',
                success: function(response){
                    document.getElementById('form-request-check-result').innerHTML = response;
                    document.getElementById('form-request-check-result').style.display = "block";
                }
            });
        });
    </script>

<?php include $dir.'/templates/admin/footer.php';?>
