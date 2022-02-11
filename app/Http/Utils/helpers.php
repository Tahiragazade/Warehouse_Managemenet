<?php

/**
 * @param $organizations
 * @param null $parent
 * @return array
 */

/*
 * Dropdown selector
 */
function GenerateCategorySelectTree($categories, $parent = null) {
    $tree = [];
    foreach ($categories as $category) {
        if($category->parent_id == $parent) {
            $tree[] = array(
                'key' => $category->id,
                'value' => $category->id,
                'title' => $category->name,
                'children' => GenerateCategorySelectTree($categories, $category->id)
            );
        }
    }

    return $tree;
}
/*
Dropdown delete empty children
*/
function clearEmptyChildren(&$tree)
{
    foreach ($tree as $key =>$value )
    {
        if(empty($value['children']))
        {
            unset($tree[$key]['children']);
        }
        else
        {
            clearEmptyChildren($tree[$key]['children']);
        }
    }
}
