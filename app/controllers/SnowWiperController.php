<?php

namespace App\Controllers;

use App\System\SnowWiper;

class SnowWiperController extends ControllerBase
{
    public function getStateAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->getState());
    }

    public function turnOnAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->turnOn());
    }

    public function turnOffAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->turnOff());
    }

    public function pulseAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->pulse());
    }

    public function autoPulseAction($state = 0)
    {
        $filename = 'c:/xampp/htdocs/solar/job/autopulse.ini';
        file_put_contents($filename, "state=$state");
        return json_encode([ 'autopulse' => $state ]);
    }
}
