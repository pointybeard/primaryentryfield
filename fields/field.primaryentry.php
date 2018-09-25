<?php

/**
 * @package toolkit
 */
require "vendor/autoload.php";
require_once(TOOLKIT . '/fields/field.checkbox.php');

/**
 * Checkbox field simulates a HTML checkbox field, in that it represents a
 * simple yes/no field.
 */
class FieldPrimaryEntry extends FieldCheckbox
{
    public function __construct()
    {
        parent::__construct();
        $this->_name = __('Primary Entry');
    }

    /*-------------------------------------------------------------------------
        Settings:
    -------------------------------------------------------------------------*/

    public function displaySettingsPanel(XMLElement &$wrapper, $errors = null)
    {
        Field::displaySettingsPanel($wrapper, $errors);

        $label = Widget::Label();
        $input = Widget::Input('fields['.$this->get('sortorder').'][auto_toggle]', 'yes', 'checkbox');
        if($this->get('auto_toggle') == 'yes') $input->setAttribute('checked', 'checked');
        $label->setValue($input->generate() . ' ' . __('Automatically toggle existing default entry off'));
        $label->appendChild(new XMLElement('p', __('When a an existing default entry is detected, rather than throw an error, the existing entry is removed as the default instead.'), ['class' => 'help']));

        $wrapper->appendChild($label);

        // Checkbox Default State
        $label = Widget::Label();
        $label->setAttribute('class', 'column');
        $input = Widget::Input('fields['.$this->get('sortorder').'][default_state]', 'on', 'checkbox');

        if ($this->get('default_state') == 'on') {
            $input->setAttribute('checked', 'checked');
        }

        $label->setValue(__('%s Checked by default', [$input->generate()]));
        $wrapper->appendChild($label);

        // Requirements and table display
        $this->appendStatusFooter($wrapper);
    }

    public function appendStatusFooter(XMLElement &$wrapper)
    {
        $fieldset = new XMLElement('fieldset');
        $div = new XMLElement('div', null, ['class' => 'two columns']);

        $this->appendShowColumnCheckbox($div);

        $fieldset->appendChild($div);
        $wrapper->appendChild($fieldset);
    }

    protected function commitCoreData()
    {
        $fields = [
            'label' => General::sanitize($this->get('label')),
            'element_name' => (
                $this->get('element_name')
                    ? $this->get('element_name')
                    : Lang::createHandle($this->get('label'))
            ),
            'parent_section' => $this->get('parent_section'),
            'location' => $this->get('location'),
            'required' => $this->get('required'),
            'type' => $this->_handle,
            'show_column' => $this->get('show_column'),
            'sortorder' => (string)$this->get('sortorder'),
        ];

        if ($id = $this->get('id')) {
            return FieldManager::edit($id, $fields);

        } elseif ($id = FieldManager::add($fields)) {
            $this->set('id', $id);
            if ($this->requiresTable()) {
                return $this->createTable();
            }
            return true;
        }

        return false;
    }

    public function commit()
    {

        if(!$this->commitCoreData()) {
            return false;
        }

        if ($this->get('id') === false) {
            return false;
        }

        $fields = [];

        $fields['default_state'] = ($this->get('default_state') ? $this->get('default_state') : 'off');
        $fields['auto_toggle'] = ($this->get('auto_toggle') ? $this->get('auto_toggle') : 'no');

        return FieldManager::saveSettings($this->get('id'), $fields);
    }

    /*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		public static function doesDefaultEntryAlreadyExist($fieldId, $selfEntryId = NULL){
            $result = SymphonyPDO\Loader::instance()->query(sprintf(
                "SELECT COUNT(*) FROM `tbl_entries_data_%d` WHERE `value` = 'yes' %s",
                $fieldId,
                !is_null($selfEntryId) && is_numeric($selfEntryId)
                    ? "AND `entry_id` != {$selfEntryId}"
                    : NULL
            ));

            return ($result->fetchColumn() > 0);
        }

        public static function toggleAllPrimaryFieldValuesToNo($fieldId) {
            return SymphonyPDO\Loader::instance()->exec(sprintf(
                "UPDATE `tbl_entries_data_%d` SET `value` = 'no'",
                $fieldId
            ));
        }

    /*-------------------------------------------------------------------------
        Publish:
    -------------------------------------------------------------------------*/

    public function checkPostFieldData($data, &$message, $entry_id = null)
    {
        $status = parent::checkPostFieldData($data, $message, $entry_id);

        if($status !== self::__OK__) {
            return $status;
        }

        if($this->get('auto_toggle') == 'no' && self::doesDefaultEntryAlreadyExist($this->get('id'), $entry_id)){
            $message = __('A primary entry already exists. You must uncheck it first or enable Auto Toggle in the field settings.');
            return self::__INVALID_FIELDS__;
        }

        return self::__OK__;
    }

    public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null)
    {
        $status = self::__OK__;

        if($this->get('auto_toggle') == 'yes' && $this->doesDefaultEntryAlreadyExist($entry_id)){
            self::toggleAllPrimaryFieldValuesToNo($this->get('id'));
        }

        $value = (
            in_array(strtolower($data), ['yes', 'on']) ||
            $data === true
                ? 'yes'
                : 'no'
        );

        return [
            'value' => $value
        ];
    }

}
