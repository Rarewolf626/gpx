<?php
/*
 Plugin Name: Migrate Images
 Description: Migrate images away from daelive.com. This Plugin only runs in WP-CLI wp dae:migrate
 Author: Jonathan Bernardi, Jeffrey Shaikh
 Requires PHP:      7.0
 Version: 1.1
 */

/**
 * checks to make sure CLI is available
 */
if ( ! function_exists( 'is_cli_running' ) ) {
    function is_cli_running() {
        return defined( 'WP_CLI' ) && WP_CLI;
    }
}
/**
 * add commands to CLI
 */
if ( is_cli_running() ) {

    WP_CLI::add_command( 'dae:migrate', [ 'GPX_DAE_MigrateImages', 'run' ], [
        'shortdesc' => 'Migrate images away from daelive.com',
        'longdesc'  => "Searches for all instances of an image with a daelive.com domain and downloads the image to the local server and updates the database.",
    ] );

    WP_CLI::add_command( 'gpximages:local', [ 'GPX_DAE_MigrateImages', 'local' ], [
        'shortdesc' => 'check, dowload and clean-up images on staging server',
        'longdesc'  => "Searches for all resort images on the staging server and verifies, downloads to local server and updates the database.",
    ] );

}


class GPX_DAE_MigrateImages {
    public wpdb $wpdb;
    private $dir = "images/resort";  // path where images are saved. This is determined by the source files, can't be changed

    private $verbose = false;   // verbose will disable the progress bar and display each step to stndout
    private $safemode = false;  // mostly for testing. This will do everything except make database changes
                                // safemode = downloads, but doesn't change database
    private $dryrun = false;    // dryrun will run code but will not actually download or change database. just tells you what it's going to do
                                // dryrun =  no download, no database changes. Only processing

    private $image_types = array();

    public static $regex = "/(https?:\/\/(?:www\.)daelive\.com\/images\/resort\/(.+\.(?:jpg|jpeg|png|gif|webp|svg)))/i";

    public static $regex_local = "/\/images\/resort\/(.+\.(?:jpg|jpeg|png|gif|webp|svg))/i";

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;    // use the wpdb object
    }

    /**
     * check the server for missing resort images
     * @param $args
     * @param $assoc_args
     * @return void
     */
    public function local($args, $assoc_args ) {
        // set the passed through args
        $this->set_args($args, $assoc_args);

        //acceptable image types
        $this->image_types= array(1,2,3,18); // GIF, JPEG, PNG, WEBP

        $this->local_resorts();
        $this->local_meta();


    }
    private function remove_trailing_slashes( $url ): string
    {
        return rtrim($url, '/');
    }

    /**
     * checks for broken and missing local images in the wp_resorts table
     * @return void
     */
    public function local_resorts () {
        WP_CLI::line( "\n-----------------" );
        WP_CLI::line( WP_CLI::colorize( "%BChecking wp_resorts%n" ) );
        WP_CLI::line( "-----------------\n" );
        $broken_image_counter = 0;
        $fixed_image_counter = 0;
            foreach ( [ 'ImagePath1', 'ImagePath2', 'ImagePath3' ] as $column ) {
                // blue column display for column working on...
                WP_CLI::line(WP_CLI::colorize( "%BColumn {$column}%n" ));
                $sql = "SELECT id,ResortName, {$column} FROM wp_resorts WHERE {$column} LIKE '/images/%' ORDER BY id ASC "; // LIMIT 100 OFFSET {$offset}

                $results = $this->wpdb->get_results( $sql, OBJECT );
                $count   = count( $results );
                WP_CLI::line( "Processing Images ({$count})" );


                foreach ( $results as $result ) {

                    if (!$this->check_image($result->$column)) {
                        $broken_image_counter++;
                        //try to fix
                        $url =    $this->download( $result->$column);
                        if ($url){
                            $fixed_image_counter++;
                            WP_CLI::success( "Downloaded Image from DAE" );
                        } else {
                            WP_CLI::warning( "Unable to repair" );
                        }
                    }
                }

                if ($broken_image_counter > 0) {
                    WP_CLI::warning( "There were {$broken_image_counter} broken images." );
                    WP_CLI::success( "Fixed {$fixed_image_counter} broken images." );
                    $remaining_broken = $broken_image_counter - $fixed_image_counter;
                    if ($remaining_broken > 0)
                        WP_CLI::error( "There are {$remaining_broken} remaining broken images." );
                }else{
                    WP_CLI::success( "There are no broken images" );
                }
            }
        }

    /**
     * checks an image returns if it's an valid image or not
     *
     * @param $image  absolute path to image
     * @return bool
     */
        private function check_image($image){

            $wphome = $this->remove_trailing_slashes(get_home_path());
            $valid_image = true;
            //checking the file exists
            if (!file_exists($wphome.$image) ) {
                WP_CLI::line(WP_CLI::colorize( "%rMissing File: %n".$image ));
                $valid_image = false;
            } else {
                // check the exif data to make sure it's valid image
                $type   = @exif_imagetype($wphome.$image);
                if (!$type or !in_array($type,$this->image_types)){
                    WP_CLI::line(WP_CLI::colorize( "%rCorrupt Image File: %n".$image ));
                    $valid_image = false;
                }
            }
            return $valid_image;

        }



    public function local_meta(){
        WP_CLI::line( "\n-----------------" );
        WP_CLI::line( WP_CLI::colorize( "%BSearching wp_resorts_meta%n" ) );
        WP_CLI::line( "-----------------\n" );

        $sql     = "SELECT * FROM wp_resorts_meta WHERE meta_value LIKE '%images%' ESCAPE '|' ORDER BY id ASC";  //LIMIT 100 OFFSET {$offset}
        $results = $this->wpdb->get_results( $sql, OBJECT );
        $count   = count( $results );

        WP_CLI::line("Found {$count} Meta Rows");

        $broken_image_counter = 0;
        $fixed_image_counter = 0;

        // loop through rows
        foreach ($results as $result) {

            $value = $result->meta_value;
            $meta  = json_decode( $value, true );

            // loop through images
            foreach ( $meta as $key => $image ) {
                if ( ! isset( $image['src'] ) ) {
                    continue;
                }
                if ( ! preg_match( static::$regex_local,
                    $image['src'],
                    $matches ) ) {
                    // not a match, skip row
                    continue;
                }
                if ($this->verbose) WP_CLI::line($image['src'] );

                    if (!$this->check_image( $image['src'])) {
                        $broken_image_counter++;
                        //try to fix
                          $url =    $this->download( "https://www.daelive.com".$image['src']);

                          if ($url){
                              $fixed_image_counter++;
                              WP_CLI::success( "Downloaded Image from DAE" );
                          } else {
                              WP_CLI::warning( "Unable to repair" );
                          }

                    }
            }
        }
        if ($broken_image_counter > 0) {
            WP_CLI::warning( "There were {$broken_image_counter} broken images." );
            WP_CLI::success( "Fixed {$fixed_image_counter} broken images." );
            $remaining_broken = $broken_image_counter - $fixed_image_counter;
            if ($remaining_broken > 0)
                WP_CLI::error( "There are {$remaining_broken} remaining broken images." );
        }else{
            WP_CLI::success( "There are no broken images" );
        }


    }





    /**
     *  process dae images
     *
     * @return void
     */
    public function run( $args, $assoc_args ) {

        // set the passed through args
        $this->set_args($args, $assoc_args);

        if ($this->check_dir())  { // check directory before running
            // green lights!
            $this->resorts();
            $this->resort_meta();
        } else {
            // nowhere to write to, get outta here
            exit;
        }
    }

    /**
     * Sets the args for the app
     *
     * @param $args
     * @param $assoc_args
     * @return void
     */
    private function set_args($args, $assoc_args) {

        // set args --dryrun  --verbose
        $this->dryrun = WP_CLI\Utils\get_flag_value($assoc_args, 'dryrun', false );
        $this->verbose = WP_CLI\Utils\get_flag_value($assoc_args, 'verbose', false );
        $this->safemode = WP_CLI\Utils\get_flag_value($assoc_args, 'safemode', false );

        // FORCE safemode for testing
       // $this->safemode = true;

        //debug dump flags
        $verbose_flag = ($this->verbose) ? "On" : "Off";
        $dryrun_flag = ($this->dryrun)? "On": "Off";
        $safemode_flag = ($this->safemode)? "On": "Off";

        // display the flags
        WP_CLI::line( "Verbose is:  ". $verbose_flag  );
        WP_CLI::line( "Safemode is:  ". $safemode_flag  );
        WP_CLI::line( "Dryrun is:  ". $dryrun_flag  );

    }


    /**
     * checks to make sure dest dir exists and writeable
     * and creates if doesn't exist
     * @return boolean
     */
    public function check_dir() {

        if ($this->verbose)  WP_CLI::line( "Checking Directory ". $this->dir  );
        // check directory
        if (!is_dir($this->dir)) {
            // no directory, create it
           if ($this->verbose) WP_CLI::line( "Creating directory ". $this->dir  );
            mkdir($this->dir,0755, true);
        }
        // make sure it's there and writable
        if (is_dir($this->dir) && is_writable($this->dir)){
            WP_CLI::success( "Direcotry ".$this->dir." is writable..." );
            return true;
        } else {
            WP_CLI::error( "Direcotry ".$this->dir." is not writable" );
            return false;
        }

    }

    /**
     * migrates the images in the wp_resorts table
     * @return void
     */
    public function resorts() {
        WP_CLI::line( "\n-----------------" );
        WP_CLI::line( WP_CLI::colorize( "%BSearching wp_resorts%n" ) );
        WP_CLI::line( "-----------------\n" );
        // $offset = 0;
        foreach ( [ 'ImagePath1', 'ImagePath2', 'ImagePath3' ] as $column ) {
            // blue column display for column working on...
            $sql     = "SELECT id,ResortName, {$column} FROM wp_resorts WHERE {$column} LIKE '%daelive.com%' ORDER BY id ASC "; // LIMIT 100 OFFSET {$offset}

            WP_CLI::colorize( "%BColumn {$column}%n" );
            $results = $this->wpdb->get_results( $sql, OBJECT );
            $count   = count( $results );

            if (!$this->verbose)   // hide progress bar on verbose
                $progress = \WP_CLI\Utils\make_progress_bar( 'Processing Images ('.$count.')', $count );

                foreach ( $results as $result ) {

                    if ($this->verbose) WP_CLI::line(PHP_EOL .$result->ResortName. "(".$result->id.")"  );

                    if (!preg_match(static::$regex,
                        $result->{$column},
                        $matches)) {
                        // not a match, skip row
                        continue;
                    }
                    if ($this->verbose)  WP_CLI::line( "Image: ". $matches[1] );
                    $url = $this->download($matches[1]);
                    if (!$url) {
                        // not a match, skip row
                        continue;
                    }
                    $value = str_replace($matches[1], $url, $result->{$column});
                    if (!$value) {
                        // not downloaded, skip
                        continue;
                    }
                    if (!$this->safemode and !$this->dryrun) {
                        $this->wpdb->update(
                            'wp_resorts',
                            [$column => $value],
                            ['id' => $result->id],
                            ['%s'],
                            ['%d']
                        );
                    }
                    if (!$this->verbose)   // hide progress bar on verbose
                        $progress->tick();

                 if ($this->verbose) WP_CLI::success( ($this->safemode?"(Safemode) ":" ").($this->dryrun?"(Dryrun) ":" ")."Updated url to {$value}" );
                }
                if (!$this->verbose)   // hide progress bar on verbose
                    $progress->finish();
        }
    }

    /**
     * migrates the images in the wp_resorts_meta table
     * @return void
     */
    public function resort_meta() {
        WP_CLI::line( "\n-----------------" );
        WP_CLI::line( WP_CLI::colorize( "%BSearching wp_resorts_meta%n" ) );
        WP_CLI::line( "-----------------\n" );

        do {
            $sql     = "SELECT id, meta_value FROM wp_resorts_meta WHERE meta_key = 'images' AND meta_value LIKE '%daelive.com%' ORDER BY id ASC ";  //LIMIT 100 OFFSET {$offset}
            $results = $this->wpdb->get_results( $sql, OBJECT );
            $count   = count( $results );

            if (!$this->verbose) { // hide progress bar on verbose
                $progress = \WP_CLI\Utils\make_progress_bar('Processing Images (' . $count . ')', $count);
            } else {
                WP_CLI::line("Processing {$count} Meta Rows.");
            }

            foreach ( $results as $result ) {
                $value = $result->meta_value;
                $meta  = json_decode( $value, true );
                $found = false;
                foreach ( $meta as $key => $image ) {
                    if ( ! isset( $image['src'] ) ) {
                        continue;
                    }
                    if ( ! preg_match( static::$regex,
                                       $image['src'],
                                       $matches ) ) {
                        // not a match, skip row
                        continue;
                    }

                    if ($this->verbose) WP_CLI::line( PHP_EOL . $matches[1] );
                    $url = $this->download( $matches[1] );
                    if ( ! $url ) {
                        // not a match, skip row
                        continue;
                    }
                    $meta[$key]['src'] = $url;
                    $found = true;
                    if ($this->verbose) WP_CLI::line( "Update url to {$url}" );
                }
                if ( !$found and $this->verbose)
                    WP_CLI::line('No Match found... skipping [OK]' );

                if($found and !$this->safemode and !$this->dryrun ){
                    // not a dryrun, safemode and match was found.. update the database
                    $this->wpdb->update(
                        'wp_resorts_meta',
                        [ 'meta_value' => json_encode($meta) ],
                        [ 'id' => $result->id ],
                        [ '%s' ],
                        [ '%d' ]
                    );
                }
                if ($found and $this->verbose) {
                    WP_CLI::success(($this->safemode ? "(Safemode) " : " ") . ($this->dryrun ? "(Dryrun) " : " ") . "Updated url in json");
                 //   WP_CLI::line( json_encode($meta));
                }

            }
           if (!$this->verbose)   // hide progress bar on verbose
            $progress->tick();

        } while ( $count > 0 );
        if (!$this->verbose) { // hide progress bar on verbose
            $progress->finish();
        } else {
            WP_CLI::success('Done.' );
        }
    }


    /**
     * download the image and save to the $this->dir
     * returns the new path to save in the database
     * new path does not include domain. Absolute path.
     *
     * @param string $image
     * @return string|null
     */
    function download( string $image ) {
        $path = parse_url( $image, PHP_URL_PATH );
        $dest = untrailingslashit( ABSPATH ) . str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );

        if ( file_exists( $dest ) ) {
           if ($this->verbose) WP_CLI::colorize( "%cAlready downloaded:%n {$image}" );
            return str_replace( [ '/', '\\' ], "/", $path );
        }

        if ($this->verbose)  WP_CLI::colorize( "%cDowloading to%n: {$dest}" );

        // if it's not a dryrun then download the image
        if (!$this->dryrun) {
            $response = wp_remote_get($image, [
                'stream' => true,
                'filename' => $dest,
            ]);
        }

        if ( isset($response) and is_wp_error( $response ) ) {
            if ($this->verbose) WP_CLI::warning( $response->get_error_message() );
            if ( file_exists( $dest ) ) {
                @unlink( $dest );
                return null;
            }
        }

        // make sure the file was written, otherwise stop and don't modify database
        // but continue to next file, anyways
        if (!file_exists( $dest ) ) {
            if ($this->verbose)  WP_CLI::warning( "Unable to save file ".$dest );
            return null;
        }

        return str_replace( [ '/', '\\' ], "/", $path );
    }
}

