<?php


/**
 *
 *
 *
 *
 */
function send_welcome_email_by_resort()
{
    global $wpdb;

    $resortID4Owner = substr($_POST['resort'], 0, 15);
    $sql = $wpdb->prepare("SELECT DISTINCT userID FROM wp_owner_interval WHERE resortID=%s", $resortID4Owner);
    $allOwners = $wpdb->get_results($sql);


    $sent = [];
    $data = [];
    foreach($allOwners as $ao)
    {
        $sql = $wpdb->prepare("SELECT umeta_id  FROM wp_usermeta WHERE meta_key='welcome_email_sent' AND user_id=%s", $ao->userID);
        $row = $wpdb->get_var($sql);
        if(empty($row))
        {
            $sendemail = send_welcome_email($ao->userID);
            $sent[] = 1;
            update_user_meta($ao->userID, 'welcome_email_sent', '1');

            //for testing we want to output the email address to the screen
            $sql = $wpdb->prepare("SELECT SPI_Email__c, SPI_Owner_Name_1st__c FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $ao->userID);
            $row = $wpdb->get_row($sql);

            $allEmails[] = $row->SPI_Email__c;
        }
    }

    $data['emails'] = implode("<br />", $allEmails);
    $data['message'] = count($sent).' emails sent!';

    wp_send_json($data);
}
add_action('wp_ajax_send_welcome_email_by_resort', 'send_welcome_email_by_resort');






/**
 *
 *
 *
 *
 */
function send_welcome_email($cid = '')
{
    global $wpdb;

    $returnFalse = false;
    if(!empty($cid))
    {
        $returnFalse = true;
        $_REQUEST['cid'] = $cid;
    }
    $id = $_REQUEST['cid'];

    $sql = $wpdb->prepare("SELECT SPI_Email__c, SPI_Owner_Name_1st__c FROM wp_GPR_Owner_ID__c WHERE user_id=%s", $id);
    $row = $wpdb->get_row($sql);

    $name = $row->SPI_Owner_Name_1st__c;
    $email = \GPX\Repository\OwnerRepository::instance()->get_email($id);

    /*
     * TODO  create the link for the email
     */

    $hashKey = wp_generate_password(10, false);

    update_user_meta($id, 'gpx_upl_hash', $hashKey);
    $url = get_site_url().'?welcome='.$hashKey;

    $headers = array('Content-Type: text/html; charset=UTF-8');

    $msg = 'Message TBD. <a href="'.$url.'">Click here to create account.</a>';
    $msg = '<body bgcolor="#FAFAFA" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; height: 100% !important; width: 100% !important; background-color: #FAFAFA; margin: 0; padding: 0;">
<style type="text/css">#outlook a {
              padding: 0;
          }
          .body{
              width: 100% !important;
              -webkit-text-size-adjust: 100%;
              -ms-text-size-adjust: 100%;
              margin: 0;
              padding: 0;
          }
          .ExternalClass {
              width:100%;
          }
          .ExternalClass,
          .ExternalClass p,
          .ExternalClass span,
          .ExternalClass font,
          .ExternalClass td,
          .ExternalClass div {
              line-height: 100%;
          }
          img {
              outline: none;
              text-decoration: none;
              -ms-interpolation-mode: bicubic;
          }
          a img {
              border: none;
          }
          p {
              margin: 1em 0;
          }
          table td {
              border-collapse: collapse;
          }
          /* hide unsubscribe from forwards*/
          blockquote .original-only, .WordSection1 .original-only {
            display: none !important;
          }

          @media only screen and (max-width: 480px){
            body, table, td, p, a, li, blockquote{-webkit-text-size-adjust:none !important;} /* Prevent Webkit platforms from changing default text sizes */
                    body{width:100% !important; min-width:100% !important;} /* Prevent iOS Mail from adding padding to the body */

            #bodyCell{padding:10px !important;}

            #templateContainer{
              max-width:600px !important;
              width:100% !important;
            }

            h1{
              font-size:24px !important;
              line-height:100% !important;
            }

            h2{
              font-size:20px !important;
              line-height:100% !important;
            }

            h3{
              font-size:18px !important;
              line-height:100% !important;
            }

            h4{
              font-size:16px !important;
              line-height:100% !important;
            }

            #templatePreheader{display:none !important;} /* Hide the template preheader to save space */

            #headerImage{
              height:auto !important;
              max-width:600px !important;
              width:100% !important;
            }

            .headerContent{
              font-size:20px !important;
              line-height:125% !important;
            }

            .bodyContent{
              font-size:18px !important;
              line-height:125% !important;
            }

            .templateColumnContainer{display:block !important; width:100% !important;}

            .columnImage{
              height:auto !important;
              max-width:480px !important;
              width:100% !important;
            }

            .leftColumnContent{
              font-size:16px !important;
              line-height:125% !important;
            }

            .rightColumnContent{
              font-size:16px !important;
              line-height:125% !important;
            }

            .footerContent{
              font-size:14px !important;
              line-height:115% !important;
            }

            .footerContent a{display:block !important;} /* Place footer social and utility links on their own lines, for easier access */
          }
</style>
<table align="center" border="0" cellpadding="0" cellspacing="0" id="bodyTable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FAFAFA; border-collapse: collapse !important; height: 100% !important; margin: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 0; width: 100% !important" width="100%">
	<tbody>
		<tr>
			<td align="center" id="bodyCell" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; height: 100% !important; width: 100% !important; border-top-width: 4px; border-top-color: #dddddd; border-top-style: solid; margin: 0; padding: 20px;" valign="top">
			<p style="text-align:center; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 12.5px;">To make sure this goes to your inbox, just add <a href="GPVSpecialist@gpresorts.com" style="color:#00adef;">GPVSpecialist@gpresorts.com</a> to your address book.</p>
			<!-- BEGIN TEMPLATE // -->

			<table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; width: 600px; border: 1px solid #dddddd;">
				<tbody>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN PREHEADER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templatePreheader" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="preheaderContent" pardot-region="preheader_content00" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 12.5px; text-align: left; padding: 10px 20px;" valign="top"><a href="https://www.gpxvacations.com/"><img alt="Grand Pacific Exchange" border="0" height="45" src="http://www2.grandpacificresorts.com/l/130601/2016-08-23/hfbs5/130601/20220/GPX_logo_sans_125x44.png" style="width: 125px; height: 45px; border-width: 0px; border-style: solid;" width="125"></a></td>
									<td align="left" class="preheaderContent" pardot-data="line-height:20px;" pardot-region="preheader_content01" style="color: rgb(128, 128, 128); font-family: Helvetica; font-size: 10px; line-height: 20px; text-align: left; padding: 10px 20px 10px 0px; background: rgb(255, 255, 255);" valign="top" width="180">
									<h6 style="text-align: right;"><span style="font-size:18px;">Welcome to GPX</span></h6>
									</td>
								</tr>
							</tbody>
						</table>
						<!-- // END PREHEADER --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN HEADER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateHeader" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr>
								</tr>
							</tbody>
						</table>
						<!-- // END HEADER --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN BODY // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateBody" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="bodyContent" pardot-data="" pardot-region="body_content" style="color: rgb(80, 80, 80); font-family: Helvetica; font-size: 16px; line-height: 24px; text-align: left; padding: 20px;" valign="top"><div style="display: block; font-family: Helvetica; font-size: 26px; font-style: normal; font-weight: bold; letter-spacing: normal; line-height: 26px; margin: 0px; padding-bottom: 10px; color: rgb(32, 32, 32) !important; text-align: center;"><span style="font-size:40px; line-height:50px;">Welcome to GPX</span><br>
&nbsp;</div>

<p>Dear&nbsp;'.$name.',</p>
We are excited to welcome you, a valued OwnerRepository with Grand Pacific Resorts, to your exclusive OwnerRepository Benefit program. Your GPX membership opens up more opportunities to vacation anytime throughout the year to some of top destinations. There are no annual membership fees or complicated point systems, which makes vacationing more often easier than ever.<br>
<br>
Vacation specialists are standing by at (866) 325-6295 to provide you with exceptional service. You may look and book online at anytime by visiting&nbsp;<a href="http://www.gpxvacations.com/" style="text-decoration:none;color:#00adef;" target="_blank">GPXvacations.com</a>.&nbsp;<br>
<br>
Let\'s get started! Simply click the button to walk through the steps of setting up your online account.&nbsp;<br>
&nbsp;
<div style="text-align: center;">
<table border="0" cellpadding="0" cellspacing="0" class="mobile-button-container" width="100%">
	<tbody>
		<tr>
			<td align="center" class="padding-copy" style="padding: 0;">
			<table border="0" cellpadding="0" cellspacing="0" class="responsive-table">
				<tbody>
					<tr>
						<td align="center"><a class="mobile-button" href="'.$url.'" style="font-size: 16px; font-family: Arial, sans-serif; font-weight: bold; color: #ffffff !important; text-decoration: none; background-color: #009ad6; border-top: 10px solid #009ad6; border-bottom: 10px solid #009ad6; border-left: 25px solid #009ad6; border-right: 25px solid #009ad6; border-radius: 5px; -webkit-border-radius: 5px; -moz-border-radius: 5px; display: inline-block; margin:10px 0;" target="_blank"><font color="#ffffff">Get Started Here!</font></a></td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
</div>
</td>
								</tr>
							</tbody>
						</table>
						<!-- // END BODY --></td>
					</tr>
					<tr>
						<td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN FOOTER // -->
						<table border="0" cellpadding="0" cellspacing="0" id="templateFooter" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
							<tbody>
								<tr style="">
									<td align="left" class="footerContent" pardot-data="" pardot-region="preheader_content01" style="color: rgb(128, 128, 128); font-family: Helvetica; font-size: 10px; line-height: 15px; text-align: left; padding: 0px 20px 20px; background: rgb(239, 239, 239);" valign="top"><div style="text-align: center;">Copyright� '.date('Y').'&nbsp;Grand Pacific Resorts, All rights reserved. You are an owner at a resort managed by Grand Pacific Resorts and may receive periodic communications from the company.<br>
&nbsp;<br>
You are receiving this email because you are an owner with Grand Pacific Resorts. If you believe an error has been made, please contact us at gpvspecialist@gpresorts.com.<br>
<br>';

    /*
         <a href="%%view_online%%" style="color:#00adef;">View online</a>
         */
    $msg .= '</div>
</td>
								</tr>
								<tr style="">
									<td align="left" class="footerContent original-only" pardot-region="preheader_content02" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #808080; font-family: Helvetica; font-size: 10px; line-height: 15px; text-align: left; padding: 0 20px 20px;" valign="top"><div style="text-align: center;"><a href="	https://www2.grandpacificresorts.com/emailPreference/e/epc/130601/VM1N5YXix4WEdxBb7rXsuE8ogp9HFelSYjBXnvhsLeY/263">Update My Preferences</a><br>
&nbsp;
<div style="text-align: center;"><a href="https://gpxvacations.com/privacy-policy/" style="color:#00adef;">Privacy Policy</a><br>
&nbsp;</div>
</div>
</td>
								</tr>
							</tbody>
						</table>
						<!-- // END FOOTER --></td>
					</tr>
				</tbody>
			</table>
			<!-- // END TEMPLATE --></td>
		</tr>
	</tbody>
</table>
<br>
<!--
          This email was originally designed by the wonderful folks at MailChimp and remixed by Pardot.
          It is licensed under CC BY-SA 3.0
        -->


</body>';
    if($emailresults = wp_mail($email, 'Welcome to GPX', $msg, $headers))
    {
        $data['success'] = true;
        $data['msg'] = 'Email Sent!';
    }
    else
    {
        $data['msg'] = "Email not sent.  Please verify email address in profile.";
    }
    if($returnFalse)
    {
        return false;
    }
    else
    {
        wp_send_json($data);
    }
}
add_action('wp_ajax_send_welcome_email', 'send_welcome_email');

/**
 *
 *
 *
 *
 */
// define the wp_mail_failed callback
function action_wp_mail_failed($wp_error)
{

    return error_log(print_r($wp_error, true));
}

// add the action
add_action('wp_mail_failed', 'action_wp_mail_failed', 10, 1);

