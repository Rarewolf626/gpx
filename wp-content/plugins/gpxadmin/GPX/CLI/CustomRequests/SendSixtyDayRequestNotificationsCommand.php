<?php

namespace GPX\CLI\CustomRequests;

use GPX\CLI\BaseCommand;
use GPX\Model\CustomRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendSixtyDayRequestNotificationsCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('request:sixtyday');
        $this->setDescription('Disable missed custom requests');
        $this->setHelp('Disabled custom requests that have not been matched after 60 days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Sixty Day custom requests');
        $this->io->writeln(date('m/d/Y g:i:s A'));
        $this->io->warning('This script is disabled.');
        return Command::FAILURE;

        $requests = CustomRequest::active()
            ->where('sixtydayemail', '!=', 1)
            ->whereRaw("DATE_ADD( `datetime`, INTERVAL 60 DAY ) < NOW()")
            ->get();

        foreach ($requests as $request) {
            $request->update(['sixtydayemail' => '1', 'active' => '0', 'forCron' => '0']);

            $template = 'custom_request_match_sixtyday';
            $fromEmailName = get_option('gpx_crsixtydayemailName');
            $fromEmail = get_option('gpx_crsixtydayemail');
            $subject = get_option('gpx_crsixtydayemailSubject', 'No Matches for your Custom Search Request');

            $message = gpx_email_render_content($template, [
                'resort' => $request->resort,
                'nearby' => $request->nearby,
                'region' => $request->region,
                'city' => $request->city,
                'adults' => $request->adults,
                'checkin' => $request->checkIn->format('m/d/Y'),
                'checkin2' => $request->checkIn2 ? $request->checkIn2->format('m/d/Y') : null,
                'url' => get_site_url(null, '/member-dashboard/'),
                'weekID' => $request->week_on_hold,
                'submitted' => $request->datetime->format('m/d/Y g:i:s A'),
                'matcheddate' => $request->match_date_time ? $request->match_date_time->format('m/d/Y g:i:s A') : '',
                'releaseddate' => $request->match_release_date_time ? $request->match_release_date_time->format('m/d/Y g:i:s A') : '',
                'who' => $request->who,
            ], false);
            $headers = [
                "Reply-To: " . $fromEmailName . " <" . $fromEmail . ">",
                "Content-Type: text/html; charset=UTF-8",
            ];
            $sent = wp_mail($request->email, $subject, $message, $headers);
            if ($sent) {
                $wpdb->insert('wp_gpxCREmails', [
                    'cr_id' => $request->id,
                    'email' => 'sixtyday',
                ]);
            } else {
                $wpdb->insert('wp_gpxCREmails', [
                    'cr_id' => $request->id,
                    'email' => 'sixtyday_email_error',
                ]);
            }
        }
    }
}
