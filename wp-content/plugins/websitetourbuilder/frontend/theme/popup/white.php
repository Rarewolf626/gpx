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
 
// No direct access to this file
if(!defined('ABSPATH')) die('Direct access of plugin file not allowed');

for ($i = 0; $i < $totalSteps; $i++) 
{
    //$item = $items[$i];
?>
  <?php /*?>  <div id="<?php echo $jfwstbdiv[$i]['textarea']; ?>" style="display: none;"><?php */?>
    <div id="jfwstb_<?php echo $i; ?>" style="display: none;">
        <p class="tooltipTitle"><?php echo $jfwstbdiv[$i]['steptitle']; ?></p>
        <p class="tooltipText"><?php echo $jfwstbdiv[$i]['textarea']; ?></p>

        <?php
        if ($i == $totalSteps-1 && $jfwstbopt['user_confirm_dialog'] == 'yes')
        {
        ?>
            <p style="margin: 10px 0;"><input type="checkbox" id="dont_show_again" value="1" onclick="show_me_again(this);" /> <?php echo __("Don't show me again next time", 'websitetourbuilder');?></p>
        <?php } ?>
        
        <?php
        if (($i > 0)&& ($totalSteps-1))
        {
        ?>
            <a href="javascript:;" class="prev-step" style="float:left;"><?php echo __("Prev", 'websitetourbuilder'); ?></a>
        <?php    
        }
        if ($i < $totalSteps-1)
        {
        ?>
            <a href="javascript:;" class="next-step" style="float:right;"><?php echo __("Next", 'websitetourbuilder'); ?></a>
        <?php    
        }
        ?>
        
        <?php /* TIMER CONTROLS ?>   <?php
        if((!empty($jfwstbdiv[$i]['steptime'])) && is_numeric($jfwstbdiv[$i]['steptime']) && $jfwstbdiv[$i]['steptime'] > 0)
        {
        ?> 
            <?php
            //if($params->get('show_timer_controls',0))
			if($jfwstbopt['wbstime'])
            {
            ?>
                <br /><br /><div id="websitetour-time-wrap" class="clearfix">
                    <div id="websitetour-time-ctrls">
                        <a id="time-prev" href="#" title="prev"></a> 
                        <a id="time-stop" href="#" title="stop"></a> 
                        <a id="time-pause" href="#" title="pause"></a> 
                        <a id="time-play" href="#" title="play"></a> 
                        <a id="time-next" href="#" title="next"></a>
                    </div>
                    <div id="websitetour_timer"><?php echo "Next Step start as" ?>: <div id="time_progress"></div></div>
                </div>
                <div class="clearfix"></div>
            <?php
           }
            ?>
            
        <?php    
        }
        ?>
        
    </div><?php */?>
<?php    
}
?>