<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageLoader extends Controller
{

    private function page_loader($viewName,$data) {
        
        echo view("templates.header",$data);
        echo view("pages.".$viewName,$data);
        echo view("templates.footer",$data);

    }
    
    function home() {
        
        $this->page_loader("app",[
            "title" => "Screen Candidates based on Job Description"
        ]);

    }

}
