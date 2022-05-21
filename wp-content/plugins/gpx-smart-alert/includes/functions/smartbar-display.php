<?php
//add action
/*
 * note that all themes must have <?php gpr_smartbar_load();?> right after the body for this to work!
 * this requirement will change when WP is updated to version 5.2 and the theme is compatible with the change wp_body_open() action
 */
function gpr_smartbar_load()
{
    do_action('gpr_smartbar_load');
}
function gpr_display_smartbar()
{
    global $wpdb;
    //retrieve all of the published items from this website
    $args = array(
        'posts_per_page' => -1,
        'post_type' => GPR_SA,
        'post_status' => 'publish'

    );
    $sb_query = new WP_Query( $args );
    if ( $sb_query->have_posts() )
    {
        while ( $sb_query->have_posts() )
        {
            $sb_query->the_post();
            $id = get_the_ID();
            $gprSB[$id]['id'] = $id;
            $gprSB[$id]['title'] = get_the_title();
            $gprSB[$id]['name'] = get_the_title().$id;
            $gprSB[$id]['content'] = get_the_content();
            $gprSB[$id]['start_date'] = rwmb_meta('gprsb-start_date');
            $gprSB[$id]['end_date'] = rwmb_meta('gprsb-end_date');
            $gprSB[$id]['background_color'] = rwmb_meta('gprsb-background_color');
            $gprSB[$id]['text_color'] = rwmb_meta('gprsb-text_color');
            $gprSB[$id]['priority'] = rwmb_meta('gprsb-priority');
            $gprSB[$id]['cta_text'] = rwmb_meta('gprsb-cta_text');
            $gprSB[$id]['cta_action'] = rwmb_meta('gprsb-cta_action');
            $gprSB[$id]['cta_background'] = rwmb_meta('gprsb-cta_background');
            $gprSB[$id]['cta_text_color'] = rwmb_meta('gprsb-cta_text_color');
            $gprSB[$id]['display_page'] = rwmb_meta('gprsb-display_page');
            $gprSB[$id]['custom_page'] = rwmb_meta('gprsb-custom_page');
            $gprSB[$id]['websites'] = rwmb_meta('gprsb-websites');
        }
    }

    wp_reset_query();

    //retrieve all of the published items from the parent website
    $url = get_option('gpr_smartbar_parent_url');
    //get all the fees from the api...

    $inputMembers = [
        'per_page' => 100,
    ];
    $url .= '/wp-json/wp/v2/'.GPR_SA;
    $url .= '?'.http_build_query($inputMembers);

    $action = '';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array('Content-Type:application/json')
        );
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $response = curl_exec($ch);

    $datas = json_decode($response, true);

    if(!empty($datas))
    {
        foreach($datas as $data)
        {
            $id = 'R_'.$data['id'];
            $gprSB[$id]['id'] = $id;
            $gprSB[$id]['title'] = $data['title']['rendered'];
            $gprSB[$id]['name'] = $data['title']['rendered'].$id;
            $gprSB[$id]['content'] = $data['content']['rendered'];
            $gprSB[$id]['start_date'] =  $data['meta_box']['gprsb-start_date'];
            $gprSB[$id]['end_date'] = $data['meta_box']['gprsb-end_date'];
            $gprSB[$id]['background_color'] = $data['meta_box']['gprsb-background_color'];
            $gprSB[$id]['text_color'] = $data['meta_box']['gprsb-text_color'];
            $gprSB[$id]['priority'] = $data['meta_box']['gprsb-priority'];
            $gprSB[$id]['cta_text'] = $data['meta_box']['gprsb-cta_text'];
            $gprSB[$id]['cta_action'] = $data['meta_box']['gprsb-cta_action'];
            $gprSB[$id]['cta_background'] = $data['meta_box']['gprsb-cta_background'];
            $gprSB[$id]['cta_text_color'] = $data['meta_box']['gprsb-cta_text_color'];
            $gprSB[$id]['display_page'] = $data['meta_box']['gprsb-display_page'];
            $gprSB[$id]['custom_page'] = $data['meta_box']['gprsb-custom_page'];
            $gprSB[$id]['websites'] = $data['meta_box']['gprsb-websites'];
        }
    }

    //order by priority
    $titles = [];
    $sbp = '';
    $page_id = get_queried_object_id();
    $website = get_site_url();
    $website = str_replace("https", "", $website);
    $website = str_replace("http", "", $website);
    $website = str_replace("://", "", $website);
    $website = str_replace("www.", "", $website);
    $website = str_replace("/", "", $website);

    //are there any cookies set?
    if(isset($_COOKIE['gpr_sb_close']))
    {
        $cookies = json_decode(html_entity_decode(stripslashes ($_COOKIE['gpr_sb_close'])), true);
    }
    //get the users IP for use when closing the alert
    $userIP = $_SERVER['REMOTE_ADDR'];
    // note: that we are only storine the $_SERVER['REMOTE_ADDR'] because we are only keeping this for the purpose of hiding the bar
    // if they are using a proxy then it's on them if they use a different when they visit again.

    //don't block GPR
    if($userIP != '70.167.2.250')
    {
        //get all the IP's from the database
        $sql = $wpdb->prepare("SELECT name FROM {$wpdb->prefix}gpr_smartbar_hide WHERE user_ip=%s", $userIP);
        $blockedNames = $wpdb->get_results($sql);
        foreach($blockedNames as $bn)
        {
            $blocked[] = $bn->name;
        }
    }
    foreach($gprSB as $sbk=>$sb)
    {
        //have we set this title?
        if(in_array($sb['title'], $titles))
        {
            continue;
        }

        //add the "name" of this alert
        //note: the name is made up of the title plus id appended to the end.  This was done so that an admin can push
        $name = $sb['name'];

        //has the user chosen to close this smart bar?
        if(isset($cookies) && in_array($name, $cookies))
        {
            continue;
        }
        //did this person hide it in the database?
        if(isset($blockedNames) && isset($blocked) && in_array($name, $blocked))
        {
            continue;
        }

        //is this the right webiste?
        if(isset($sb['websites']) && !empty($sb['websites']))
        {
            if(!in_array($website, $sb['websites']))
            {
                continue;
            }
        }

        //does this belong on this page?
        if(isset($sb['display_page']) && !empty($sb['display_page']))
        {
            if(!in_array($page_id, $sb['display_page']))
            {
                continue;
            }
        }

        if(isset($sb['custom_page']) && !empty($sb['custom_page']))
        {
            //get the query string
            $querystring = explode("&", $_SERVER['QUERY_STRING']);
            $splitQuestion = explode("?", $sb['custom_page']);
            if(count($splitQuestion) == 1)
            {
                $sbs = $splitQuestion[0];
            }
            else
            {
                $sbs = $splitQuestion[1];
            }
            $sbQueryString = explode("&", $sbs);
            $i = 0;
            foreach($sbQueryString as $qs)
            {
                if(!in_array($qs, $querystring))
                {
                    continue;
                }
                $i++;
            }
            if(count($sbQueryString) != $i)
            {
                continue;
            }
        }

        //does this fall within the date range?
        if(date('h:i:s', strtotime($sb['start_date'])) == '00:00:00')
        {
            $compareStart = strtotime(date("Y-m-d 00:00:01"));
        }
        else
        {
            $compareStart = strtotime('-7 hours');
        }
        if(date('h:i:s', strtotime($sb['start_date'])) == '00:00:00')
        {
            $compareEnd = strtotime(date("Y-m-d 23:59:59"));
        }
        else
        {
            $compareEnd = strtotime('-7 hours');
        }
        if(strtotime($sb['start_date'] ) > $compareStart)
        {
            continue;
        }
        if($compareEnd > strtotime($sb['end_date']))
        {
            continue;
        }

        //find the priority
        $priority = $sb['priority'];
        if($sbp == $priority)
        {
            //this priority key already exists -- change it slightly
            $priority = $priority.".0";
        }

        //add titles so that we don't duplicate these posts
        $titles[] = $sb['title'];

        //set the prioirty
        $sbByPriority[$priority] = $sb;
    }

    $html  = '';
    if(isset($sbByPriority))
    {
        //rearange by priority
        ksort($sbByPriority);
        $html .= '<div class="gpr_smart_bars">';
            foreach($sbByPriority as $v)
            {
                $name = $v['name'];
            	$html .= '<div class="each_gpr_sb" data-id="'.$v['id'].'" style="background-color: '.$v['background_color'].'; color: '.$v['text_color'].'">';
            		$html .= '<div class="gpr_sb_close" data-sb="'.$name.'" data-ip="'.$userIP.'">&times;</div>';
            		$html .= '<div class="gpr_sb_content_wrapper">';
            			$html .= '<div class="gpr_sb_content">';
            				$html .= $v['content'];
            			$html .= '</div>';
            			if(!empty($v['cta_text']))
            			{
            			    $textColor = $v['background_color'];
            			    if(!empty($v['cta_text_color']))
            			    {
            			        $textColor = $v['cta_text_color'];
            			    }
            			$html .= '<div class="gpr_sb_cta_wrapper">';
            				$html .= '<a href="'.$v['cta_action'].'" style="background-color: '.$v['cta_background'].'; color: '.$textColor.';" target="_blank">';
            					$html .= $v['cta_text'];
            				$html .= '</a>';
            			$html .= '</div>';
            			}
            		$html .= '</div>';
            	$html .= '</div>';
            	if(!isset($v['text-color']))
            	{
            	    $v['text-color'] = '';
            	}
            	$tabs[] = '<div class="sb-tabs" data-id="'.$v['id'].'"style="display: none; background-color: '.$v['background_color'].';"><span class="circled" style="background-color: '.$v['text-color'].'; color: '.$v['background_color'].';">&excl;</span></div>';
            }
            if(isset($tabs))
            {
                $html .= '<div class="sb-tabs-wrapper">';
                $html .= implode('', $tabs);
                $html .= '</div>';
            }
    	$html .= '</div>';
    }
    echo $html;
}
// add_action('wp_body_open', 'gpr_display_smartbar');
// add_action('wp_body_open', 'gpr_display_smartbar');
add_action('gpr_smartbar_load', 'gpr_display_smartbar');
