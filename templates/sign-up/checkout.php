<?php
/**
 * Edit account form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

fp_display_flash_message();
?>
<h2>Membership Payment</h2>

<table>
	<tr>
		<th style="width:50%;">Name</th>
		<th>Credits</th>
		<th>Term</th>
		<th>Price</th>
	</tr>
	<tr>
		<td><?php echo $membership['name'];?></td>
		<td><?php echo $membership['credits'];?></td>
		<td><?php echo $membership['term'];?></td>
		<td style="text-align: right;">R <?php echo $membership['price'];?></td>
	</tr>
	<?php if( 'Once Off' == $membership['term'] ):?>
		<tr>
			<td colspan="3" style="text-align: right; font-weight: bold;">Due Now</td>
			<td style="text-align: right; font-weight: bold;">R <?php echo $membership['price'];?></td>
		</tr>
	<?php elseif ( $pay_now !== false ) :?>
		<tr>
			<td colspan="3" style="text-align: right; font-weight: bold;">Due Now</td>
			<td style="text-align: right; font-weight: bold;">R <?php echo $pay_now;?></td>
		</tr>
		<tr>
			<td colspan="3" style="text-align: right; font-weight: bold;">Future Recurring Amount</td>
			<td style="text-align: right; font-weight: bold;">R <?php echo $membership['price'];?></td>
		</tr>
	<?php else :?>
		<tr>
			<td colspan="3" style="text-align: right; font-weight: bold;">Recurring Amount</td>
			<td style="text-align: right; font-weight: bold;">R <?php echo $membership['price'];?></td>
		</tr>
	<?php endif;?>
</table>
