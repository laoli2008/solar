<?php

namespace App\System;

class Project
{
    const GENMETERS = [7, 16, 17, 19, 20];

    protected $id;
    protected $name;
    protected $ftpdir;
    protected $interval;

    protected $capacityAC;
    protected $capacityDC;

    protected $modulePowerCoefficient = -0.43;
    protected $inverterEfficiency     = 0.98;
    protected $transformerLoss        = 0.015;
    protected $otherLoss              = 0.02;

    protected $devices   = [];  // all devices
    protected $inverters = [];
    protected $envkits   = [];
    protected $genmeters = [];
    protected $combiners = [];

    public function __construct($info)
    {
        $this->id                     = $info['id'];
        $this->name                   = $info['name'];
        $this->ftpdir                 = $info['ftpdir'];
        $this->interval               = $info['interval'];
        $this->capacityDC             = $info['DC_Nameplate_Capacity'];
        $this->capacityAC             = $info['AC_Nameplate_Capacity'];
        $this->modulePowerCoefficient = $info['Module_Power_Coefficient'];
        $this->inverterEfficiency     = $info['Inverter_Efficiency'];
        $this->transformerLoss        = $info['Transformer_Loss'];
        $this->otherLoss              = $info['Other_Loss'];
    }

    public function initDevices($info)
    {
        $type = $info['type'];
        $code = $info['devcode'];

        switch (strtoupper($type)) {
        case 'INVERTER':
            $inverter = new Inverter($this, $info);
            $this->inverters[$code] = $inverter;
            $this->devices[$code] = $inverter;
            break;

        case 'ENVKIT':
            $envkit = new EnvKit($this, $info);
            $this->envkits[$code] = $envkit;
            $this->devices[$code] = $envkit;
            break;

        case 'GENMETER':
            $genmeter = new GenMeter($this, $info);
            $this->genmeters[$code] = $genmeter;
            $this->devices[$code] = $genmeter;
            break;

        case 'COMBINER':
            $combiner = new Combiner($this, $info);
            $this->combiners[$code] = $combiner;
            $this->devices[$code] = $combiner;
            break;

        default:
            throw new \InvalidArgumentException("Unknown device type '$type'");
            break;
        }
    }

    protected function getDb()
    {
        $di = \Phalcon\Di::getDefault();
        return $di->get('db');
    }

    public function getDevices()
    {
        return $this->devices;
    }

    public function getInverters()
    {
        return $this->inverters;
    }

    public function getFirstInverter()
    {
        return current($this->inverters);
    }

    public function getEnvKits()
    {
        return $this->envkits;
    }

    public function getFirstEnvKit()
    {
        return current($this->envkits);
    }

    public function getGenMeters()
    {
        return $this->genmeters;
    }

    public function getFirstGenMeter()
    {
        return current($this->genmeters);
    }

    public function getMonthlyBudget($year, $month)
    {
        $prj = $this->id;
        return $this->getDb()->fetchOne("SELECT * FROM monthly_budget
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    /**
     * @deprecated
     */
    public function getRefData($year, $month)
    {
        $prj = $this->id;
        return $this->getDb()->fetchOne("SELECT * FROM project_reference_data
            WHERE project_id=$prj AND year=$year AND month=$month");
    }

    public function getIRR($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getIRR($period) / $this->interval;
    }

    public function getTMP($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getTMP($period) / $this->interval;
    }

    public function getKW($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getKVA($period) / $this->interval;

        //return $this->getKWH($period);
    }

    public function getAvgIRR($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getAvgIRR($period);
    }

    public function getAvgTMP($period)
    {
        $envkit = current($this->envkits);
        return $envkit->getAvgTMP($period);
    }

    public function getAvgKW($period)
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getAvgKVA($period);
    }

    public function getKWH($period)
    {
        $col = in_array($this->id, self::GENMETERS) ? 'del' : 'rec';
        $genmeter = current($this->genmeters);
        return $genmeter->getKWH($period, $col);
    }

    public function getChartData()
    {
        $envkit = current($this->envkits);
        $irr = $envkit->getChartData();

        $genmeters = current($this->genmeters);
        $kva = $envkit->getChartData();

        return [$irr, $kva];
    }

    public function export($params)
    {
        $filename = BASE_DIR.'/tmp/export-'.str_replace(' ', '-', $this->name).'-'.date('Ymd-His').'.csv';

        $file = fopen($filename, 'w');

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('1 day'));

        $interval  = empty($params['interval'])   ? 1         : $params['interval'];   // set interval=1 if not specified
        $startTime = empty($params['start-time']) ? $today    : $params['start-time'];
        $endTime   = empty($params['end-time'])   ? $tomorrow : $params['end-time'];

        if ($startTime == $endTime) {
            $endTime = date('Y-m-d', strtotime('1 day', strtotime($startTime)));
        }

        fputs($file, 'Project:    ' .$this->name. PHP_EOL);
        fputs($file, 'Interval:   ' .$interval. ' minutes'. PHP_EOL);
        fputs($file, 'Start Time: ' .$startTime. PHP_EOL);
        fputs($file, 'End Time:   ' .$endTime. PHP_EOL. PHP_EOL);

        if (isset($params['envkits'])) {
            foreach ($this->envkits as $envkit) {
                $envkit->export($file, $interval, $startTime, $endTime);
            }
        }

        if (isset($params['genmeters'])) {
            foreach ($this->genmeters as $genmeter) {
                $genmeter->export($file, $interval, $startTime, $endTime);
            }
        }

        if (isset($params['inverters'])) {
            foreach ($this->inverters as $inverter) {
                $inverter->export($file, $interval, $startTime, $endTime);
            }
        }

        fclose($file);

        return $filename;
    }

    public function getDataToCompare($startTime, $endTime, $interval)
    {
        $envkitData = [];
        foreach ($this->envkits as $envkit) {
            $envkitData = $envkit->getDataToCompare($startTime, $endTime, $interval);
        }

        $genmeterData = [];
        foreach ($this->genmeters as $genmeter) {
            $col = in_array($this->id, self::GENMETERS) ? 'kwh_del' : 'kwh_rec';
            $genmeterData = $genmeter->getDataToCompare($startTime, $endTime, $interval, $col);
        }

        $inverterData = [];
        foreach ($this->inverters as $inverter) {
            $inverterData = $inverter->getDataToCompare($startTime, $endTime, $interval);
        }

        $result = [];

        foreach ($envkitData as $time => $irr) {
            $key = substr($time, 0, 16); // remove second
            $result[$key]['irr'] = $irr;
        }

        foreach ($genmeterData as $time => $kwh) {
            $key = substr($time, 0, 16); // remove second
            $result[$key]['kwh'] = $kwh;
        }

        foreach ($inverterData as $time => $kw) {
            $key = substr($time, 0, 16); // remove second
            $result[$key]['kw'] = $kw;
        }

        return $result;
    }

    /**
     * Latest
     */
    public function getLatestIRR()
    {
        $envkit = current($this->envkits);
        return $envkit->getLatestIRR();
    }

    public function getLatestKW()
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getLatestKVA();
    }

    public function getLatestTime()
    {
        $envkit = current($this->envkits);
        return $envkit->getLatestTime();
    }

    /**
     * Snapshot
     */
    public function getSnapshotIRR()
    {
        $envkit = current($this->envkits);
        return $envkit->getSnapshotIRR();
    }

    public function getSnapshotKW()
    {
        $genmeter = current($this->genmeters);
        return $genmeter->getSnapshotKVA();
    }

    public function getSnapshotTime()
    {
        $envkit = current($this->genmeters);
        return $envkit->getSnapshotTime();
    }

    public function getTotalInverters()
    {
        return max(count($this->inverters), 1);
    }

    public function getGeneratingInverters()
    {
        $min30ago = gmdate('Y-m-d H:i:s', strtotime('-30 minutes'));

        $prj = $this->id;
        $sql = "SELECT data FROM latest_data WHERE project_id=$prj AND devtype='Inverter' AND time>'$min30ago'";
        $rows = $this->getDb()->fetchAll($sql);

        $cnt = 0;
        foreach ($rows as $row) {
            $json = $row['data'];
            $data = json_decode($json, true);
            if (isset($data['kw']) && $data['kw'] > 4) {
                $cnt++;
            } else if (isset($data['line_kw']) && $data['line_kw'] > 4) {
                $cnt++;
            }
        }

        if (count($this->inverters) == 0) {
            $cnt += 1;
        }

        return $cnt;
    }

    public function getTotalDevices()
    {
        // old code: return count($this->devices);
        return max(count($this->inverters), 1)
             + count($this->envkits)
             + count($this->genmeters);
    }

    public function getCommunicatingDevices()
    {
        $min30ago = gmdate('Y-m-d H:i:s', strtotime('-30 minutes'));

        $prj = $this->id;
        $sql = "SELECT count(*) AS cnt FROM latest_data WHERE project_id=$prj AND time>'$min30ago'";
        $result = $this->getDb()->fetchOne($sql);

        $cnt = 0;

        if ($result) {
            $cnt = $result['cnt'];
            if (count($this->inverters) == 0) {
                $cnt += 1;
            }
        }

        return $cnt;
    }

    public function __get($prop)
    {
        if (isset($this->$prop)) {
            return $this->$prop;
        }

        return null;
    }

    public function getPR()
    {
        $DC_Nameplate_Capacity    = $this->capacityDC;
        $AC_Nameplate_Capacity    = $this->capacityAC;

        $Module_Power_Coefficient = $this->modulePowerCoefficient;
        $Inverter_Efficiency      = $this->inverterEfficiency;
        $Transformer_Loss         = $this->transformerLoss;
        $Other_Loss               = $this->otherLoss;

        $Avg_Irradiance_POA       = $this->getAvgIRR('LAST-HOUR');
        $Avg_Module_Temp          = $this->getAvgTMP('LAST-HOUR');
        $Measured_Energy          = $this->getAvgKW('LAST-HOUR');

       #$Avg_Irradiance_POA       = $this->getAvgIRR('LAST-HOUR'); // avg 60 minutes
       #$Avg_Module_Temp          = $this->getAvgTMP('LAST-HOUR'); // PANELT
       #$Measured_Energy          = $this->getAvgKW('LAST-HOUR');  // sum 60 minutes

        if ($DC_Nameplate_Capacity == 0) return 0;

        $Maximum_Theory_Output = ($Avg_Irradiance_POA / 1000) * $DC_Nameplate_Capacity;

        if ($Maximum_Theory_Output == 0) return 0;

        $Temperature_Losses = ($Maximum_Theory_Output * ($Module_Power_Coefficient * (25 - $Avg_Module_Temp))) / 1000.0;
        $Inverter_Losses = (1 - $Inverter_Efficiency) * ($Maximum_Theory_Output - $Temperature_Losses);

        if (($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses) > $AC_Nameplate_Capacity) {
            $Inverter_Clipping_Loss = $Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $AC_Nameplate_Capacity;
        } else {
            $Inverter_Clipping_Loss = 0;
        }

        $Transformer_Losses  = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss) * $Transformer_Loss;
        $Other_System_Losses = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss) * $Other_Loss;
        $Total_Losses = ($Temperature_Losses + $Inverter_Losses + $Inverter_Clipping_Loss + $Transformer_Loss + $Other_System_Losses) / $Maximum_Theory_Output;
        $Theoretical_Output = ($Maximum_Theory_Output - $Temperature_Losses - $Inverter_Losses - $Inverter_Clipping_Loss - $Transformer_Loss - $Other_System_Losses);

        if ($Theoretical_Output == 0) return 0;

        $GCS_Performance_Index = $Measured_Energy / $Theoretical_Output;

        return $GCS_Performance_Index;
    }
}
