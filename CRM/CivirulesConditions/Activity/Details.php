<?php
/**
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesConditions_Activity_Details extends CRM_Civirules_Condition {

  private $conditionParams = array();

  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/condition/activity_details/',
      'rule_condition_id='.$ruleConditionId);
  }

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method to check if the condition is valid, will check if the contact
   * has an activity of the selected type
   *
   * @param object $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $isConditionValid = FALSE;
    $activityData = $triggerData->getEntityData('Activity');
    $activity = civicrm_api3('Activity', 'getsingle', array(
      'return' => array("details"),
      'id' => $activityData['id'],
    ));
    switch ($this->conditionParams['operator']) {
      case 'exact_match':
        if (trim(strtolower($activity['details'])) == trim(strtolower($this->conditionParams['text']))) {
          $isConditionValid = TRUE;
        }
        break;
      case 'contains':
        if (strpos(strtolower($activity['details']), strtolower($this->conditionParams['text'])) !== false){
          $isConditionValid = TRUE;
        }
        break;
    }
    return $isConditionValid;
  }
  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $friendlyText = "";
    if ($this->conditionParams['operator'] == 'contains') {
      $friendlyText = "Activity details contains the text '{$this->conditionParams['text']}'.";
    }
    if ($this->conditionParams['operator'] == 'exact_match') {
      $friendlyText = "Activity details is an exact match to '{$this->conditionParams['text']}'.";
    }
    return $friendlyText;
  }

  /**
   * Returns an array with required entity names
   *
   * @return array
   * @access public
   */
  public function requiredEntities() {
    return array(
      'Activity',
    );
  }
}
