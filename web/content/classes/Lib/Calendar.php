<?php
// file: /Lib/Lib_Calendar.php

class Lib_Calendar
{

    public $Months = array(
        '',
        '[T~MONTH_JANUARY]', '[T~MONTH_FEBRUARY]', '[T~MONTH_MARCH]', '[T~MONTH_APRIL]',
        '[T~MONTH_MAY]', '[T~MONTH_JUNE]', '[T~MONTH_JULY]', '[T~MONTH_AUGUST]',
        '[T~MONTH_SEPTEMBER]', '[T~MONTH_OCTOBER]', '[T~MONTH_NOVEMBER]', '[T~MONTH_DECEMBER]'
        );

    public $Days = array(
        '[T~DAY_SUNDAY]',
        '[T~DAY_MONDAY]',
        '[T~DAY_TUESDAY]',
        '[T~DAY_WEDNESDAY]',
        '[T~DAY_THURSDAY]',
        '[T~DAY_FRIDAY]',
        '[T~DAY_SATURDAY]',
        );


    public function GetDayContent($date)
    {
        // --------- extend the function ----------
        return '';
    }

    function GetCalendar($set_string)
    {
    /*
        echo  GetCalendar(date('Y-m-d'));
        echo  GetCalendar('+1 month');
    */

        $t = strtotime($set_string);
        $month = date('m', $t);

        $thismonth = date('m');

        $month_text = $this->Months[date('n', $t)];

        $year  = date('Y', $t);
        $day  = date('j', $t);
        $first_day_of_week = date('w', strtotime("$year-$month-01"));
        $days_in_month = date('t', $t);
        $blank = '<td></td>';

        $calendar = '<table class="CALENDAR">
        <tbody>
        <tr><td colspan="7" class="CALENDAR_HEADING">' . $month_text . ' ' . $year . '</td></tr>
        <tr>
            <th>' . $this->Days[0] . '</th>
            <th>' . $this->Days[1] . '</th>
            <th>' . $this->Days[2] . '</th>
            <th>' . $this->Days[3] . '</th>
            <th>' . $this->Days[4] . '</th>
            <th>' . $this->Days[5] . '</th>
            <th>' . $this->Days[6] . '</th>
        </tr>
        <tr>';

        $n = 0;
        for ($d=0; $d<$first_day_of_week; $d++) {
            $calendar .= $blank;
        }
        $d = $first_day_of_week;
        for ($n=1; $n<=$days_in_month; $n++) {

            if (($n == $day) and ($thismonth == $month)) {
                $class='CALENDAR_TODAY';
            } elseif (($d == 0) or ($d == 6)) {
                $class='CALENDAR_WEEKEND';
            } else {
                $class='CALENDAR_WEEKDAY';
            }


            $date_n = sprintf('%02d', $n);
            $date =  "$year-$month-$date_n";
            $content = $this->GetDayContent($date);
            $calendar .= "<td  class=\"$class\"><div class=\"CALENDAR_DAY_NUMBER\">$n</div>$content</td>\n";
            $d++;

            if (($d == 7) and ($n<$days_in_month)) {
                $d=0;
                $calendar .="</tr>\n<tr>";

            } elseif ($n == $days_in_month) {
                if ($d < 7) {
                    for($i=$d; $d<7; $d++) {
                        $calendar .= $blank;
                    }
                }
                $calendar .= '</tr>';
            }
        }
        $calendar .= "</tbody>\n</table>\n";

        return $calendar;

    }


}
