<?php
class SiteController extends Controller {
 
    function index() {
        $this->_template->render('index');
    }
}