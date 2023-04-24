<?php

function gpx_admin_render_email_template(string $content, string $title = null, array $action = null, bool $preview = false)
{
    ob_start();
    require GPXADMIN_PLUGIN_DIR . '/templates/email/layout.php';
    return ob_get_clean();
}

function gpx_email_render_content(string $template, array $data = [], bool $preview = false)
{
    if (!in_array($template, ['custom_request_match', 'custom_request_match_resort', 'custom_request_match_missed','custom_request_match_sixtyday'])) {
        throw new InvalidArgumentException('Not a valid email template');
    }

    $form = "<ul>";
    if (isset($data['resort']) && $data['resort']) {
        $form .= sprintf("<li><strong>Resort:</strong> %s</li>", $data['resort']);
        if (isset($data['nearby']) && $data['nearby']) {
            $form .= "<li><strong>Include Nearby Resort Matches</strong></li>";
        }
    }
    if (isset($data['region']) && $data['region']) {
        $form .= sprintf("<li><strong>Region:</strong> %s</li>", $data['region']);
    }
    if (isset($data['city']) && $data['city']) {
        $form .= sprintf("<li><strong>City/Sub Region:</strong> %s</li>", $data['city']);
    }
    $form .= sprintf("<li><strong>Adults:</strong> %s</li>", $data['adults']);
    $form .= sprintf("<li><strong>Date:</strong> %s%s</li>", $data['checkin'], $data['checkin2'] ? ' - ' . $data['checkin2'] : '');
    $form .= "</ul>";

    if ($template === 'custom_request_match') {
        $title = $data['title'] ?? get_option('gpx_cremailTitle', 'Success!');
        $button = $data['button'] ?? get_option('gpx_cremailButton', 'Review Your Match');
        $content = wpautop($data['content'] ?? get_option('gpx_cremailMessage', ''));
    }
    if ($template === 'custom_request_match_resort') {
        $title = $data['title'] ?? get_option('gpx_crresortmatchemailTitle', 'Success!');
        $button = $data['button'] ?? get_option('gpx_crresortmatchemailButton', 'Review & Confirm Reservation');
        $content = wpautop($data['content'] ?? get_option('gpx_crresortmatchemailMessage', ''));
    }

    if ($template === 'custom_request_match_missed') {
        $title = $data['title'] ?? get_option('gpx_crresortmissedemailTitle', 'Did You Find Another Vacation?');
        $button = $data['button'] ?? get_option('gpx_crresortmissedemailButton', 'Submit a New Request');
        $content = wpautop($data['content'] ?? get_option('gpx_crresortmissedemailMessage', ''));
    }

    if ($template === 'custom_request_match_sixtyday') {
        $title = $data['title'] ?? get_option('gpx_crsixtydayemailTitle', 'Should We Keep Searching?');
        $button = $data['button'] ?? get_option('gpx_crsixtydayemailButton', 'Keep Searching');
        $content = wpautop($data['content'] ?? get_option('gpx_crsixtydayemailMessage', ''));
    }

    $content = str_replace('[FORM]', $form, $content);
    $content = str_replace('[URL]', $data['url'] ?? null, $content);
    $content = str_replace('[weekID]', $data['weekID'] ?? '', $content);
    $content = str_replace('[submitted]', $data['submitted'] ?? '', $content);
    $content = str_replace('[matcheddate]', $data['matcheddate'] ?? '', $content);
    $content = str_replace('[releaseddate]', $data['releaseddate'] ?? '', $content);
    $content = str_replace('[who]', $data['who'] ?? '', $content);

    $rendered = gpx_admin_render_email_template($content, $title, ['label' => $button, 'url' => $data['url']], $preview);

    return $rendered;
}


function gpx_admin_email_preview()
{
    $template = $_POST['template'];
    if (!in_array($template, ['custom_request_match', 'custom_request_match_resort', 'custom_request_match_missed', 'custom_request_match_sixtyday'])) {
        wp_send_json(['success' => false, 'message' => 'Not a valid email template'], 404);
    }
    $rendered = '';
    if ($template === 'custom_request_match') {
        $title = $_POST['title'] ?? get_option('gpx_cremailTitle', 'Success!');
        $button = $_POST['button'] ?? get_option('gpx_cremailButton', 'Review Your Match');
        $content = wpautop($_POST['content'] ?? get_option('gpx_cremailMessage', ''));
        $rendered = gpx_email_render_content($template, [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'region' => 'Region Name',
            'city' => 'City Name',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => '#',
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ], true);
    }

    if ($template === 'custom_request_match_resort') {
        $title = $_POST['title'] ?? get_option('gpx_crresortmatchemailTitle', 'Success!');
        $button = $_POST['button'] ?? get_option('gpx_crresortmatchemailButton', 'Review & Confirm Reservation');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crresortmatchemailMessage', ''));
        $rendered = gpx_email_render_content($template, [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => '#',
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ], true);
    }

    if ($template === 'custom_request_match_missed') {
        $title = $_POST['title'] ?? get_option('gpx_crresortmissedemailTitle', 'Did You Find Another Vacation?');
        $button = $_POST['button'] ?? get_option('gpx_crresortmissedemailButton', 'Submit a New Request');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crresortmissedemailMessage', ''));
        $rendered = gpx_email_render_content($template, [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'region' => '',
            'city' => '',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => get_site_url(null, '/member-dashboard/'),
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ], true);
    }

    if ($template === 'custom_request_match_sixtyday') {
        $title = $_POST['title'] ?? get_option('gpx_crsixtydayemailTitle', 'Should We Keep Searching?');
        $button = $_POST['button'] ?? get_option('gpx_crsixtydayemailButton', 'Keep Searching');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crsixtydayemailMessage', ''));
        $rendered = gpx_email_render_content($template, [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'region' => '',
            'city' => '',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => get_site_url(null, '/member-dashboard/'),
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ], true);
    }

    wp_send_json([
        'success' => true,
        'content' => $rendered,
    ]);
}

add_action('wp_ajax_gpx_admin_email_preview', 'gpx_admin_email_preview');
function gpx_admin_email_test()
{
    $to = sanitize_email($_POST['sendto']);
    $template = $_POST['template'];
    if (!in_array($template, ['custom_request_match', 'custom_request_match_resort', 'custom_request_match_missed', 'custom_request_match_sixtyday'])) {
        wp_send_json(['success' => false, 'message' => 'Not a valid email template'], 404);
    }

    if ($template === 'custom_request_match') {
        $fromName = $_POST['name'] ?? get_option('gpx_cremailName');
        $fromEmail = $_POST['email'] ?? get_option('gpx_cremail');
        $title = $_POST['title'] ?? get_option('gpx_cremailTitle', 'Success!');
        $button = $_POST['button'] ?? get_option('gpx_cremailButton', 'Review Your Match');
        $subject = $_POST['subject'] ?? get_option('gpx_cremailSubject', 'There is a Match! Confirm your Custom Search Request');
        $content = wpautop($_POST['content'] ?? get_option('gpx_cremailMessage', ''));

        $params = [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'region' => 'Region Name',
            'city' => 'City Name',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => '#',
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ];
    }

    if ($template === 'custom_request_match_resort') {
        $fromName = $_POST['name'] ?? get_option('gpx_crresortmatchemailName');
        $fromEmail = $_POST['email'] ?? get_option('gpx_crresortmatchemail');
        $title = $_POST['title'] ?? get_option('gpx_crresortmatchemailTitle', 'Success!');
        $button = $_POST['button'] ?? get_option('gpx_crresortmatchemailButton', 'Review Your Match');
        $subject = $_POST['subject'] ?? get_option('gpx_crresortmatchemailSubject', 'There is a Match! Confirm your Custom Search Request');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crresortmatchemailMessage', ''));
        $params = [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => '#',
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ];
    }

    if ($template === 'custom_request_match_missed') {
        $fromName = $_POST['name'] ?? get_option('gpx_crresortmissedemailName');
        $fromEmail = $_POST['email'] ?? get_option('gpx_crresortmissedemail');
        $title = $_POST['title'] ?? get_option('gpx_crresortmissedemailTitle', 'Did You Find Another Vacation?');
        $button = $_POST['button'] ?? get_option('gpx_crresortmissedemailButton', 'Submit a New Request');
        $subject = $_POST['subject'] ?? get_option('gpx_crresortmissedemailSubject', 'Your Custom Search Request has been Released');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crresortmissedemailMessage', ''));
        $params = [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'region' => '',
            'city' => '',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => get_site_url(null, '/member-dashboard/'),
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ];
    }

    if ($template === 'custom_request_match_sixtyday') {
        $fromName = $_POST['name'] ?? get_option('gpx_crsixtydayemailName');
        $fromEmail = $_POST['email'] ?? get_option('gpx_crsixtydayemail');
        $title = $_POST['title'] ?? get_option('gpx_crsixtydayemailTitle', 'Should We Keep Searching?');
        $button = $_POST['button'] ?? get_option('gpx_crsixtydayemailButton', 'Keep Searching');
        $subject = $_POST['subject'] ?? get_option('gpx_crsixtydayemailSubject', 'No Matches for your Custom Search Request');
        $content = wpautop($_POST['content'] ?? get_option('gpx_crsixtydayemailMessage', ''));
        $params = [
            'title' => $title,
            'button' => $button,
            'content' => $content,
            'resort' => 'Resort Name',
            'nearby' => true,
            'region' => '',
            'city' => '',
            'adults' => 2,
            'checkin' => date('m/d/Y'),
            'checkin2' => date('m/d/Y', strtotime('next week')),
            'url' => get_site_url(null, '/member-dashboard/'),
            'weekID' => '12345',
            'submitted' => date('m/d/Y g:i:s A'),
            'matcheddate' => date('m/d/Y g:i:s A'),
            'releaseddate' => date('m/d/Y g:i:s A'),
            'who' => 'Agent',
        ];
    }

    $rendered = gpx_email_render_content($template, $params, false);

    $sent = wp_mail($to, $subject, $rendered, [
        "Reply-To: " . $fromName . " <" . $fromEmail . ">",
        "Content-Type: text/html; charset=UTF-8",
    ]);

    wp_send_json([
        'success' => $sent,
        'message' => $sent ? 'Message was sent to ' . $to : 'Failed to send test message',
    ]);
}

add_action('wp_ajax_gpx_admin_email_test', 'gpx_admin_email_test');
