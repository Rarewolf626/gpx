<?php
if(!empty($resort->HTMLAlertNotes) || (isset($resort->AdditionalInfo) && !empty($resort->AdditionalInfo)) || !empty($resort->DisabledNotes) || !empty($resort->AlertNote))
{
    ?>

<div class="title">
    <div class="close">
        <i class="icon-close"></i>
    </div>
    <h4>Important Information</h4>
</div>
<div class="cnt-list">
    <ul class="list-cnt full-list">
    	<!--  
        <li>
            <p><strong>Office Hours</strong></p>
        </li>
        <li>
            <p>Mon and Tues: 8.3 0am – 3 pm</p>
        </li>
        <li>
            <p>Wed and Thurs: 8.30 am – 5 pm</p>
        </li>
        <li>
            <p>Fri and Sat: 8 am – 6 pm</p>
        </li>
        <li>
            <p>Sun and Public Holidays: 9 am – 12 noon</p>
        </li>
        -->
        <?php 
            if(!empty($resort->HTMLAlertNotes) || $resort->AlertNote)
            {
            ?>
        <li>
            <p><strong>Alert Note</strong></p>
        </li>
        		<?php 
        		if(!empty($resort->HTMLAlertNotes) && empty($resort->AlertNote))
        		{
        		?>
        <li class="alert-note-info">
			<p><?=$resort->HTMLAlertNotes?></p>
        </li>            
            <?php 
        		}
        		if(!empty($resort->AlertNote))
        		{
        		    if(is_array($resort->AlertNote))
        		    {
        		        foreach($resort->AlertNote as $ral)
        		        {
        		            $theseDates = [];
        		            foreach($ral['date'] as $thisdate)
        		            {
        		                $theseDates[] = date('m/d/y', $thisdate);
        		            }
        		?>
        <li class="alert-note-info">
        	
			<p><strong>Beginning <?php echo implode(" Ending ", $theseDates)?>:</strong><br/><?=nl2br(stripslashes($ral['desc']))?></p>
        </li>            
            	<?php 
        		        }
        		    }
        		    else
        		    {
        		?>
        <li class="alert-note-info">
			<p><?=$resort->AlertNote?></p>
        </li>            
            	<?php 
        		    }
        		}
            }
            if(isset($resort->AdditionalInfo) && !empty($resort->AdditionalInfo))
            {
        ?>
        <li>
            <p><strong>Additional Info</strong></p>
        </li>
        <li>
            <p><?=$resort->AdditionalInfo?></p>
        </li>
        <?php 
            }
            if(!empty($resort->DisabledNotes))
            {
                /*
            ?>
        <li>
            <p><strong>Disabled Notes</strong></p>
        </li>
        <li>
			<p><?=$resort->DisabledNotes?></p>
        </li>            
            <?php 
            */
            }
        ?>

    </ul>
</div>
<?php 
}
?>
