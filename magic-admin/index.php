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
use MagicAdmin\Entity\Data\ModuleCreated;
use MagicAdmin\Entity\Data\WorkspaceCreated;

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

require_once $appInclude->mainAppHeader(__DIR__);

$periods = ChartDataUtil::getLastMonth(12);

$datasets = [
    "applicationMonthly" => [
        "entity" => new ApplicationCreated(null, $databaseBuilder),
        "label"  => $appLanguage->getNewApplication(),
        "title"  => $appLanguage->getNewApplicationPerMonth()
    ],
    "moduleMonthly" => [
        "entity" => new ModuleCreated(null, $databaseBuilder),
        "label"  => $appLanguage->getNewModule(),
        "title"  => $appLanguage->getNewModulePerMonth()
    ],
    "workspaceMonthly" => [
        "entity" => new WorkspaceCreated(null, $databaseBuilder),
        "label"  => $appLanguage->getNewWorkspace(),
        "title"  => $appLanguage->getNewWorkspacePerMonth()
    ],
    "adminMonthly" => [
        "entity" => new AdminCreated(null, $databaseBuilder),
        "label"  => $appLanguage->getNewAdmin(),
        "title"  => $appLanguage->getNewAdminPerMonth()
    ],
];

foreach ($datasets as $id => &$config) {
    $data = ChartDataUtil::getData($config["entity"], $periods);
    $config["labels"] = array_keys($data);
    $config["data"]   = array_values($data);
}
unset($config);

?>
<style>
    canvas{
        width: 100%;
    }
</style>
<div class="row">
    <?php foreach ($datasets as $id => $config): ?>
        <div class="col col-12 col-lg-6">
            <canvas id="<?= $id ?>"></canvas>
        </div>
    <?php endforeach; ?>
</div>

<script src="vendors/chartjs/chart.js"></script>
<script>
function createLineChart(canvasId, labels, data, label, title, color) {
    const ctx = document.getElementById(canvasId);
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: color.replace('1)', '0.2)'),
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: title
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Data PHP ke JS
const chartConfigs = <?php echo json_encode($datasets); ?>;

const colors = [
    'rgba(75, 192, 192, 1)',
    'rgba(255, 99, 132, 1)',
    'rgba(54, 162, 235, 1)',
    'rgba(255, 159, 64, 1)'
];

const charts = {};
let i = 0;
for (const [id, cfg] of Object.entries(chartConfigs)) {
    const color = colors[i % colors.length];
    charts[id] = createLineChart(
        id,
        cfg.labels,
        cfg.data,
        cfg.label,
        cfg.title,
        color
    );
    i++;
}

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

// Resize handler untuk semua chart
const handleResize = debounce(() => {
    Object.values(charts).forEach(chart => chart.resize());
}, 250);

window.addEventListener('resize', handleResize);
</script>

<?php
require_once $appInclude->mainAppFooter(__DIR__);
