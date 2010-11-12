<?php

$calendar = $vars["calendar"];
$iMonth = $vars["month"];
$iYear = $vars["year"];
$events = $vars["events"];

//var_dump($events);
$showYear = 1;

$a = $calendar->adjustDate($iMonth, $iYear);
$iMonth = $a[0];
$iYear = $a[1];

$daysInMonth = $calendar->getDaysInMonth($iMonth, $iYear);
$date = getdate(mktime(12, 0, 0, $iMonth, 1, $iYear));

$first = $date["wday"];
$monthName = $calendar->monthNames[$iMonth - 1];

$prev = $calendar->adjustDate($iMonth - 1, $iYear);
$next = $calendar->adjustDate($iMonth + 1, $iYear);

if ($showYear == 1)
{
	$prevMonth = "/events/index/day/01/month/" . $prev[0] . "/year/" . $prev[1];
	$nextMonth = "/events/index/day/01/month/" . $next[0] . "/year/" . $next[1];
	//$prevMonth = $calendar->getCalendarLink($prev[0], $prev[1]);
	//$nextMonth = $calendar->getCalendarLink($next[0], $next[1]);
}
else
{
		$prevMonth = "";
		$nextMonth = "";
}

$header = $monthName . (($showYear > 0) ? " " . $iYear : "");

?>
<table class="calendar">
	<tr>
		<td align="center" valign="middle">
			<?=(($prevMonth == "") ? "&nbsp;" : "<a href=\"$prevMonth\"><img src='/img/base/calendar-arrowleft.png' border='0'></a>")?>
		</td>
		<td align="center" valign="middle" class="calendarHeader" colspan="5">
			<?=$header?>
		</td>
		<td align="center" valign="middle">
			<?=(($nextMonth == "") ? "&nbsp;" : "<a href=\"$nextMonth\"><img src='/img/base/calendar-arrowright.png' border='0'></a>") ?>
		</td>
	</tr>
	<tr>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+1)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+2)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+3)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+4)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+5)%7]?>
		</td>
		<td align="center" valign="top" class="calendarHeader">
			<?=$calendar->dayNames[($calendar->startDay+6)%7]?>
		</td>
	</tr>

<?

// We need to work out what date to start at so that the first appears in the correct column
$d = $this->startDay + 1 - $first;
while ($d > 1)
{
		$d -= 7;
}

// Make sure we know when today is, so that we can use a different CSS style
$today = getdate(time());
?>

<?
while ($d <= $daysInMonth)
{
?>
	<tr>
		<?
		for ($i = 0; $i < 7; $i++)
		{
			$sclass = ($iYear == $today["year"] && $iMonth == $today["mon"] && $d == $today["mday"]) ? "calendarToday" : "calendar";
			?>
			<td class="<?=$sclass?>" align="right" valign="top">
				<?
					if ($d > 0 && $d <= $daysInMonth)
    	    {
						$iCurrentDate = mktime(0,0,0, $iMonth, $d, $iYear);
						$sSQLDate = $iYear . "-" . date("m", $iCurrentDate) . "-" . date("d", $iCurrentDate);
						$bHasEvent = false;
						foreach($events as $oEvent):
							if ($oEvent->eventDate == $sSQLDate):
								$bHasEvent = true;
								break;
							endif;

						endforeach;

						$link = "/events/index/day/" . $d . "/month/" . $iMonth . "/year/" . $iYear;
						//$link = $calendar->getDateLink($d, $iMonth, $iYear);
				?>
					<? if ($bHasEvent): ?><strong><? endif; ?>
					<?=(($link == "") ? $d : "<a href=\"$link\">$d</a>") ?>
					<? if ($bHasEvent): ?></strong><? endif; ?>
				<?
					} else {
				?>
					&nbsp;
				<?
					} //end if
				?>
			</td>
			<?
				$d++;
		}//endfor
  ?>
  </tr>
  <?
}//wend
	?>

</table>

<div class="promotitle" style="margin-top: 5px; margin-bottom: 5px; text-align:center; ">
	<a href="/events">Upcoming Events</a>
</div>
