<?php
// placeholder, member 5 builds the real dashboard
class DashboardController extends Controller {
    function index() {
        $this->needLogin();
        $this->view('dashboard');
    }
}
