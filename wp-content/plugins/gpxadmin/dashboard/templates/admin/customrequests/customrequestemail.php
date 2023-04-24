<?php

extract($static);
extract($data);
include $dir . '/templates/admin/header.php';

?>
<div class="right_col" role="main">
    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3>Custom Request Email</h3>
            </div>
            <?php if (current_user_can('administrator_plus')): ?>
                <div class="pull-right">
                    <h5>Send Emails
                        <?php
                        $gfActive = get_option('gpx_global_cr_email_send');
                        if ($gfActive == 1) {
                            ?>
                            <span class="badge btn-success" id="activeCREmail" data-active="0">Active</span>
                            <?php
                        } else {
                            ?>
                            <span class="badge btn-danger" id="activeCREmail" data-active="1">Inactive</span>
                            <?php
                        }
                        ?>
                    </h5>
                </div>
            <?php endif; ?>
        </div>

        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Edit Custom Request Email</h2>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <form id="custom-request-form" method="POST">
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-xs-12">
                                            <label for="crEmail">Email From</label>
                                            <input id="crEmail" type="email" class="form-control" name="email"
                                                   value="<?= $cremail; ?>" placeholder="From Email" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-xs-12">
                                            <label for="crEmailName">Email From Name</label>
                                            <input id="crEmailName" type="text" class="form-control" name="name"
                                                   value="<?= $cremailName; ?>" placeholder="From Email Name" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-xs-12">
                                            <label for="crEmailSubject">Subject</label>
                                            <input id="crEmailSubject" type="text" class="form-control" name="subject"
                                                   value="<?= $cremailSubject; ?>" placeholder="Email Subject" required>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-xs-12">
                                            <label for="cremailTitle">Title</label>
                                            <input id="cremailTitle" type="text" class="form-control" name="title"
                                                   value="<?= esc_attr($cremailTitle); ?>" placeholder="Email Title">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-sm-12">
                                            <label for="cremailButton">Button Label</label>
                                            <input id="cremailButton" type="text" class="form-control" name="button"
                                                   value="<?= esc_attr($cremailButton); ?>" placeholder="Button Label"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <label for="customrequestemail">Message</label>
                                            <?php wp_editor($cremailMessage, 'customrequestemail', array('textarea_name' => 'content')); ?>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-xs-12">
                                            <input type="submit" name="submit-custom-request-email"
                                                   class="btn btn-primary" value="Update">
                                            <input type="button" id="email-preview" class="btn btn-warning"
                                                   value="Preview">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <h4>Placeholders</h4>
                                            <ul>
                                                <li>[FORM]</li>
                                                <li>[URL]</li>
                                                <li>[weekID]</li>
                                                <li>[submitted]</li>
                                                <li>[matcheddate]</li>
                                                <li>[releaseddate]</li>
                                                <li>[who]</li>
                                            </ul>
                                        </div>
                                        <div class="col-sm-6">
                                            <h4>Send Test Email</h4>
                                            <div id="send-result"></div>
                                            <div style="margin-bottom:20px;">
                                                <label for="send-to">Send To:</label>
                                                <input id="send-to" type="email" class="form-control" name="sendto"
                                                       value="<?= esc_attr(get_userdata(get_current_user_id())->user_email); ?>">
                                            </div>
                                            <div>
                                                <button id="send-test" class="btn btn-info" type="button">
                                                    Send Test
                                                    <span></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="preview-window"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<script>
    document.getElementById('email-preview').addEventListener('click', function (e) {
        e.preventDefault();
        const preview = document.getElementById('preview-window');
        preview.innerHTML = '<div style="text-align:center;padding:20px;"><i style="font-size:100px;" class="fa fa-spinner fa-spin"></i></div>';
        const form = document.getElementById('custom-request-form');
        const $form = new FormData(form);
        $form.append('action', 'gpx_admin_email_preview');
        $form.append('template', 'custom_request_match');
        fetch("<?= admin_url('admin-ajax.php')?>", {
            method: 'POST',
            body: $form,
        })
            .then(response => response.json())
            .then(data => {
                const iframe = document.createElement("iframe");
                iframe.setAttribute('style', 'width:100%;height:500px;margin:20px auto;');
                preview.innerHTML = '';
                preview.appendChild(iframe);
                let frameDoc = iframe.contentWindow ? iframe.contentWindow.document : iframe.document;
                frameDoc.open();
                frameDoc.writeln(data.content);
                frameDoc.close();
            })
            .catch(error => {
                preview.innerHTML = '<div class="alert alert-danger">Failed to render email preview</div>';
            })
    });
    document.getElementById('send-test').addEventListener('click', function (e) {
        e.preventDefault();
        const button = document.getElementById('send-test');
        button.setAttribute('disabled', 'disabled');
        button.querySelector('span').innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        const result = document.getElementById('send-result');
        result.innerHTML = '';
        const form = document.getElementById('custom-request-form');
        const $form = new FormData(form);
        $form.append('action', 'gpx_admin_email_test');
        $form.append('template', 'custom_request_match');
        fetch("<?= admin_url('admin-ajax.php')?>", {
            method: 'POST',
            body: $form,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    result.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                } else {
                    result.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                result.innerHTML = '<div class="alert alert-danger">Failed to send test email</div>';
            })
            .finally(() => {
                button.removeAttribute('disabled');
                button.querySelector('span').innerHTML = '';
            })
    });

</script>


<?php include $dir . '/templates/admin/footer.php'; ?>
