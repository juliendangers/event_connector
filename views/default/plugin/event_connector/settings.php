<?php
	if($vars['entity']->timezone == "")
		$vars['entity']->timezone = "Europe/Paris";
?>
<p>
	<?php echo elgg_echo('event_connector:timezone'); ?>
	<select name="params[timezone]">
    <?php
    $timezone_identifiers = DateTimeZone::listIdentifiers();
    foreach( $timezone_identifiers as $value ){
        if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $value ) ){
            $ex=explode("/",$value);//obtain continent,city    
            if ($continent!=$ex[0]){
                if ($continent!="") echo '</optgroup>';
                echo '<optgroup label="'.$ex[0].'">';
            }
    
            $city=$ex[1];
            $continent=$ex[0];
            echo '<option value="'.$value.'"'; if ($vars['entity']->timezone == $value) echo " selected=\"yes\" "; echo ">".$city.'</option>';        
        }
    }
    ?>
        </optgroup>
    </select>
</p>