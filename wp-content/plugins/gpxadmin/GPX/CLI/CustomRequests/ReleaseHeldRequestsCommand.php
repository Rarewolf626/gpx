<?php

namespace GPX\CLI\CustomRequests;

use GPX\CLI\BaseCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseHeldRequestsCommand extends BaseCommand {
    protected function configure(): void {
        $this->setName( 'request:held' );
        $this->setDescription( 'Release held custom requests' );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $this->io = new SymfonyStyle( $input, $output );
        $this->io->title( 'Held Custom Requests' );
        $this->io->warning('This script is disabled.');
        return Command::FAILURE;

        $twentyfourhours = date('Y-m-d H:i:s', strtotime('-24 hours'));
        global $wpdb;
        //start by seeing if held properties need to be released
        $sql = $wpdb->prepare("SELECT * FROM wp_gpxCustomRequest
            WHERE matched != ''
            AND match_release_date_time IS NULL
            AND match_date_time IS NOT NULL
            AND match_date_time < %s", $twentyfourhours);
        /*
         * TODO
         * this block of code will never run
         * The // on the $rows line has make $sql= above and $rows below undefined and the foreach will never run
         *
         */
        //suppress for now
        foreach($rows as $row)
        {

            //first release the match date time
            $update['match_release_date_time'] = date("Y-m-d H:i:s");
            $update['week_on_hold'] = 0;
            $wpdb->update('wp_gpxCustomRequest', $update, array('id'=>$row->id));
            $rowmatched = explode(",", $row->matched);
            //was this week booked?
            $crresortmissedemail = stripslashes(get_option('gpx_crresortmissedemailMessage'));
            foreach($rowmatched as $holdMatch)
            {
                $sql = $wpdb->prepare("SELECT * FROM wp_gpxTransactions a
                        INNER JOIN wp_properties b on a.weekId=b.weekId
                        WHERE b.id=%s", $holdMatch);
                $trans = $wpdb->get_row($sql);
                if(isset($trans) && !empty($trans))
                {
                    // this week has been booked we don't need to do anything else
                }
                else
                {
                    //realease the week

                    //get the week details
                    $sql = $wpdb->prepare("SELECT * FROM wp_properties WHERE id=%s", $holdMatch);
                    $propDets = $wpdb->get_row($sql);

                    //we always need to check the "display date" prior to making it active. Only make this active when the sell date is in the future.
                    $sql = $wpdb->prepare("SELECT active_specific_date FROM wp_room WHERE record_id=%d",$propDets->weekId);
                    $activeDate = $wpdb->get_var($sql);

                    if(strtotime('NOW') >  strtotime($activeDate))
                    {
                        $wpdb->update('wp_room', array('active'=>1), array('record_id'=>$propDets->weekId));
                    }

                    $inputVars = array(
                        'WeekEndpointID' => $propDets->WeekEndpointID,
                        'WeekID' => $propDets->weekId,
                        'DAEMemberNo' => $row->emsID,
                        'ForImmediateSale' => true,
                    );
                    //release it from dae
//                     $dae->DAEReleaseWeek($inputVars);

                    $message = $crresortmissedemail;
                    $fromEmailName = get_option('gpx_crresortmissedemailName');
                    $fromEmail = get_option('gpx_crresortmissedemail');
                    $subject = get_option('gpx_crresortmissedemailSubject');
                    //we aren't using the link on this message.  This will need to be adjusted if it is ever enabeld.
                    //                     $link = get_site_url("", "/wp-admin/admin-ajax.php?action=custom_request_status_change&croid=".$toemail->id."221a2d2s33d564334ne3".$toemail->emsID, "https");

                    //add additional details
                    $replaceExtra['[weekID]'] = $holdMatch->matched;
                    $replaceExtra['[submitted]'] = $holdMatch->datetime;
                    $replaceExtra['[matcheddate]'] = $holdMatch->match_date_time;
                    $replaceExtra['[releaseddate]'] = $holdMatch->match_release_date_time;
                    $replaceExtra['[who]'] = $holdMatch->who;

                    foreach($replaceExtra as $reK=>$reV)
                    {
                        $message = str_replace($reK, $reV, $message);
                    }

                    $message = str_replace("[FORM]", $form, $message);
                    //                     $message = str_replace("HTTP://[URL]", $link, $message);
                    //                     $message = str_replace("[URL]", $link, $message);

                    $headers[]= "From: ".$fromEmailName." <".$fromEmail.">";
                    //$headers[]= "Cc: GPX <gpx@gpxvacations.com>";
                    $headers[] = "Content-Type: text/html; charset=UTF-8";

                }
            }
        }
    }
}
