<?php
/**
 * Class for CiviRules ValueComparison Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesConditions_Form_FieldValueComparison extends CRM_CivirulesConditions_Form_ValueComparison {

  protected function getEntities() {
    $return = array();
    foreach($this->eventClass->getProvidedEntities() as $entityDef) {
      if (!empty($entityDef->daoClass) && class_exists($entityDef->daoClass)) {
        $return[$entityDef->entity] = $entityDef->label;
      }
    }
    return $return;
  }

  protected function getFields() {
    $return = array();
    foreach($this->eventClass->getProvidedEntities() as $entityDef) {
      if (!empty($entityDef->daoClass) && class_exists($entityDef->daoClass)) {
        $key = $entityDef->entity . '_';
        $className = $entityDef->daoClass;
        if (!is_callable(array($className, 'fields'))) {
          continue;
        }
        $fields = call_user_func(array($className, 'fields'));
        foreach ($fields as $field) {
          $fieldKey = $key . $field['name'];
          $label = $field['title'];
          if (empty($label)) {
            $label = $field['name'];
          }
          $return[$fieldKey] = $label;
        }
      }
    }
    return $return;
  }

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();


    $this->add('hidden', 'rule_condition_id');

    $this->add('select', 'entity', ts('Entity'), $this->getEntities(), true);
    $this->add('select', 'field', ts('Field'), $this->getFields(), true);
    /*$this->add('select', 'operator', ts('Operator'), $this->conditionClass->getOperators(), true);
    $this->add('text', 'value', ts('Compare value'));
    $this->add('textarea', 'multi_value', ts('Compare values'));

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));*/
  }

  /**
   * Function to add validation condition rules (overrides parent function)
   *
   * @access public
   */
  public function addRules()
  {
    parent::addRules();
    $this->addFormRule(array('CRM_CivirulesConditions_Form_FieldValueComparison', 'validateEntityAndField'));
  }

  public static function validateEntityAndField($fields) {
    $entity = $fields['entity'];
    if (empty($entity)) {
      return array('entity' => ts('Entity could not be empty'));
    }
    if (stripos($fields['field'], $fields['entity'].'_')!==0) {
      return array('entity' => ts('Field is not valid'));
    }
    return true;
  }

  /*public static function validateOperatorAndComparisonValue($fields) {
    $operator = $fields['operator'];
    switch ($operator) {
      case '=':
      case '!=':
      case '>':
      case '>=':
      case '<':
      case '<=':
        if (empty($fields['value'])) {
          return array('value' => ts('Compare value is required'));
        }
        break;
      case 'is one of':
      case 'is not one of':
        if (empty($fields['multi_value'])) {
          return array('multi_value' => 'Compare values is a required field');
        }
        break;
    }
    return true;
  }*/

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $data = array();
    $defaultValues = parent::setDefaultValues();
    $defaultValues['rule_condition_id'] = $this->ruleConditionId;
    if (!empty($data['entity'])) {
      $defaultValues['entity'] = $data['entity'];
    }
    if (!empty($data['entity']) && !empty($data['field'])) {
      $defaultValues['field'] = $data['entity'].'_'.$data['field'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['operator'] = $this->_submitValues['operator'];
    $data['value'] = $this->_submitValues['value'];
    $data['multi_value'] = explode("\r\n", $this->_submitValues['multi_value']);
    $data['entity'] = $this->_submitValues['entity'];
    $data['field'] = substr($this->_submitValues['field'], strlen($data['entity'].'_'));

    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Condition '.$this->condition->label .'Parameters updated to CiviRule '
      .$this->rule->label,
      'Condition parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->rule->id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Edit Condition parameters';
    $this->assign('ruleConditionHeader', 'Edit Condition '.$this->condition->label.' of CiviRule '.$this->rule->label);
    CRM_Utils_System::setTitle($title);
  }
}