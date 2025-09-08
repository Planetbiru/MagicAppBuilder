<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use AppBuilder\Util\ChartDataUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\PicoModule;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\AdminCreated;
use MagicAdmin\Entity\Data\ApplicationCreated;
use MagicAdmin\Entity\Data\ApplicationGroupCreated;
use MagicAdmin\Entity\Data\ModuleCreated;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "index", $appLanguage->getHome());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);
if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
    require_once $appInclude->appForbiddenPage(__DIR__);
    exit();
}

$dataFilter = null;

require_once $appInclude->mainAppHeader(__DIR__);

$periods = ChartDataUtil::getLastMonth(12);
$applicationMonthly = ChartDataUtil::getData(new ApplicationCreated($databaseBuilder), $periods);
$adminMonthly = ChartDataUtil::getData(new AdminCreated($databaseBuilder), $periods);
$applicationGroupMonthly = ChartDataUtil::getData(new ApplicationGroupCreated($databaseBuilder), $periods);
$moduleMonthly = ChartDataUtil::getData(new ModuleCreated($databaseBuilder), $periods);

?>
<style>
	canvas{
		width: 100%;
	}
</style>
<div class="row">
    <div class="col col-12 col-lg-6">
        <canvas id="applicationMonthly"></canvas>
    </div>
    <div class="col col-12 col-lg-6">
        <canvas id="adminMonthly"></canvas>
    </div>
</div>
<div class="row">
    <div class="col col-12 col-lg-6">
        <canvas id="applicationGroupMonthly"></canvas>
    </div>
    <div class="col col-12 col-lg-6">
        <canvas id="moduleMonthly"></canvas>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	
let applicationMonthlyLabels = <?php echo json_encode(array_keys($applicationMonthly)); ?>;
let applicationMonthlyData = <?php echo json_encode(array_values($applicationMonthly)); ?>;

let adminMonthlyLabels = <?php echo json_encode(array_keys($adminMonthly)); ?>;
let adminMonthlyData = <?php echo json_encode(array_values($adminMonthly)); ?>;

let applicationGroupMonthlyLabels = <?php echo json_encode(array_keys($applicationGroupMonthly)); ?>;
let applicationGroupMonthlyData = <?php echo json_encode(array_values($applicationGroupMonthly)); ?>;

let moduleMonthlyLabels = <?php echo json_encode(array_keys($moduleMonthly)); ?>;
let moduleMonthlyData = <?php echo json_encode(array_values($moduleMonthly)); ?>;

const ctx1 = document.getElementById('applicationMonthly');
const applicationMonthly = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: applicationMonthlyLabels,
        datasets: [{
            label: 'New Application',
            data: applicationMonthlyData,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'New Application per Month'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const ctx2 = document.getElementById('adminMonthly');
const adminMonthly = new Chart(ctx2, {
    type: 'line',
    data: {
        labels: adminMonthlyLabels,
        datasets: [{
            label: 'New Admin',
            data: adminMonthlyData,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'New Admin per Month'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});



const ctx3 = document.getElementById('applicationGroupMonthly');
const applicationGroupMonthly = new Chart(ctx3, {
    type: 'line',
    data: {
        labels: applicationGroupMonthlyLabels,
        datasets: [{
            label: 'New Application Group',
            data: applicationGroupMonthlyData,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'New Application Group per Month'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

const ctx4 = document.getElementById('moduleMonthly');
const moduleMonthly = new Chart(ctx4, {
    type: 'line',
    data: {
        labels: moduleMonthlyLabels,
        datasets: [{
            label: 'New Module',
            data: moduleMonthlyData,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'New Module per Month'
            }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Function to debounce events to prevent excessive calls
function debounce(func, wait) {
    let timeout;
    return function executed(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Create a debounced resize handler
const handleResize = debounce(() => {
    applicationChart.resize();
    adminChart.resize();
}, 250); // Resize after 250ms of no activity

// Add a window resize event listener
window.addEventListener('resize', handleResize);

</script>

<?php
require_once $appInclude->mainAppFooter(__DIR__);
