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
    <body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="color: #000;background: #ccc;">
    	<div id="wrapper">
        	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                    	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="background: #fff;">
                        	<tr>
                            	<td align="center" valign="top">
                                    <!-- Header -->
                                	<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style="background-color: #000;padding:10px;">
                                        <tr>
                                            <td style="text-align: left;color: #fff;">
                                            	<a href="http://cfxa.co.za"><img src="http://cfxa.co.za/wp-content/uploads/2015/05/ExAnimo_logo.png" title="CrossFit Ex Animo" alt="CrossFit Ex Animo" style="width:300px;" /></a><br />
                                                <h2><?php $header;?></h2>
                                            </td>
                                            <td style="text-align: right;color: #fff;">

                                                <p>P.O Box 48 Linbro Park 2065</p>

                                                <p>Tel: 082 373 4946<br />
                                                Email: heartbeat@crossfitexanimo.co.za<br />
                                                Registration No: 2014/107380/07<br />
                                                VAT No: 4700272299</p>

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
                                                            <p>
                                                                <strong>Banking Details:</strong><br />
                                                                Fitness Ex Animo (Pty) Ltd<br />
                                                                FNB Hyde Park<br />
                                                                Account Number: 624 7658 8250<br />
                                                                Branch: 255 805<br />
                                                                Please use your name and surname as reference.
                                                            </p>
                                                            <p>
                                                                <strong>CrossFit Ex Animo</strong><br />
                                                                Shop 21 A Valley Centre, 396 Jan Smuts Avenue, Craighall Park, 2196<br /><br />
                                                                Steven: 082 561 2602 / steven@crossfitexanimo.co.za<br />
                                                                Bronwyn: 060 974 9186 / bronwyn@crossfitexanimo.co.za
                                                            </p>
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
