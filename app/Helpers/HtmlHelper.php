<?php

namespace App\Helpers;


class HtmlHelper
{





    static public function dropdownMenuButton($list)
    {

        $links = [];
        foreach ($list as $key => $value) {
            $links[] = '<a class="dropdown-item" target="_blank" href="' . $value['url']   . '">' . $value['label'] . '</a>';
        }


        return '<div class="btn-group">
        <div class="dropdown">
          <button class="btn btn-primary btn-sm dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="la la-print"></i>
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">

                    ' . implode('', $links) . '

          </div>
        </div>
      </div>';
    }
}
