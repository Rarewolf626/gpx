<?php

namespace GPX\CLI\CustomRequests;

use GPX\CLI\BaseCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendSixtyDayRequestNotificationsCommand extends BaseCommand {
    protected function configure(): void {
        $this->setName( 'request:sixtyday' );
        $this->setDescription( 'Disabled expired custom requests' );
        $this->setHelp( 'Checks active custom requests and disabled any with checkin dates in the past' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io = new SymfonyStyle( $input, $output );
        $this->io->title( 'Sixty Day custom requests' );

        //check for requests that are over 60 days old
        $sql = "SELECT * FROM wp_gpxCustomRequest
                WHERE active=1
                AND matched = ''
                and sixtydayemail <> '1'
                AND UNIX_TIMESTAMP(datetime) < UNIX_TIMESTAMP( NOW() - INTERVAL 60 DAY )";
        //turn off sixty day trigger per Ashley/

        foreach($sixty as $toemail)
        {

            $wpdb->update('wp_gpxCustomRequest', array('sixtydayemail'=>'1', 'active'=>'0', 'forCron'=>'0'), array('id'=>$toemail->id));

            $message =stripslashes(get_option('gpx_crsixtydayemailMessage'));

            $checkIn = $toemail->checkIn;
            if(isset($toemail->checkIn2) && !empty($toemail->checkIn2))
                $checkIn .= ' - '.$toemail->checkIn2;
            $formData = array(
                'Region'=>$toemail->region,
                'City/Sub Region'=>$toemail->city,
                'Resort'=>$toemail->resort,
                'Adults'=>$toemail->adults,
                'Children'=>$toemail->children,
                'Date'=>$checkIn,
            );

            $form = '<ul>';
            foreach($formData as $key=>$value)
            {
                if(!empty($value))
                {
                    $form .= '<li><strong>'.$key.':</strong> '.$value.'</li>';
                }
            }
            $form .= '</ul>';

            $fromEmailName = get_option('gpx_crsixtydayemailName');
            $fromEmail = get_option('gpx_crsixtydayemail');
            $subject = get_option('gpx_crsixtydayemailSubject');

            $link = get_site_url("", "/wp-admin/admin-ajax.php?action=custom_request_status_change&croid=".$toemail->id."221a2d2s33d564334ne3".$toemail->emsID, "https");

            $message = str_replace("[FORM]", $form, $message);
            $message = str_replace("HTTP://[URL]", $link, $message);
            $message = str_replace("[URL]", $link, $message);

            //add additional details
            $replaceExtra['[weekID]'] = $toemail->matched;
            $replaceExtra['[submitted]'] = $toemail->datetime;
            $replaceExtra['[matcheddate]'] = $toemail->match_date_time;
            $replaceExtra['[releaseddate]'] = $toemail->match_release_date_time;
            $replaceExtra['[who]'] = $toemail->who;

            foreach($replaceExtra as $reK=>$reV)
            {
                $message = str_replace($reK, $reV, $message);
            }

            $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
            $headers[] = "Content-Type: text/html; charset=UTF-8";

            if(!in_array($toemail->email, $sentEmailSixty))
            {
                $sentEmailSixty[] = $toemail->email;
                if(wp_mail($toemail->email, $subject, $message, $headers))
                {
                    $insertData = [
                        'cr_id'=>$row->id,
                        'email'=>'sixtyday',
                    ];
                    $wpdb->insert('wp_gpxCREmails',$insertData);
                }
                else
                {
                    //do nothing right now but maybe in the future
                    $insertData = [
                        'cr_id'=>$row->id,
                        'email'=>'sixtyday_email_error',
                    ];
                    $wpdb->insert('wp_gpxCREmails',$insertData);
                }
            }
        }
    }
}
