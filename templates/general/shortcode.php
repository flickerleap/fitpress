<?php if ( count( $memberships ) > 0 ) : ?>

	<ul class="memberships">
		<?php while( $memberships->have_posts() ) :?>
			<?php $memberships->the_post();?>
			<li>
				<h3><?php the_title(); ?></h3>
				<a href="<?php echo $signup['link'] . '?membership_id=' . get_the_ID();?>" class="button"><?php echo $signup['text'];?></a>
			</li>
		<?php endwhile;?>
	</ul>

<?php else: ?>

	<p>There are no memberships at this stage.</p>

<?php endif;?>
