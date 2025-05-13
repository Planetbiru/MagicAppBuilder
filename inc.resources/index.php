<?php

use MagicApp\PicoModule;
use MagicAppTemplate\AppIncludeImpl;

require_once __DIR__ . "/inc.app/auth.php";

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "index", $appLanguage->getHome());
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

require_once $appInclude->mainAppHeader(__DIR__);

?>

<style>
    .row > .col {
        margin-top: 20px;
    }
    .dark-mode .card {
        background-color: #343a40;
        color: #fff;
    }
</style>
<div class="row">
    <div class="col col-xl-3 col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                <span class="fs-1">12.345 <i class="fa-solid fa-arrow-up"></i> 23.65%</span>
                <p class="card-text"><span class="text-primary">342</span> songs in this year</p>
                
            </div>
        </div>
    </div>

    <div class="col col-xl-3 col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                <span class="fs-1">12.345 <i class="fa-solid fa-arrow-up"></i> 23.65%</span>
                <p class="card-text"><span class="text-primary">342</span> songs in this year</p>
                
            </div>
        </div>
    </div>

    <div class="col col-xl-3 col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                <span class="fs-1">12.345 <i class="fa-solid fa-arrow-up"></i> 23.65%</span>
                <p class="card-text"><span class="text-primary">342</span> songs in this year</p>
                
            </div>
        </div>
    </div>

    <div class="col col-xl-3 col-lg-4 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                <span class="fs-1">12.345 <i class="fa-solid fa-arrow-up"></i> 23.65%</span>
                <p class="card-text"><span class="text-primary">342</span> songs in this year</p>
                
            </div>
        </div>
    </div>
</div>

<div class="row">
  <div class="col col-xl-9">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Line Chart</h5>
        <canvas id="line-chart" class="flex-grow-1"></canvas>
      </div>
    </div>
  </div>
  <div class="col col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Pie Chart</h5>
        <canvas id="pie-chart"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col col-xl-9">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Bar Chart</h5>
        <canvas id="bar-chart" class="flex-grow-1"></canvas>
      </div>
    </div>
  </div>
  <div class="col col-xl-3">
    <div class="card h-100">
      <div class="card-body d-flex flex-column">
        <h5 class="card-title">Area Chart</h5>
        <canvas id="area-chart"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo $themeAssetsPath;?>vendors/chartjs/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const ctxLine = document.getElementById('line-chart');
  new Chart(ctxLine, {
    type: 'line',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: 'Select data',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1,
        tension: 0.2,
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  
  const ctxPie = document.getElementById('pie-chart');
  new Chart(ctxPie, {
    type: 'doughnut',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: 'Select data',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  
  const ctxBar = document.getElementById('bar-chart');
  new Chart(ctxBar, {
    type: 'bar',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: 'Select data',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  
  const ctxArea = document.getElementById('area-chart');
  new Chart(ctxArea, {
    type: 'polarArea',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: 'Select data',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1,
        fill: true,
        tension: 0.2,
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
});
</script>
<?php

require_once $appInclude->mainAppFooter(__DIR__);
