<?php

namespace GPX\CLI\CustomRequests;

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
        if($cr){
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
                    $request->checkIn->format('m/d/Y'),
                    $request->adults,
                    $request->children,
                    $request->larger ? $request->roomType . ' or larger' : $request->roomType,
                    $request->preference,
                    $request->email,
                ],
            ]);
            $matches = $this->matcher->get_matches($request);
            if ($matches->notRestricted()->isEmpty()) {
                $this->io->success('Request has no matches');

                return;
            }
            $this->io->info('Request matched the following weeks');
            $this->io->listing($matches->notRestricted()->ids());
            $match = $matches->notRestricted()->first();
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
                    'matched' => $matches->notRestricted()->ids(),
                    'active' => false,
                    'forCron' => false,
                    'match_date_time' => Carbon::now(),
                ]
            );
            if (!$this->debug) {
                $request->save();
            }
            $this->io->success('Saved matches and set request to inactive');
            $case = $this->sendToSalesforce($request, $week, $hold);
            $this->sendNotification($request, $week, $case);
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
        $weekType = 'N/A';
        if ($request->preference == 'Exchange') {
            $weekType = 'ExchangeWeek';
        }
        if ($request->preference == 'Rental') {
            $weekType = 'RentalWeek';
        }

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

    private function sendToSalesforce(CustomRequest $request, Week $week, PreHold $hold = null): SObject
    {
        $resort_request = $request->isResortRequest($week->theresort ? $week->theresort->ResortName : null);
        $case = new SObject();
        $case->type = 'Case';
        $case->fields = [
            'Reason' => $resort_request ? 'GPX: Resort Matched' : 'GPX: Area Matched',
            'Origin' => 'Web',
            'RecordTypeID' => $resort_request ? '01240000000MJdI' : '01240000000MJdI',
            'Search_Req_ID__c' => $request->id,
            'Priority' => 'Standard',
            'Status' => 'Open',
            'Subject' => $resort_request ? 'GPX Search Request – Resort Match' : 'GPX Search Request – Area Match',
            'Description' => $this->getRequestDescription($request),
            'Resort__c' => $week->theresort ? $week->theresort->ResortName : null,
            'GPX_Unit_Type__c' => $week->unit ? $week->unit->name : null,
            'Check_In_Date1__c' => $week->check_in_date->format('Y-m-d'),
            'City__c' => $request->city,
            'State__c' => $request->region,
            'Country__c' => $request->country,
            'EMS_Account_No__c' => $request->userID,
            'AccountId' => $this->getAccountId($request),
            'Inventory_Found_On__c' => Carbon::now()->toW3cString(),
            'Request_Submission_Date__c' => $request->datetime->toW3cString(),
            'SuppliedEmail' => $request->email,
        ];
        if ($hold && $hold->release_on) {
            $case->fields['Inventory_Hold_Expires_On__c'] = $hold->release_on->toW3cString();
        }
        $this->io->success('Send to salesforce as case');
        $this->io->horizontalTable(array_keys($case->fields), [$case->fields]);
        if (!$this->debug) {
            try {
                $sfAdd = $this->sf->gpxUpsert('Search_Req_ID__c', [$case]);
                if (is_string($sfAdd)) {
                    throw new \Exception($sfAdd);
                }
                $case->response = $sfAdd;
                $this->io->info('Added to salesforce');
                $this->io->info($sfAdd[0]->id);
            } catch (\Exception $e) {
                $case->response = $e->getMessage();
                $this->io->error('Failed to add to salesforce');
                $this->io->error($e->getMessage());
            }
        }

        return $case;
    }

    private function sendNotification(CustomRequest $request, Week $week, SObject $case)
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
            $message = stripslashes(get_option('gpx_crresortmatchemailMessage'));
            $fromEmailName = get_option('gpx_crresortmatchemailName');
            $fromEmail = get_option('gpx_crresortmatchemail');
            $subject = get_option('gpx_crresortmatchemailSubject');
        } else {
            $message = stripslashes(get_option('gpx_cremailMessage'));
            $fromEmailName = get_option('gpx_cremailName');
            $fromEmail = get_option('gpx_cremail');
            $subject = get_option('gpx_cremailSubject');
        }
        $form = "<ul>";
        if ($request->region) {
            $form .= "<li><strong>Region:</strong> {$request->region}</li>";
        }
        if ($request->city) {
            $form .= "<li><strong>City/Sub Region:</strong> {$request->city}</li>";
        }
        if ($request->resort) {
            $form .= "<li><strong>Resort:</strong> {$request->resort}</li>";
            if ($request->nearby) {
                $form .= "<li><strong>Include Nearby Resort Matches</strong></li>";
            }
        }
        $form .= "<li><strong>Adults:</strong> {$request->adults}</li>";
        $form .= sprintf("<li><strong>Date:</strong> %s%s</li>",
            $request->checkIn->format('m/d/Y'),
            $request->checkIn2 ? ' - ' . $request->checkIn2->format('m/d/Y') : '');

        $form .= "</ul>";
        $message = str_replace("[FORM]", $form, $message);
        $message = str_replace("HTTP://[URL]", $request->booking_path, $message);
        $message = str_replace("[URL]", $request->booking_path, $message);
        $message = str_replace("[weekID]", $week->record_id, $message);
        $message = str_replace("[submitted]", $request->datetime->format('m/d/Y g:i:s A'), $message);
        $message = str_replace("[matcheddate]",
            $request->match_date_time ? $request->match_date_time->format('m/d/Y g:i:s A') : '',
            $message);
        $message = str_replace("[releaseddate]",
            $request->match_release_date_time ? $request->match_release_date_time->format('m/d/Y g:i:s A') : '',
            $message);
        $message = str_replace("[who]", $request->who, $message);

        $headers = [
            "Reply-To: " . $fromEmailName . " <" . $fromEmail . ">",
            "Content-Type: text/html; charset=UTF-8",
        ];
        $sent = true;
        if (!$this->debug) {
            $sent = wp_mail($request->email, $subject, $message, $headers);
        }
        $mail = [
            'cr_id' => $request->id,
            'sfData' => json_encode($case->fields),
            'sf_response' => json_encode($case->response),
            'email' => $request->email,
        ];
        if ($sent) {
            $this->io->success(sprintf('Sent email to %s', $request->email));
        } else {
            $mail['email'] = 'match_email_error';
            $this->io->error(sprintf('Failed to send email to %s', $request->email));
        }

        $this->io->info($subject);
        $this->io->info($headers);
        if ($this->io->isVerbose()) {
            $this->io->write($message);
        }
        $this->io->newLine();

        \DB::table('wp_gpxCREmails')->insert($mail);
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
            $description .= " - {$request->checkIn->format('m/d/Y')}";
        }

        return $description;
    }
}
