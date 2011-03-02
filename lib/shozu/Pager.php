<?php
namespace shozu;
/**
 * Creates pagination for records
 */
class Pager
{
    /**
     * Paginate
     *
     * <code>
     * $total_number_of_items = 57;
     * $number_of_items_per_page = 10;
     * $item_offset = 20;
     * $pagination = Pager::paginate($total_number_of_items, $number_of_items_per_page, $item_offset);
     * </code>
     *
     * @param integer $total Total number of items
     * @param integer $limit Limit per page
     * @param integer $offset current offset
     * @param integer $stop limit number of page
     * @return array Pages as array(number,offset,current)
     */
    public static function paginate($total, $limit, $offset = 0, $stop = 0)
    {
        $pages = array();
        if ($total > $limit)
        {
            for ($k = 1, $j = 0;$j < $total;$k++, $j = $j + $limit)
            {
                if($stop > 0)
                {
                    if($j > ($offset + $limit * $stop) or $j < ($offset - $limit * $stop))
                    {
                        continue;
                    }
                }
                $current = ($j == $offset) ? true : false;
                $pages[] = array('number' => $k, 'offset' => $j, 'current' => $current);
            }
        }
        return $pages;
    }
}