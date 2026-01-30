<?php

/**
 * Class IndexController
 */
class DashboardController extends LSBaseController
{
    /**
     * responses constructor.
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        App()->loadHelper('surveytranslator');
    }

    /**
     * Set filters for all actions
     * @return string[]
     */
    public function filters()
    {
        return [];
    }

    /**
     * this is part of _renderWrappedTemplate implement in old responses.php
     *
     * @param string $view
     * @return bool
     */
    public function beforeRender($view)
    {
        $this->layout = 'with_sidebar';

        return parent::beforeRender($view);
    }

    /**
     * View the dashboard index/index
     */
    public function actionView(): void
    {
        $aData = $this->getData();
        $this->render('welcome', $aData);
    }

    /**
     * Used to get responses data for browse etc
     *
     * @param int|null $surveyId
     * @param int|null $responseId
     * @param string|null $language
     * @return array
     */
    private function getData(): array
    {
        $aData = [];
        $aData['issuperadmin'] = false;
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }
        // display old dashboard interface
        $aData['oldDashboard'] = App()->getConfig('display_old_dashboard') === '1';
        // Last survey
        $aData['showLastSurvey'] = false;
        $lastsurveyId = SettingsUser::getUserSettingValue('last_survey');
        if ($lastsurveyId) {
            $survey = Survey::model()->findByPk(intval($lastsurveyId));
            if ($survey) {
                $aData['showLastSurvey'] = true;
                $aData['surveyTitle'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $lastsurveyId . ")";
                $aData['surveyUrl'] = $this->createUrl("surveyAdministration/view", ['surveyid' => $lastsurveyId]);
            }
        }
        // Last question
        $aData['showLastQuestion'] = false;
        $lastquestionID = SettingsUser::getUserSettingValue('last_question');
        if ($lastquestionID) {
            $question = Question::model()->findByPk(intval($lastquestionID));
            if ($question) {
                $survey = Survey::model()->findByPk($question->sid);
                if ($survey) {
                    $aData['showLastQuestion'] = true;
                    $baselang = $survey->language;
                    $aData['last_question_name'] = $question['title'];
                    if (!empty($question->questionl10ns[$baselang]['question'])) {
                        $aData['last_question_name'] .= ' : ' . $question->questionl10ns[$baselang]['question'];
                    }
                    $aData['last_question_link'] = $this->createUrl(
                        "questionAdministration/view",
                        [
                            'surveyid' => $question->sid,
                            'gid' => $question->gid,
                            'qid' => $question->qid
                        ]
                    );
                } // else need a data entegrity check question without survey
            }
        }
        $aData['countSurveyList'] = Survey::model()->count();

        //show banner after welcome logo
        $event = new PluginEvent('beforeWelcomePageRender');
        App()->getPluginManager()->dispatchEvent($event);
        $belowLogoHtml = $event->get('html');

        // We get the home page display setting
        $aData['bShowSurveyList'] = (App()->getConfig('show_survey_list') == "show");
        $aData['bShowSurveyListSearch'] = (App()->getConfig('show_survey_list_search') == "show");
        $aData['bShowLogo'] = (App()->getConfig('show_logo') == "show");
        $aData['oSurveySearch'] = new Survey('search');
        $aData['bShowLastSurveyAndQuestion'] = (App()->getConfig('show_last_survey_and_question') == "show");
        $aData['iBoxesByRow'] = (int)App()->getConfig('boxes_by_row');
        $aData['sBoxesOffSet'] = (int)App()->getConfig('boxes_offset');
        $aData['bBoxesInContainer'] = (App()->getConfig('boxes_in_container') == 'yes');
        $aData['belowLogoHtml'] = $belowLogoHtml;

        return $aData;
    }


    /**
 * Survey Analytics Dashboard
 * URL:
 * index.php/dashboard/surveyAnalytics/surveyid/548543
 */
public function actionSurveyAnalytics(int $surveyid): void
{
    $surveyid = (int)$surveyid;

    // 1. Build response table name with prefix
    $table = App()->db->tablePrefix . "survey_" . $surveyid;

    // 2. Check response table exists
    $exists = App()->db->createCommand(
        "SHOW TABLES LIKE :t"
    )->bindValue(':t', $table)->queryScalar();

    if (!$exists) {
        throw new CHttpException(
            404,
            "Survey response table not found: {$table}"
        );
    }

    // 3. Total responses
    $totalResponses = App()->db->createCommand(
        "SELECT COUNT(*) FROM {$table}"
    )->queryScalar();

    // --------------------------------------------------
    // FINALPRIMARYBUCKET01 → qid → response column
    // --------------------------------------------------

    // 4. Get question ID from question code
    $qid = App()->db->createCommand(
        "SELECT qid
         FROM {{questions}}
         WHERE title = :code AND sid = :sid"
    )->bindValues([
        ':code' => 'FINALPRIMARYBUCKET01',
        ':sid'  => $surveyid
    ])->queryScalar();

    if (!$qid) {
        throw new CHttpException(
            500,
            "Question code FINALPRIMARYBUCKET01 not found in survey {$surveyid}"
        );
    }

    // 5. Build response column name (qidX)
    $bucketColumn = $qid . 'X';

    // 6. Verify column exists in response table
    $columns = App()->db->schema->getTable($table)->columns;
    if (!isset($columns[$bucketColumn])) {
        throw new CHttpException(
            500,
            "Response column {$bucketColumn} not found in {$table}"
        );
    }

    // 7. Query final bucket distribution
    $finalBuckets = App()->db->createCommand(
        "SELECT {$bucketColumn} AS label, COUNT(*) AS total
         FROM {$table}
         GROUP BY {$bucketColumn}"
    )->queryAll();

    // 8. Render dashboard view
    $this->render(
        'surveyAnalytics',
        [
            'surveyid'       => $surveyid,
            'totalResponses' => $totalResponses,
            'finalBuckets'   => $finalBuckets,
            'bucketColumn'   => $bucketColumn
        ]
    );
}




}
