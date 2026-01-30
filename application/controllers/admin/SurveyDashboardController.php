<?php

/**
 * Custom Survey Dashboard Controller
 * Works in LimeSurvey v6+
 */
class SurveyDashboardController extends AdminController
{
    /**
     * Main dashboard page
     * URL:
     * index.php/surveyDashboard/index/surveyid/548543
     */
    public function actionIndex($surveyid)
    {
        $surveyid = (int) $surveyid;
        $table = "survey_" . $surveyid;

        // Safety: check table exists
        $tableExists = Yii::app()->db->createCommand("
            SHOW TABLES LIKE :table
        ")->bindValue(':table', $table)->queryScalar();

        if (!$tableExists) {
            throw new CHttpException(404, "Survey response table not found.");
        }

        // Total responses
        $totalResponses = Yii::app()->db->createCommand("
            SELECT COUNT(*) FROM {$table}
        ")->queryScalar();

        // Final primary bucket distribution
        $finalBuckets = Yii::app()->db->createCommand("
            SELECT FINALPRIMARYBUCKET01 AS label, COUNT(*) AS total
            FROM {$table}
            GROUP BY FINALPRIMARYBUCKET01
        ")->queryAll();

        // Skill level columns
        $skills = [
            'TECHSKILLLEVELG03Q01' => 'Technical Skill',
            'CLASSROOMG05Q01'      => 'Classroom Engagement',
            'COMMSKILLLEVELG07Q53' => 'Communication',
            'CONFIDENCELEVELG09Q1' => 'Confidence',
            'INTERVIEWLEVELG11Q01' => 'Interview Readiness'
        ];

        $skillData = [];

        foreach ($skills as $column => $label) {
            $skillData[$label] = Yii::app()->db->createCommand("
                SELECT {$column} AS level, COUNT(*) AS total
                FROM {$table}
                GROUP BY {$column}
            ")->queryAll();
        }

        // Render dashboard view
        $this->render('index', [
            'surveyid'       => $surveyid,
            'totalResponses' => $totalResponses,
            'finalBuckets'   => $finalBuckets,
            'skillData'      => $skillData
        ]);
    }
}
