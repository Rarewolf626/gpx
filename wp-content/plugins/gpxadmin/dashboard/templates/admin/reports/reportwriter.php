<?php

extract($static);
extract($data);
include $dir . '/templates/admin/header.php';
$sidebar = '<div class="col-xs-12 col-sm-3 col-md-6">';
$sidebar .= '<h3 style="margin-bottom:20px;">Select Existing Report</h3>';

$reportMap = array();

foreach ($reports as $report) {
  if (empty($report->name)) {
    continue;
  }
  if (!array_key_exists($report->reportType, $reportMap)) {
    $reportMap[$report->reportType] = array();
  }
  array_push($reportMap[$report->reportType], $report);
}

$order = array( 'Individual', 'Group', 'Universal' );
$orderedArray = array();

foreach ($order as $key) {
  if (array_key_exists($key, $reportMap)) {
    $orderedArray[$key] = $reportMap[$key];
  }
}

foreach ($orderedArray as $reportType => $reports) {
  $sidebar .= '<h4>' . $reportType . '</h4>';
  $sidebar .= '<ul class="reportLinks" style="margin-bottom: 20px">';
  foreach ($reports as $report) {
    $sidebar .= '<li>';
    $sidebar .= '<a href="/wp-admin/admin.php?page=gpx-admin-page&amp;gpx-pg=reports_writer&amp;id=' . $report->id . '" target="_blank">' . $report->name . '</a>';
    $sidebar .= '&nbsp;&nbsp;<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer&editid=' . $report->id . '"><i class="fa fa-pencil"></i></a>';
    $sidebar .= '</li>';
  }
  $sidebar .= '</ul>';
}
$sidebar .= '</div>';

$showName = 'style="display: none;"';
$name = '';
$reportHeadName = 'Custom Reports';
$currentUser = wp_get_current_user();
$reportUser = get_user_by('id', $editreport->userID);
$isFormDisabled = false;
$userInstruction = '';

if (isset($editreport) && $editreport->reportType === 'Universal' && $editreport->userID != $currentUser->ID) {
  $isFormDisabled = true;
  if ($reportUser) {
    $userInstruction = 'Only editable by: ' . $reportUser->data->user_nicename . ' (' . $reportUser->data->user_email . ')';
  }
}

if(isset($_GET['admin_override']))
{
    $isFormDisabled = false;
}

if (isset($editreport->name)) {
  $name = $reportHeadName = $editreport->name;
  $showName = '';
}
?>
<div class="right_col" role="main">
  <div class="gpxRW">

    <div class="page-title">
      <div class="title_left">
        <h3><?= $reportHeadName ?></h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12">
        <?php
        if (!empty($reportid)) {
        ?>
          <table data-toggle="table" data-url="<?= admin_url("/admin-ajax.php?action=gpx_report_writer_table&id=" . $id); ?>" data-cache="false" data-pagination="true" data-page-size="20" data-page-list="[10,20,50,100]" data-sort-name="status" data-show-refresh="true" data-show-toggle="true" data-filter-default="true" data-show-columns="true" data-show-export="true" data-export-data-type="all" data-export-types="['csv', 'txt', 'excel']" data-search="true" data-sort-order="asc" data-show-columns="true" data-filter-control="true" data-filter-show-clear="true" data-escape="false">
            <thead>
              <tr>
                <?php
                foreach ($th as $field) {
                  $exp = explode(".", $field);
                  //                   	    echo '<pre>'.print_r($exp, true).'</pre>';
                  //                   	    echo '<pre>'.print_r($rw[$exp[0]]['fields'][$exp[1]], true).'</pre>';
                  if (isset($rw[$exp[0]]['fields'][$exp[1]]['type']) && ($rw[$exp[0]]['fields'][$exp[1]]['type'] == 'join' || $rw[$exp[0]]['fields'][$exp[1]]['type'] == 'join_case' || $rw[$exp[0]]['fields'][$exp[1]]['type'] == 'case')) {
                    $name = $rw[$exp[0]]['fields'][$exp[1]]['name'];
                    $field = $exp[0] . "." . $rw[$exp[0]]['fields'][$exp[1]]['column'];
                    $col = $rw[$exp[0]]['fields'][$exp[1]]['column'];
                    if ($rw[$exp[0]]['fields'][$exp[1]]['type'] == 'join_case' && isset($rw[$exp[0]]['fields'][$exp[1]]['column_special'])) {
                      $field = $col;
                    } elseif(substr( $col, 0, 5 ) === "data.") {
                      if (substr( $col, 0, 5 ) === "data.") {
                        $coll = substr( $col, 5, strlen($col) );
                        $field = $exp[0].'.'.$coll;
                      }
                    }
                    if(isset($rw[$exp[0]]['fields'][$exp[1]]['column_override']))
                    {
                        $field = $exp[0] . "." . $rw[$exp[0]]['fields'][$exp[1]]['column_override'];
                    }
                    //                   	        echo '<pre>'.print_r($field, true).'</pre>';
                    //                   	        $field = $exp[0].".".$rw[$exp[0]]['fields'][$exp[1]]['column'];
                  } elseif ($exp[1] == 'cancelledData') {
                    $name = $rw[$exp[0]]['fields'][$exp[1]][$exp[1]][$exp[2]];
                  } elseif ($exp[0] == 'wp_credit' && count($exp) == 3) {
                    $name = $rw[$exp[0]]['fields'][$exp[2]]['name'];
                  } elseif (isset($rw[$exp[0]]['fields'][$exp[2]]['type']) && $rw[$exp[0]]['fields'][$exp[2]]['type'] == 'usermeta') {
                    $name = $rw[$exp[0]]['fields'][$exp[2]]['name'];
                  } elseif (count($exp) == 3) {
                    $name = $rw[$exp[0]]['fields'][$exp[1]]['data'][$exp[2]];
                  } else {
                    $name = $rw[$exp[0]]['fields'][$exp[1]];
                  }
                ?>
                  <th data-field="<?= $field ?>" data-filter-control="input" data-sortable="true"><?= $name ?></th>
                <?php
                }
                ?>
              </tr>
            </thead>
          </table>
        <?php
        } else {
          $gened = 'Generate';
          if (isset($_GET['editid'])) {
            $gened = 'Edit';
          }
        ?>
          <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-6">
              <div class="row">
                <div class="col-xs-6">
                  <?php echo $isFormDisabled ? '<h4>' . $userInstruction . '</h4>' : '<h3>' . $gened . ' Report' . '</h3>'; ?>
                </div>
                <div class="col-xs-6 pull-right" style="text-align: right;">
                  <?php
                  if ($gened == 'Edit') {
                  ?>
                    <button class="btn btn-danger" id="remove-report" data-id="<?= $editreport->id ?>" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>Remove</button>
                  <?php
                  }
                  ?>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-12 col-sm-9 col-md-6">
              <form name="reportwriter<?php echo $isFormDisabled ? ' rwdisabled' : ''; ?> " id="reportwriter" method="post">
                <?php
                if (isset($editreport->id)) {
                ?>
                  <input type="hidden" name="editid" id="editid" value="<?= $editreport->id ?>" />
                <?php
                }
                ?>
                <div class="row well">
                  <div class="col-xs-12">
                    <label for="reportType">Report Type</label>
                    <select name="type" id="reportType" class="form-control select2" id="reportType">
                      <option value="0">Select Option</option>
                      <?php
                      $options = [
                        'Single',
                        'Individual',
                        'Group',
                        'Universal',
                      ];
                      if (isset($editreport->reportType)) {
                        $reportTypes = explode(",", $editreport->reportType);
                      }
                      foreach ($options as $option) {
                        $selected = '';
                        if (isset($reportTypes) && in_array($option, $reportTypes)) {
                          $selected = 'selected="selected"';
                        }
                      ?>
                        <option <?= $selected ?>><?= $option ?></option>
                      <?php
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="row well" id="reportName" <?= $showName ?>>
                  <div class="col-xs-12">
                    <label for="name">Report Name</label>
                    <input type="text" class="form-control" name="name" id="name" value="<?= $name ?>" <?php echo $isFormDisabled ? ' disabled' : ''; ?> />
                  </div>
                </div>
                <?php
                ?>
                <div class="row well" id="groupType">
                  <div class="col-xs-12">
                    <label for="role">Role</label>
                    <select name="role[]" id="role" class="form-control select2" multiple="multiple" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>
                      <option value="0">Select Role</option>
                      <?php
                      if (isset($editreport->role)) {
                        $selRoles = explode(",", $editreport->role);
                      }
                      foreach ($available_roles as $rk => $rv) {
                        $selected = '';
                        if (isset($selRoles) && in_array($rk, $selRoles)) {
                          $selected = 'selected="selected"';
                        }
                      ?>
                        <option value="<?= $rk ?>" <?= $selected ?>><?= $rv['name'] ?></option>
                      <?php
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="row well">
                  <div class="col-xs-12 col-md-6">
                    <div class="form-row">
                      <label for="table">Select Table</label>
                      <select name="table" id="table" class="form-control select2" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>
                        <option value="0">Select Table</option>
                        <?php
                        foreach ($tables as $tk => $tv) {
                        ?>
                          <option value="<?= $tk ?>"><?= $tv ?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </div>
                    <?php
                    foreach ($tables as $tk => $tv) {
                    ?>
                      <div class="reportwriter-drag well">
                        <ul class="sortconnect" id="<?= $tk ?>">
                          <?php
                          foreach ($fields[$tk] as $field) {

                          ?>
                            <li data-field="<?= $field['field'] ?>"><?= $field['name'] ?></li>
                          <?php
                          }
                          ?>
                        </ul>
                      </div>
                    <?php
                    }
                    ?>
                  </div>
                  <div class="col-xs-12 col-md-6">
                    <div class="reportwriter-drop well">
                      <ul class="sortconnect">
                        <?php
                        if (isset($editreport->formData)) {
                          echo stripslashes(base64_decode($editreport->formData));
                        }
                        ?>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="row well">
                  <div class="col-xs-12">
                    <h3>Conditions</h3>
                    <?php
                    $conditions = [
                      'blank'
                    ];
                    if (isset($editreport->conditions)) {
                      $conditions = json_decode($editreport->conditions);
                    }
                    $ci = 1;
                    foreach ($conditions as $condition) {
                    ?>
                      <div class="row conditionGroup well" id="cgp<?= $ci ?>" data-gp="<?= $ci ?>">
                        <?php if (!$isFormDisabled) : ?>
                          <a href="#" class="removeWell"><i class="fa fa-close"></i></a>
                        <?php endif; ?>
                        <?php
                        if (isset($condition->operand)) {
                          $andSel = 'btn-secondary';
                          if ($condition->operand == 'and') {
                            $andSel = 'btn-primary';
                          }
                          $orSel = 'btn-secondary';
                          if ($condition->operand == 'or') {
                            $orSel = 'btn-primary';
                          }
                        ?>
                          <input type="hidden" name="operand[1]" class="operand" value="<?= $condition->operand ?>" />
                          <div class="btn-group">
                            <button class="btn <?= $andSel ?> selectoperand" type="button" data-value="and">AND</button>
                            <button class="btn <?= $orSel ?> selectoperand" type="button" data-value="or">OR</button>
                          </div><br>
                        <?php
                        }
                        ?>
                        <select name="condition[<?= $ci ?>]" class="condition form-contorl select2" width="100%" style="min-width: 380px;" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>
                          <option>Select Item</option>
                          <?php
                          foreach ($wheres as $table => $each) {
                            foreach ($each as $where) {
                              $selected = '';
                              if (isset($condition->condition) && $condition->condition == $where['field']) {
                                $selected = 'selected="selected"';
                              }
                          ?>
                              <option class="<?= $table ?>-enable disabled" value="<?= $where['field'] ?>" <?= $selected ?>><?= $table ?> <?= $where['name'] ?></option>
                          <?php
                            }
                          }
                          ?>
                        </select>
                        <select name="operator[<?= $ci ?>]" class="operator form-control select2" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>
                          <option value="0">Select Operator</option>
                          <?php
                          $options = [
                            'equals',
                            'greater',
                            'less',
                            'like',
                            'yesterday',
                            'today',
                            'this week',
                            'last week',
                            'this month',
                            'last month',
                            'this year',
                            'last year',
                          ];
                          foreach ($options as $option) {
                            $optionValue = $option;
                            if ($option == 'greater') {
                              $optionValue = 'greater than';
                            }
                            if ($option == 'less') {
                              $optionValue = 'less than';
                            }
                            $option = str_replace(" ", "_", $option);
                            $selected = '';
                            if (isset($condition->operator) && $option == $condition->operator) {
                              $selected = 'selected="selected"';
                            }
                          ?>
                            <option value="<?= $option ?>" <?= $selected ?>><?= ucwords($optionValue) ?></option>
                          <?php
                          }
                          ?>
                        </select>
                        <?php
                        $conditionValue = '';
                        if (isset($condition->conditionValue)) {
                          $conditionValue = $condition->conditionValue;
                        }
                        ?>
                        <input type="text" name="conditionValue[<?= $ci ?>]" class="conditionValue form-control" placeholder="Value" value="<?= $conditionValue ?>" />
                      </div>
                    <?php
                      $ci++;
                    }
                    ?>
                    <div id="addConditions"></div>
                    <div class="row">
                      <button id="newCondition" class="btn btn-primary" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>+</button>
                    </div>
                  </div>
                </div>
                <div class="row well" id="emailReport" <?= $showName ?>>
                  <div class="col-xs-12">
                    <h3>Email Report</h3>
                    <label for="emailrepeat">Frequency</label>
                    <select name="emailrepeat" id="emailrepeat" class="form-control select2" <?php echo $isFormDisabled ? ' disabled' : ''; ?>>
                      <option value="0">Select Option</option>
                      <?php
                      $options = [
                        'Daily',
                        'Weekdays',
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                        'Sunday',
                        'Monthly',
                      ];
                      foreach ($options as $option) {
                        $selected = '';
                        if (isset($editreport->emailrepeat) && $editreport->emailrepeat == $option) {
                          $selected = 'selected="selected"';
                        }
                      ?>
                        <option <?= $selected ?>><?= $option ?></option>
                      <?php
                      }
                      ?>
                    </select>
                    <label for="emailrecipients" style="margin-top: 15px;">Recipents</label>
                    <?php
                    $recipients = '';
                    if (isset($editreport->emailrecipients)) {
                      $recipients = $editreport->emailrecipients;
                    }
                    ?>
                    <textarea id="emailrecipients" name="emailrecipients" class="form-control" placeholder="(email address seperated by comma)" <?php echo $isFormDisabled ? ' disabled' : ''; ?>><?= $recipients ?></textarea>
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-12">
                      <button type="submit" id="reportWriterSubmit" class="btn btn-primary">Submit</button>
                      <a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer" class="btn btn-secondary">Cancel</a>
                  </div>
                </div>
              </form>
            </div>
            <?= $sidebar ?>
          </div>
        <?php
        }
        ?>
      </div>
    </div>
  </div>
</div>
<?php include $dir . '/templates/admin/footer.php'; ?>