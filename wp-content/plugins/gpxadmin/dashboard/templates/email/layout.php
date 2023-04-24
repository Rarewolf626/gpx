<?php
/**
 * @var ?string $title
 * @var string $content
 * @var ?array{url: string, label: string} $action
 * @var ?bool $preview
 */
?>
<html>
<head>
    <?php if($title):?>
        <title><?= esc_html($title)?></title>
    <?php endif; ?>
    <style type="text/css">
        #outlook a {
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
</head>
<body bgcolor="#FAFAFA" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; height: 100% !important; width: 100% !important; background-color: #FAFAFA; margin: 0; padding: 0; <?= (isset($preview) && $preview) ? 'pointer-events: none' : ''?>">

<table align="center" border="0" cellpadding="0" cellspacing="0" id="bodyTable" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FAFAFA; border-collapse: collapse !important; height: 100% !important; margin: 0; mso-table-lspace: 0pt; mso-table-rspace: 0pt; padding: 0; width: 100% !important" width="100%">
    <tbody>
    <tr>
        <td align="center" id="bodyCell" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; height: 100% !important; width: 100% !important; border-top-width: 4px; border-top-color: #dddddd; border-top-style: solid; margin: 0; padding: 20px;" valign="top">
            <!-- BEGIN TEMPLATE // -->

            <table border="0" cellpadding="0" cellspacing="0" id="templateContainer" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; width: 600px; border: 1px solid #dddddd;">
                <tbody>
                <tr>
                    <td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN PREHEADER // -->
                        <table border="0" cellpadding="0" cellspacing="0" class="templateColumnContainer" id="" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color:#13233d; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
                            <tbody>
                            <tr>
                                <td align="left" class="leftColumnContent" pardot-region="preheader_content00" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #363636; font-family: Helvetica; font-size: 10px; line-height: 12.5px; text-align: left; padding: 10px 20px;" valign="top"><a href="https://www.gpxvacations.com/" target="_blank"><img alt="" border="0" height="45" src="https://www2.grandpacificresorts.com/l/130601/2017-02-21/kr3sl/130601/32815/logo_gpx_162.png" style="width: 162px; height: 45px; border-width: 0px; border-style: solid; padding-top:15px;" width="162"></a></td>
                                <td align="left" class="leftColumnContent" pardot-data="line-height:20px;" pardot-region="preheader_content01" style="color: rgb(128, 128, 128); font-family: Helvetica; font-size: 10px; line-height: 20px; text-align: left; padding: 10px 20px 10px 0px;" valign="top" width="180">
                                    <div style="text-align: right;">&nbsp; &nbsp;</div>

                                    <div style="text-align: right;"><span style="font-family:arial,helvetica,sans-serif; font-size:15px; color:#ffffff; font-weight:bold;">(866) 325-6295</span></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- // END PREHEADER --></td>
                </tr>
                <tr>
                    <td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN BODY // -->
                        <table border="0" cellpadding="0" cellspacing="0" id="templateBody" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 0px; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
                            <tbody>
                            <tr>
                                <td align="left" class="bodyContent" pardot-data="" pardot-region="body_content" style="color: rgb(80, 80, 80); font-family: Helvetica; font-size: 16px; line-height: 24px; text-align: left; padding: 20px;" valign="top">
                                    <?php if($title):?>
                                        <div style="display: block; font-family: Helvetica; font-style: normal; font-weight: bold; letter-spacing: normal; line-height: 35px; margin: 0px; padding-bottom: 0px; color: #1d98d4 !important; text-align: center; font-size:32px;"><?= $title ?></div>
                                    <?php endif; ?>

                                    <?= $content; ?>

                                    <?php if($action):?>
                                        <div style="text-align: center;">
                                            <table border="0" cellpadding="0" cellspacing="0" class="mobile-button-container" width="100%">
                                                <tbody>
                                                <tr>
                                                    <td align="center" class="padding-copy" style="padding: 0;">
                                                        <table border="0" cellpadding="0" cellspacing="0" class="responsive-table">
                                                            <tbody>
                                                            <tr>
                                                                <td align="center"><a class="mobile-button" href="<?= esc_attr($action['url']) ?>" style="font-size: 16px; font-family: Arial, sans-serif; font-weight: bold; color: #ffffff !important; text-decoration: none; background-color: #f8c337; border-top: 10px solid #f8c337; border-bottom: 10px solid #f8c337; border-left: 25px solid #f8c337; border-right: 25px solid #f8c337; display: inline-block; margin:10px 0;" target="_blank"><font color="#000000"><?= esc_html($action['label']) ?></font></a></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>

                                    <div style="text-align: center;">
                                        <table border="0" cellpadding="0" cellspacing="0" class="mobile-button-container" width="100%">
                                            <tbody>
                                            <tr>
                                                <td align="center" class="padding-copy" style="padding: 0;">
                                                    <table border="0" cellpadding="0" cellspacing="0" class="responsive-table">
                                                        <tbody>
                                                        <tr>
                                                            <td align="center"><a class="mobile-button" href="tel:+18663256295" style="font-size: 16px; font-family: Arial, sans-serif; font-weight: bold; color: #ffffff !important; text-decoration: none; background-color: #ffffff; border-top: 1px solid #1d98d4; border-bottom: 1px solid #1d98d4; border-left: 1px solid #1d98d4; border-right: 1px solid #1d98d4; display: inline-block; margin:10px 0; padding: 7px 25px;" target="_blank"><font color="#000000">(866) 325-6295</font></a></td>
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
                    <td pardot-region="bottom_banner" class=""></td>
                </tr>
                <tr>
                    <td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN FOOTER // -->
                        <table border="0" cellpadding="0" cellspacing="0" id="templateColumns" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #13233d; border-bottom-color: #13233d; border-bottom-style: solid; border-bottom-width: 1px; border-collapse: collapse !important; border-top-color: #13233d; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
                            <tbody>
                            <tr pardot-repeatable="" class="">
                                <td align="center" class="templateColumnContainer" style="padding-top: 20px; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 260px;" valign="top">
                                    <table border="0" cellpadding="20" cellspacing="0" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important;" width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="left" class="leftColumnContent" pardot-region="left_column_content" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #ffffff; font-family: Helvetica; font-size: 12px; line-height: 16px; text-align: left; padding: 0 20px 20px;" valign="top"><span style="font-size:16px; color:#1d98d4;"><strong>CONNECT</strong></span><br>
                                                <br>
                                                <a href="https://www.facebook.com/GPXvacations/"><img alt="" border="0" height="40" src="https://www2.grandpacificresorts.com/l/130601/2017-02-21/krbls/130601/32861/icon_gpx_facebook.png" style="width: 40px; height: 40px; border-width: 0px; border-style: solid; padding-right:10px;" width="40"></a>&nbsp;<a href="https://www.youtube.com/playlist?list=PLOI9S6NSyNA7uQVVATrM2Ypcr-wBwWxOX"><img alt="" border="0" height="40" src="https://www2.grandpacificresorts.com/l/130601/2017-02-21/krblx/130601/32865/icon_gpx_youtube.png" style="width: 40px; height: 40px; border-width: 0px; border-style: solid;" width="40"></a><br>
                                                <br>
                                                <a href="https://gpxvacations.com" style="color:#1d98d4; font-size:16px; font-weight:bold;">GPXvacations.com</a><br>
                                                <br>
                                                <a href="%%unsubscribe%%"></a></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td align="center" class="templateColumnContainer" style="padding-top: 20px; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 260px;" valign="top">
                                    <table border="0" cellpadding="20" cellspacing="0" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important;" width="100%">
                                        <tbody>
                                        <tr>
                                            <td align="left" class="rightColumnContent" pardot-region="right_column_content" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #ffffff; font-family: Helvetica; font-size: 12px; line-height: 16px; text-align: left; padding: 0 20px 20px;" valign="top">Special request availability is not automatically held. All prices charged in US dollars.<br>
                                                <br>
                                                Copyright© 2018 Grand Pacific Exchange®, All rights reserved.</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- // END FOOTER --></td>
                </tr>
                <tr>
                    <td align="center" style="-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; mso-table-lspace: 0pt; mso-table-rspace: 0pt;" valign="top"><!-- BEGIN BODY // -->
                        <table border="0" cellpadding="0" cellspacing="0" id="templateBody" style="-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #FFFFFF; border-bottom-color: #CCCCCC; border-bottom-style: solid; border-bottom-width: 0px; border-collapse: collapse !important; border-top-color: #FFFFFF; border-top-style: solid; border-top-width: 1px; mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
                            <tbody>
                            <tr>
                                <td align="left" class="bodyContent" pardot-data="" pardot-region="body_content" style="color: #aaaaaa; font-family: Helvetica; font-size: 11px; line-height: 16px; text-align: left; padding: 20px; text-align:center;" valign="top">You have received this email because you are an owner of a Grand Pacific Resort© and GPX is your official internal exchange program. Add <a href="mailto:gpx@gpresorts.com" style="color:#1d9bd3;">gpx@gpresorts.com</a> so you won’t miss a deal!.<br>
                                    <br>
                                    For Gmail users: If you are using the tabbed inbox, our emails may be pushed to the 'Promotions' tab. To receive updates from Grand Pacific Resorts directly to your primary inbox, drag and drop this email to that tab.<br>
                                    <br>
                                    For Canadian residents only: We have updated our records and intend only to send e-communication to residents who have consented to receipt of this communication. We will make all corrections necessary. If you believe an error has been made, please contact us at <a href="mailto:gpx@gpresorts.com" style="color:#1d9bd3;">gpx@gpresorts.com</a>.</td>
                            </tr>
                            </tbody>
                        </table>
                        <!-- // END BODY --></td>
                </tr>
                </tbody>
            </table>
            <!-- // END TEMPLATE --></td>
    </tr>
    </tbody>
</table>
</body>
</html>
