<?php if( $credits <= 0):?>
	<p>You do not have credits. Why not upgrade your membership?</p>
<?php else:?>
	<p>You currently have <strong><span class="credits"><?php echo $credits;?></span></strong> credits remaining.</p>
<?php endif;?>

<?php if( count( $sessions ) > 0 ):?>

	<?php
	/* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0" class="fp-calendar">';

	/* table headings */
	$headings = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	$calendar.= '<thead><tr class="fp-calendar-row"><th class="fp-calendar-day-head">'.implode('</th><th class="fp-calendar-day-head">',$headings).'</th></tr></thead>';

	$current_date = strtotime( 'midnight' );
	$start_date = $running_day = strtotime('sunday last week', strtotime('sunday last week'));
	$end_date = strtotime('+1 week', strtotime('sunday this week'));

	$day_counter = 0;

	/* row for week one */
	$calendar.= '<tbody><tr class="fp-calendar-row">';

	/* keep going with days.... */
	while( $running_day < $end_date ):

		$day_counter++;

		if( $running_day < $current_date ):

			$calendar.= '<td class="fp-calendar-day-np">';
			$calendar.= '<div class="fp-day-number">'.date( 'j M', $running_day ).'</div>';
			$calendar.= '</td>';

		else:

			$has_sessions = isset( $sessions[date( 'l - j F Y', $running_day )] ) && !empty( $sessions[date( 'l - j F Y', $running_day )]);
			$day_class = $has_sessions ? 'fp-calendar-day' : 'fp-calendar-day fp-calendar-day-empty';

			$calendar.= '<td class="' . $day_class . '">';

			/* add in the day number */
			$calendar.= '<div class="fp-day-number"><span class="mobile-day">' . date( 'D', $running_day ) . ' - </span>' . date( 'j M', $running_day ) . '</div>';

			$calendar = apply_filters ( 'fitpress_before_day', $calendar, $running_day );

			if( $has_sessions ):

				foreach( $sessions[date( 'l - j F Y', $running_day )] as $day_session ):
					
					$calendar .= '<div class="day-session">';

					$calendar.= '<p class="class-name">' . $day_session->class_name . '</p>';

					$calendar.= '<p class="session-time">';

					$calendar.= date( 'H:i', $day_session->start_time ) . ' - ' . date( 'H:i', $day_session->end_time );

					$calendar.= '</p>';

					$calendar.= '<p class="session-book">';

					$calendar.= $day_session->action;

					$calendar.= '</p>';

					$calendar.= '<p class="session-space">';

					$calendar .= 'Space: <br />';
					$calendar .= ( isset( $day_session->class_limit ) && !empty( $day_session->class_limit )) ? '<span class="session-bookings">' . ($day_session->class_limit - $day_session->current_bookings) . '</span> / <span class="session-limit">' . $day_session->class_limit . '</span>' : '<span class="session-limit">Unlimited</span>';

					$calendar.= '</p>';

					$calendar.= '</div>';

				endforeach;

			endif;

			$calendar.= '</td>';
			
			if( $day_counter == 7 ):

				$day_counter = 0;
				$calendar.= '</tr>';

				if( $running_day != $end_date):

					$calendar.= '<tr class="fp-calendar-row">';

				endif;

			endif;

		endif;

		$running_day = strtotime( '+1 day', $running_day );

	endwhile;

	/* final row */
	$calendar.= '</tr></tbody>';

	/* end the table */
	$calendar.= '</table>';

	/* all done, return result */
	echo $calendar;
	?>

<?php else:?>

	<p>There are currently no sessions available. Please come back at a later stage to book a session.</p>

<?php endif;?>