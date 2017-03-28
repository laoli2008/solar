<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

  <title>{% block title %}{% if pageTitle is defined %}{{ pageTitle }} - {% endif %}Great Circle Solar{% endblock %}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  {{ stylesheet_link("/css/w3.css") }}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
  <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">

  <style>
    html,body,h1,h2,h3,h4,h5 {font-family: "Segoe UI",Arial,sans-serif}
  </style>

  {{ stylesheet_link("/css/style.css") }}
</head>
<body>
  {# include "partials/sidebar.volt" #}
  {% include "partials/navbar.volt" %}

  <div class="w3-main" style="margin-top:43px;">
    <!-- Header -->
    <header class="w3-container">
      <img class="w3-left" src="/img/gcs-logo-3.png" style="width: 64px; height: 55px; margin-right: 15px;">
      <h3 class="w3-left">{{ pageTitle }}</h3>
    </header>

    {% block main %}{% endblock %}

    <!-- Footer -->
    <footer class="w3-container w3-padding-16" style="text-align:center">
      {% block footer %}{% endblock %}
    </footer>
  </div>

  <!-- Overlay effect when opening sidenav on small screens -->
  <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu"></div>

  <script type='text/javascript' src='/js/jquery-2.1.0.min.js'></script>
  {% block jsfile %}{% endblock %}

  <script type="text/javascript">
    {% block jscode %}{% endblock %}
  </script>

  <script type="text/javascript">
    $(document).ready(function() {
      {% block domready %}{% endblock %}
    });
  </script>

  <script type="text/javascript">
    // Script to open and close sidenav
    function w3_open() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "block";
      document.getElementsByClassName("w3-overlay")[0].style.display = "block";
    }
     
    function w3_close() {
      document.getElementsByClassName("w3-sidenav")[0].style.display = "none";
      document.getElementsByClassName("w3-overlay")[0].style.display = "none";
    }
  </script>
</body>
</html>
