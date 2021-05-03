<?php
 /**
 * Web Site Tour Builder for Wordpress
 *
 * @package   websitetourbuilder
 * @author    JoomlaForce Team [joomlaforce.com]
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      http://joomlaforce.com
 * @copyright Copyright © 2014 JoomlaForce
 */
 
 
// todo nota: la funzione dello shortcode viene eseguita dopo il caricamento del wphead quindi non prende i valori
//ricavare prima i valori e passarli successivamente
// 2. caricare lo script del tour con i valori solo una volta se i post sono multipli !


class JFWSTshortcodehandler {
	
	//da vedere: funziona se la funzione create tour la carico nel body perchè recupera i metabox!
	//vedere di passarla nell'' head !
	public $shortid;
	public $post_type;
	public $mb_key_settings; 
	public $mb_key_steps;
	public $newmetabox1;
	public $newmetabox2;
	public $shortcount;
	

    public function __construct()  
     {
		 	
		$this->mb_key_settings = '_jfwst_metabox_2';
		$this->mb_key_steps = '_jfwst_metabox_1';
		$this->post_type='websitetour';
		$this->jquery_var='gQuery';
		$this->shortcount = -1;
		
        
        //session_start();                
		
		if(!is_admin()){
				
			//Call Output for Shortcode
			add_shortcode( 'websitetour' , array( $this, 'jfwbst_shortcode_handler' ) );
			//for widgets
			add_filter('widget_text', 'do_shortcode');
			//for comments
			add_filter( 'comment_text', 'do_shortcode' );
			//for excerpt
			add_filter( 'the_excerpt', 'do_shortcode');
			
			//vedere di inserirlo in componenti esterni !!!;
			
			//use this to call and count
			add_action( 'wp_enqueue_scripts', array( $this, 'jfwbst_shortcode_handler' ));
			
		} 	
    } 
	
	function linkbutton_function( $atts, $content = null ) {
   		return '<button type="button">'.do_shortcode($content).'</button>';
}

	
	function jfwstb_popup_styles() {
		$popupTheme= $this->newmetabox1['popuptheme'];
		$popupThemeLayoutPath = '../frontend/theme/popup/'.$popupTheme.'.css';	
		wp_register_style( 'jfwstb_popup', plugins_url($popupThemeLayoutPath, __FILE__), array(), '1.0', 'all' );
		wp_enqueue_style( 'jfwstb_popup' );
	}


	/* Shortcode output for "websitetour" */
	function jfwbst_shortcode_handler( $atts, $content = null) {
        
		//count shortcode istance found in page and pass to other function to avoid multiple script in body
		if(!$this->shortcount)
		{
			$this->shortcount = 0;
		}
		$this->shortcount++;	
			
		// da include in funzione esterna xcome avevo già fatto !
		wp_register_script('jfwstb_gquery', plugins_url('/assets/js/gquery-1.7.2.js', __FILE__));
		wp_register_script('jfwstb_pagetour',  plugins_url('/assets/js/jquery.wp_websitetour.js?'.time(), __FILE__));
		wp_register_script('jfwstb_chrony',  plugins_url('/assets/js/jquery.gotour.chrony.js', __FILE__));
		wp_enqueue_script('jfwstb_gquery');
		wp_enqueue_script('jfwstb_pagetour');
		wp_enqueue_script('jfwstb_chrony');
		wp_register_style( 'jfwstb_tourstyle', plugins_url('/assets/css/style.css', __FILE__), array(), '1.0', 'all' );
		wp_enqueue_style( 'jfwstb_tourstyle' );
		
	
		$opzioni = extract( shortcode_atts(array(
			'id'       => false,  // default value
			'title'    => 'no-title' // default value
			//'value'    => 'no-value',  // default value
			//'theme'    => 'dark', // default value
			
		  ),$atts));
        
        global $pid;     
        
        $id = $this->jfwbst_allow_usertypes($id); 
        $pid = $id;
                
        if($id)
        {     
            
            $metabox_settings = get_post_meta( $id, '_jfwst_metabox_2', true );     
                //print $metabox_settings['cookie_enable'];                    
            if ($metabox_settings['cookie_enable']=='no'){
                
                    unset($_COOKIE['play_tour_'.$id]);
                    add_action( 'wp_print_footer_scripts', function(){ global $pid; echo '<script type="text/javascript">setCookie("play_tour_'.$pid.'", "", -1);</script>'; } );
                    //setcookie ("play_tour_".$id, "", -1);    
                                                        
            }     
            //print_r($metabox_settings);                 
            if ($metabox_settings['user_confirm_dialog'] == "no"){
                
                unset($_COOKIE['dont_showme_'.$id]);
                //setcookie ("dont_showme_".$id, "", -1);
                add_action( 'wp_print_footer_scripts', function(){ global $pid; echo '<script type="text/javascript">setCookie("dont_showme_'.$pid.'", "", -1);</script>'; } );    
                                                        
            }
            
            if((isset($_COOKIE['play_tour_'.$id]) && $_COOKIE['play_tour_'.$id] == 'yes') || (isset($_COOKIE['dont_showme_'.$id]) && $_COOKIE['dont_showme_'.$id] == 'yes')){
                $id = 0;
                        
            }             
            
            /*print '<pre>';       
            print_R($_COOKIE);
            print '</pre>';  */  
        
        }
                 
		
		if (!is_null($content)) {
			/*Ricavo l'id dallo shortcode e li passo ad un altra funzione  per eseguire la query per recuperare i valori dei metabox*/
			//$this->shortid = $opzioni['id'];
			$this->shortid = $id;

		if ($this->shortid){
			
			//Assegno alle variabili globali newmetabox1 e newmetabox1 i valori 
			$metabox1 = $this->jfwbst_getmetabox_settings_values($this->shortid);
			$metabox2 = $this->jfwbst_getmetabox_steps_values($this->shortid);
            
            if(!$metabox2){
                return $htmlshortcode = '';
            }            
            
			$this->newmetabox1 =  $metabox1;
			$this->newmetabox2 =  $metabox2;		

		
			echo $lighttheme = $this->jfwbst_body_displayas();
			$tourscript = $this->jfwstb_script_createtour();
			//da trasferire nel head (attualmente sono nel body)
			$tourdiv = $this->jfwbst_body_createtour();
			$this->jfwstb_popup_styles();
				
			}	
			//$htmlshortcode = '<h1 title="' . $opzioni['title'] . '"><span>' . $opzioni['title'] . '</span></h1>';
			$htmlshortcode = '';
			//return '<p>'. $id . " title=".$title.'</p>';
			return $htmlshortcode;

		}
		
	}
    
    
    function jfwbst_allow_usertypes($id) {
        
        global $current_user;
        
        $mb_settings = get_post_meta( $id, '_jfwst_metabox_2', true );
        
        if(!isset($mb_settings['allow_usertypes']))
        {
            return 0;  
        }
        
	    $allow_usertypes  = (array)$mb_settings['allow_usertypes'];
        
        /*echo '<pre>';
        var_dump($allow_usertypes);
        echo '</pre>';*/
    
    	$user_roles = $current_user->roles;
    	$user_role = array_shift($user_roles);
        //print $user_role;
        
        if(in_array('all', $allow_usertypes))
        {
            return $id;
            
        } else if (in_array($user_role, $allow_usertypes))
        {
            return $id;
        } else {
            return 0;            
                    
        }        
        
    }
	
	function jfwbst_getmetabox_steps_values() {
		// Ricavo i Valori da Passare agli step
		$mb_steps = get_post_meta( $this->shortid, $this->mb_key_steps, true );
		//print_r($mb_steps['repeating_textareas']);
	return $mb_steps;
	}
	
	/* Shortcode output for "websitetour" */
	function jfwbst_getmetabox_settings_values() {
		// Ricavo i Valori da Passare agli step
		$mb_settings = get_post_meta( $this->shortid, $this->mb_key_settings, true );
		//print_r($mb_settings);
	return $mb_settings;
	}
	

	function jfwbst_body_displayas () {
	//richiamo i valori del metabox
	$totaltest = count ($this->newmetabox2['repeating_textareas']);
	//FUNZIONE LIGHTBOX TOUR 
	$displayas 	   = $this->newmetabox1['displayas'];
	$lightboxtitle = $this->newmetabox1['lightboxtitle'];
	$lightboxdesc  = $this->newmetabox1['lightboxdescription']; 
    
    //$this->newmetabox1['displayas'] = 'nodisplay';
    
	$autoStartJs   = '';
	
	switch ($displayas) {
		case 'link':
			//link
			$startlink ='<div class="tour-menu"><ul style="list-style:none;"><li><a id="open-walkthrough" href="javascript:;">'.'Start the Tour'.'</a></li></ul></div>';
			break;
		case 'button':
		echo "button";
			//button
			$startlink ='<div class="tour-menu"><ul style="list-style:none;"><li><a id="open-walkthrough" class="addslidebutton" href="javascript:;">'.'Start the Tour'.'</a></li></ul></div>';
			break;

		case 'lightbox':
		
			//lightbox	
			$lightboxtheme = $this->newmetabox1['lightboxtheme'];
			$lightboxThemePath = JFWST_FRONT.'theme/lightbox/'.$lightboxtheme.'.php';
			if(is_file($lightboxThemePath))
			{
				ob_start();
				include($lightboxThemePath);
				$startlink = ob_get_contents();
				ob_end_clean();
			}
			else
			{
				
				$startlink ='
					<div class="popup-overlay"></div>
					<div id="example-popup" class="popup">
						<div class="popup-body">    
							<span class="popup-exit"></span>
							<div class="popup-content">
									<h2 class="popup-title">'.$lightboxtitle.'</h2>
								<p>'.$lightboxdesc.'</p><div class="tour-menu">
								<a id="open-walkthrough" class="addslidebutton" >Start the Tour</a>
								</div>
							</div>
						</div>
					</div>';
			}
			
			break;
		case 'nodisplay':
			
			$startlink="";
			break;
			
		default: 
			$startlink="";
			break;
		}	
		
		return $startlink;
	
	}
	
	function jfwstb_script_createtour() {
	   
    global $wp_version;     
		
	//echo $this->shortcount;
	if ($this->shortcount>1) return;	
	
	//richiamo i valori del metabox
	$jfwstbsteps = $this->newmetabox2['repeating_textareas'];
	$jfwstboptions =  $this->newmetabox1;
	$totalSteps = count($jfwstbsteps);      
        
	
	$jquery_var="gQuery";
	$redirectPendingJS = '';
	$websiteTourModuleId='';
	$websiteTourModuleId=$this->shortid;
	
	//choose if start onload
	$autoStartJs='';
	if ($this->newmetabox1['displayas']=='autostart'){
	$autoStartJs .= "gQuery.pagewalkthrough('show', 'walkthrough');\n"; 
    }

    if ($this->newmetabox1['cookie_enable']=='yes'){
	   $autoStartJs .= "setCookie('play_tour_".$websiteTourModuleId."', 'yes', ".$this->newmetabox1['cookie_expired_date'].");\n";
    } else {
       $autoStartJs .= "setCookie('play_tour_".$websiteTourModuleId."', '', -1);\n";
    }
	
	$redirectPendingJS = "if(redirectCookie!='undefined' && redirectCookie!='' && !isNaN(redirectCookie)){
		setCookie('redirect_pending', '', -1);
		if(gQuery('.popup-exit').length > 0) clearPopup();
		gQuery.pagewalkthrough('show', 'walkthrough');
	   }\n";
	$onload = 'true';
	$enableKeyboard = "enableKeyboard: true,\n";
	//added by sam for remembering steps from cookie
	$remember_last_step = "walkthroughCookieKey: '".'walkthrought_last_step'.$websiteTourModuleId."',cookie_expire_day: 365,";
	$timerControlJS = "";
	//$enableTimer = "enableTimer : true,";  
	$enableTimer = "";  
		    
        //$totalSteps = count($items);
     
	$stepsscript = "var redirectCookie = getCookie('redirect_pending'); 
    var websiteTourTimer=false;
	var websiteTourTime=0;
	var totalSteps = $totalSteps; ";
    
    $stepsscript .= "\n
    function show_me_again(obj) {\n
        if (gQuery(obj).is(':checked')) {\n
            setCookie('dont_showme_".$websiteTourModuleId."', 'yes', ".$this->newmetabox1['cookie_expired_date'].");\n
            gQuery('#jpwClose a').click();\n
        } else {\n
            //setCookie('dont_showme_".$websiteTourModuleId."', '', -1);\n
        }\n
	}\n ";
    
    $stepsscript .="
	gQuery(document).ready(function(){
       
	//inizio popup
	 gQuery('html').addClass('overlay');
	 gQuery('#example-popup').addClass('visible');
	//end
	function clearPopup() {
	 gQuery('.popup.visible').addClass('transitioning').removeClass('visible');
	 gQuery('html').removeClass('overlay');
	setTimeout(function () {
		 gQuery('.popup').removeClass('transitioning');
	}, 200); 
	}";
	$stepsscript .= $jquery_var."(document).keyup(function (e) {
	  if (e.keyCode == 27 &&  gQuery('html').hasClass('overlay')) { clearPopup(); } });
     
	 gQuery('.popup-exit').click(function () { clearPopup(); });
	 gQuery('.addslidebutton').click(function () { clearPopup(); });
	 gQuery('.popup-overlay').click(function () { clearPopup(); });
	 gQuery('.tour-menu ul li a#open-walkthrough').click(function () { clearPopup(); });
	 //fine popup ";
	
	$stepsscript .= "
	gQuery('#walkthrough').pagewalkthrough({
	steps:
	[
	";
	//print_r($jfwstbsteps);
	//print_r($jfwstboptions);
	//print_r($this->shortid);

	for ($i = 0; $i < $totalSteps; ++$i) {	
		
        
        if(!isset($jfwstbsteps[$i]['wrapname']))
        {
            $jfwstbsteps[$i]['wrapname'] = '';
        }
        
		//$item = $items[$i];
		//print_r($item->stepcontent);
		//echo $items->stepcontent;
		$stepsscript .="{";
        
        if(!isset($jfwstbsteps[$i]['steptype']))
        {
            $jfwstbsteps[$i]['steptype'] = '';
        }
                  
		if ($jfwstbsteps[$i]['steptype']=='modal'){
			$stepsscript .=" wrapper: '', ";
		} else {
		  
            if(!isset($jfwstbsteps[$i]['wraptype']))
            {
                $jfwstbsteps[$i]['wraptype'] = '';
            }
          
			if($jfwstbsteps[$i]['wraptype'] == 'id'){ $stepsscript .=" wrapper: '#".$jfwstbsteps[$i]['wrapname']."', "; }
			else if($jfwstbsteps[$i]['wraptype'] == 'class'){ $stepsscript .=" wrapper: '".$jfwstbsteps[$i]['wrapname']."', "; }
			else if($jfwstbsteps[$i]['wraptype'] == 'name') { $stepsscript .=" wrapper: '[name=".$jfwstbsteps[$i]['wrapname']."]', "; }
		}
		$stepsscript .=" margin: '0', popup: { content: '#jfwstb_".$i."', type: '".$jfwstbsteps[$i]['steptype']."', ";
		if(isset($jfwstbsteps[$i]['wrapdrag']))
        {
            if ($jfwstbsteps[$i]['steptype']=='modal'){
    			$stepsscript .=" position:'', ";
    		} else {
    			$stepsscript .=" position: type".$i.", ";
    		}
        } else {
            $stepsscript .=" position: type".$i.", ";
        }
        
        if(isset($jfwstbsteps[$i]['wrapdrag']))
        {
		  if($jfwstbsteps[$i]['wrapdrag']=='yes'){$wrap = '1';} else {  $wrap= '0'; }
        } else {
            $wrap= '0';
        }
        
        if(isset($jfwstbsteps[$i]['stepwidth'])){$stepwidth = $jfwstbsteps[$i]['stepwidth'];} else {  $stepwidth= '200'; }
        
        

		$stepsscript .=" offsetHorizontal: 0, 
						 offsetVertical: 0, 
						 width: '".$stepwidth."', 
						 draggable: ".$wrap.",
						 ";
						 
		if(!empty($jfwstbsteps[$i]['steprotation'])) {
			$stepsscript .= "contentRotation: '".$jfwstbsteps[$i]['steprotation']."'";
		}else {
			$stepsscript .= "contentRotation: 0 ";
		}
		
		$stepsscript .=	" }, ";
		$comma = '';
		//if((($i+1)<count($items)))
		if((($i+1)<count($jfwstbsteps))){ $comma = ','; }
		if(!empty( $jfwstbsteps[$i]['stepredirect'] ))
		{
			$stepsscript .=" redirect_to:'".$jfwstbsteps[$i]['stepredirect']."', ";
		}
		if((!empty($jfwstbsteps[$i]['steptime'])) && is_numeric($jfwstbsteps[$i]['steptime']) && $jfwstbsteps[$i]['steptime'] > 0)
		{
			$stepsscript .=" hault_time:".(int)$jfwstbsteps[$i]['steptime'].", ";
		}
		$stepsscript .=" overlay: true }  ".$comma;

	} // end for
	
			$stepsscript .="],
			$enableKeyboard
			$enableTimer
			$remember_last_step        
			name: 'Walkthrough',
			onLoad: $onload,
			onClose: function(){
				gQuery('.tour-menu ul li a#open-walkthrough').removeClass('active');
				return true;
			},
			onCookieLoad: function(){
			   //console.log('This callback executed when onLoad cookie is FALSE');		
			return null;
						}
		});
	
	
	// START THE TOUR
	gQuery('.tour-menu a').each(function(){
	  gQuery('.tour-menu').find('a.active').removeClass('active');
	  gQuery(this).click(function(){
		  gQuery(this).addClass('active');
		  var id = gQuery(this).attr('id').split('-');
		  if(id == 'parameters') return;
		  gQuery.pagewalkthrough('show', id[1]); 
	  });
	});
	gQuery( \"body\" ).on( \"click\", \".prev-step\", function(e) {
	
	//Timer Controls
    
	if(websiteTourTimer){
		gQuery('#time_progress','#tooltipInner').chrony('set', { destroy: true });
		websiteTourTimer = false;
	}    
	
	gQuery.pagewalkthrough('prev',e);
	});
	gQuery( \"body\" ).on( \"click\", \".next-step\", function(e) {
		
	if(websiteTourTimer){
		gQuery('#time_progress','#tooltipInner').chrony('set', { destroy: true });
		websiteTourTimer = false;
	}   
	gQuery.pagewalkthrough('next',e);
	});
	gQuery( \"body\" ).on( \"click\", \".restart-step\", function(e) {
		gQuery.pagewalkthrough('restart',e);
	});  
	gQuery( \"body\" ).on( \"click\", \".close-step\", function(e) {
		gQuery.pagewalkthrough('close');
	});
	$timerControlJS
	$autoStartJs
	$redirectPendingJS
	}); 
	";

	//Print Javascript Tour in Head    
    echo '
    <script type="text/javascript">
    
    var step_close = unescape("'.__('Click to close', 'websitetourbuilder').'");
    
    var wp_version_var = "'.$wp_version.'";
    
 var viewportwidth;
 var viewportheight;
   
 if (typeof window.innerWidth != "undefined")
 {
      viewportwidth = window.innerWidth,
      viewportheight = window.innerHeight
 } 
 else if (typeof document.documentElement != "undefined"
     && typeof document.documentElement.clientWidth !=
     "undefined" && document.documentElement.clientWidth != 0)
 {
       viewportwidth = document.documentElement.clientWidth,
       viewportheight = document.documentElement.clientHeight
 }  
 else
 {
       viewportwidth = document.getElementsByTagName("body")[0].clientWidth,
       viewportheight = document.getElementsByTagName("body")[0].clientHeight
 }
 
gQuery( window ).resize(function(e) {
    //gQuery.pagewalkthrough(\'show\', \'walkthrough\');
    if(gQuery( "#jpWalkthrough" ).length > 0)
    {
        gQuery.pagewalkthrough(\'responsiveshow\', \'walkthrough\', current_step);
    }
    //console.log(rokon);
});
 </script>
 ';
    
    $extrajs = '<script type="text/javascript">';   
    
    for ($i = 0; $i < $totalSteps; ++$i) {	    
    
        if(!isset($jfwstbsteps[$i]['steppos']))
        {
            $jfwstbsteps[$i]['steppos'] = 'bottom';
        }
    
        $extrajs .= '        
        if(viewportwidth < 956)
        {
            var type'.$i.' = "bottom";
        } else {
            var type'.$i.' = "'.$jfwstbsteps[$i]['steppos'].'";
        }';
    
    }
    
    $extrajs .= '</script>';
    
    echo $extrajs;
    
	echo "<script type='text/javascript'>\n";
	echo $stepsscript;
	echo "\n</script>\n";
		
	}
	
	function jfwbst_body_createtour () {
		
		if ($this->shortcount>1) return;	

		$jfwstbdiv = $this->newmetabox2['repeating_textareas'];
		$jfwstbopt = $this->newmetabox1;
				
		$popupTheme = $jfwstbopt['popuptheme'];
		$steps = '<div id="walkthrough">';
		$totalSteps = count($jfwstbdiv);
		$popupThemeLayoutPath = JFWST_FRONT.'theme/popup/'.$popupTheme.'.php';
		if(is_file($popupThemeLayoutPath))
		{
			ob_start();
			include($popupThemeLayoutPath);
			$steps .= ob_get_contents();
			ob_end_clean();
		}
		else
		{
			for ($i = 0; $i < $totalSteps; $i++) {
				//$item = $items[$i];				
				$steps .='<div id="jfwstb_'.$i.'" style="display: none;">';
					$steps .='<p class="tooltipTitle">'.$jfwstbdiv[$i]['steptitle'].'</p>';
					$steps .='<p>'.$jfwstbdiv[$i]['textarea'].'</p>';	
					
                    
                    if ($i == $totalSteps-1 && $jfwstbopt['user_confirm_dialog'] == 'yes')
                    {
                        $steps .='<p style="margin: 10px 0;"><input type="checkbox" id="dont_show_again" value="1" onclick="show_me_again(this);" /> '.__("Don't show me again next time", 'websitetourbuilder').'</p>';
                    }                    
                    
					if (($i > 0)&& ($totalSteps-1))
					$steps .= '<a href="javascript:;" class="prev-step" style="float:left;">'.__("Prev", 'websitetourbuilder').'</a>';
					
					if ($i < $totalSteps-1)
					$steps .= ' <a href="javascript:;" class="next-step" style="float:right;">'.__("Next", 'websitetourbuilder').'</a>';
				$steps .='</div>';
			}    
		}
		$steps .= "";
		$steps .='</div>';//end of walkthrough div
		
		echo $steps;
		?>
		<!-- END PAGES TOUR -->
		<?php
	} //EOF
	

} //EOC

?>