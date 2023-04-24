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
        $this->io->writeln(date('m/d/Y g:i:s A'));
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



                    $fromEmailName = get_option('gpx_cremailName');
                    $fromEmail = get_option('gpx_cremail');
                    $subject = get_option('gpx_cremailSubject');

                    $message = $crresortmissedemail;

                    $template = 'custom_request_match_missed';
                    $fromEmailName = get_option('gpx_crresortmissedemailName');
                    $fromEmail = get_option('gpx_crresortmissedemail');
                    $subject = get_option('gpx_crresortmissedemailSubject');
                    $message = gpx_email_render_content($template, [
                        'resort' => $row->resort,
                        'nearby' => $row->nearby,
                        'region' => $row->region,
                        'city' => $row->city,
                        'adults' => $row->adults,
                        'checkin' => $row->checkIn->format('m/d/Y'),
                        'checkin2' => $row->checkIn2 ? $row->checkIn2->format('m/d/Y') : null,
                        'url' => $row->booking_path,
                        'weekID' => $holdMatch->matched,
                        'submitted' => date('m/d/Y g:i:s A', strtotime($holdMatch->datetime)),
                        'matcheddate' => date('m/d/Y g:i:s A', strtotime($holdMatch->match_date_time)),
                        'releaseddate' => date('m/d/Y g:i:s A', strtotime($holdMatch->match_release_date_time)),
                        'who' => $holdMatch->who,
                    ], false);


                }
            }
        }
    }
}
