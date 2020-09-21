<?php

class Paginator
{
    private $total_item_count;
    private $per_page_item_count = 10;
    private $current_page_num    = 1;
    private $page_btn_count      = 5;

    public function __construct($total_item_count)
    {
        $this->total_item_count = $total_item_count;
    }

    public function getCurrentPageNum()
    {
        return $this->current_page_num;
    }

    public function setCurrentPageNum($page_num)
    {
        if ($page_num >= 1 && $page_num <= $this->getMaxPageNum()) {
            $this->current_page_num = $page_num;
        } elseif ($page_num > $this->getMaxPageNum()) {
            $this->current_page_num = $this->getMaxPageNum();
        }
    }

    public function getPerPageItemCount()
    {
        return $this->per_page_item_count;
    }

    public function setPerPageItemCount($per_page_item_count)
    {
        $this->per_page_item_count = $per_page_item_count;
    }

    // $page_btn_countのgetterは必要ないの？
    public function setPageBtnCount($page_btn_count)
    {
        $this->page_btn_count = $page_btn_count;
    }

    public function getMaxPageNum()
    {
        $max_page_num = 1;
        if ($this->total_item_count > 0) {
            $max_page_num = (int) ceil($this->total_item_count / $this->per_page_item_count);
        }

        return $max_page_num;
    }

    public function hasPrevPageNum()
    {
        return ($this->current_page_num > 1);
    }

    public function getPrevPageNum()
    {
        return $this->current_page_num - 1;
    }

    public function hasNextPageNum()
    {
        return ($this->current_page_num < $this->getMaxPageNum());
    }

    public function getNextPageNum()
    {
        return $this->current_page_num + 1;
    }

    public function getPageNums()
    {
        $page_nums = [];

        $max_page_num = $this->getMaxPageNum();

        $prev_page_btn_count = (int) ceil(($this->page_btn_count - 1) / 2);
        $next_page_btn_count = $this->page_btn_count - 1 - $prev_page_btn_count;

        $prev_over_count = 0;
        if (($this->current_page_num - $prev_page_btn_count) < 1) {
            $prev_over_count = abs($this->current_page_num - $prev_page_btn_count - 1);
        }

        $next_over_count = 0;
        if (($this->current_page_num + $next_page_btn_count) > $max_page_num) {
            $next_over_count = $this->current_page_num + $next_page_btn_count - $max_page_num;
        }

        $start_page_num = 1;
        if (($this->current_page_num - $prev_page_btn_count - $next_over_count) >= 1) {
            $start_page_num = $this->current_page_num - $prev_page_btn_count - $next_over_count;
        }

        $end_page_num = $max_page_num;
        if (($this->current_page_num + $next_page_btn_count + $prev_over_count) < $max_page_num) {
            $end_page_num = $this->current_page_num + $next_page_btn_count + $prev_over_count;
        }

        for ($page_num = $start_page_num; $page_num <= $end_page_num; $page_num++) {
            $page_nums[] = $page_num;
        }

        return $page_nums;
    }
}
