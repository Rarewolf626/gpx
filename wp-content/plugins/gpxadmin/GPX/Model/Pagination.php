<?php

namespace GPX\Model;

class Pagination
{

    private $offset;

    private $limit;

    private $total;

    private $soql_max = 2000;

    // 10 pages at a time
    private $display_pages = 20;   // make sure it's an even number

    private $pagination_data = array();

    public function __construct($page, $total, $limit) {
            $this->total = $total;
            $this->limit = $limit;
            $page = intval($page);
            $this->buttons($page);
    }

    private function buttons ($page) {
        // calculate range...
        // start at page 1
        if ($page < 1) $page = 1;

        // first number =  page - 5
        $first_number = $page - $this->display_pages/2;
        if ($first_number < 1 ) $first_number = 1;

        $max_pages = ceil($this->total / $this->limit);

        $last_number = $first_number + $this->display_pages -1;
        if ($last_number > $max_pages) $last_number = $max_pages;

        // work in the soql limits
        if (($page * $this->limit) > $this->soql_max) {
            // gone over the soql_max, set the page back under the limit
            $page = floor($this->soql_max / $this->limit);
        }


        $this->pagination_data['current_page'] = $page;
        $this->pagination_data['first_page'] = $first_number;
        $this->pagination_data['last_page'] = $last_number;
        $this->pagination_data['max_pages'] = $max_pages;

        $this->pagination_data['previous_page'] = ($first_number == 1) ? null : $this->pagination_data['current_page']-1;
        $this->pagination_data['next_page'] = ($last_number ==  $max_pages) ? null :  $this->pagination_data['current_page']+1;

    }


    public function display_html($link, $pagevar='p') {

        $html = '<ul class="pagination">';
        // if in url use it.
        $params['total'] = $this->total;

        if ($this->pagination_data['previous_page'])  {
            $params[$pagevar] = $this->pagination_data['previous_page'];
            $html .= '<li> <a href="'.$this->url($link,$params).'">&#8656; Prev</a></li>';
        }

        for ($x =  $this->pagination_data['first_page']; $x <= $this->pagination_data['last_page']; $x++ ){

            $active = ($this->pagination_data['current_page'] == $x) ? "class='active'"  : '';

            $params[$pagevar] = $x;
            $html .=  '<li><a '.$active.'href="'.$this->url($link,$params).'">' . $x . '</a></li>';
        }

        if ($this->pagination_data['next_page'])  {
            $params[$pagevar] = $this->pagination_data['next_page'];
            $html .= '<li> <a href="'.$this->url($link,$params).'">Next &#8658;</a></li>';
        }

        $html .= '</ul>';

    return $html;
    }

    private function url($link,$params){
        return $link.'?'.http_build_query($params);
    }


    public function get_data() {
        return $this->pagination_data;
    }





}
