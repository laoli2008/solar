{% extends "layouts/base.volt" %}

{% block main %}
<style type="text/css">
  table, th, td { border: 1px solid #ddd; }
  #snapshot th { text-align: center; }
  #snapshot td { text-align: right; }
  #snapshot tr td:first-child{ text-align: left; }
  #statsbox .numval { font-size: 24px; }
  #statsbox .label  { font-size: 12px; }
  #statsbox .icon {
    position: absolute;
    top: 90px;
    bottom: 5px;
    z-index: 0;
    font-size: 80px;
    color: rgba(0, 0, 0, 0.09);
  }
  .bg-red  { background-color: #f56954 !important; }
  .bg-blue { background-color: #3c8dbc !important; }
  .bg-teal { background-color: #39cccc !important; }
</style>

<div id="statsbox" class="w3-row-padding w3-margin-bottom">
  <div class="w3-third">
    <div class="w3-container bg-blue w3-text-white w3-padding-12">
      <div class="w3-right numval">{{ data['total']['project_size_ac'] }}</div>
      <div class="w3-clear"></div>
      <div class="w3-right label">Total Project Size KWAC</div>
      <div class="w3-left icon"><i class="fa fa-bar-chart"></i></div>
    </div>
  </div>
  <div class="w3-third">
    <div class="w3-container bg-teal w3-text-white w3-padding-12">
      <div class="w3-right numval">{{ data['total']['current_power'] }}</div>
      <div class="w3-clear"></div>
      <div class="w3-right label">Total Current Power</div>
      <div class="w3-left icon"><i class="fa fa-area-chart"></i></div>
    </div>
  </div>
  <div class="w3-third">
    <div class="w3-container bg-red w3-text-white w3-padding-12">
      <div class="w3-right numval">{{ data['total']['PR'] }}</div>
      <div class="w3-clear"></div>
      <div class="w3-right label">Production, Performance %</div>
      <div class="w3-left icon"><i class="fa fa-line-chart"></i></div>
    </div>
  </div>
</div>

{%- macro tablecell(row, key, align) %}
  {%- set classes = align %}
  {%- if row['error'][key] is defined %}
    {%- set classes = classes ~ " w3-deep-orange" %}
  {%- endif %}
  <td class="{{ classes }}">{{ row[key] }}</td>
{% endmacro %}

<div class="w3-container">
<table id="snapshot" class="w3-table w3-white w3-bordered w3-border">
<tr>
  <th style="vertical-align: middle;">Site</th>
  <th style="vertical-align: middle;">GC PR</th>
  <th>Project Size<br>(AC)</th>
  <th>Current Power<br>(kW)</th>
  <th>Irradiance<br>(W/m<sup>2</sup>)</th>
  <th>Inverters<br>Generating</th>
  <th>Devices<br>Communicating</th>
  <th>Data Received<br>(Time Stamp)</th>
</tr>
{% for row in data['rows'] %}
<tr>
  {{ tablecell(row, 'project_name',          '') }}
  {{ tablecell(row, 'GCPR',                  '') }}
  {{ tablecell(row, 'project_size_ac',       'w3-center') }}
  {{ tablecell(row, 'current_power',         '') }}
  {{ tablecell(row, 'irradiance',            '') }}
  {{ tablecell(row, 'inverters_generating',  'w3-center') }}
  {{ tablecell(row, 'devices_communicating', 'w3-center') }}
  {{ tablecell(row, 'last_com',              'w3-center') }}
</tr>
{% endfor %}
</table>
</div>
{% endblock %}

{% block jscode %}
  function AutoRefresh(t) {
    setTimeout("location.reload(true);", t);
  }
  window.onload = AutoRefresh(1000*60*1);
{% endblock %}

{% block domready %}
{% endblock %}
