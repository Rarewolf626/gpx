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
// no direct access
if(!defined('ABSPATH')) die('Direct access of plugin file not allowed'); 
	

?>

<div class="popup-overlay"></div>
<div id="example-popup" class="popup">
	<div class="popup-body" style="background-color:#fff !important;">    
		<span class="popup-exit"></span>
		<div class="popup-content">
		  <?php /*?>  <h2 class="popup-title"><?php echo $lightboxtitle; ?></h2><?php */?>
          <h2 class="popup-title"><?php echo $lightboxtitle; ?></h2>
			<p><?php echo $lightboxdesc; ?></p>
                <div class="tour-menu">
                    <a id="open-walkthrough" class="addslidebutton">Start the Tour<?php //echo JText::_('MOD_WEBSITETOUR_START'); ?></a>
    			</div>
            
		</div>
	</div>
</div>


