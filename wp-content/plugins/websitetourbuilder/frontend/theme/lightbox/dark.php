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
if(!defined('ABSPATH')) die('Direct access of plugin file not allowed'); ?>

<div class="popup-overlay-dark"></div>
<div id="example-popup" class="popup">
	<div class="popup-body" style="background-color:#000 !important;">    
		<span class="popup-exit"></span>
		<div class="popup-content">
		    <h2 class="popup-title" style="color:#fff;"><?php echo $lightboxtitle; ?></h2>
			<p style="color: #fff;"><?php echo $lightboxdesc; ?>
                <div class="tour-menu">
                <a id="open-walkthrough" class="addslidebutton">Start the Tour<?php //echo JText::_('MOD_WEBSITETOUR_START'); ?></a>
                </div>
            </p>
		</div>
	</div>
</div>