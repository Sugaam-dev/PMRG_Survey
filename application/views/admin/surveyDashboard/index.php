<?php
Yii::app()->clientScript->registerCssFile(
    Yii::app()->baseUrl . '/application/assets/survey-dashboard/css/dashboard.css'
);
Yii::app()->clientScript->registerScriptFile(
    'https://cdn.jsdelivr.net/npm/chart.js'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->baseUrl . '/application/assets/survey-dashboard/js/dashboard.js'
);
?>

<div class="dashboard-container">

  <h2>Survey Dashboard (Survey ID: <?= $surveyid ?>)</h2>

  <!-- KPI CARDS -->
  <div class="kpi-row">
    <div class="kpi-card">
      <span>Total Responses</span>
      <h1><?= $totalResponses ?></h1>
    </div>

    <?php foreach ($finalBuckets as $b): ?>
      <div class="kpi-card">
        <span><?= CHtml::encode($b['label']) ?></span>
        <h1><?= $b['total'] ?></h1>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- FINAL BUCKET CHART -->
  <div class="card">
    <h3>Final Risk Distribution</h3>
    <canvas id="finalBucketChart"></canvas>
  </div>

  <!-- SKILL LEVELS -->
  <?php foreach ($skillData as $skillName => $rows): ?>
    <div class="card">
      <h3><?= $skillName ?></h3>
      <table class="table">
        <tr>
          <th>Level</th>
          <th>Count</th>
        </tr>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= $r['level'] ?: 'NA' ?></td>
            <td><?= $r['total'] ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endforeach; ?>

</div>

<script>
  window.dashboardData = {
    labels: <?= json_encode(array_column($finalBuckets, 'label')) ?>,
    values: <?= json_encode(array_column($finalBuckets, 'total')) ?>
  };
</script>
