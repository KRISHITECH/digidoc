<?php
/**
 * This report lists referrals for a given date range.
 *
 *  Copyright (C) 2008-2016 Rod Roark <rod@sunsetsystems.com>
 *  Copyright (C) 2016      Roberto Vasquez <robertogagliotta@gmail.com>
 *  Copyright (C) 2017      Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Core\Header;

 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once "$srcdir/options.inc.php";

 $from_date = (isset($_POST['form_from_date']))  ? fixDate($_POST['form_from_date'], date('Y-m-d')) : '';
 $form_from_date = $from_date;
 $to_date   = (isset($_POST['form_to_date']))    ? fixDate($_POST['form_to_date'], date('Y-m-d')) : '';
;
 $form_to_date = $to_date;
 $form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>

<title><?php echo xlt('Referrals'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 $(document).ready(function() {
  oeFixedHeaderSetup(document.getElementById('mymaintable'));
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
 });

 // The OnClick handler for referral display.

 function show_referral(transid) {
  dlgopen('../patient_file/transaction/print_referral.php?transid=' + transid,
   '_blank', 550, 400,true); // Force new window rather than iframe because of the dynamic generation of the content in print_referral.php
  return false;
 }

</script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

<script language="JavaScript">

</script>

</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Referrals'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(date("d F Y", strtotime($form_from_date))) ." &nbsp; to &nbsp; ". text(date("d F Y", strtotime($form_to_date))); ?>
</div>

<form name='theform' id='theform' method='post' action='referrals_report.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='control-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility(($form_facility), 'form_facility', true); ?>
            </td>
            <td class='control-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date) ?>'
         class='datepicker form-control'
                 title='<?php echo xla('yyyy-mm-dd') ?>'>
            </td>
            <td class='control-label'>
                <?php echo xlt('To'); ?>:
            </td>
            <td>
               <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date) ?>'
         class='datepicker form-control'
                 title='<?php echo xla('yyyy-mm-dd') ?>'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                     <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                     </a>
                        <?php if ($_POST['form_refresh']) { ?>
                       <a href='#' class='btn btn-default btn-print' id='printbutton'>
                            <?php echo xlt('Print'); ?>
                       </a>
                        <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php
if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table width='98%' id='mymaintable'>
<thead>
<th> <?php echo xlt('Refer To'); ?> </th>
<th> <?php echo xlt('Refer Date'); ?> </th>
<th> <?php echo xlt('Reply Date'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('ID'); ?> </th>
<th> <?php echo xlt('Reason'); ?> </th>
</thead>
<tbody>
<?php
if ($_POST['form_refresh']) {
    $query = "SELECT t.id, t.pid, " .
    "d1.field_value AS refer_date, " .
    "d3.field_value AS reply_date, " .
    "d4.field_value AS body, " .
    "ut.organization, uf.facility_id, p.pubpid, " .
    "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
    "CONCAT(ut.fname,' ', ut.lname) AS referer_to, " .
    "CONCAT(p.fname,' ', p.lname) AS patient_name " .
    "FROM transactions AS t " .
    "LEFT JOIN patient_data AS p ON p.pid = t.pid " .
    "JOIN      lbt_data AS d1 ON d1.form_id = t.id AND d1.field_id = 'refer_date' " .
    "LEFT JOIN lbt_data AS d3 ON d3.form_id = t.id AND d3.field_id = 'reply_date' " .
    "LEFT JOIN lbt_data AS d4 ON d4.form_id = t.id AND d4.field_id = 'body' " .
    "LEFT JOIN lbt_data AS d7 ON d7.form_id = t.id AND d7.field_id = 'refer_to' " .
    "LEFT JOIN lbt_data AS d8 ON d8.form_id = t.id AND d8.field_id = 'refer_from' " .
    "LEFT JOIN users AS ut ON ut.id = d7.field_value " .
    "LEFT JOIN users AS uf ON uf.id = d8.field_value " .
    "WHERE t.title = 'LBTref' AND " .
    "d1.field_value >= ? AND d1.field_value <= ? " .
    "ORDER BY ut.organization, d1.field_value, t.id";
    $res = sqlStatement($query, array($from_date, $to_date));

    while ($row = sqlFetchArray($res)) {
        // If a facility is specified, ignore rows that do not match.
        if ($form_facility !== '') {
            if ($form_facility) {
                if ($row['facility_id'] != $form_facility) {
                    continue;
                }
            } else {
                if (!empty($row['facility_id'])) {
                    continue;
                }
            }
        }

    ?>
   <tr>
    <td>
        <?php if ($row['organization']!=null || $row['organization']!='') {
            echo text($row['organization']);
} else {
    echo text($row['referer_to']);
}

    ?>
    </td>
    <td>
     <a href='#' onclick="return show_referral(<?php echo attr($row['id']); ?>)">
        <?php echo text(oeFormatShortDate($row['refer_date'])); ?>&nbsp;
     </a>
    </td>
    <td>
        <?php echo text(oeFormatShortDate($row['reply_date'])) ?>
    </td>
    <td>
        <?php echo text($row['patient_name']) ?>
    </td>
    <td>
        <?php echo text($row['pubpid']) ?>
    </td>
    <td>
        <?php echo text($row['body']) ?>
    </td>
   </tr>
    <?php
    }
}
?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>
</form>

</body>
</html>
