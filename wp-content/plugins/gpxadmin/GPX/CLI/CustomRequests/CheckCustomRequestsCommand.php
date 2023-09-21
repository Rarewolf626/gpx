<?php

namespace GPX\CLI\CustomRequests;

use DB;
use SObject;
use GpxRetrieve;
use GPX\Model\Week;
use GPX\Model\PreHold;
use GPX\CLI\BaseCommand;
use GPX\Model\CustomRequest;
use Illuminate\Support\Carbon;
use GPX\Model\CustomRequestMatch;
use GPX\Api\Salesforce\Salesforce;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCustomRequestsCommand extends BaseCommand
{

    protected CustomRequestMatch $matcher;
    protected Salesforce $sf;
    protected GpxRetrieve $dae;
    protected bool $debug = false;
    protected ?SymfonyStyle $io;

    public function __construct(CustomRequestMatch $matcher, Salesforce $sf, GpxRetrieve $dae)
    {
        $this->matcher = $matcher;
        $this->sf = $sf;
        $this->dae = $dae;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('request:checker');
        $this->setDescription('Checks active custom requests');
        $this->setHelp('Checks active custom requests for matches');
        $this->addOption('request',
            'r',
            InputOption::VALUE_REQUIRED,
            'Only check a specific request');
        $this->addOption('debug',
            'd',
            InputOption::VALUE_NONE,
            'In debug mode no updates are made');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->debug = (bool)$input->getOption('debug');
        $this->expiredRequests($output);

        $this->io->title('Check active custom requests');
        $this->io->writeln(date('m/d/Y g:i:s A'));
        if ($this->debug) {
            $this->io->warning('Currently in debug mode. Any updates will not be persisted, nothing will be sent to salesforce, and no emails will be sent.');
        }
        $cr = array_filter(explode(',', $input->getOption('request')));
        if ($cr) {
            $this->io->info(sprintf('Check custom requests %s', implode(', ', $cr)));
        }

        $requests = CustomRequest::active()
            ->notMatched()
            ->active()
            ->open()
            ->when($cr, fn($query) => $query->whereIn('id', $cr))
            ->orderBy('resort', 'desc')
            ->orderBy('BOD', 'desc')
            ->orderBy('datetime', 'asc')
            ->get();

        if ($requests->isEmpty()) {
            $this->io->success('There are no unmatched active custom requests that need checking');

            return Command::SUCCESS;
        }

        $requests->each(function (CustomRequest $request) use ($input) {
            $request->emsID = gpx_get_member_number($request->userID);
            if (!$request->resort_id && $request->resort) {
                $request->resortLookup(true);
                $request->save();
            }
            $this->io->section(sprintf('Custom Request #%d', $request->id));
            $this->io->horizontalTable([
                'Owner',
                'Region/Area',
                'Nearby',
                'Checkin',
                'Adults',
                'Children',
                'Room Type',
                'Preference',
                'Email',
            ], [
                [
                    $request->userID,
                    $request->resort ? $request->resort : $request->city . ', ' . $request->region,
                    $request->resort && $request->nearby ? "within " . CustomRequestMatch::MILES . " miles" : '',
                    $request->checkIn->format('m/d/Y') . ($request->checkIn2 ? ' - ' . $request->checkIn2->format('m/d/Y') : ''),
                    $request->adults,
                    $request->children,
                    $request->larger ? $request->roomType . ' or larger' : $request->roomType,
                    $request->preference,
                    $request->email,
                ],
            ]);
            if ($this->debug) {
                DB::connection()->enableQueryLog();
                DB::connection()->flushQueryLog();
            }
            $matches = $this->matcher->get_matches($request);

            if ($this->debug) {
                $queries = DB::getQueryLog();
                DB::connection()->flushQueryLog();
                DB::connection()->disableQueryLog();
                foreach ($queries as $query) {
                    $this->io->writeln($query['query']);
                }
            }
            $matched = $this->matcher->has_restricted_date() ? $matches->notRestricted() : $matches;
            $week_ids = $matched->ids();
            if (!$week_ids) {
                $this->io->success('Request has no matches');

                return;
            }
            $this->io->info('Request matched the following weeks');
            $this->io->listing($week_ids);

            if ($request->resort_id) {
                $match = $matched->first(fn($match) => $match['resort_id'] == $request->resort_id) ?? $matched->first();
            } else {
                $match = $matched->first();
            }

            /** @var Week $week */
            $week = Week::with(['unit', 'theresort'])->find($match['weekId']);
            $this->io->info(sprintf('Using week %d', $week->record_id));
            $this->io->horizontalTable(['WeekID', 'Checkin', 'Checkout', 'Resort', 'Room Type', 'Type'],
                [
                    [
                        $week->record_id,
                        $week->check_in_date,
                        $week->check_out_date,
                        $week->theresort !== null ? $week->theresort->ResortName : null,
                        $week->unit !== null ? $week->unit->name : null,
                        $week->room_type,
                    ],
                ]);
            $hold = null;
            if ($request->isResortRequest($week->theresort ? $week->theresort->ResortName : null)) {
                $hold = $this->putWeekOnHold($request, $week);
            }
            $request->fill(
                [
                    'matched' => $matched->ids(),
                    'active' => false,
                    'forCron' => false,
                    'match_date_time' => Carbon::now(),
                ]
            );
            if (!$this->debug) {
                $request->save();
            }
            $this->io->success('Saved matches and set request to inactive');
            $this->sendNotification($request, $week);
        });

        return Command::SUCCESS;
    }

    protected function expiredRequests(OutputInterface $output): int
    {
        $params = ['command' => 'request:expired',];
        if ($this->debug) {
            $params['--debug'] = true;
        }

        return gpx_run_command($params, $output);
    }

    private function putWeekOnHold(CustomRequest $request, Week $week): PreHold
    {
        if (!$this->debug) {
            $this->dae->DAEHoldWeek($week->record_id, $request->userID, $request->emsID);
        }
        $weekType = $request->preference == 'Rental' ? 'RentalWeek' : 'ExchangeWeek';

        $hold = new PreHold(
            [
                'weekId' => $week->record_id,
                'propertyID' => $week->record_id,
                'weekType' => $weekType,
                'user' => $request->userID,
                'lpid' => 0,
                'data' => [
                    time() => [
                        'action' => 'held',
                        'by' => 'Custom Request',
                    ],
                ],
                'released' => false,
                'release_on' => Carbon::now()->addDay(),
            ]
        );
        $week->fill(['active' => false]);
        $request->fill(['week_on_hold' => $week->record_id, 'match_release_date_time' => $hold->release_on]);
        if (!$this->debug) {
            $hold->save();
            $week->save();
            $request->save();
        }
        $this->io->success(sprintf('Week put on hold until %s', $hold->release_on->format('m/d/Y h:i:s A')));

        return $hold;
    }


    private function sendNotification(CustomRequest $request, Week $week)
    {
        if (!get_option('gpx_global_cr_email_send')) {
            $this->io->warning('Custom request emails are disabled');

            return;
        }
        $this->io->info('Send notification email');
        if (!is_email($request->email)) {
            $this->io->warning("Email recipient {$request->email} is invalid.");

            return;
        }
        if ($request->isResortRequest($week->theresort ? $week->theresort->ResortName : null)) {
            $template = 'custom_request_match_resort';
            $fromEmailName = get_option('gpx_crresortmatchemailName');
            $fromEmail = get_option('gpx_crresortmatchemail');
            $subject = get_option('gpx_crresortmatchemailSubject', 'There is a Match! Confirm your Custom Search Request');
            $url = site_url('/view-profile/#holdweeks-profile');
        } else {
            $template = 'custom_request_match';
            $fromEmailName = get_option('gpx_cremailName');
            $fromEmail = get_option('gpx_cremail');
            $subject = get_option('gpx_cremailSubject', 'There is a Match! Confirm your Custom Search Request');
            $url = $request->booking_path;
        }
        $message = gpx_email_render_content($template, [
            'resort' => $request->resort,
            'nearby' => $request->nearby,
            'region' => $request->region,
            'city' => $request->city,
            'adults' => $request->adults,
            'checkin' => $request->checkIn->format('m/d/Y'),
            'checkin2' => $request->checkIn2 ? $request->checkIn2->format('m/d/Y') : null,
            'url' => $url,
            'weekID' => $request->record_id,
            'submitted' => $request->datetime->format('m/d/Y g:i:s A'),
            'matcheddate' => $request->match_date_time ? $request->match_date_time->format('m/d/Y g:i:s A') : '',
            'releaseddate' => $request->match_release_date_time ? $request->match_release_date_time->format('m/d/Y g:i:s A') : '',
            'who' => $request->who,
        ], false);


        $headers = [
            "Reply-To: " . $fromEmailName . " <" . $fromEmail . ">",
            "Content-Type: text/html; charset=UTF-8",
        ];
        $sent = true;
        if (!$this->debug) {
            $sent = wp_mail($request->email, $subject, $message, $headers);
        }

        if ($sent) {
            $this->io->success(sprintf('Sent email to %s', $request->email));
        } else {
            $this->io->error(sprintf('Failed to send email to %s', $request->email));
        }

        $this->io->info($subject);
        $this->io->info($headers);
        if ($this->io->isVerbose()) {
            $this->io->write($message);
        }
        $this->io->newLine();
    }

    private function getAccountId(CustomRequest $request)
    {
        $query = sprintf("SELECT Property_Owner__c FROM GPR_Owner_ID__c WHERE Name='%d'", $request->emsID);
        $results = $this->sf->query($query);
        if (empty($results)) {
            return null;
        }
        $result = array_pop($results);

        return $result->Property_Owner__c ?: null;
    }

    private function getRequestDescription(CustomRequest $request): string
    {
        $description = "Special Request Details:\n\n";
        if ($request->resort) {
            $description .= "Resort: {$request->resort}\n";
        }
        if ($request->region) {
            $description .= "Region: {$request->region}\n";
        }
        if ($request->city) {
            $description .= "City: {$request->city}\n";
        }
        if ($request->state) {
            $description .= "State: {$request->state}\n";
        }
        if ($request->country) {
            $description .= "Country: {$request->country}\n";
        }
        $description .= "Adults: {$request->adults}\n";
        if ($request->children > 0) {
            $description .= "Children: {$request->children}\n";
        }
        $description .= "Date: {$request->checkIn->format('m/d/Y')}";
        if ($request->checkIn2) {
            $description .= " - {$request->checkIn2->format('m/d/Y')}";
        }

        return $description;
    }
}
