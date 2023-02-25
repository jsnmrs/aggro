<?php

namespace App\Controllers;

/**
 * Default home contoller.
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
