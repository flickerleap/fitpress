<?php
/**
 * Email Header
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	</head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="color: <?php echo $email_settings['text_color'];?>;background: <?php echo $email_settings['background_color'];?>;">
    	<div id="wrapper">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                    	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="background: <?php echo $email_settings['body_background_color'];?>;">
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Header -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style="background-color: <?php echo $email_settings['header_background_color'];?>;color:<?php echo $email_settings['header_text_color'];?>;padding:10px 20px;">
                                        <tr>
                                            <td style="text-align: center;">
                                            	<?php if ( ! empty ( $email_settings['header_image'] ) ) : ?>
                                            		<img src="<?php echo $email_settings['header_image'];?>" title="<?php echo $header;?>" alt="<?php echo $header;?>" />
                                            	<?php endif;?>
                                                <h2><?php echo $header;?></h2>
                                            </td>
                                            <td style="text-align: right;color: #fff;font-size: 11px;">

                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Body -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                    	<tr>
                                            <td valign="top" id="body_content" style="padding:10px;">
                                                <!-- Content -->
												<div  style="padding:10px;">
													<?php echo $message;?>
												</div>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Footer -->
                                    <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer" style="text-align:center;">
                                        <tr>
                                            <td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td colspan="2" valign="middle" id="credit">
                                                            <?php echo $email_settings['footer'];?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Footer -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
