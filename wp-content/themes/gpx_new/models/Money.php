<?php

class Money
{
    public string $str;
    public string $currency;
    public string $symbol;
    public float $amount = 0.00;
    public string $weekEndPointID;
    private string $symbols;

    /*
     * @params $string - WeekPrice format
     */
    public function __construct(string $str, string $endpointid=''){

        $this->$symbols = '£$R€';
        $this->string_to_money($str);

        if (isset($endpointid)){
            $this->weekEndPointID = $endpointid;
        }
    }

    public function string_to_money (string $str){

        preg_match('/^([A-Z]{3})\s(.)([0-9\.\,]+)/u', $str, $data);

        $this->str = $data[0];
        $this->currency = $data[1];
        $this->symbol = $data[2];
        $this->amount = floatval(str_replace(',','',$data[3]));

    }

    public function get_string(){
        return $this->currency.' '.$this->symbol.' '.$this->amount;
    }
}
