<?php

namespace App\Controllers;

/**
 * Default home controller.
 */
class Home extends BaseController
{
    /**
     * Default home index.
     */
    public function getIndex()
    {
        return view('welcome_message');
    }
}
