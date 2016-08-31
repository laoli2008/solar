<?php

namespace App\Controllers;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->pageTitle = 'My Dashboard';
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'chart'
        ]);
    }

    public function testAction()
    {
        $this->view->pageTitle = 'Test Page';
    }

    public function tableAction()
    {
        $this->view->pageTitle = 'Table';

        // TODO: put these stuff to database
        $projects = [
            1 => [
                'EnvKit'   => [ 'table' => 'solar_data_3', 'devcode' => 'mb-071' ],
                'GenMeter' => [ 'table' => 'solar_data_5', 'devcode' => 'mb-100' ],
                'Inverter' => [
                    'table' => 'solar_data_1',
                    'column' => 'kw',
                    'devcodes' => [ 'mb-001', 'mb-002', 'mb-003' ]
                ]
            ],

            2 => [
                'EnvKit'   => [ 'table' => 'solar_data_3', 'devcode' => 'mb-047' ],
                'GenMeter' => [ 'table' => 'solar_data_5', 'devcode' => 'mb-100' ],
                'Inverter' => [
                    'table' => 'solar_data_4',
                    'column' => 'line_kw',
                    'devcodes' => [ 'mb-080', 'mb-081', 'mb-xxx' ]
                ]
            ]
        ];

        $data = [];

        foreach ($projects as $prj => $info) {
            // EnvKit
            $data[$prj]['EnvKit'] = array (
                'OAT' => '',
                'PANELT' => '',
                'IRR' => '',
            );
            $table = $info['EnvKit']['table'];
            $devcode = $info['EnvKit']['devcode'];

            $sql = "SELECT * FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($sql)->fetchAll(\Phalcon\Db::FETCH_ASSOC);
            if ($result) {
                $data[$prj]['EnvKit'] = $result[0];
            }

            // GenMeter
            $data[$prj]['GenMeter'] = array(
                'kva'   => '',
                'vln_a' => '',
                'vln_b' => '',
                'vln_c' => '',
            );
            $table = $info['GenMeter']['table'];
            $devcode = $info['GenMeter']['devcode'];

            $sql = "SELECT * FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
            $result = $this->db->query($sql)->fetchAll(\Phalcon\Db::FETCH_ASSOC);
            if ($result) {
                $data[$prj]['GenMeter'] = $result[0];
            }

            // Inverter 1-2-3
            $table = $info['Inverter']['table'];
            $column = $info['Inverter']['column'];
            $devcodes = $info['Inverter']['devcodes'];

            foreach ($devcodes as $i => $devcode) {
                $data[$prj]['Inverter'][$i+1] = '';
                $sql = "SELECT $column FROM $table WHERE project_id=$prj AND devcode='$devcode' ORDER BY id DESC LIMIT 1";
                $result = $this->db->fetchColumn($sql);
                if ($result) {
                    $data[$prj]['Inverter'][$i+1] = $result;
                }
            }
        }

        $this->view->data = $data;
    }

    public function chartAction()
    {
        $this->view->pageTitle = 'Chart';
    }
}
