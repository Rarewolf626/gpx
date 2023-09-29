<?php
/**
 * @var Collection $regions
 * @var array{oddness: int, duplicates: int, wrong_parent: int, missing_parent: int} $errors
 */

use Kalnoy\Nestedset\Collection;

$traverse = function ($regions, $parent = null) use (&$traverse) {
    echo '<table class="region-list">';
    foreach ($regions as $region) {
        echo '<tr class="header">';
        echo '<td>' . esc_html($region->id) . '</td>';
        echo '<td>' . esc_html($region->name) . '</td>';
        echo '<td>';
        if($region->parent != 1) {
            echo 'Parent: ' . esc_html($region->parent);
        }
        if($parent){
            echo ' (' . esc_html($parent->name) . ')';
        }
        echo '</td>';
        echo '<td>Left: ' . esc_html($region->lft) . '</td>';
        echo '<td>Right: ' . esc_html($region->rght) . '</td>';
        echo '<td>' . ($region->ddHidden ? 'Inactive' : 'Active') . '</td>';
        echo '</tr>';

        if ($region->children->isNotEmpty()) {
            echo '<tr><td colspan="6" class="children">';
            $traverse($region->children, $region);
            echo '</td></tr>';
        }

    }
    echo '</table>';
};

?>
<style>
    ul.region-list {
        list-style: disc;
        padding-left: 25px;
    }

    table.region-list {
        border-collapse: collapse;
        width: auto;
        margin:0;
    }
    table.region-list table.region-list {
        width: 100%;
    }

    table.region-list th, table.region-list td {
        padding: 6px;
        border: solid 1px #ccc;
    }
    table.region-list tr.header td {
        background-color: #eee;
    }
    table.region-list td.children {
        padding: 15px;
    }
    .error-table {
        width: auto;
    }
    .error-table th, .error-table td {
        padding: 6px;
        border: solid 1px #ccc;
    }
</style>
<?php gpx_admin_view('header.php', ['active' => 'regions']); ?>
<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Region Hierarchy</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="x_content">

                <h2>Tree Errors</h2>
                <table class="error-table">
                    <tr>
                        <th># of regions with incorrect left/right values</th>
                        <td><?= esc_html($errors['oddness'])?></td>
                    </tr>
                    <tr>
                        <th># of regions with duplicate left/right values</th>
                        <td><?= esc_html($errors['duplicates'])?></td>
                    </tr>
                    <tr>
                        <th># of regions with incorrect parent id</th>
                        <td><?= esc_html($errors['wrong_parent'])?></td>
                    </tr>
                    <tr>
                        <th># of regions with missing parent</th>
                        <td><?= esc_html($errors['missing_parent'])?></td>
                    </tr>
                </table>
                <h2>Regions</h2>
                <?php $traverse($regions);?>
            </div>
        </div>
    </div>
</div>
<?php gpx_admin_view('footer.php'); ?>
