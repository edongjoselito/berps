<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Mobile app pages (public, no auth required).
 */
class Mobile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Public privacy policy page for the BERPS mobile app.
     * This URL can be submitted to Google Play and the Apple App Store.
     */
    public function privacy()
    {
        $this->load->view('mobile_privacy');
    }
}
