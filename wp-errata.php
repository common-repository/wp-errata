<?php
  /*
    Plugin Name: WP Errata
    Plugin URI: http://jaspreetchahal.org/wordpress-errata-plugin
    Description: This plugin allows you to receive post corrections from your blog readers. You can make make specific containers such as DIV, p etc inside the post editable.
    Author: Jaspreet Chahal
    Version: 1.0
    Author URI: http://jaspreetchahal.org
    License: GPLv2 or later
    */

    /*
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    */
    
    // if not an admin just block access
    if(preg_match('/admin\.php/',$_SERVER['REQUEST_URI']) && is_admin() == false) {
        return false;
    }
    require_once 'ErrataMobile_Detect.php';
    $detectjcucn = new ErrataMobile_Detect();
    register_activation_hook(__FILE__,'jcwperrata_activate');
    function jcwperrata_activate() {
            add_option('jcwperrata_active','1');
            add_option('jcwperrata_email','');
            add_option('jcwperrata_selectall',"Yes");
            add_option('jcwperrata_resetclose',"Yes");
            add_option('jcwperrata_savebuttonlabel',"Send for correction");
            add_option('jcwperrata_cancelbuttonlabel','Reset and close');
            add_option('jcwperrata_usermessage',"");
            add_option('jcwperrata_disableon_tablet','');
            add_option('jcwperrata_disableon_mobile','');
            add_option('jcwperrata_linkback','No');
    }
    
    add_action("admin_menu","jcwperrata_menu");
    function jcwperrata_menu() {
        add_options_page('WP Errata', 'WP Errata', 'manage_options', 'jcwperrata-plugin', 'jcwperrata_plugin_options');
    }
    add_action('admin_init','jcwperrata_regsettings');
    function jcwperrata_regsettings() {        
        register_setting("jcwperrata-setting","jcwperrata_active");
        register_setting("jcwperrata-setting","jcwperrata_email");
        register_setting("jcwperrata-setting","jcwperrata_selectall");
        register_setting("jcwperrata-setting","jcwperrata_resetclose");
        register_setting("jcwperrata-setting","jcwperrata_savebuttonlabel");
        register_setting("jcwperrata-setting","jcwperrata_cancelbuttonlabel");
        register_setting("jcwperrata-setting","jcwperrata_fancydialog");
        register_setting("jcwperrata-setting","jcwperrata_usermessage");
        register_setting("jcwperrata-setting","jcwperrata_disableon_mobile");
        register_setting("jcwperrata-setting","jcwperrata_disableon_tablet");
        register_setting("jcwperrata-setting","jcwperrata_linkback");
        wp_enqueue_script('jquery');
        wp_enqueue_script('jqueryui');
    }
    
    
    add_action('wp_head','jcwperrata_init');
    function jcwperrata_init() {
        global $detectjcucn;
        global $post;
        if((get_option("jcwperrata_disableon_mobile") == "Yes" && $detectjcucn->isMobile()) || (get_option("jcwperrata_disableon_tablet") == "Yes" && $detectjcucn->isTablet())) {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_style("jcwperrata_style",plugins_url("jcedit.css",__FILE__));
        wp_enqueue_script('jcwperrata_script',plugins_url("jcedit.min.js",__FILE__), array('jquery'),'1.0');// embed the javascript file that makes the AJAX request
        wp_enqueue_script( 'jcedit-ajax-request', plugin_dir_url( __FILE__ ) . 'ajax.js', array( 'jquery' ) );
        wp_localize_script( 'jcedit-ajax-request', 'JCEditAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),'postID'=> $post->ID) );
            
    }

    add_action( 'wp_ajax_nopriv_jcedit-submit', 'jcedit_submit' );
    add_action( 'wp_ajax_jcedit-submit', 'jcedit_submit' );

    function jcedit_submit() {
        // get the submitted parameters
        $postID = $_POST['postID'];
        $message = "Hello,\n\nA blog reader (Name: ".$_POST["name"].", Email: ".$_POST["email"].") has sent a correction for your article. \n\nArticle: ".get_permalink($postID)."\n\nOld Content:\n*******************\n\n ".$_POST["oldContent"]."\n\nNew Content\n*****************\n\n".$_POST["newContent"]."\n\n\nMessage sent by WP Errata plugin by http://jaspreetchahal.org";
        wp_mail(get_option("jcwperrata_email"),"Suggested correction for your post",$message);
        // generate the response
        $response = json_encode( array( 'success' => true) );

        // response output
        header( "Content-Type: application/json" );
        echo $response;

        // IMPORTANT: don't forget to "exit"
        exit;
    }


    add_action('wp_footer','jcwperrata_inclscript',20);
    function jcwperrata_inclscript() {
        global $detectjcucn;
        if((get_option("jcwperrata_disableon_mobile") == "Yes" && $detectjcucn->isMobile()) || (get_option("jcwperrata_disableon_tablet") == "Yes" && $detectjcucn->isTablet())) {
            return;
        }
        if(get_option('jcwperrata_active') == "1") {
        ?> 
         <script>

               jQuery(window).load(function() {
             jQuery(".jcedit").jcedit({saveAction:function (originalContent,newContent,editableElement) {
                 jQuery.post(
                             // see tip #1 for how we declare global javascript variables
                             JCEditAjax.ajaxurl,
                             {
                                 action : 'jcedit-submit',
                                 // other parameters can be added along with "action"
                                 postID : JCEditAjax.postID,
                                 oldContent:originalContent,
                                 newContent:newContent,
                                 url:location.href,
                                 name:jQuery("#jcedit-user").val(),
                                 email:jQuery("#jcedit-email").val()
                             },
                             function( response ) {
                                 if(response.success) {


                                         alert('<?php if(strlen(trim(get_option("jcwperrata_usermessage"))) == 0) echo 'Thank you for your contribution for making this post better. An administrator has been notified.'; else echo get_option("jcwperrata_usermessage");?>');

                                 }
                             }
                     );
                 },
                 fancyDialog:<?php echo (trim(get_option("jcwperrata_fancydialog")))== "Yes"?'true':'false'?>,
                 stripTagsOnPasteInsert:true,
                 debug:true,
                 showHelpAlertAfterSelectAll:true,
                 saveBtnLabel:"<?php echo get_option("jcwperrata_savebuttonlabel")?>",
                 cancelBtnLabel:"<?php echo get_option("jcwperrata_cancelbuttonlabel")?>"
             });

         });
         </script>
         
        <?php
        if(get_option('jcwperrata_linkback') =="Yes") {
            echo '<a style="font-size:0em !important;color:transparent !important" href="http://jaspreetchahal.org">Article correction is powered by http://jaspreetchahal.org</a>';
        }
        }
    }
    
    function jcwperrata_plugin_options() {
        jcwperrataDonationDetail();
           
        ?> 
        <style type="text/css">
        .jcorgbsuccess, .jcorgberror {   border: 1px solid #ccc; margin:0px; padding:15px 10px 15px 50px; font-size:12px;}
        .jcorgbsuccess {color: #FFF;background: green; border: 1px solid  #FEE7D8;}
        .jcorgberror {color: #B70000;border: 1px solid  #FEE7D8;}
        .jcorgb-errors-title {font-size:12px;color:black;font-weight:bold;}
        .jcorgb-errors { border: #FFD7C4 1px solid;padding:5px; background: #FFF1EA;}
        .jcorgb-errors ul {list-style:none; color:black; font-size:12px;margin-left:10px;}
        .jcorgb-errors ul li {list-style:circle;line-height:150%;/*background: url(/images/icons/star_red.png) no-repeat left;*/font-size:11px;margin-left:10px; margin-top:5px;font-weight:normal;padding-left:15px}
        td {font-weight: normal;}
        </style><br>
        <div class="wrap" style="float: left;" >
            <?php             
            
            screen_icon('tools');?>
            <h2>JaspreetChahal's WP Errata settings</h2>
            <?php 
                $errors = get_settings_errors("",true);
                $errmsgs = array();
                $msgs = "";
                if(count($errors) >0)
                foreach ($errors as $error) {
                    if($error["type"] == "error")
                        $errmsgs[] = $error["message"];
                    else if($error["type"] == "updated")
                        $msgs = $error["message"];
                }

                echo jcwperrataMakeErrorsHtml($errmsgs,'warning1');
                if(strlen($msgs) > 0) {
                    echo "<div class='jcorgbsuccess' style='width:90%'>$msgs</div>";
                }

            ?><br><br>
            <form action="options.php" method="post" id="jcorgbotinfo_settings_form">
            <?php settings_fields("jcwperrata-setting");?>
            <table class="widefat" style="width: 700px;" cellpadding="7">
                <tr valign="top">
                    <th scope="row">Enabled</th>
                    <td><input type="radio" name="jcwperrata_active" <?php if(get_option('jcwperrata_active') == "1"|| get_option('jcwperrata_active') == "") echo "checked='checked'";?>
                            value="1" 
                            /> Yes
                            <input type="radio" name="jcwperrata_active" <?php if(get_option('jcwperrata_active') == "0" ) echo "checked='checked'";?>
                            value="0" 
                            /> No 
                    </td>
                </tr>
                <tr valign="top">
                    <th width="25%" scope="row">Email address</th>
                    <td><input type="text" name="jcwperrata_email"
                            value="<?php echo get_option('jcwperrata_email'); ?>"  style="padding:5px" size="40"/> (Correction suggestions by users will be sent here)</td>
                </tr>
                <tr valign="top">
                    <th width="25%" scope="row">User message on correction submission</th>
                    <td><input type="text" name="jcwperrata_usermessage"
                               value="<?php echo get_option('jcwperrata_usermessage'); ?>"  style="padding:5px" size="40"/> </td>
                </tr>
                <!--<tr valign="top">
                    <th scope="row">Select All button</th>
                    <td>
                        <input type="radio" name="jcwperrata_selectall"
                               value="Yes" <?php /*if(get_option('jcwperrata_selectall') =="Yes" || get_option('jcwperrata_selectall') =="") echo "checked='checked'";*/?> /> Include <br>
                        <input type="radio" name="jcwperrata_selectall"
                               value="Yes" <?php /*if(get_option('jcwperrata_selectall') =="No") echo "checked='checked'";*/?> /> Exclude
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Reset and close button</th>
                    <td>
                        <input type="radio" name="jcwperrata_resetclose"
                               value="Yes" <?php /*if(get_option('jcwperrata_resetclose') =="Yes" || get_option('jcwperrata_resetclose') =="") echo "checked='checked'";*/?> /> Include <br>
                        <input type="radio" name="jcwperrata_resetclose"
                               value="Yes" <?php /*if(get_option('jcwperrata_resetclose') =="No") echo "checked='checked'";*/?> /> Exclude
                    </td>
                </tr>-->
                <tr valign="top">
                    <th scope="row">Save button label</th>
                    <td><input type="text" name="jcwperrata_savebuttonlabel"
                            value="<?php echo get_option('jcwperrata_savebuttonlabel'); ?>"  style="padding:5px" size="40"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Reset/Close button label</th>
                    <td><input type="text" name="jcwperrata_cancelbuttonlabel"
                            value="<?php echo get_option('jcwperrata_cancelbuttonlabel'); ?>"  style="padding:5px" size="40"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Fancy dialog when user tries to paste content</th>
                    <td>
                        <input type="radio" name="jcwperrata_fancydialog"
                               value="Yes" <?php if(get_option('jcwperrata_fancydialog') =="Yes" || get_option('jcwperrata_fancydialog') =="") echo "checked='checked'";?> /> Yes <br>
                        <input type="radio" name="jcwperrata_fancydialog"
                               value="Yes" <?php if(get_option('jcwperrata_fancydialog') =="No") echo "checked='checked'";?> /> No (A Javascript prompt will be used instead)
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Disable on</th>
                    <td>
                        <input type="checkbox" name="jcwperrata_disableon_mobile"
                            value="Yes" <?php if(get_option('jcwperrata_disableon_mobile') =="Yes") echo "checked='checked'";?> /> Mobile Phones <br>
                        <input type="checkbox" name="jcwperrata_disableon_tablet"
                            value="Yes" <?php if(get_option('jcwperrata_disableon_tablet') =="Yes") echo "checked='checked'";?> /> Tablets
                            </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Link to authors website</th>
                    <td><input type="checkbox" name="jcwperrata_linkback"
                               value="Yes" <?php if(get_option('jcwperrata_linkback') =="Yes") echo "checked='checked'";?> /> <br>
                        <Strong>An un-noticeable link will be placed in the footer which points to authors website http://jaspreetchahal.org. Please check this checkbox to support this plugin in future.</strong></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tested on</th>
                    <td>IE10, FF 18, Chrome 20+, Opera 12, Safari, WP 3.5 </td>
                </tr>
            </table>
        <p class="submit">
            <input type="submit" class="button-primary"
                value="Save Changes" />
        </p>          
            </form>
        </div>
        <?php     
        echo "<div style='float:left;margin-left:20px;margin-top:75px'>".jcwperratafeeds()."</div>";
    }
    
    function jcwperrataDonationDetail() {
        ?>    
        <style type="text/css"> .jcorgcr_donation_uses li {float:left; margin-left:20px;font-weight: bold;} </style> 
        <div style="padding: 10px; background: #f1f1f1;border:1px #EEE solid; border-radius:15px;width:98%"> 
        <h2>If you like this Plugin, please consider donating</h2> 
        You can choose your own amount. Developing this awesome plugin took a lot of effort and time; days and weeks of continuous voluntary unpaid work. 
        If you like this plugin or if you are using it for commercial websites, please consider a donation to the author to 
        help support future updates and development. 
        <div class="jcorgcr_donation_uses"> 
        <span style="font-weight:bold">Main uses of Donations</span><ol ><li>Web Hosting Fees</li><li>Cable Internet Fees</li><li>Time/Value Reimbursement</li><li>Motivation for Continuous Improvements</li></ol> </div> <br class="clear"> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MHMQ6E37TYW3N"><img src="https://www.paypalobjects.com/en_AU/i/btn/btn_donateCC_LG.gif" /></a> <br><br><strong>For help please visit </strong><br> 
        <a href="http://jaspreetchahal.org/wordpress-errata-plugin">http://jaspreetchahal.org/wordpress-errata-plugin</a> <br><strong> </div>
        
        <?php
        
    }
    function jcwperratafeeds() {
        $list = "
        <table style='width:400px;' class='widefat'>
        <tr>
            <th>
            Latest posts from JaspreetChahal.org
            </th>
        </tr>
        ";
        $max = 5;
        $feeds = fetch_feed("http://feeds.feedburner.com/jaspreetchahal/mtDg");
        $cfeeds = $feeds->get_item_quantity($max); 
        $feed_items = $feeds->get_items(0, $cfeeds); 
        if ($cfeeds > 0) {
            foreach ( $feed_items as $feed ) {    
                if (--$max >= 0) {
                    $list .= " <tr><td><a href='".$feed->get_permalink()."'>".$feed->get_title()."</a> </td></tr>";}
            }            
        }
        return $list."</table>";
    }
    
    
    function jcwperrataMakeErrorsHtml($errors,$type="error")
    {
        $class="jcorgberror";
        $title=__("Please correct the following errors","jcorgbot");
        if($type=="warnings") {
            $class="jcorgberror";
            $title=__("Please review the following Warnings","jcorgbot");
        }
        if($type=="warning1") {
            $class="jcorgbwarning";
            $title=__("Please review the following Warnings","jcorgbot");
        }
        $strCompiledHtmlList = "";
        if(is_array($errors) && count($errors)>0) {
                $strCompiledHtmlList.="<div class='$class' style='width:90% !important'>
                                        <div class='jcorgb-errors-title'>$title: </div><ol>";
                foreach($errors as $error) {
                      $strCompiledHtmlList.="<li>".$error."</li>";
                }
                $strCompiledHtmlList.="</ol></div>";
        return $strCompiledHtmlList;
        }
    }