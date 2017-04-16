<?php

namespace App\Controllers;

class ReportController extends ControllerBase
{
    public function indexAction()
    {
        return $this->dispatcher->forward([
            'controller' => 'report',
            'action' => 'daily'
        ]);
    }

    public function dailyAction()
    {
        $this->view->pageTitle = 'Daily Report';
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $date = date('Ymd', strtotime('-1 day'));
        $filename = BASE_DIR . "/app/logs/daily-report-$date.json";

        if (!file_exists($filename)) {
            return;
        }

        $json = file_get_contents($filename);
        $report = json_decode($json, true);

        $this->view->report = $report;
    }

    public function monthlyAction()
    {
        $this->view->pageTitle = 'Monthly Report';
        $this->view->today = date('l, F jS Y');
        $this->view->report = [];

        $date = date('Ymd', strtotime('-1 day'));
        $filename = BASE_DIR . "/app/logs/monthly-report-$date.json";

        if (!file_exists($filename)) {
            return;
        }

        $json = file_get_contents($filename);
        $report = json_decode($json, true);

        $this->view->report = $report;
    }
}
