<?php

extract($static);
extract($data);
include $dir . '/templates/admin/header.php';

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
                                <div class="col-sm-6">
                                    <form id="form-request-result" action="<?php echo admin_url('admin-ajax.php') ?>"
                                          method="post">
                                        <input type="hidden" name="action" value="gpx_review_custom_requests">
                                        <?php if ($last_run): ?>
                                            <div class="form-row">
                                                Last run <?php echo $last_run->format('m/d/Y g:i:s A') ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text text-danger">No run data available.</div>
                                        <?php endif; ?>
                                        <div>
                                            <button class="btn btn-primary"
                                                    type="submit" <?php echo $last_run ? '' : 'disabled' ?>>Show Last
                                                Result
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <!--
                                <div class="col-sm-6">

                                    <form id="form-request-check" action="<?php echo admin_url('admin-ajax.php') ?>"
                                          method="post">
                                        <input type="hidden" name="action" value="gpx_check_custom_requests">
                                        <div class="form-row">
                                            <div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="debug"
                                                           id="form-request-debug" value="1" checked>
                                                    <label class="form-check-label" for="form-request-debug">
                                                        Run in debug mode
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="debug"
                                                           id="form-request-nodebug" value="0">
                                                    <label class="form-check-label" for="form-request-nodebug">
                                                        Run in live mode
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" type="submit">Check for Matches</button>
                                        </div>
                                    </form>

                                </div>
                                -->
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div id="form-request-check-result"
                                     style="display:none;white-space:pre;font-family:monospace;background-color:black;color:white;overflow:auto;line-height:1;padding:10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const forms = document.querySelectorAll('#form-request-check,#form-request-result');
        for (let i = 0; i < forms.length; i++) {
            forms[i].addEventListener('submit', function (e) {
                e.preventDefault();
                document.getElementById('form-request-check-result').innerHTML = '<i class="fa fa-spinner fa-spin" style="font-size:48px;"></i>';
                document.getElementById('form-request-check-result').style.display = "block";
                fetch(this.getAttribute('action'), {
                    method: "POST",
                    body: new FormData(this),
                })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (response) {
                        document.getElementById('form-request-check-result').textContent = response;
                        document.getElementById('form-request-check-result').style.display = "block";
                    })
                    .catch(function (error) {
                        console.error(error);
                        document.getElementById('form-request-check-result').innerHTML = '<div class="alert alert-danger">Request failed.</div>'
                            + '<div>' + error.message + '</div>';
                        document.getElementById('form-request-check-result').style.display = "block";
                    });
            });
        }
    })();
</script>

<?php include $dir . '/templates/admin/footer.php'; ?>
