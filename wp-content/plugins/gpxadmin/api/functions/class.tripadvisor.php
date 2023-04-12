<?php 
class TARetrieve
{

    public TripadvisorModel $ta_model;

    public function __construct($uri, $dir)
    {
        require_once $dir.'/models/tripadvisormodel.php';
        $this->ta_model = new TripadvisorModel;
    }
    
    public function location($locationID)
    {
        $inputMember = array(
            'type' => 'location',
            'data' => $locationID
        );
        
        $data = $this->ta_model->retrieve($inputMember);
        
        return $data;
    }
    
    public function location_mapper($coords, $name='')
    {
        $inputMember = array(
            'type' => 'location_mapper',
            'data' => $coords,
        );
        
        if(!empty($name))
        {
            $inputMember['q'] = 'q='.urlencode($name);
        }
        
        
        $retrieve = $this->ta_model->retrieve($inputMember);
        
        $data = json_decode($retrieve);
        
        return $data;
    }
}