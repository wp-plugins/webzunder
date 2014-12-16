<?php
    /*
     Plugin Name: webZunder Open Graph Plugin
     Plugin URI: http://www.webzunder.com/de/webZunder-Open-Graph-Plugin
     Description: Die eigene Webseite kinderleicht mit Open Graph Meta Tags für Google und Facebook aufbessern.
     Author URI: http://www.twentyzen.com
     Author: twentyZen
     Version: 1.6.2.6
     License: GPL v2 or Later
     Text Domain: webzunder
     
    webZunder Open Graph Plugin
    Copyright (C) 2013-2014, twentyZen GmbH - contact@twentyZen.com

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

    //TODO Kommentare ergänzen bzw erneuern    
global $wbZ_version;
$wbZ_version='1.6.2.6';

load_plugin_textdomain('webzunder', false, basename( dirname( __FILE__ ) ) . '/languages' );
      
/*enqueuing scripts and styles*/
 function wbZ_admin_scripts()
 {
 if (isset($_GET['page']) && $_GET['page'] == 'wbZ_settings')
 {
 wp_enqueue_script('jquery');
 wp_enqueue_script('media-upload');
 wp_enqueue_script('thickbox');
 wp_register_script('wbZ-upload', plugin_dir_url(__FILE__).'wbZ-admin-script.js', array('jquery','media-upload','thickbox'));
 wp_enqueue_script('wbZ-upload');
 }
 }
 
 
 function wbZ_admin_styles()
 {
 if (isset($_GET['page']) && $_GET['page'] == 'wbZ_settings')
 {
     wp_enqueue_style('thickbox');
     wp_register_style('wbZ-style', plugin_dir_url(__FILE__).'wbZ-style.css');
     wp_enqueue_style('wbZ-style');
 }
 }
 add_action('admin_print_scripts', 'wbZ_admin_scripts');
 add_action('admin_print_styles', 'wbZ_admin_styles');
 
   
/*Menu Entry in Dashboard/Tools*/
add_action('admin_menu', 'wbZ_plugin_settings');

function wbZ_plugin_settings() {
    /*add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);*/                
    add_options_page('webZunder Plugin', 'webZunder Plugin', 'administrator', 'wbZ_settings', 'wbZ_display_settings');

}


/*Optionen unterhalb von Post/Page Editor*/
function wbZ_add_meta_box() {

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'wbZ-section',
			 __('webZunder Optionen', 'webzunder'),
			'wbZ_meta_box',
			$screen,
			$context='advanced'
		);
	}
}
add_action( 'add_meta_boxes', 'wbZ_add_meta_box' );

/* Darstellung der Optionsfelder */
function wbZ_meta_box( $post ) { /* $post Objekt vom aktuellen Post*/

	/* nonce für Sicherheitsabfrage beim Speichern*/
	wp_nonce_field( 'wbZ_meta_box', 'wbZ_meta_box_nonce' );

	$ogtitle = get_post_meta( $post->ID, 'og:title', true );
	$ogdesc = get_post_meta( $post->ID, 'og:description', true );
	$ogimage = get_post_meta( $post->ID, 'og:image', true );
?>
	
	<table class="form-table" width="100%">
        <tr valign="top">
        <th scope="row"><?php echo '<label for="wbZ_ogtitle_field">'.__('Beitragstitel', 'webzunder').': </label> ';?></th>
        <td><?php echo '<input type="text" id="wbZ_ogtitle_field" name="wbZ_ogtitle_field" value="' . esc_attr( $ogtitle ) . '" size="60" maxlength="60" />'.'<br>';
     ?></td></tr>
	
	    <tr valign="top">
        <th scope="row"><?php echo '<label for="wbZ_ogdesc_field">'.__('Beitragsbeschreibung', 'webzunder').': </label> ';?></th>
        <td><?php echo '<textarea id="wbZ_ogdesc_field" name="wbZ_ogdesc_field" maxlength="160" rows="2" cols="60">'. esc_attr( $ogdesc ).'</textarea>'.'<br>';
     ?></td></tr>
     
         <tr valign="top">
        <th scope="row"><?php echo '<label for="wbZ_ogimage_field">'.__('Beitragsbild URL', 'webzunder').'</label> ';?></th>
        <td><?php
              if($ogimage==""){
                 $ogimage=wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); //wenn kein bild durch nutzer oder webzunder definiert, nimm artikelbild
              }
              echo '<input type="text" id="wbZ_ogtitle_field" name="wbZ_ogimage_field" value="' . esc_attr( $ogimage ) . '" size="60" />'.'<br>';
            
     ?></td></tr>
	
   
	
<?php
}


/* Speichervorgang */
function wbZ_save_meta_box_data( $post_id ) { //$post_id ist die ID von dem zu speichernden Post.

	
	/* checkt ob Nounce gesetzt*/
	if ( ! isset( $_POST['wbZ_meta_box_nonce'] ) ) {
		return;
	}

	/* prüfung ob nounce korrekt */
	if ( ! wp_verify_nonce( $_POST['wbZ_meta_box_nonce'], 'wbZ_meta_box' ) ) {
		return;
	}

	
	/* check ob Nutzerberechtigungen stimmen */
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

    
		
	/* aufräumen der eingaben*/
	$wbZ_user_title = sanitize_text_field( $_POST['wbZ_ogtitle_field'] );
	$wbZ_user_desc = sanitize_text_field( $_POST['wbZ_ogdesc_field'] );
	$wbZ_user_image = sanitize_text_field( $_POST['wbZ_ogimage_field'] );
	
	if($wbZ_user_image==""){
	    $wbZ_user_image=wp_get_attachment_url( get_post_thumbnail_id($post_id) );
	}

	/*eigentlichen Speichervorgang*/
	update_post_meta( $post_id, 'og:title', $wbZ_user_title );
	update_post_meta( $post_id, 'og:description', $wbZ_user_desc );
	update_post_meta( $post_id, 'og:image', $wbZ_user_image );
}
add_action( 'save_post', 'wbZ_save_meta_box_data' );



/* Registering option fields */
add_action('admin_init', 'wbZ_plugin_init');

function wbZ_plugin_init() {
    global $wbZ_version;
    
	register_setting( 'wbZ-settings-group', 'wbZ_description' );
	register_setting( 'wbZ-settings-group', 'wbZ_image' );
	register_setting( 'wbZ-settings-group', 'wbZ_keywords' );
	register_setting( 'wbZ-settings-group', 'wbZ_googleid' );
	register_setting( 'wbZ-settings-group', 'wbZ_fbid' );
	register_setting( 'wbZ-settings-group', 'wbZ_twtid' );
	register_setting( 'wbZ-settings-group', 'wbZ_twtcheck' );
	
	if ( ( ! isset( $_GET['page'] )) && is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
		    add_action( 'admin_notices', 'wbZ_notice_aioseo' );
	}	
    if ( ( ! isset( $_GET['page'] )) && is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
		    add_action( 'admin_notices', 'wbZ_notice_yoast' );
	}	
	
    if(!get_option('wbZ_version')){
        update_option( 'wbZ_version','0');
    }
        
    $version= get_option( 'wbZ_version' );
    
    if ( $version <=  $wbZ_version)  {
		update_option( 'wbZ_version', $wbZ_version );
	}
}

/*Option Page*/
function wbZ_display_settings() {
  ?>
<div class="wrap">
<div class="opleft">
<h2>webZunder Plugin</h2>
<p><?php _e('Die hier erfassten Daten werden als Standard Angaben für die Open Graph Meta Tags genutzt und ausgeben, wenn keine anderen Daten angeben werden.', 'webzunder');?></p>
<form method="post" action="options.php">
    
    <?php 
        /* settings_fields( $option_group )*/
        settings_fields( 'wbZ-settings-group' ); /*Output nonce, action, and option_page fields for a settings page*/
    ?>
    <table class="form-table" width="100%">
        <tr valign="top">
        
        <tr valign="top">
        <th scope="row"><?php _e('Standard-Beschreibung','webzunder');?></th>
        <td><textarea name="wbZ_description" maxlength="160" rows="2" cols="50"><?php echo esc_html(get_option('wbZ_description')); ?></textarea></td>
        </tr>
        
        <tr valign="top">
        <th scope="row"><?php _e('Standard-Bild','webzunder');?></th>
        <td>
            <input id="wbZ_image" type="text" name="wbZ_image" value="<?php echo get_option('wbZ_image'); ?>" size="50" maxlength="256" />
            <input  class="wbZ_upload button" type="button" value="<?php _e('Bild auswählen','webzunder');?>" />
        </td>
        
        <tr valign="top">
        <th scope="row"><?php _e('Keywords per Komma getrennt','webzunder');?></th>
        <td>
            <input id="wbZ_keywords" type="text" name="wbZ_keywords" value="<?php echo esc_html(get_option('wbZ_keywords')); ?>" size="50" maxlength="256" />
        </td>
                    
        </tr>
 
    </table>
        <p><?php _e('Verbinden Sie ihre Google+ Seite mit ihrer Webseite. Einfach die Google+-ID angeben.','webzunder');?></p>
        <table class="form-table" width="100%">
            <tr valign="top">
                <th scope="row">Google+ ID</th>
                <td><input type="text" name="wbZ_googleid" value="<?php echo get_option('wbZ_googleid'); ?>" /></td>
            </tr>
         </table>
        <p><?php _e('Verbinden Sie ihre Facebook Seite mit ihrer Webseite. Einfach die URL zu Facebook angeben.','webzunder');?></p>
        <table class="form-table" width="100%">
            <tr valign="top">
                <th scope="row"><?php _e('Facebook Seiten URL','webzunder');?></th>
                <td><input type="text" name="wbZ_fbid" value="<?php echo get_option('wbZ_fbid'); ?>" /></td>
            </tr>
         </table>
        <table class="form-table" width="100%">
            <tr valign="top">
                <th scope="row"><?php _e('Twitter Cards aktivieren','webzunder');?></th>
                <td><input type="checkbox" name="wbZ_twtcheck" value="1" <?php checked( get_option('wbZ_twtcheck'), 1 ); ?> /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Twittername der Seite ohne @','webzunder');?></th>
                <td><input type="text" name="wbZ_twtid" value="<?php echo get_option('wbZ_twtid'); ?>" /></td>
            </tr>
       
         </table>
   
    
    <?php submit_button(); ?>
    <p><b><?php _e('Hinweis: ','webzunder');?></b>
    <?php _e('Twitter Cards müssen vorher validiert werden damit diese vollständig funktionieren. Sie können Ihre Twitter Cards testen und von Twitter validieren lassen ','webzunder');
        $url='https://dev.twitter.com/docs/cards/validation/validator';
       $link=sprintf(__('<a href="%s">mit dem Validator.</a>','webzunder'),esc_url($url));
       echo $link ?></p>
    <p><b><?php _e('Tipp: ','webzunder'); ?></b><?php
        _e('Sie können in Ihrem Nutzerprofil Links zu Ihren Social Media Profilen eintragen. Diese Angaben werden für alle Autorendaten genutzt ','webzunder');
       $url=get_edit_user_link().'#profiles';
       $link=sprintf(__('<a href="%s">Zum Nutzerprofil gehen.</a>','webzunder'),esc_url($url));
       echo $link ?></p>
</form>
</div>
<div class="opright">
    <img class="logo" src="<?php echo plugin_dir_url(__FILE__) ?>logo.png" />
    <p>
       <?php
        _e('Du willst deine Social Media Aktivitäten <b>besser kontrollieren und steuern können</b>? Dann probiere doch mal <b>das Plugin in Kombination 
        mit webZunder aus</b>. Ganz einfach <b>30 Tage unverbindlich testen </b> und das eigene Online Marketing anheizen.','webzunder');
        echo '<br><br>';
        $adurl="http://www.webzunder.com/de/wordpress-webzunder/?pk_campaign=plugin";
        $adlink=sprintf(__('Mehr Informationen dazu findest du auf <a href="%s"> www.webzunder.com</a> ','webzunder'),esc_url($adurl));
        echo $adlink;       
        ?>
    </p>
    <br>    
    <form class="layout_form cr_form cr_font" action="http://29405.seu.cleverreach.com/f/29405-82025/wcs/" method="post" target="_blank">
	    <p><b><?php _e('Du willst über Neuerungen rund um das Plugin informiert werden?</b><br>Dann trag dich einfach in unsere Mailingliste ein. (kein SPAM)','webzunder');?></p>
        <label for="text1829098" class="itemname">E-Mail</label> <input id="text1829098" name="email" value="" type="text"  />
        <button type="submit" class="cr_button"><?php _e('Anmelden','webzunder');?></button>
    </form>
    
</div>
</div>
<?php 
} 


add_action( 'show_user_profile', 'wbZ_social_links' );
add_action( 'edit_user_profile', 'wbZ_social_links' );

function wbZ_social_links( $user )
{
    ?>
        <h3 id="profiles"><?php _e('Profile in Sozialen Netzwerken','webzunder'); ?></h3>

        <table class="form-table">
            <tr>
                <th><label for="facebook_profile">Facebook URL</label></th>
                <td><input type="text" name="facebook_profil" value="<?php echo esc_attr(get_the_author_meta( 'facebook_profil', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="twitter_profile">Twitter ohne @</label></th>
                <td><input type="text" name="twitter_profil" value="<?php echo esc_attr(get_the_author_meta( 'twitter_profil', $user->ID )); ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="google_profile">Google+ URL</label></th>
                <td><input type="text" name="google_profil" value="<?php echo esc_attr(get_the_author_meta( 'google_profil', $user->ID )); ?>" class="regular-text" /></td>
            </tr>
        </table>
<?php
}

add_action( 'personal_options_update', 'wbZ_save_social_links' );
add_action( 'edit_user_profile_update', 'wbZ_save_social_links' );

function wbZ_save_social_links( $user_id )
{
    update_user_meta( $user_id,'facebook_profil', sanitize_text_field( $_POST['facebook_profil'] ) );
    update_user_meta( $user_id,'twitter_profil', sanitize_text_field( $_POST['twitter_profil'] ) );
    update_user_meta( $user_id,'google_profil', sanitize_text_field( $_POST['google_profil'] ) );
}


/*og- Meta Tags Output in Pageheader*/
add_action( 'wp_head', 'wbZ_meta_tags' );
function wbZ_meta_tags() {
    global $wp_query;
        $custom_fields = get_post_custom(get_query_var('p')); /* Post ID */
        $post=get_post($post_id);
        $user_id = $post->post_author;
        
        $posttags = get_the_tags($post_id);
         if ($posttags) {
              foreach($posttags as $tag) {
                $keywords.=$tag->name.','; 
              }
            }
        
        
        echo "\r\n".'<!-- Begin webZunder Plugin -->'."\r\n"; 
        
        /* if frontpage og:type=website else og:type=article  */        
        if (is_front_page()){ 
           $type= "website";
           
        } else {
           $type= "article";
           
        }
        
        /*Test if Homepage or Archive if true take default values from plugin optionpage*/
        /*else take values from webZunder or Metaboxes or SEO Plugins*/
        
        
        if(is_home()||is_archive()){
            
            if(get_option('wbZ_description')!=""){$desc=esc_html(get_option('wbZ_description'));}
            if(get_option('wbZ_image')!=""){$image=get_option('wbZ_image');} 
            if(get_option('wbZ_keywords')!=""){$keyw=esc_html(get_option('wbZ_keywords'));} 
                                        
             echo '<link rel="canonical" href="'.get_bloginfo('url').'" />'."\r\n";
             if($desc!=""){
              echo '<meta name="description" content="'.$desc.'"/>'."\r\n";   /* description defined in plugin (default)*/
             }
             if($keyw!=""){
              echo '<meta name="keywords" content="'.$keyw.'"/>'."\r\n";   /* keywords defined in plugin (default)*/
             }
             echo "\r\n".'<!-- Open Graph -->'."\r\n"; 
            echo '<meta property="og:title" content="'.esc_html(get_bloginfo('title')).'"/>'."\r\n"; /*og:title  filled by wordpress*/
             if(get_option('wbZ_description')!=""){
             echo '<meta property="og:description" content="'.esc_html(get_option('wbZ_description')).'"/>'."\r\n";   /* og:description defined in plugin (default)*/
            }
            if(get_option('wbZ_image')!=""){
             echo '<meta property="og:image" content="'.get_option('wbZ_image').'"/>'."\r\n";   /* og:image defined in plugin (default)*/
             
            }
            echo '<meta property="og:url" content="'.get_bloginfo('url').'" />'."\r\n";  /* og:url */
            echo '<meta property="og:site_name" content="'.get_bloginfo('name').'"/>'."\r\n";   /* og:site_name */
            echo '<meta property="og:locale" content="'.strtr(get_bloginfo('language'),'-','_').'"/>'."\r\n";  /* og:locale changes from de-DE to de_DE*/
            echo '<meta property="og:type" content="'.$type.'"/>'."\r\n";  /* og:type */
                
            echo "\r\n".'<!-- Google Stuff -->'."\r\n"; 
            /*Google Publisher*/      
            if(get_option('wbZ_googleid')!=""){
                if(preg_match('/^[0-9]*$/',get_option('wbZ_googleid'))){
                echo '<link rel="publisher" href="https://plus.google.com/'.get_option('wbZ_googleid').'"/>'."\r\n";
            }else{
                 echo '<link rel="publisher" href="https://plus.google.com/+'.get_option('wbZ_googleid').'"/>'."\r\n";
            }
            }
            echo '<meta itemprop="name" content="'.get_bloginfo('name').'">'."\r\n";
            echo '<meta itemprop="description" content="'.$desc.'">'."\r\n";
        if($image!=""){
            echo '<meta itemprop="image" content="'.$image.'">'."\r\n";
        }
            
          if(get_option('wbZ_twtcheck')=="1"){
            echo "\r\n".'<!-- Twitter Card -->'."\r\n";
            echo '<meta name="twitter:card" content="summary">'."\r\n";
            echo '<meta name="twitter:site" content="'.get_option('wbZ_twtid').'">'."\r\n";
            echo '<meta name="twitter:title" content="'.get_bloginfo('name').'">'."\r\n";
            echo '<meta name="twitter:description" content="'.$desc.'">'."\r\n";
            echo '<meta name="twitter:image:src" content="'.$image.'">'."\r\n";
            echo '<meta name="twitter:domain" content="'.get_bloginfo('url').'">'."\r\n";
         }
         
         }else{        
         
        
              
      if(get_post_meta(get_the_ID(), 'og:description', true)==""){
            if(get_option('wbZ_description')!=""){
                $desc=get_option('wbZ_description');   /* og:description defined in plugin*/
            
            }else{
                $post_object = get_post(get_the_ID());
                $content= $post_object->post_content;
                $desc = substr( strip_tags( $content ), 0, 160 ); /*og:description  filled by wordpress*/
                
                
            }
        }else{
            $desc= $custom_fields['og:description'][0]; /* og:description custom field filled by webZunder*/
            
       }
             
                echo '<link rel="canonical" href="'.get_permalink(get_query_var('p')).'" />'."\r\n";
        if($keywords!=""){
            echo '<meta name="keywords" content="'.esc_html($keywords).'"/>'."\r\n";   /* keywords generated by post tags*/
        }elseif(get_option('wbZ_keywords')!=""){
            echo '<meta name="keywords" content="'.get_option('wbZ_keywords').'"/>'."\r\n";   /* keywords generated by post tags*/
        }  
             echo '<meta name="description" content="'.esc_html($desc).'"/>'."\r\n"; 
             
            echo "\r\n".'<!-- Open Graph -->'."\r\n"; 
       if(get_post_meta(get_the_ID(), 'og:title', true)==""){
            $title=get_the_title();
        }else{
            $title= $custom_fields['og:title'][0]; /* og:title custom field filled by webZunder*/
       }
              echo '<meta property="og:title" content="'.esc_html($title).'"/>'."\r\n"; /*og:title  filled by wordpress*/
             
              echo '<meta property="og:description" content="'.esc_html($desc).'"/>'."\r\n"; 
             
       
        
        echo '<meta property="og:url" content="'.get_permalink(get_query_var('p')).'" />'."\r\n";  /* og:url */
        
        /*if og:image not in custom field use image from plugin*/
        if(get_post_meta(get_the_ID(), 'og:image', true)==""){
            if(get_option('wbZ_image')!=""){
                        echo '<meta property="og:image" content="'.get_option('wbZ_image').'"/>'."\r\n";   /* og:image defined in plugin*/
                        $image=get_option('wbZ_image');
            }else if (wp_get_attachment_url( get_post_thumbnail_id($post->ID)!="")) {
                        echo '<meta property="og:image" content="'.wp_get_attachment_url( get_post_thumbnail_id($post->ID) ).'"/>'."\r\n";   /* og:image defined in postimage*/    
                        $image=wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
            }else{
                //nothing
            }
        }else{
            $my_custom_field = $custom_fields['og:image'];  /* og:image custom field filled by webZunder*/
            echo_meta_tag($my_custom_field, 'og:image');
            $image=$custom_fields['og:image'][0];
        }
        
        echo '<meta property="og:site_name" content="'.get_bloginfo('name').'"/>'."\r\n";   /* og:site_name */
        echo '<meta property="og:locale" content="'.strtr(get_bloginfo('language'),'-','_').'"/>'."\r\n";  /* og:locale changes from de-DE to de_DE*/
        echo '<meta property="og:type" content="'.$type.'"/>'."\r\n";  /* og:type */
        
        
        
          
        if(is_single() && $type=="article" && !is_page()){
         
         if(get_the_author_meta( 'facebook_profil', $user_id )==""){
             echo '<meta property="article:author" content="'.get_author_posts_url($user_id).'"/>'."\r\n";   /* link to article author */
         }else{
                 echo '<meta property="article:author" content="'.get_the_author_meta( 'facebook_profil', $user_id ).'"/>'."\r\n";   /* link to article author */
         }
         
         if(get_option('wbZ_fbid')!=""){
             echo '<meta property="article:publisher" content="'.get_option('wbZ_fbid').'"/>'."\r\n";   /* article publisher FB URL via Plugin Page */
         }
         echo '<meta property="article:published_time" content="'. get_the_date('c').'"/>'."\r\n";   /* publish date */
         echo '<meta property="article:modified_time" content="'.get_the_modified_date('c').'"/>'."\r\n";   /* modified date */
         echo '<meta property="og:updated_time" content="'.get_the_modified_date('c').'"/>'."\r\n";   /* modified date */
         
        }
         echo "\r\n".'<!-- Google Stuff -->'."\r\n";
         
         
         /*Google Authorship*/
	    if(is_single() && $type=="article" && !is_page() && get_the_author_meta( 'google_profil', $user_id )!=""){
            echo '<link rel="author" href="'.get_the_author_meta( 'google_profil', $user_id ).'" />'."\r\n";
        }else{
            //nothing
        }
        /*Google Publisher*/      
        if(get_option('wbZ_googleid')!=""){
            if(preg_match('/^[0-9]*$/',get_option('wbZ_googleid'))){
            echo '<link rel="publisher" href="https://plus.google.com/'.get_option('wbZ_googleid').'"/>'."\r\n";
        }else{
             echo '<link rel="publisher" href="https://plus.google.com/+'.get_option('wbZ_googleid').'"/>'."\r\n";
        }
        }
        echo '<meta itemprop="name" content="'.esc_html($title).'">'."\r\n";
        echo '<meta itemprop="description" content="'.$desc.'">'."\r\n";
        if($image!=""){
            echo '<meta itemprop="image" content="'.$image.'">'."\r\n";
        }
        /*Twittercards*/
       
        if(get_option('wbZ_twtcheck')=="1"){
            echo "\r\n".'<!-- Twitter Card -->'."\r\n";
            echo "\r\n".'<meta name="twitter:card" content="summary">'."\r\n";
            echo '<meta name="twitter:site" content="'.get_option('wbZ_twtid').'">'."\r\n";
            echo '<meta name="twitter:title" content="'.esc_html($title).'">'."\r\n";
            echo '<meta name="twitter:description" content="'.$desc.'">'."\r\n";
            echo '<meta name="twitter:creator" content="'.get_the_author_meta( 'twitter_profil', $user_id ).'">'."\r\n";
            echo '<meta name="twitter:image:src" content="'.$image.'">'."\r\n";
            echo '<meta name="twitter:domain" content="'.get_bloginfo('url').'">'."\r\n";
        }
        
       
         
       }
 
    
    
    echo "\r\n".'<!-- End -->'."\r\n"; 
    echo "\r\n"; 
    
}

function echo_meta_tag($my_custom_field, $property) {
    if ($my_custom_field) {
        foreach ( $my_custom_field as $key => $value ) {
            echo '<meta property="'.$property.'" content="'.$value.'" />'."\r\n"; /*Output of og-tags from custom fields*/
        }
    }
    
}

function wbZ_notice_aioseo(){
        global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta($user_id, 'aioseo_ignore_notice') ) {
             echo '<div class="error" style="padding:10px;">';
             printf(__(' Plugin "All in One SEO" ist aktiv. Bitte deaktivieren Sie dieses um Probleme mit webZunder zu vermeiden. <a class="button" style="margin-left:15px;" href="%1$s">Ist mir egal!</a>','webzunder'), '?aioseo_nag_ignore=0');
             echo '</div>';
         }
} 

add_action('admin_init', 'aioseo_nag_ignore');
function aioseo_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['aioseo_nag_ignore']) && '0' == $_GET['aioseo_nag_ignore'] ) {
             add_user_meta($user_id, 'aioseo_ignore_notice', 'true', true);
	}
}


function wbZ_notice_yoast(){
        global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta($user_id, 'yoast_ignore_notice') ) {
             echo '<div class="error" style="padding:10px;">';
             printf(__(' Plugin "Yoast WordPress SEO" ist aktiv. Bitte deaktivieren Sie dieses um Probleme mit webZunder zu vermeiden. <a class="button" style="margin-left:15px;" href="%1$s">Ist mir egal!</a>','webzunder'), '?yoast_nag_ignore=0');
             echo '</div>';
         }
} 

add_action('admin_init', 'yoast_nag_ignore');
function yoast_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['yoast_nag_ignore']) && '0' == $_GET['yoast_nag_ignore'] ) {
             add_user_meta($user_id, 'yoast_ignore_notice', 'true', true);
	}
}



add_filter('language_attributes', 'add_default_ns');
    function add_default_ns($content) {
          if(!is_single()){
              return 'itemscope itemtype="http://schema.org/WebPage" prefix="og: http://ogp.me/ns#" ' . $content;
          }else{
              return 'itemscope itemtype="http://schema.org/Article" prefix="og: http://ogp.me/ns#"' . $content;
          }
    }




/*Remove canonical Tag generated by wordpress*/
remove_action('wp_head', 'rel_canonical')
?>