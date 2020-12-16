<?php 

class GpxModel {
    
    public function logged_in_user()
    {
        
    }
    
    public function parse_page($page)
    {
        $exp = explode("_", $page);
        if(isset($exp[1]))
        {
            $data['parent'] = $exp[0];
            
            if($exp[1] == 'all')
                $data['child'] = $exp[0];
            else 
            {
                if (substr($data['parent'], -1) == 's')
                    $str = substr($data['parent'], 0, -1);
                else
                    $str = $data['parent'];
                
                $data['child'] = $str.$exp[1];
            }
               
        }
        else 
            $data = $data = array('parent'=>'', 'child'=>$exp[0]);
        
        return $data;
    }

    public function rebuild_tree($parent, $left) {   

        global $wpdb;
        // the right value of this node is the left value + 1   
    
        $right = $left+1;   

        $sql = 'SELECT id FROM wp_gpxRegion WHERE parent="'.$parent.'"';
        $regions = $wpdb->get_results($sql);
        foreach($regions as $regionID)
        {
            $right = $this->rebuild_tree($regionID->id, $right);
        }
        
        $rld = array('lft'=>$left, 'rght'=>$right);
        $wpdb->update('wp_gpxRegion', $rld, array('id'=>$parent));
    
        return $right+1;   

    }  
   

    
}