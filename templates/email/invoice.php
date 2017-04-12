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
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style="background-color: <?php echo $email_settings['header_background_color'];?>;color:<?php echo $email_settings['header_text_color'];?>;padding:10px;">
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
                                            <td valign="top" id="body_content">
                                                <!-- Content -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="600">
                                                    <tr>
                                                        <td valign="top">
                                                            <div id="body_content_inner">

                                                                <p>
                                                                    <strong><?php echo $member->display_name;?></strong><br />
                                                                    <strong>Invoice Number:</strong> <?php echo $invoice['number']; ?><br />
                                                                    <strong>Date:</strong> <?php echo $invoice['date']; ?><br />
                                                                    <strong>Due Date:</strong><?php echo $invoice['due_date']; ?>
                                                                </p>

                                                                <table width="600">

                                                                    <thead>

                                                                        <tr>
                                                                            <th style="width: 75%">Description</th>
                                                                            <th>Price</th>
                                                                        </tr>

                                                                    </thead>

                                                                    <tbody>

                                                                    <?php $total = 0;?>

                                                                    <?php foreach( $line_items as $line_item ):?>

                                                                        <tr>

                                                                            <td>
                                                                                <?php echo $line_item['name'];?>
                                                                            </td>
                                                                            <td style="text-align: right;">
                                                                                R <?php echo number_format( $line_item['price'], 2, '.', ' ');?>
                                                                                <?php $total += $line_item['price'];?>
                                                                            </td>
                                                                        </tr>

                                                                    <?php endforeach;?>

                                                                    </tbody>

                                                                    <tfooter>

                                                                        <tr>
                                                                            <th style="text-align: right;padding: 5px;">VAT</th>
                                                                            <th style="text-align: right;padding: 5px;">
                                                                                R <?php
                                                                                $VAT = ( ( $total / (1 + 14 / 100) ) - $total ) * -1;
                                                                                echo number_format( ROUND($VAT, 2), 2, '.', ' ');
                                                                                ?>
                                                                            </th>
                                                                        </tr>

                                                                        <tr>
                                                                            <th style="text-align: right;padding: 5px;">Total (incl. VAT)</th>
                                                                            <th style="text-align: right;padding: 5px;">R <?php echo number_format( $total, 2, '.', ' ');?></th>
                                                                        </tr>

                                                                    </tfooter>

                                                                </table>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
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
