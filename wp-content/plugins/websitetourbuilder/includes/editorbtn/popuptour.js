 /**
 * Web Site Tour Builder for Wordpress
 *
 * @package   websitetourbuilder
 * @author    JoomlaForce Team [joomlaforce.com]
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      http://joomlaforce.com
 * @copyright Copyright © 2014 JoomlaForce
 */


(function() {
	//Prendo i valori dello shortcode e li converto
	var myTourObject = JSON.parse(mce_options);
    tinymce.PluginManager.add('jfwstb_btn', function( editor, url ) {
        editor.addButton( 'jfwstb_btn', {
            title: 'Insert WebSite Tour Builder Shortcode',
            //type: 'menubutton',
            icon: 'icon jfwstb-editorbtn-icon',
			onclick: function() {
                        editor.windowManager.open( {
                            title: 'WebSite Tour Builder Shortcodes',
                            body: [	
							{   type: 'label',
								name: 'intro',
								text: "Select the Tour that you want to put on this page "},		
							{   type: 'label',
								name: 'spacer',
								text: ""},		
							{
								type: 'listbox',
								name: 'tourboxName',
								label: 'Select Title',
								'values': myTourObject
							}],
                             onsubmit: function( e ) {						   
							   var obj = myTourObject.filter(function ( obj ) {
    							return obj.text === e.data.tourboxName;
							   })[0];
							   //editor.insertContent( '[websitetour' + ' '+'title="'+e.data.tourboxName+'" '+'id="'+obj.id+'"'+']'+'</h4>');	
							   editor.insertContent( '[websitetour' + ' '+'id="'+obj.id+'"'+']'+'</h4>');	
							   //console.log( obj );
                            }
                        });
			}
        });
    });
})();